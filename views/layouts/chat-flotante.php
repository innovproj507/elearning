<?php
/**
 * Botón flotante del chat interno: lleva a /mensajes y muestra los mensajes
 * sin leer. Solo con sesión iniciada y fuera del propio chat.
 */
if (!isset($_SESSION['user'])) { return; }
$rutaActual = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (str_starts_with((string) $rutaActual, '/mensajes')) { return; }
?>
<a href="/mensajes" id="chat-fab" title="<?= htmlspecialchars(__('layout.messages')) ?>" aria-label="<?= htmlspecialchars(__('layout.messages')) ?>"
   style="position:fixed;right:20px;bottom:20px;z-index:60"
   class="w-14 h-14 rounded-full bg-blue-600 hover:bg-blue-700 text-white shadow-lg flex items-center justify-center transition">
    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.6 7.6 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    <span id="chat-fab-badge" class="hidden absolute -top-1 -right-1 min-w-[22px] h-[22px] px-1 rounded-full bg-red-500 text-white text-[11px] font-black flex items-center justify-center"></span>
</a>
<script>
(function () {
    const badge = document.getElementById('chat-fab-badge');
    async function contar() {
        if (document.hidden) return;
        try {
            const r = await fetch('/mensajes/no-leidos', { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!r.ok) return;
            const n = parseInt((await r.json()).count, 10) || 0;
            badge.textContent = n > 99 ? '99+' : String(n);
            badge.classList.toggle('hidden', n === 0);
        } catch (e) { /* se reintenta en el siguiente ciclo */ }
    }
    contar();
    setInterval(contar, 30000);
    document.addEventListener('visibilitychange', contar);
})();
</script>
