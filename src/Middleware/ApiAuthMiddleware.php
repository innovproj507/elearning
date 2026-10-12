<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\JWT;

class ApiAuthMiddleware
{
    public function handle(array $params = []): void
    {
        $payload = JWT::fromRequestSafe();

        if ($payload === null) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Token de autenticación requerido o inválido.',
            ]);
            exit;
        }

        // Inyectar payload en $_REQUEST para que los controladores lo lean
        $_REQUEST['_jwt_payload'] = $payload;
    }
}
