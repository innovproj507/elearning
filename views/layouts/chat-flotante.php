<?php
/**
 * Chat flotante (estilo chatbot): una burbuja abajo a la derecha que abre un
 * panel con las conversaciones y la charla en globos. Habla con
 * /mensajes/* (ChatController), así que sirve con sesión iniciada.
 */
if (!isset($_SESSION['user'])) { return; }
// La conexión en vivo va en todas las páginas, también en /mensajes (que no lleva la burbuja)
include __DIR__ . '/chat-vivo.php';
$rutaActual = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (str_starts_with((string) $rutaActual, '/mensajes')) { return; }
$t = static fn(string $k) => htmlspecialchars(__($k), ENT_QUOTES, 'UTF-8');
?>
<style>
#cw-btn{position:fixed;right:20px;bottom:20px;z-index:60}
#cw-panel{position:fixed;right:20px;bottom:90px;z-index:60;width:360px;max-width:calc(100vw - 24px);height:520px;max-height:calc(100vh - 110px);display:none;flex-direction:column;background:#fff;border-radius:16px;box-shadow:0 12px 40px rgba(10,22,40,.28);overflow:hidden;font-family:inherit}
#cw-panel.abierto{display:flex}
#cw-panel header{background:#0a1628;color:#fff;padding:12px 14px;display:flex;align-items:center;gap:10px}
#cw-panel header b{flex:1;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#cw-panel header button{color:#fff;opacity:.8;font-size:18px;line-height:1;background:none;border:0;cursor:pointer}
#cw-body{flex:1;overflow-y:auto;background:#f1f5f9;padding:12px;display:flex;flex-direction:column;gap:8px}
.cw-item{background:#fff;border-radius:12px;padding:10px 12px;cursor:pointer;border:1px solid #e2e8f0;text-align:left;width:100%}
.cw-item:hover{border-color:#2563eb}
.cw-item b{font-size:13px;color:#0f172a;display:flex;justify-content:space-between;gap:8px}
.cw-item small{display:block;color:#64748b;font-size:11px;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cw-dot{background:#ef4444;color:#fff;border-radius:99px;font-size:10px;padding:1px 6px}
.cw-msg{max-width:82%;padding:8px 12px;border-radius:14px;font-size:13px;line-height:1.35;white-space:pre-wrap;word-break:break-word}
.cw-msg.mio{align-self:flex-end;background:#2563eb;color:#fff;border-bottom-right-radius:4px}
.cw-msg.otro{align-self:flex-start;background:#fff;color:#0f172a;border-bottom-left-radius:4px;border:1px solid #e2e8f0}
.cw-msg i{display:block;font-style:normal;font-size:10px;font-weight:800;opacity:.65;margin-bottom:2px}
.cw-msg time{display:block;font-size:10px;opacity:.6;margin-top:2px;text-align:right}
#cw-form{display:flex;gap:8px;padding:10px;border-top:1px solid #e2e8f0;background:#fff;align-items:flex-end}
#cw-form textarea{flex:1;resize:none;border:1px solid #cbd5e1;border-radius:12px;padding:8px 10px;font-size:13px;max-height:90px;outline:none}
#cw-form textarea:focus{border-color:#2563eb}
#cw-form button,.cw-primario{background:#2563eb;color:#fff;border:0;border-radius:12px;padding:8px 14px;font-weight:700;font-size:13px;cursor:pointer}
#cw-form button:disabled,.cw-primario:disabled{opacity:.5}
.cw-sel{width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:8px;font-size:13px;background:#fff}
.cw-aviso{text-align:center;color:#64748b;font-size:12px;padding:20px 10px}
.cw-err{color:#dc2626;font-size:12px;text-align:center}
</style>

<button type="button" id="cw-btn" title="<?= $t('layout.messages') ?>" aria-label="<?= $t('layout.messages') ?>"
        class="w-14 h-14 rounded-full bg-blue-600 hover:bg-blue-700 text-white shadow-lg flex items-center justify-center transition">
    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.6 7.6 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    <span id="cw-badge" class="hidden absolute -top-1 -right-1 min-w-[22px] h-[22px] px-1 rounded-full bg-red-500 text-white text-[11px] font-black flex items-center justify-center"></span>
</button>

<div id="cw-panel" role="dialog" aria-label="<?= $t('layout.messages') ?>">
    <header>
        <button type="button" id="cw-atras" style="display:none" aria-label="Volver">&#8592;</button>
        <b id="cw-titulo"><?= $t('layout.messages') ?></b>
        <button type="button" id="cw-cerrar" aria-label="Cerrar">&times;</button>
    </header>
    <div id="cw-body"></div>
    <form id="cw-form" style="display:none">
        <textarea id="cw-texto" rows="1" maxlength="2000" placeholder="<?= $t('chat.message_ph') ?>"></textarea>
        <button type="submit"><?= $t('chat.send') ?></button>
    </form>
</div>

<script>
(function () {
    const TOKEN = <?= json_encode(\App\Core\CSRF::generate()) ?>;
    const T = <?= json_encode([
        'nuevo'   => __('chat.new_conversation'),
        'vacio'   => __('chat.empty_student'),
        'general' => __('chat.general'),
        'curso'   => __('chat.about_course'),
        'mensaje' => __('chat.message'),
        'iniciar' => __('chat.start'),
        'titulo'  => __('layout.messages'),
        'error'   => __('chat.csrf'),
    ], JSON_UNESCAPED_UNICODE) ?>;
    const $ = id => document.getElementById(id);
    const btn = $('cw-btn'), panel = $('cw-panel'), body = $('cw-body'), form = $('cw-form'),
          texto = $('cw-texto'), titulo = $('cw-titulo'), atras = $('cw-atras'), badge = $('cw-badge');
    const H = { 'X-Requested-With': 'XMLHttpRequest' };
    let vista = 'lista', convId = 0, ultimo = 0, sondeo = null, esAlumno = false, cursos = [];

    const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const hora = f => { const d = new Date(String(f).replace(' ', 'T')); return isNaN(d) ? '' : d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}); };
    const get = async u => { const r = await fetch(u, { credentials: 'same-origin', headers: H }); if (!r.ok) throw new Error(r.status); return r.json(); };
    const post = async (u, datos) => {
        const f = new FormData(); f.append('_token', TOKEN);
        Object.entries(datos).forEach(([k, v]) => f.append(k, v));
        const r = await fetch(u, { method: 'POST', credentials: 'same-origin', headers: H, body: f });
        return r.json().catch(() => ({ ok: false, error: T.error }));
    };

    function pararSondeo() { if (sondeo) { clearInterval(sondeo); sondeo = null; } }
    function bajar() { body.scrollTop = body.scrollHeight; }

    async function mostrarLista() {
        vista = 'lista'; convId = 0; pararSondeo();
        atras.style.display = 'none'; form.style.display = 'none'; titulo.textContent = T.titulo;
        body.innerHTML = '<div class="cw-aviso">…</div>';
        try {
            const d = await get('/mensajes/widget');
            esAlumno = d.esAlumno; cursos = d.cursos || [];
            let h = '';
            if (esAlumno) h += '<button type="button" class="cw-primario" id="cw-nuevo">+ ' + esc(T.nuevo) + '</button>';
            if (!d.conversaciones.length) h += '<div class="cw-aviso">' + esc(esAlumno ? T.vacio : '') + '</div>';
            d.conversaciones.forEach(c => {
                const quien = esAlumno ? (c.course_title || T.general) : (c.student_name + (c.course_title ? ' · ' + c.course_title : ''));
                h += '<button type="button" class="cw-item" data-id="' + c.id + '"><b><span>' + esc(c.subject) + '</span>'
                   + (c.sin_leer > 0 ? '<span class="cw-dot">' + c.sin_leer + '</span>' : '') + '</b><small>' + esc(quien) + '</small></button>';
            });
            body.innerHTML = h;
            const n = $('cw-nuevo'); if (n) n.onclick = mostrarNuevo;
            body.querySelectorAll('.cw-item').forEach(b => b.onclick = () => abrir(parseInt(b.dataset.id, 10)));
        } catch (e) { body.innerHTML = '<div class="cw-aviso cw-err">' + esc(T.error) + '</div>'; }
    }

    function mostrarNuevo() {
        vista = 'nuevo'; pararSondeo();
        atras.style.display = ''; form.style.display = 'none'; titulo.textContent = T.nuevo;
        const opts = '<option value="0">' + esc(T.general) + '</option>' + cursos.filter(c => c.id).map(c => '<option value="' + c.id + '">' + esc(c.title) + '</option>').join('');
        body.innerHTML = '<label style="font-size:12px;font-weight:700;color:#475569">' + esc(T.curso) + '</label>'
            + '<select id="cw-curso" class="cw-sel">' + opts + '</select>'
            + '<label style="font-size:12px;font-weight:700;color:#475569">' + esc(T.mensaje) + '</label>'
            + '<textarea id="cw-primero" class="cw-sel" rows="5" maxlength="2000"></textarea>'
            + '<div id="cw-error" class="cw-err"></div>'
            + '<button type="button" class="cw-primario" id="cw-crear">' + esc(T.iniciar) + '</button>';
        $('cw-primero').focus();
        $('cw-crear').onclick = async () => {
            const b = $('cw-crear'), cuerpo = $('cw-primero').value.trim();
            if (!cuerpo) { $('cw-primero').focus(); return; }
            b.disabled = true;
            const r = await post('/mensajes/nuevo', { course_id: $('cw-curso').value, body: cuerpo });
            if (r.ok) abrir(r.id); else { b.disabled = false; $('cw-error').textContent = r.error || T.error; }
        };
    }

    function pintar(m) {
        const d = document.createElement('div');
        d.className = 'cw-msg ' + (m.mio ? 'mio' : 'otro'); d.dataset.id = m.id;
        d.innerHTML = (m.mio ? '' : '<i>' + esc(m.sender_name) + '</i>') + esc(m.body) + '<time>' + esc(hora(m.created_at)) + '</time>';
        body.appendChild(d); ultimo = Math.max(ultimo, parseInt(m.id, 10));
    }

    async function cargar(despues) {
        const d = await get('/mensajes/' + convId + '/nuevos?after=' + despues);
        if (!d.ok) return;
        if (despues === 0) { body.innerHTML = ''; titulo.textContent = d.conversacion.subject; }
        const abajo = body.scrollHeight - body.scrollTop - body.clientHeight < 60 || despues === 0;
        d.mensajes.forEach(m => { if (!body.querySelector('[data-id="' + m.id + '"]')) pintar(m); });
        if (abajo) bajar();
    }

    async function abrir(id) {
        vista = 'hilo'; convId = id; ultimo = 0; pararSondeo();
        atras.style.display = ''; form.style.display = 'flex';
        body.innerHTML = '<div class="cw-aviso">…</div>';
        try { await cargar(0); } catch (e) { body.innerHTML = '<div class="cw-aviso cw-err">' + esc(T.error) + '</div>'; return; }
        // Con el chat en vivo solo hace falta un sondeo lento de seguridad; sin él, cada 5 s
        let tic = 0;
        sondeo = setInterval(() => {
            if (document.hidden) return;
            if (window.ChatLive && window.ChatLive.vivo && ++tic % 12) return;
            cargar(ultimo).catch(() => {});
        }, 5000);
        texto.focus(); contar();
    }

    form.onsubmit = async ev => {
        ev.preventDefault();
        const cuerpo = texto.value.trim(); if (!cuerpo || !convId) return;
        const b = form.querySelector('button'); b.disabled = true;
        const r = await post('/mensajes/' + convId + '/enviar', { body: cuerpo });
        b.disabled = false;
        if (r.ok) { texto.value = ''; texto.style.height = ''; await cargar(ultimo).catch(() => {}); bajar(); }
        else { const e = document.createElement('div'); e.className = 'cw-err'; e.textContent = r.error || T.error; body.appendChild(e); bajar(); }
    };
    texto.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); } });
    texto.addEventListener('input', () => { texto.style.height = 'auto'; texto.style.height = Math.min(texto.scrollHeight, 90) + 'px'; });

    atras.onclick = mostrarLista;
    $('cw-cerrar').onclick = () => { panel.classList.remove('abierto'); pararSondeo(); };
    btn.onclick = () => {
        if (panel.classList.toggle('abierto')) { (vista === 'hilo' && convId) ? abrir(convId) : mostrarLista(); }
        else pararSondeo();
    };

    async function contar() {
        if (document.hidden) return;
        try {
            const n = parseInt((await get('/mensajes/no-leidos')).count, 10) || 0;
            badge.textContent = n > 99 ? '99+' : String(n);
            badge.classList.toggle('hidden', n === 0);
        } catch (e) {}
    }
    contar(); setInterval(() => { if (!(window.ChatLive && window.ChatLive.vivo)) contar(); }, 30000);

    // Aviso en vivo: si es la conversación abierta se trae el mensaje al momento; si no, se
    // actualiza el contador (y la lista si está a la vista) y la burbuja da un respingo
    window.addEventListener('chat-live', ev => {
        const cid = parseInt(ev.detail.cid, 10) || 0;
        const abierto = panel.classList.contains('abierto');
        if (abierto && vista === 'hilo' && convId && (!cid || cid === convId)) {
            cargar(ultimo).then(() => contar()).catch(() => {});
            return;
        }
        contar();
        if (abierto && vista === 'lista') mostrarLista();
        if (!abierto && cid) {
            btn.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.18)' }, { transform: 'scale(1)' }], { duration: 500, iterations: 3 });
        }
    });
    window.addEventListener('chat-live-estado', () => contar());
    document.addEventListener('visibilitychange', contar);
})();
</script>
