<?php

declare(strict_types=1);

namespace App\Api;

use App\Core\Controller;
use App\Core\JWT;

/**
 * Base para todos los controladores de la API.
 * Responde siempre en JSON. Nunca renderiza vistas.
 */
abstract class ApiController extends Controller
{
    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): void
    {
        $payload = ['success' => false, 'message' => $message];
        if ($errors !== null) $payload['errors'] = $errors;
        $this->json($payload, $status);
    }

    protected function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw ?: '', true);
        return is_array($data) ? $data : [];
    }

    protected function jwtPayload(): array
    {
        return $_REQUEST['_jwt_payload'] ?? [];
    }

    protected function jwtUserId(): int
    {
        return (int) ($this->jwtPayload()['sub'] ?? 0);
    }

    protected function jwtUserRole(): string
    {
        return $this->jwtPayload()['role'] ?? '';
    }
}
