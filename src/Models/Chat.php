<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Chat interno: cliente del API del CMS (/api/v1/chat/...).
 *
 * Las reglas de acceso las aplica el CMS. Todas las lecturas van sin caché:
 * el ApiClient cachea 30 s los GET, y en un chat un mensaje que tarda 30 s
 * en aparecer es un chat roto.
 */
class Chat extends Model
{
    protected string $table = 'chat';

    /** Mensajes sin leer del usuario (para el contador del menú). */
    public function sinLeer(): int
    {
        $r = $this->api->get('/chat/unread', [], true);
        return ($r && ($r['status'] ?? '') === 'success') ? (int) ($r['data']['count'] ?? 0) : 0;
    }

    /** @return array{rol:?string,conversaciones:array} */
    public function conversaciones(): array
    {
        $r = $this->api->get('/chat/conversations', [], true);
        if ($r && ($r['status'] ?? '') === 'success') {
            return ['rol' => $r['data']['rol'] ?? null, 'conversaciones' => $r['data']['conversaciones'] ?? []];
        }
        return ['rol' => null, 'conversaciones' => []];
    }

    /**
     * Una conversación y sus mensajes (desde el id $despuesDe).
     *
     * @return array{conversacion:array,mensajes:array}|false false si no existe o no puede verla.
     */
    public function conversacion(int $id, int $despuesDe = 0): array|false
    {
        $r = $this->api->get('/chat/conversations/' . $id, $despuesDe > 0 ? ['after' => $despuesDe] : [], true);
        return ($r && ($r['status'] ?? '') === 'success') ? $r['data'] : false;
    }

    /** @return array{id:int}|array{error:string} */
    public function abrir(?int $courseId, string $asunto, string $cuerpo): array
    {
        $r = $this->api->post('/chat/conversations', [
            'course_id' => $courseId,
            'subject'   => $asunto,
            'body'      => $cuerpo,
        ]);
        if ($r && ($r['status'] ?? '') === 'success') {
            return ['id' => (int) $r['data']['id']];
        }
        return ['error' => $this->api->lastApiMessage() ?? 'No se pudo enviar el mensaje. Inténtalo de nuevo.'];
    }

    /** @return array{id:int}|array{error:string} */
    public function responder(int $conversacionId, string $cuerpo): array
    {
        $r = $this->api->post('/chat/conversations/' . $conversacionId . '/messages', ['body' => $cuerpo]);
        if ($r && ($r['status'] ?? '') === 'success') {
            return ['id' => (int) $r['data']['id']];
        }
        return ['error' => $this->api->lastApiMessage() ?? 'No se pudo enviar el mensaje. Inténtalo de nuevo.'];
    }

    /** Cursos del alumno sobre los que puede escribir (para el desplegable). */
    public function misCursos(int $userId): array
    {
        $r = $this->api->get('/student/' . $userId . '/courses', [], true);
        if ($r && ($r['status'] ?? '') === 'success') {
            return array_map(
                static fn($c) => ['id' => (int) ($c['course_id'] ?? $c['id'] ?? 0), 'title' => (string) ($c['title'] ?? $c['course_title'] ?? '')],
                $r['data'] ?? []
            );
        }
        return [];
    }
}
