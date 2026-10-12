<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Model base — Versión Robusta para evitar caídas si el API no responde.
 */
abstract class Model
{
    protected ApiClient $api;
    protected string $table = '';
    protected string $pk    = 'id';

    public function __construct()
    {
        $this->api = ApiClient::getInstance();
    }

    public function find(int|string $id): array|false
    {
        $response = $this->api->get("/{$this->table}/{$id}");
        return ($response && isset($response['status']) && $response['status'] === 'success') ? $response['data'] : false;
    }

    public function all(array $params = []): array
    {
        $response = $this->api->get("/{$this->table}", $params);
        
        // Si el API falla, devolvemos una estructura vacía pero segura para el controlador
        if (!$response || !isset($response['status']) || $response['status'] !== 'success') {
            return ['data' => [], 'total' => 0, 'current_page' => 1, 'last_page' => 1];
        }

        return $response['data'];
    }

    public function create(array $data): array|false
    {
        $response = $this->api->post("/{$this->table}", $data);
        return ($response && isset($response['status']) && $response['status'] === 'success') ? $response['data'] : false;
    }

    public function count(array $params = []): int
    {
        $response = $this->api->get("/{$this->table}/count", $params);
        return ($response && isset($response['status']) && $response['status'] === 'success') ? (int)($response['count'] ?? 0) : 0;
    }

    public function findOrFail(int|string $id): array
    {
        $result = $this->find($id);
        if ($result) {
            return $result;
        }

        // Si el CMS no respondió (timeout, conexión, 5xx) esto NO es un
        // "no existe": mostrar 404 manda al estudiante a buscar una lección
        // que sí está ahí, y oculta la avería real.
        $fallo = $this->api->lastTransportError();
        if ($fallo !== null) {
            error_log("LMS: {$this->table}/{$id} no se pudo consultar: {$fallo}");
            http_response_code(503);
            header('Retry-After: 10');
            include BASE_PATH . '/views/errors/500.php';
            exit;
        }

        http_response_code(404);
        include BASE_PATH . '/views/errors/404.php';
        exit;
    }

    protected function generateSlug(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));
        return $text;
    }
}
