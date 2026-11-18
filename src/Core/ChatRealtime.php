<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Chat en vivo: lo que comparten el portal (PHP-FPM) y el servicio WebSocket
 * (bin/chat-ws.php).
 *
 * El WebSocket no transporta mensajes, solo avisos («hay algo nuevo en la
 * conversación 12»): el contenido sigue viajando por HTTP, donde pamel decide
 * quién puede verlo. Así un fallo o una suplantación del WebSocket no filtra
 * conversaciones.
 *
 * - El navegador pide un ticket firmado (usuario + caducidad) al portal y
 *   abre el WebSocket con él; el servicio lo verifica con el mismo secreto.
 * - Tras guardar un mensaje, el portal avisa al servicio (por localhost) de
 *   a quién notificar; el servicio contesta quién está conectado, y a los
 *   demás se les avisa por correo.
 *
 * Sin CHAT_WS_SECRET el chat en vivo queda apagado y el navegador sigue
 * sondeando como antes.
 */
class ChatRealtime
{
    public static function secret(): string
    {
        return (string) ($_ENV['CHAT_WS_SECRET'] ?? '');
    }

    public static function enabled(): bool
    {
        return strlen(self::secret()) >= 32;
    }

    /** URL pública del WebSocket para el navegador. */
    public static function wsUrl(): string
    {
        return (string) ($_ENV['CHAT_WS_URL'] ?? '');
    }

    /** Dirección interna donde el servicio recibe los avisos del portal. */
    private static function internalUrl(): string
    {
        return rtrim((string) ($_ENV['CHAT_WS_INTERNAL'] ?? 'http://127.0.0.1:8082'), '/');
    }

    private static function b64(string $s): string
    {
        return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    }

    private static function firmar(string $datos, string $secreto): string
    {
        return self::b64(hash_hmac('sha256', 'chat-ws|' . $datos, $secreto, true));
    }

    /** Ticket de conexión de corta vida para un usuario. */
    public static function ticket(int $userId, int $ttl = 300, ?string $secreto = null): string
    {
        $datos = self::b64(json_encode(['u' => $userId, 'e' => time() + $ttl]));
        return $datos . '.' . self::firmar($datos, $secreto ?? self::secret());
    }

    /** Id de usuario del ticket, o null si no es válido o caducó. */
    public static function verificar(string $ticket, ?string $secreto = null): ?int
    {
        $secreto ??= self::secret();
        if ($secreto === '' || substr_count($ticket, '.') !== 1) {
            return null;
        }
        [$datos, $firma] = explode('.', $ticket, 2);
        if (!hash_equals(self::firmar($datos, $secreto), $firma)) {
            return null;
        }
        $d = json_decode((string) base64_decode(strtr($datos, '-_', '+/')), true);
        if (!is_array($d) || (int) ($d['e'] ?? 0) < time() || (int) ($d['u'] ?? 0) <= 0) {
            return null;
        }
        return (int) $d['u'];
    }

    /**
     * Avisa al servicio de que hay un mensaje nuevo para estos usuarios.
     *
     * @param int[] $destinatarios ids de usuario a los que avisar
     * @return int[]|null ids que estaban conectados; null si el servicio no responde
     */
    public static function avisar(array $destinatarios, int $conversacionId, int $mensajeId, int $remitenteId): ?array
    {
        if (!self::enabled() || !$destinatarios) {
            return null;
        }
        $ch = curl_init(self::internalUrl() . '/notify');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                'users' => array_values(array_unique(array_map('intval', $destinatarios))),
                'cid'   => $conversacionId,
                'id'    => $mensajeId,
                'from'  => $remitenteId,
            ]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'X-Chat-Key: ' . hash('sha256', 'interno|' . self::secret())],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT_MS => 400,
            CURLOPT_TIMEOUT_MS     => 1500,
        ]);
        $r = curl_exec($ch);
        $codigo = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($r === false || $codigo !== 200) {
            return null;
        }
        $d = json_decode((string) $r, true);
        return is_array($d['online'] ?? null) ? array_map('intval', $d['online']) : null;
    }
}
