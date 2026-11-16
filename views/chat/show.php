<?php
/** @var array $conversacion @var array $mensajes */
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$esAlumno = ($conversacion['mi_rol'] ?? '') === 'student';
$ultimoId = $mensajes ? (int) end($mensajes)['id'] : 0;
?>
<div class="max-w-3xl mx-auto">
    <a href="/mensajes" class="text-xs font-bold text-blue-600 hover:underline">← <?= __('chat.back') ?></a>

    <div class="mt-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col" style="height: min(75vh, 700px)">
        <header class="px-5 py-3 border-b border-gray-100">
            <h1 class="text-sm font-black text-navy truncate"><?= $e($conversacion['subject']) ?></h1>
            <p class="text-xs text-gray-400 truncate">
                <?php if (!$esAlumno): ?><?= __('chat.student') ?>: <?= $e($conversacion['student_name']) ?> · <?php endif; ?>
                <?= !empty($conversacion['course_title']) ? __('chat.course') . ': ' . $e($conversacion['course_title']) : $e(__('chat.general')) ?>
            </p>
        </header>

        <div id="chat-log" class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50" aria-live="polite">
            <?php foreach ($mensajes as $m): ?>
            <div class="flex <?= $m['mio'] ? 'justify-end' : 'justify-start' ?>" data-id="<?= (int) $m['id'] ?>">
                <div class="max-w-[80%] rounded-2xl px-4 py-2 text-sm shadow-sm <?= $m['mio'] ? 'bg-navy text-white rounded-br-sm' : 'bg-white text-gray-800 border border-gray-100 rounded-bl-sm' ?>">
                    <?php if (!$m['mio']): ?><p class="text-[11px] font-black opacity-70 mb-0.5"><?= $e($m['sender_name']) ?></p><?php endif; ?>
                    <p class="whitespace-pre-wrap break-words"><?= $e($m['body']) ?></p>
                    <p class="text-[10px] opacity-60 mt-1 text-right"><?= date('d/m H:i', strtotime((string) $m['created_at'])) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <form id="chat-form" method="POST" action="/mensajes/<?= (int) $conversacion['id'] ?>/enviar"
              class="border-t border-gray-100 p-3 flex items-end gap-2">
            <?= \App\Core\CSRF::field() ?>
            <textarea id="chat-input" name="body" rows="2" maxlength="2000" required
                      placeholder="<?= $e(__('chat.message_ph')) ?>"
                      class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm resize-none"></textarea>
            <button id="chat-send" type="submit" class="bg-navy text-white font-bold text-sm rounded-xl px-5 py-2.5 hover:opacity-90 transition">
                <?= __('chat.send') ?>
            </button>
        </form>
        <p class="px-4 pb-2 text-[11px] text-gray-400"><?= __('chat.enter_hint') ?> · <?= __('chat.max_chars', ['max' => 2000]) ?></p>
        <p id="chat-error" class="px-4 pb-2 text-xs font-semibold text-red-600 hidden"></p>
    </div>
</div>

<script>
(function () {
    const convId   = <?= (int) $conversacion['id'] ?>;
    const log      = document.getElementById('chat-log');
    const form     = document.getElementById('chat-form');
    const input    = document.getElementById('chat-input');
    const send     = document.getElementById('chat-send');
    const errorEl  = document.getElementById('chat-error');
    const textos   = {
        enviar: <?= json_encode(__('chat.send'), JSON_UNESCAPED_UNICODE) ?>,
        enviando: <?= json_encode(__('chat.sending'), JSON_UNESCAPED_UNICODE) ?>,
        error: <?= json_encode(__('chat.send_error'), JSON_UNESCAPED_UNICODE) ?>
    };
    let ultimo = <?= $ultimoId ?>;
    let enviandoAhora = false;

    const abajo = () => { log.scrollTop = log.scrollHeight; };
    abajo();

    function hora(s) {
        const m = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(s || '');
        return m ? m[3] + '/' + m[2] + ' ' + m[4] + ':' + m[5] : '';
    }

    // Todo texto entra con textContent: nunca como HTML.
    function pintar(m) {
        if (log.querySelector('[data-id="' + m.id + '"]')) return;
        const fila = document.createElement('div');
        fila.className = 'flex ' + (m.mio ? 'justify-end' : 'justify-start');
        fila.dataset.id = m.id;
        const burbuja = document.createElement('div');
        burbuja.className = 'max-w-[80%] rounded-2xl px-4 py-2 text-sm shadow-sm ' +
            (m.mio ? 'bg-navy text-white rounded-br-sm' : 'bg-white text-gray-800 border border-gray-100 rounded-bl-sm');
        if (!m.mio) {
            const quien = document.createElement('p');
            quien.className = 'text-[11px] font-black opacity-70 mb-0.5';
            quien.textContent = m.sender_name;
            burbuja.appendChild(quien);
        }
        const cuerpo = document.createElement('p');
        cuerpo.className = 'whitespace-pre-wrap break-words';
        cuerpo.textContent = m.body;
        burbuja.appendChild(cuerpo);
        const t = document.createElement('p');
        t.className = 'text-[10px] opacity-60 mt-1 text-right';
        t.textContent = hora(m.created_at);
        burbuja.appendChild(t);
        fila.appendChild(burbuja);
        log.appendChild(fila);
        ultimo = Math.max(ultimo, m.id);
    }

    async function traerNuevos() {
        if (enviandoAhora || document.hidden) return;
        try {
            const r = await fetch('/mensajes/' + convId + '/nuevos?after=' + ultimo, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin'
            });
            if (!r.ok) return;
            const d = await r.json();
            if (d.ok && d.mensajes && d.mensajes.length) {
                const pegado = log.scrollHeight - log.scrollTop - log.clientHeight < 80;
                d.mensajes.forEach(pintar);
                if (pegado) abajo();
            }
        } catch (e) { /* sin red: el siguiente sondeo lo reintenta */ }
    }

    async function enviar() {
        const texto = input.value.trim();
        if (!texto || enviandoAhora) return;
        enviandoAhora = true;
        send.disabled = true; send.textContent = textos.enviando;
        errorEl.classList.add('hidden');
        try {
            const r = await fetch(form.action, {
                method: 'POST', body: new FormData(form), credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const d = await r.json().catch(() => ({}));
            if (!r.ok || !d.ok) throw new Error(d.error || textos.error);
            input.value = '';
            enviandoAhora = false;
            await traerNuevos();
            abajo();
        } catch (e) {
            errorEl.textContent = e.message || textos.error;
            errorEl.classList.remove('hidden');
        } finally {
            enviandoAhora = false;
            send.disabled = false; send.textContent = textos.enviar;
            input.focus();
        }
    }

    form.addEventListener('submit', (ev) => { ev.preventDefault(); enviar(); });
    input.addEventListener('keydown', (ev) => {
        if (ev.key === 'Enter' && !ev.shiftKey) { ev.preventDefault(); enviar(); }
    });

    setInterval(traerNuevos, 5000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) traerNuevos(); });
})();
</script>
