<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CSRF — generación y verificación de tokens anti-CSRF.
 * Usa hash_hmac con la APP_KEY del .env.
 */
class CSRF
{
    private const SESSION_KEY = '_csrf_token';

    public static function generate(): string
    {
        if (!Session::has(self::SESSION_KEY)) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::SESSION_KEY, $token);
        }
        return Session::get(self::SESSION_KEY);
    }

    public static function verify(string $token): bool
    {
        $stored = Session::get(self::SESSION_KEY, '');
        return hash_equals($stored, $token);
    }

    /**
     * Genera el campo hidden HTML para formularios.
     */
    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES) . '">';
    }
}
