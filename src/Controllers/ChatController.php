<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\CSRF;
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
        if ($this->esAjax()) {
            isset($r['error'])
                ? $this->json(['ok' => false, 'error' => $r['error']], 422)
                : $this->json(['ok' => true, 'id' => $r['id']]);
        }
        if (isset($r['error'])) {
            $this->flashError($r['error']);
            $this->redirect('/mensajes' . ($curso > 0 ? '?curso=' . $curso : ''));
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
