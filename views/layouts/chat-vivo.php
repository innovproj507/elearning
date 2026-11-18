<?php
/**
 * Conexión en vivo del chat (WebSocket). La incluye chat-flotante.php, así que
 * está en todas las páginas con sesión, también en /mensajes.
 *
 * El socket solo trae avisos: al llegar uno se lanza el evento "chat-live" en
 * window ({cid, id, from}) y cada pantalla decide qué refrescar. Mientras no
 * haya conexión, window.ChatLive.vivo es false y las pantallas siguen
 * sondeando como antes.
 */
if (!isset($_SESSION['user']) || !\App\Core\ChatRealtime::enabled()) { return; }
?>
<script>
(function () {
    if (window.ChatLive || !('WebSocket' in window)) return;
    const live = window.ChatLive = { vivo: false };
    let ws = null, latido = null, espera = 2000;

    async function conectar() {
        let d;
        try {
            const r = await fetch('/mensajes/ticket', { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!r.ok) return reintentar();
            d = await r.json();
        } catch (e) { return reintentar(); }
        if (!d.ok) return;                       // chat en vivo apagado en el servidor

        try { ws = new WebSocket(d.url + '?t=' + encodeURIComponent(d.t)); } catch (e) { return reintentar(); }

        ws.onopen = () => {
            live.vivo = true; espera = 2000;
            latido = setInterval(() => { if (ws.readyState === 1) ws.send('ping'); }, 30000);
            window.dispatchEvent(new CustomEvent('chat-live-estado', { detail: { vivo: true } }));
        };
        ws.onmessage = ev => {
            if (ev.data === 'pong') return;
            try {
                const m = JSON.parse(ev.data);
                if (m.t === 'msg') window.dispatchEvent(new CustomEvent('chat-live', { detail: m }));
            } catch (e) { /* aviso ilegible: se ignora */ }
        };
        ws.onclose = () => {
            clearInterval(latido);
            if (live.vivo) { live.vivo = false; window.dispatchEvent(new CustomEvent('chat-live-estado', { detail: { vivo: false } })); }
            reintentar();
        };
        ws.onerror = () => { try { ws.close(); } catch (e) {} };
    }

    function reintentar() {
        setTimeout(conectar, espera);
        espera = Math.min(espera * 2, 60000);
    }

    conectar();
    // Al volver a la pestaña, si el socket se durmió se reconecta y se refresca lo que haya
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) window.dispatchEvent(new CustomEvent('chat-live', { detail: { t: 'msg', cid: 0, id: 0, from: 0 } }));
    });
})();
</script>
