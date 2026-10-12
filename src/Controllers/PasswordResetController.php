<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Database;
use App\Models\User;

class PasswordResetController extends Controller
{
    // ─── Paso 1: Formulario de solicitud ─────────────────────────

    public function showRequest(array $params = []): void
    {
        $this->view('auth.forgot', ['title' => 'Recuperar Contraseña', 'layout' => 'auth']);
    }

    public function sendCode(array $params = []): void
    {
        $this->verifyCsrf();

        $email = trim($this->input('email', ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashError('Ingresa un correo electrónico válido.');
            $this->redirect('/recuperar-contrasena');
        }

        $userModel = new User();
        $user      = $userModel->findByEmail($email);

        // Respuesta genérica: no revelar si el email existe o no
        $this->flashSuccess('Si el correo está registrado, recibirás un código en los próximos minutos.');

        if ($user && $user['is_active']) {
            $this->generateAndSendCode($user);
        }

        $this->redirect('/recuperar-contrasena/verificar?email=' . urlencode($email));
    }

    // ─── Paso 2: Verificar código y nueva contraseña ─────────────

    public function showVerify(array $params = []): void
    {
        $email = $this->input('email', '');
        $this->view('auth.reset', [
            'title'  => 'Nueva Contraseña',
            'layout' => 'auth',
            'email'  => htmlspecialchars($email, ENT_QUOTES),
        ]);
    }

    public function resetPassword(array $params = []): void
    {
        $this->verifyCsrf();

        $email    = trim($this->input('email', ''));
        $code     = trim($this->input('code', ''));
        $password = $this->input('password', '');
        $confirm  = $this->input('password_confirm', '');

        // Validaciones básicas
        if (empty($email) || empty($code) || empty($password)) {
            $this->flashError('Todos los campos son requeridos.');
            $this->redirect('/recuperar-contrasena/verificar?email=' . urlencode($email));
        }

        if (strlen($password) < 8) {
            $this->flashError('La contraseña debe tener al menos 8 caracteres.');
            $this->redirect('/recuperar-contrasena/verificar?email=' . urlencode($email));
        }

        if ($password !== $confirm) {
            $this->flashError('Las contraseñas no coinciden.');
            $this->redirect('/recuperar-contrasena/verificar?email=' . urlencode($email));
        }

        $db = Database::getInstance();

        // Buscar token válido (no usado, no expirado)
        $reset = $db->queryOne(
            "SELECT * FROM password_resets
             WHERE email = ? AND used = 0 AND expires_at > NOW()
             ORDER BY created_at DESC LIMIT 1",
            [$email]
        );

        if (!$reset || !hash_equals($reset['token_hash'], hash('sha256', $code))) {
            $this->flashError('El código es incorrecto o ha expirado. Solicita uno nuevo.');
            $this->redirect('/recuperar-contrasena/verificar?email=' . urlencode($email));
        }

        // Actualizar contraseña y marcar token como usado
        $userModel = new User();
        $user      = $userModel->findByEmail($email);

        if (!$user) {
            $this->flashError('Usuario no encontrado.');
            $this->redirect('/recuperar-contrasena');
        }

        $db->execute(
            "UPDATE password_resets SET used = 1 WHERE id = ?",
            [$reset['id']]
        );

        $db->execute(
            "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?",
            [password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $user['id']]
        );

        $this->flashSuccess('¡Contraseña actualizada correctamente! Ya puedes iniciar sesión.');
        $this->redirect('/login');
    }

    // ─── Helper privado ───────────────────────────────────────────

    private function generateAndSendCode(array $user): void
    {
        $db = Database::getInstance();

        // Invalidar códigos anteriores del mismo email
        $db->execute(
            "UPDATE password_resets SET used = 1 WHERE email = ? AND used = 0",
            [$user['email']]
        );

        // Generar código de 6 dígitos criptográficamente seguro
        $code      = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $tokenHash = hash('sha256', $code);
        $expiresAt = date('Y-m-d H:i:s', time() + 1800); // 30 minutos

        $db->execute(
            "INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, ?)",
            [$user['email'], $tokenHash, $expiresAt]
        );

        $html = Mailer::resetPasswordEmail($user['email'], $user['name'], $code);
        Mailer::send($user['email'], $user['name'], 'Código de recuperación — PAMEL LMS', $html);
    }
}
