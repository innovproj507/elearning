<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\CSRF;
use App\Models\User;
use App\Services\CmsService;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ─── Login ────────────────────────────────────────────────────

    public function showLogin(array $params = []): void
    {
        if ($this->isAuth()) {
            $user = $this->authUser();
            $this->redirect(($user['role'] ?? '') === 'admin' ? '/cursos' : '/dashboard');
        }
        $this->view('auth.login', ['title' => 'Iniciar Sesión', 'layout' => 'auth']);
    }

    public function login(array $params = []): void
    {
        $this->verifyCsrf();

        $email    = $this->sanitize($this->input('email', ''));
        $password = $this->input('password', '');

        if (empty($email) || empty($password)) {
            $this->flashError('Email y contraseña son requeridos.');
            $this->redirect('/login');
        }

        $cms      = new CmsService();
        $response = $cms->login($email, $password);

        if (!$response) {
            $this->flashError('Credenciales incorrectas o acceso denegado.');
            $this->redirect('/login');
        }
        
        session_regenerate_id(true);
        Session::set('user', $response['user']);
        Session::set('user_id', $response['user']['id']);
        Session::set('role', $response['user']['role']);

        $defaultUrl = ($response['user']['role'] ?? '') === 'admin' ? '/cursos' : '/dashboard';
        $intended   = Session::get('intended_url', $defaultUrl);
        Session::remove('intended_url');

        $this->flashSuccess('¡Bienvenido, ' . htmlspecialchars($response['user']['name']) . '!');
        $this->redirect($intended);
    }

    // ─── Register ─────────────────────────────────────────────────

    public function showRegister(array $params = []): void
    {
        $this->redirect('/login');
    }

    public function register(array $params = []): void
    {
        $this->verifyCsrf();

        $name     = $this->sanitize($this->input('name', ''));
        $email    = $this->sanitize($this->input('email', ''));
        $password = $this->input('password', '');
        $confirm  = $this->input('password_confirm', '');

        // Validaciones
        $errors = $this->validateRegistration($name, $email, $password, $confirm);

        if (!empty($errors)) {
            $this->flashError(implode(' | ', $errors));
            $this->redirect('/register');
        }

        try {
            $id   = $this->userModel->register($name, $email, $password);
            $user = $this->userModel->sanitize($this->userModel->find((int) $id));

            session_regenerate_id(true);
            Session::set('user', $user);
            Session::set('user_id', $user['id']);

            $this->flashSuccess('¡Cuenta creada con éxito!');
            $this->redirect('/dashboard');
        } catch (\InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
            $this->redirect('/register');
        }
    }

    // ─── Logout ───────────────────────────────────────────────────

    public function logout(array $params = []): void
    {
        $this->verifyCsrf();
        Session::destroy();
        $this->redirect('/login');
    }

    // ─── Validación ───────────────────────────────────────────────

    private function isRateLimited(string $ip): bool
    {
        $dir  = BASE_PATH . '/storage/rate_limit/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $file    = $dir . md5($ip) . '.json';
        $window  = 900; // 15 minutos
        $maxTries = 10;
        $now     = time();

        $data = file_exists($file) ? json_decode(file_get_contents($file), true) : ['attempts' => 0, 'reset_at' => $now + $window];

        if ($now > $data['reset_at']) {
            $data = ['attempts' => 0, 'reset_at' => $now + $window];
        }

        $data['attempts']++;
        file_put_contents($file, json_encode($data), LOCK_EX);

        return $data['attempts'] > $maxTries;
    }

    public function clearRateLimit(string $ip): void
    {
        $file = BASE_PATH . '/storage/rate_limit/' . md5($ip) . '.json';
        if (file_exists($file)) unlink($file);
    }

    private function validateRegistration(string $name, string $email, string $password, string $confirm): array
    {
        $errors = [];

        if (strlen($name) < 2)      $errors[] = 'El nombre debe tener al menos 2 caracteres.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email no válido.';
        if (strlen($password) < 8)  $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        if ($password !== $confirm)  $errors[] = 'Las contraseñas no coinciden.';

        return $errors;
    }
}
