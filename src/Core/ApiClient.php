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

    public function get(string $endpoint, array $params = []): array|false
    {
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }

        $cacheKey = 'apicache_' . md5($this->baseUrl . $endpoint . ($this->token ?? ''));

        $cached = $this->cacheGet($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $result = $this->request('GET', $endpoint);

        if ($result !== false) {
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
        if (self::$ch === null) {
            self::$ch = curl_init();
            curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(self::$ch, CURLOPT_TIMEOUT, 5);
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

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);

        if ($error) {
            error_log("LMS API cURL error ({$url}): {$error}");
            return false;
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        }

        error_log("LMS API HTTP {$httpCode} ({$url}) body: " . substr($response, 0, 500));
        return false;
    }
}
