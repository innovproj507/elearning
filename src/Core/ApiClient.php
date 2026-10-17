<?php

declare(strict_types=1);

namespace App\Core;

/**
 * ApiClient — Gestiona la comunicación HTTP con el CMS Backend.
 *
 * Optimizaciones:
 *  - cURL keep-alive: reutiliza la conexión TCP dentro del mismo proceso PHP.
 *  - Caché de GET: APCu (si está disponible) o archivos, TTL de 30 s.
 *    Las escrituras (POST/PUT) invalidan la caché automáticamente.
 */
class ApiClient
{
    private static ?ApiClient $instance = null;
    private string $baseUrl;
    private ?string $token = null;

    private static ?\CurlHandle $ch = null;

    private string $cacheDir;
    private int $cacheTtl = 30;

    /**
     * Motivo del último fallo de transporte (timeout, conexión rechazada,
     * error 5xx). Queda a null si el CMS respondió correctamente, incluso
     * cuando la respuesta fue un 404.
     *
     * Permite a quien llama distinguir "no existe" de "no contestó", que
     * es lo que evita mostrar al estudiante un 404 cuando en realidad el
     * CMS no respondió a tiempo.
     */
    private ?string $lastTransportError = null;

    private function __construct()
    {
        $this->baseUrl  = rtrim($_ENV['API_BASE_URL'] ?? 'http://cms.test/api', '/');
        $this->cacheDir = BASE_PATH . '/cache/api/';

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->token = $_SESSION['api_token'] ?? null;
    }

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
        $_SESSION['api_token'] = $token;
    }

    /**
     * Motivo del último fallo de transporte, o null si el CMS respondió.
     */
    public function lastTransportError(): ?string
    {
        return $this->lastTransportError;
    }

    public function clearCache(): void
    {
        if (function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
        }
        if (is_dir($this->cacheDir)) {
            foreach (glob($this->cacheDir . '*.json') ?: [] as $f) {
                @unlink($f);
            }
        }
    }

    // ─── Métodos públicos ─────────────────────────────────────────

    /**
     * @param bool $sinCache Para datos donde una respuesta de hace 30
     *   segundos es peor que ninguna: por ejemplo cuántos intentos de un
     *   examen le quedan al estudiante, donde servir el valor viejo le
     *   enseñaría el formulario de un intento que ya no tiene.
     */
    public function get(string $endpoint, array $params = [], bool $sinCache = false): array|false
    {
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }

        $cacheKey = 'apicache_' . md5($this->baseUrl . $endpoint . ($this->token ?? ''));

        if (!$sinCache) {
            $cached = $this->cacheGet($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $result = $this->request('GET', $endpoint);

        if ($result !== false && !$sinCache) {
            $this->cacheSet($cacheKey, $result);
        }

        return $result;
    }

    public function post(string $endpoint, array $data = []): array|false
    {
        $result = $this->request('POST', $endpoint, $data);
        // Invalidar caché tras escritura para reflejar cambios inmediatamente
        if ($result !== false) {
            $this->clearCache();
        }
        return $result;
    }

    // ─── Caché ────────────────────────────────────────────────────

    private function cacheGet(string $key): array|null
    {
        if (function_exists('apcu_fetch')) {
            $hit = apcu_fetch($key, $success);
            return $success ? $hit : null;
        }

        $file = $this->cacheDir . $key . '.json';
        if (file_exists($file) && (time() - filemtime($file)) < $this->cacheTtl) {
            $data = json_decode(file_get_contents($file), true);
            return is_array($data) ? $data : null;
        }
        return null;
    }

    private function cacheSet(string $key, array $data): void
    {
        if (function_exists('apcu_store')) {
            apcu_store($key, $data, $this->cacheTtl);
            return;
        }

        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
        @file_put_contents($this->cacheDir . $key . '.json', json_encode($data), LOCK_EX);
    }

    // ─── HTTP ─────────────────────────────────────────────────────

    private function request(string $method, string $endpoint, array $data = []): array|false
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        // Reutilizar el handle cURL dentro del proceso PHP (keep-alive)
        //
        // El tiempo de espera es corto a propósito. El CMS contesta en unos
        // 40 ms; si tarda segundos no es que esté pensando, es que la
        // petición quedó encolada detrás de esta misma. Esperar 5 s no la
        // rescata, sólo retrasa el reintento, que es lo que sí funciona.
        if (self::$ch === null) {
            self::$ch = curl_init();
            curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(self::$ch, CURLOPT_TIMEOUT, 2);
            curl_setopt(self::$ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt(self::$ch, CURLOPT_TCP_KEEPALIVE, 1);
            curl_setopt(self::$ch, CURLOPT_TCP_KEEPIDLE, 10);
            curl_setopt(self::$ch, CURLOPT_FORBID_REUSE, false);
            curl_setopt(self::$ch, CURLOPT_FRESH_CONNECT, false);
        }

        $ch = self::$ch;

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Connection: keep-alive',
        ];

        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
            $headers[] = 'X-Cms-Token: ' . $this->token;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (!empty($data) && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        }

        // Un fallo de transporte se reintenta una vez.
        //
        // El CMS y el LMS comparten el mismo grupo de procesos PHP. Una
        // página del LMS ocupa uno mientras llama al CMS, y si esa llamada
        // cae en el proceso que ella misma está ocupando, ninguno de los dos
        // avanza. El servidor aparta unos instantes al que se atascó, así
        // que el reintento entra por otro y sale adelante.
        $intentos = 0;
        do {
            $intentos++;
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error    = curl_error($ch);

            if (!$error) {
                break;
            }
            if ($intentos === 1) {
                error_log("LMS API reintento tras fallo ({$url}): {$error}");
            }
        } while ($intentos < 2);

        $this->lastTransportError = null;

        if ($error) {
            $this->lastTransportError = $error;
            error_log("LMS API cURL error ({$url}): {$error}");
            return false;
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        }

        // Un 5xx es un fallo del CMS, no una respuesta de "no existe".
        if ($httpCode >= 500) {
            $this->lastTransportError = "HTTP {$httpCode}";
        }

        error_log("LMS API HTTP {$httpCode} ({$url}) body: " . substr($response, 0, 500));
        return false;
    }
}
