<?php

declare(strict_types=1);

namespace App\Api;

use App\Core\JWT;
use App\Models\User;

class AuthApiController extends ApiController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * POST /api/auth/login
     * Body: { "email": "...", "password": "..." }
     */
    public function login(array $params = []): void
    {
        $body     = $this->getJsonBody();
        $email    = filter_var($body['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $body['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->error('Email y contraseña son requeridos.', 422);
        }

        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            $this->error('Credenciales inválidas.', 401);
        }

        $token = JWT::encode($user);

        $this->success([
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => (int) ($_ENV['JWT_EXPIRY'] ?? 3600),
            'user'       => $user,
        ], 'Autenticación exitosa.');
    }

    /**
     * POST /api/auth/register
     * Body: { "name": "...", "email": "...", "password": "..." }
     */
    public function register(array $params = []): void
    {
        $body     = $this->getJsonBody();
        $name     = htmlspecialchars(strip_tags($body['name'] ?? ''), ENT_QUOTES);
        $email    = filter_var($body['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $body['password'] ?? '';

        $errors = [];
        if (strlen($name) < 2)     $errors[] = 'El nombre debe tener al menos 2 caracteres.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email no válido.';
        if (strlen($password) < 8) $errors[] = 'La contraseña debe tener al menos 8 caracteres.';

        if (!empty($errors)) {
            $this->error('Datos de registro inválidos.', 422, $errors);
        }

        try {
            $id   = $this->userModel->register($name, $email, $password);
            $user = $this->userModel->sanitize($this->userModel->find((int) $id));
            $token = JWT::encode($user);

            $this->success([
                'token'      => $token,
                'token_type' => 'Bearer',
                'expires_in' => (int) ($_ENV['JWT_EXPIRY'] ?? 3600),
                'user'       => $user,
            ], 'Usuario registrado.', 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 409);
        }
    }

    /**
     * GET /api/auth/me   [Requiere JWT]
     * Retorna el perfil del usuario autenticado.
     */
    public function me(array $params = []): void
    {
        $userId = $this->jwtUserId();
        $user   = $this->userModel->sanitize(
            $this->userModel->find($userId)
        );

        if (!$user) {
            $this->error('Usuario no encontrado.', 404);
        }

        $this->success($user);
    }
}
