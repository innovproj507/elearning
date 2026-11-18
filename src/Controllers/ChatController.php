<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\CSRF;
use App\Core\ChatRealtime;
use App\Models\Chat;

/**
 * Chat interno: el alumnado escribe a su instructor o a la administración y
 * el personal responde, todo desde el portal.
 *
 * Quién ve o escribe qué lo decide el CMS (ver Plugins\Elearning\Models\Chat);
 * aquí solo se pintan las pantallas y se hace de puente para el sondeo de
 * mensajes nuevos (el navegador no habla con el CMS: no tiene el token).
 */
class ChatController extends Controller
{
    private Chat $chat;

    public function __construct()
    {
        $this->chat = new Chat();
    }

    /** Bandeja: conversaciones y, para el alumnado, el formulario de una nueva. */
    public function index(array $params = []): void
    {
        $user = $this->authUser();
        $datos = $this->chat->conversaciones();
        $esAlumno = ($datos['rol'] ?? $user['role'] ?? '') === 'student';

        $this->view('chat.index', [
            'title'          => __('chat.title'),
            'layout'         => 'dashboard',
            'conversaciones' => $datos['conversaciones'],
            'esAlumno'       => $esAlumno,
            'cursos'         => $esAlumno ? $this->chat->misCursos((int) $user['id']) : [],
            'old'            => ['course_id' => $_GET['curso'] ?? '', 'subject' => '', 'body' => ''],
        ]);
    }

    /** Una conversación con sus mensajes. */
    public function show(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $datos = $this->chat->conversacion($id);
        if (!$datos) {
            $this->flashError(__('chat.not_found'));
            $this->redirect('/mensajes');
        }

        $this->view('chat.show', [
            'title'        => $datos['conversacion']['subject'],
            'layout'       => 'dashboard',
            'conversacion' => $datos['conversacion'],
            'mensajes'     => $datos['mensajes'],
        ]);
    }

    /** Abre una conversación nueva (alumnado). */
    public function store(array $params = []): void
    {
        $this->verifyCsrf();
        $user = $this->authUser();

        $curso   = (int) $this->input('course_id', 0);
        $asunto  = trim((string) $this->input('subject', ''));
        $cuerpo  = trim((string) $this->input('body', ''));

        // El widget no pide asunto: se toma del comienzo del mensaje
        if ($asunto === '' && $this->esAjax()) {
            $asunto = mb_strimwidth(preg_replace('/\s+/', ' ', $cuerpo), 0, 60, '…', 'UTF-8');
        }

        $r = $this->chat->abrir($curso > 0 ? $curso : null, $asunto, $cuerpo);
        if (isset($r['error'])) {
            if ($this->esAjax()) {
                $this->json(['ok' => false, 'error' => $r['error']], 422);
            }
            $this->flashError($r['error']);
            $this->redirect('/mensajes' . ($curso > 0 ? '?curso=' . $curso : ''));
        }

        $this->avisar($r, (int) $r['id'], 0, $asunto, $cuerpo, (int) $user['id']);

        if ($this->esAjax()) {
            $this->json(['ok' => true, 'id' => $r['id']]);
        }
        $this->redirect('/mensajes/' . $r['id']);
    }

    /** Envía un mensaje. Responde JSON si lo pide el navegador (fetch). */
    public function send(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $ajax = $this->esAjax();

        if (!CSRF::verify((string) ($_POST['_token'] ?? ''))) {
            if ($ajax) {
                $this->json(['ok' => false, 'error' => __('chat.csrf')], 419);
            }
            $this->flashError(__('chat.csrf'));
            $this->redirect('/mensajes/' . $id);
        }

        $r = $this->chat->responder($id, trim((string) ($_POST['body'] ?? '')));

        if (!isset($r['error'])) {
            $cuerpo = trim((string) ($_POST['body'] ?? ''));
            $this->avisar($r, $id, (int) $r['id'], '', $cuerpo, (int) ($this->authUser()['id'] ?? 0));
        }

        if ($ajax) {
            isset($r['error'])
                ? $this->json(['ok' => false, 'error' => $r['error']], 422)
                : $this->json(['ok' => true, 'id' => $r['id']]);
        }

        if (isset($r['error'])) {
            $this->flashError($r['error']);
        }
        $this->redirect('/mensajes/' . $id);
    }

    /** Ticket para abrir el WebSocket del chat en vivo; {ok:false} si está apagado. */
    public function ticket(array $params = []): void
    {
        $user = $this->authUser();
        if (!ChatRealtime::enabled() || ChatRealtime::wsUrl() === '') {
            $this->json(['ok' => false]);
        }
        $this->json(['ok' => true, 'url' => ChatRealtime::wsUrl(), 't' => ChatRealtime::ticket((int) $user['id'])]);
    }

    /**
     * Tras guardar un mensaje: avisa en vivo a los destinatarios conectados y, a los que no
     * lo están, les manda un correo (como mucho uno cada 10 minutos por conversación).
     */
    private function avisar(array $resultado, int $conversacionId, int $mensajeId, string $asunto, string $cuerpo, int $remitenteId): void
    {
        $destinatarios = $resultado['destinatarios'] ?? [];
        if (!$destinatarios) {
            return;
        }
        $ids = array_map(static fn($d) => (int) $d['id'], $destinatarios);
        $conectados = ChatRealtime::avisar($ids, $conversacionId, $mensajeId, $remitenteId);

        // Sin servicio en vivo (null) no se sabe quién está: el aviso por correo es la red de seguridad
        $conectados = $conectados ?? [];
        foreach ($destinatarios as $d) {
            if (in_array((int) $d['id'], $conectados, true) || trim((string) $d['email']) === '') {
                continue;
            }
            $this->correoDeAviso($d, $conversacionId, $cuerpo);
        }
    }

    private function correoDeAviso(array $dest, int $conversacionId, string $cuerpo): void
    {
        if (empty($_ENV['MAIL_USERNAME'])) {
            return;     // el correo no está configurado
        }
        $marca = BASE_PATH . '/cache/chat_aviso_' . (int) $dest['id'] . '_' . $conversacionId;
        if (is_file($marca) && filemtime($marca) > time() - 600) {
            return;
        }
        @touch($marca);

        $e = static fn(string $s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        $resumen = mb_strimwidth(preg_replace('/\s+/', ' ', $cuerpo), 0, 200, '…', 'UTF-8');
        $enlace = rtrim((string) ($_ENV['APP_URL'] ?? ''), '/') . '/mensajes/' . $conversacionId;
        \App\Core\Mailer::send(
            (string) $dest['email'], (string) $dest['name'],
            'Tienes un mensaje nuevo en el chat de PAMEL',
            '<p>Hola ' . $e((string) $dest['name']) . ',</p>'
            . '<p>Tienes un mensaje nuevo en el chat de PAMEL:</p>'
            . '<blockquote style="border-left:3px solid #2563eb;margin:0;padding:6px 12px;color:#334155">' . $e($resumen) . '</blockquote>'
            . '<p><a href="' . $e($enlace) . '">Abrir la conversación</a></p>'
        );
    }

    /** Sondeo: mensajes posteriores a ?after=N, en JSON. */
    public function poll(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $datos = $this->chat->conversacion($id, (int) ($_GET['after'] ?? 0));
        if (!$datos) {
            $this->json(['ok' => false], 404);
        }
        $this->json(['ok' => true, 'conversacion' => $datos['conversacion'], 'mensajes' => $datos['mensajes']]);
    }

    /** Datos del widget flotante: conversaciones y, para el alumnado, sus cursos. */
    public function widget(array $params = []): void
    {
        $user = $this->authUser();
        $datos = $this->chat->conversaciones();
        $esAlumno = ($datos['rol'] ?? $user['role'] ?? '') === 'student';

        $this->json([
            'ok'             => true,
            'esAlumno'       => $esAlumno,
            'conversaciones' => $datos['conversaciones'],
            'cursos'         => $esAlumno ? $this->chat->misCursos((int) $user['id']) : [],
        ]);
    }

    /** Contador de mensajes sin leer para el menú. */
    public function unread(array $params = []): void
    {
        $this->json(['count' => $this->chat->sinLeer()]);
    }

    private function esAjax(): bool
    {
        return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }
}
