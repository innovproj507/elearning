<?php

declare(strict_types=1);

/**
 * Servicio WebSocket del chat en vivo (Workerman).
 *
 *   php bin/chat-ws.php start        (en Linux: start -d para segundo plano)
 *   php bin/chat-ws.php              (en Windows, desarrollo)
 *
 * Escucha en dos puertos, ambos solo en 127.0.0.1:
 *   - 8081  WebSocket para los navegadores (nginx lo expone en /ws).
 *   - 8082  HTTP interno: el portal avisa aquí de cada mensaje nuevo.
 *
 * Solo reparte avisos («conversación 12, mensaje 340»), nunca contenido: el
 * navegador pide el mensaje por HTTP al portal, que se lo pide a pamel con
 * las reglas de acceso. Ver App\Core\ChatRealtime.
 *
 * Todo vive en un único proceso: los usuarios conectados están en memoria.
 */

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Timer;
use Workerman\Worker;

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';

// Misma configuración que el portal (config/app.php), sin depender del host HTTP
foreach ((require BASE_PATH . '/config/app.php') as $k => $v) {
    $_ENV[$k] ??= $v;
}

$secreto = (string) ($_ENV['CHAT_WS_SECRET'] ?? '');
if (strlen($secreto) < 32) {
    fwrite(STDERR, "Falta CHAT_WS_SECRET (mínimo 32 caracteres) en config/app.php: el chat en vivo no arranca.\n");
    exit(1);
}
$claveInterna = hash('sha256', 'interno|' . $secreto);
$origenes = array_filter(array_map('trim', explode(',', (string) ($_ENV['CHAT_WS_ORIGINS'] ?? ''))));

$puertoWs      = (int) ($_ENV['CHAT_WS_PORT'] ?? 8081);
$puertoInterno = (int) ($_ENV['CHAT_WS_INTERNAL_PORT'] ?? 8082);

Worker::$logFile    = BASE_PATH . '/cache/chat-ws.log';
Worker::$pidFile    = BASE_PATH . '/cache/chat-ws.pid';
Worker::$stdoutFile = BASE_PATH . '/cache/chat-ws.out';

$ws = new Worker("websocket://127.0.0.1:{$puertoWs}");
$ws->name  = 'chat-ws';
$ws->count = 1;   // un solo proceso: los usuarios conectados viven en memoria

/** @var array<int,array<int,TcpConnection>> usuario => [id de conexión => conexión] */
$usuarios = [];

// ── Ticket ───────────────────────────────────────────────────────────
// Se valida en el apretón de manos: sin ticket válido no se abre el socket.
$ws->onWebSocketConnect = function (TcpConnection $c, Request $req) use ($secreto, $origenes, &$usuarios): void {
    $origen = (string) $req->header('origin', '');
    if ($origenes && $origen !== '' && !in_array($origen, $origenes, true)) {
        $c->close();
        return;
    }
    $uid = \App\Core\ChatRealtime::verificar((string) $req->get('t', ''), $secreto);
    if ($uid === null) {
        $c->close();
        return;
    }
    $c->uid = $uid;
    $c->ultimo = time();
    $usuarios[$uid][$c->id] = $c;
};

$ws->onMessage = function (TcpConnection $c, $datos): void {
    $c->ultimo = time();
    if ($datos === 'ping') {
        $c->send('pong');
    }
};

$ws->onClose = function (TcpConnection $c) use (&$usuarios): void {
    if (isset($c->uid)) {
        unset($usuarios[$c->uid][$c->id]);
        if (empty($usuarios[$c->uid])) {
            unset($usuarios[$c->uid]);
        }
    }
};

$ws->onWorkerStart = function (Worker $w) use (&$usuarios, $puertoInterno, $claveInterna): void {
    // Conexiones mudas (pestaña dormida, red caída): se cierran tras 90 s sin latido
    Timer::add(30, function () use (&$usuarios): void {
        $limite = time() - 90;
        foreach ($usuarios as $conexiones) {
            foreach ($conexiones as $c) {
                if (($c->ultimo ?? 0) < $limite) {
                    $c->close();
                }
            }
        }
    });

    // Entrada interna: el portal avisa de un mensaje nuevo. Va en este mismo proceso
    // para ver a los mismos usuarios conectados.
    $interno = new Worker("http://127.0.0.1:{$puertoInterno}");
    $interno->onMessage = function (TcpConnection $c, Request $req) use (&$usuarios, $claveInterna): void {
        if ($req->path() !== '/notify' || $req->method() !== 'POST'
            || !hash_equals($claveInterna, (string) $req->header('x-chat-key', ''))) {
            $c->send(new Response(403, [], 'forbidden'));
            return;
        }
        $d = json_decode($req->rawBody(), true);
        if (!is_array($d) || !is_array($d['users'] ?? null)) {
            $c->send(new Response(400, [], 'bad request'));
            return;
        }

        $aviso = json_encode(['t' => 'msg', 'cid' => (int) ($d['cid'] ?? 0), 'id' => (int) ($d['id'] ?? 0), 'from' => (int) ($d['from'] ?? 0)]);
        $conectados = [];
        foreach ($d['users'] as $uid) {
            $uid = (int) $uid;
            if (!empty($usuarios[$uid])) {
                $conectados[] = $uid;
                foreach ($usuarios[$uid] as $conn) {
                    $conn->send($aviso);
                }
            }
        }
        $c->send(new Response(200, ['Content-Type' => 'application/json'], json_encode(['online' => $conectados])));
    };
    $interno->listen();
};

Worker::runAll();
