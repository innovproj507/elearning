<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session — wrapper seguro sobre $_SESSION.
 */
class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $name     = $_ENV['SESSION_NAME']     ?? 'elearning_session';
        $lifetime = (int) ($_ENV['SESSION_LIFETIME'] ?? 7200);

        session_name($name);

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
        self::$started = true;

        // Regenerar ID para prevenir session fixation en cada nueva sesión
        if (!isset($_SESSION['_initiated'])) {
            session_regenerate_id(true);
            $_SESSION['_initiated'] = true;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        self::$started = false;
    }

    // ─── Flash messages ───────────────────────────────────────────

    public static function flash(string $key): ?string
    {
        $value = $_SESSION["flash_{$key}"] ?? null;
        unset($_SESSION["flash_{$key}"]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION["flash_{$key}"]);
    }
}
