<?php

declare(strict_types=1);

namespace App\Core;

/**
 * JWT — Implementación nativa HS256 sin dependencias externas.
 *
 * Implementa la especificación RFC 7519.
 * Solo usa funciones nativas de PHP (hash_hmac, base64_encode).
 */
class JWT
{
    private const ALGO        = 'sha256';
    private const ALGO_HEADER = 'HS256';

    private static function secret(): string
    {
        $secret = $_ENV['JWT_SECRET'] ?? '';
        if (strlen($secret) < 16) {
            throw new \RuntimeException('JWT_SECRET debe tener al menos 16 caracteres.');
        }
        return $secret;
    }

    // ─── Codificación ─────────────────────────────────────────────

    /**
     * Genera un token JWT HS256 firmado.
     *
     * @param array $user   ['id', 'email', 'role', 'name']
     * @param int   $ttl    Segundos de validez (0 = usar env JWT_EXPIRY)
     */
    public static function encode(array $user, int $ttl = 0): string
    {
        $ttl = $ttl ?: (int) ($_ENV['JWT_EXPIRY'] ?? 3600);
        $now = time();

        $header  = self::base64url(json_encode([
            'typ' => 'JWT',
            'alg' => self::ALGO_HEADER,
        ]));

        $payload = self::base64url(json_encode([
            'iss'   => $_ENV['APP_URL'] ?? 'http://localhost',
            'iat'   => $now,
            'exp'   => $now + $ttl,
            'sub'   => $user['id'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'name'  => $user['name'],
        ]));

        $signature = self::base64url(
            hash_hmac(self::ALGO, "{$header}.{$payload}", self::secret(), true)
        );

        return "{$header}.{$payload}.{$signature}";
    }

    // ─── Decodificación ───────────────────────────────────────────

    /**
     * Decodifica y valida un token JWT.
     * Lanza excepción si es inválido o expirado.
     *
     * @return array Payload decodificado
     */
    public static function decode(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Token JWT malformado.');
        }

        [$header, $payload, $signature] = $parts;

        // Verificar firma (timing-safe)
        $expected = self::base64url(
            hash_hmac(self::ALGO, "{$header}.{$payload}", self::secret(), true)
        );

        if (!hash_equals($expected, $signature)) {
            throw new \RuntimeException('Firma del token inválida.');
        }

        // Decodificar payload
        $data = json_decode(self::base64urlDecode($payload), true);

        if (!is_array($data)) {
            throw new \RuntimeException('Payload JWT inválido.');
        }

        // Verificar expiración
        if (isset($data['exp']) && $data['exp'] < time()) {
            throw new \RuntimeException('Token expirado.');
        }

        return $data;
    }

    // ─── Helpers de request ───────────────────────────────────────

    /**
     * Extrae el token del header Authorization: Bearer <token>
     */
    public static function fromRequest(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (preg_match('/Bearer\s+(.+)$/i', $header, $matches)) {
            return trim($matches[1]);
        }

        return $_GET['token'] ?? null;
    }

    /**
     * Decodifica el token del request sin lanzar excepción.
     * Retorna null si falla.
     */
    public static function fromRequestSafe(): ?array
    {
        try {
            $token = self::fromRequest();
            if ($token === null) return null;
            return self::decode($token);
        } catch (\Throwable) {
            return null;
        }
    }

    // ─── Utilidades Base64URL ─────────────────────────────────────

    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
