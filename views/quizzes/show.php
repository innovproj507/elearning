<?php
$total     = count($quiz['questions']);
$timeLimit = (int)($quiz['time_limit'] ?? 0);
?>
<style>
.option-label { transition: all .15s ease; }
.option-label:has(input:checked) {
    border-color: #1a56db !important;
    background: #eff6ff !important;
}
.option-label:has(input:checked) .option-letter {
    background: #1a56db !important;
    color: #fff !important;
}
.q-card { animation: qIn .35s ease both; }
@keyframes qIn { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
</style>

<!-- Barra de progreso del quiz (sticky) -->
<div class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-3xl mx-auto px-4 py-2 flex items-center gap-4">
        <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>"
           class="text-xs font-bold text-gray-400 hover:text-brand-500 transition-colors flex items-center gap-1 flex-shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <?= __('quizzes.show.exit') ?>
        </a>
        <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
            <div id="quiz-progress-bar" class="h-2 bg-gradient-to-r from-brand-500 to-blue-400 rounded-full transition-all duration-500" style="width:0%"></div>
        </div>
        <span id="quiz-progress-text" class="text-xs font-black text-navy flex-shrink-0">0/<?= $total ?></span>
        <?php if ($timeLimit > 0): ?>
        <span id="timer" class="text-xs font-black text-amber-600 bg-amber-50 px-2 py-1 rounded-lg flex-shrink-0">
            <?= sprintf('%02d:%02d', intdiv($timeLimit * 60, 60), ($timeLimit * 60) % 60) ?>
        </span>
        <?php endif; ?>
    </div>
</div>

<div class="max-w-3xl mx-auto pt-16 pb-10">

    <!-- Header del quiz -->
    <div class="rounded-3xl overflow-hidden mb-8 shadow-lg"
         style="background:linear-gradient(135deg,#1e1b4b 0%,#312e81 50%,#4f46e5 100%);">
        <div class="p-7">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center text-3xl flex-shrink-0 border border-white/20">📝</div>
                <div class="flex-1 min-w-0">
                    <p class="text-indigo-300 text-xs font-bold uppercase tracking-widest mb-1"><?= htmlspecialchars($course['title']) ?></p>
                    <h1 class="text-xl font-black text-white mb-3"><?= htmlspecialchars($quiz['title']) ?></h1>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold bg-white/15 text-white px-3 py-1.5 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <?= __('quizzes.show.pass_label', ['pct' => $quiz['pass_percentage']]) ?>
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold bg-white/15 text-white px-3 py-1.5 rounded-full">
                            <?= __('quizzes.show.questions_count', ['count' => $total]) ?>
                        </span>
                        <?php if ($timeLimit): ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold bg-amber-400/30 text-amber-200 px-3 py-1.5 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <?= __('quizzes.show.minutes', ['count' => $timeLimit]) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($bestAttempt): ?>
            <div class="mt-5 flex items-center gap-3 p-4 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/15">
                <span class="text-2xl"><?= $bestAttempt['passed'] ? '🏆' : '📖' ?></span>
                <div>
                    <p class="text-sm font-black text-white">
                        <?= $bestAttempt['passed'] ? __('quizzes.show.already_passed') : __('quizzes.show.not_passed_yet') ?>
                    </p>
                    <p class="text-xs text-white/60 font-semibold">
                        <?= __('quizzes.show.best_score_prefix') ?> <strong class="text-white"><?= $bestAttempt['score'] ?>%</strong> · <?= __('quizzes.show.minimum_pct', ['pct' => $quiz['pass_percentage']]) ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    /*
        Examen cerrado, por dos motivos distintos: ya está aprobado —no se
        repite para subir nota— o se agotaron los intentos. En lugar del
        formulario se ofrece el resultado obtenido, que es lo único que
        tiene sentido consultar a estas alturas.
    */
    $cerrado   = $cerrado   ?? !empty($yaAprobado);
    $intentos  = $intentos  ?? [];
    $sinIntentos = ($intentos['motivo'] ?? null) === 'sin_intentos';
    ?>
    <?php if ($cerrado): ?>
    <div class="<?= $sinIntentos ? 'bg-rose-50 border-rose-200' : 'bg-emerald-50 border-emerald-200' ?> border-2 rounded-2xl p-8 text-center">
        <p class="text-4xl mb-3"><?= $sinIntentos ? '🔒' : '🏆' ?></p>
        <h2 class="font-black text-navy text-lg mb-1">
            <?= $sinIntentos ? __('quizzes.show.no_attempts_title') : __('quizzes.show.locked_title') ?>
        </h2>
        <p class="text-sm text-gray-600 mb-5">
            <?= $sinIntentos
                ? __('quizzes.show.no_attempts_help', ['total' => (int) ($intentos['limite'] ?? 0)])
                : __('quizzes.show.locked_help') ?>
        </p>
        <div class="flex flex-wrap items-center justify-center gap-3">
            <?php if ($bestAttempt): /* sin intento no hay resultado que enseñar */ ?>
            <a href="/cursos/<?= $course['id'] ?>/quizzes/<?= $quiz['id'] ?>/resultados?attempt=<?= (int) $bestAttempt['id'] ?>"
               class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-bold px-6 py-3 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/25">
                <?= __('quizzes.show.see_results') ?>
            </a>
            <?php endif; ?>
            <a href="/cursos/<?= htmlspecialchars($course['slug'] ?? (string) $course['id']) ?>"
               class="inline-flex items-center gap-2 bg-white border-2 border-gray-200 hover:border-gray-300 text-gray-600 font-bold px-6 py-3 rounded-xl text-sm transition-all">
                <?= __('quizzes.show.back_to_course') ?>
            </a>
        </div>
    </div>
    <?php else: ?>

    <?php /* Qué intento está presentando, para que no le pille por sorpresa */ ?>
    <?php if (!empty($intentos['limite'])): ?>
    <?php $nEste = (int) $intentos['realizados'] + 1; $tope = (int) $intentos['limite']; ?>
    <div class="mb-5 flex items-center justify-center gap-2 text-sm font-bold
                <?= $nEste >= $tope ? 'text-rose-600' : 'text-gray-500' ?>">
        <span><?= $nEste >= $tope ? '⚠️' : '📝' ?></span>
        <span><?= __('quizzes.show.attempt_of', ['n' => $nEste, 'total' => $tope]) ?></span>
        <?php if ($nEste >= $tope): ?>
        <span class="font-black"><?= __('quizzes.show.last_attempt') ?></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Formulario -->
    <form method="POST" action="/cursos/<?= $course['id'] ?>/quizzes/<?= $quiz['id'] ?>/responder" id="quiz-form">
        <?= \App\Core\CSRF::field() ?>

        <div class="space-y-6">
            <?php foreach ($quiz['questions'] as $i => $q): ?>
            <div class="q-card bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                 style="animation-delay:<?= $i * 0.06 ?>s"
                 data-question="<?= $i ?>">

                <!-- Header pregunta -->
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-brand-500 to-blue-700 text-white text-xs font-black flex items-center justify-center flex-shrink-0 shadow-md">
                        <?= $i + 1 ?>
                    </span>
                    <p class="text-navy font-bold text-sm leading-relaxed flex-1">
                        <?= htmlspecialchars($q['question']) ?>
                    </p>
                    <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-lg flex-shrink-0">
                        <?= $q['points'] ?><?= $q['points'] != 1 ? __('quizzes.show.points_abbr_plural') : __('quizzes.show.points_abbr') ?>
                    </span>
                </div>

                <!-- Opciones -->
                <div class="p-4 space-y-2">
                    <?php
                    $letters = ['A','B','C','D','E','F'];
                    foreach ($q['options'] as $oi => $opt):
                    ?>
                    <label class="option-label flex items-center gap-3 p-3.5 rounded-xl border-2 border-gray-100 hover:border-brand-200 hover:bg-brand-50/30 cursor-pointer">
                        <span class="option-letter w-7 h-7 rounded-lg bg-gray-100 text-gray-500 text-xs font-black flex items-center justify-center flex-shrink-0 transition-all">
                            <?= $letters[$oi] ?? ($oi + 1) ?>
                        </span>
                        <input type="radio"
                               name="answers[<?= $q['id'] ?>]"
                               value="<?= $opt['id'] ?>"
                               required
                               class="sr-only"
                               onchange="updateProgress()">
                        <span class="text-sm text-gray-700 font-semibold leading-snug">
                            <?= htmlspecialchars($opt['option_text']) ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Submit -->
        <div class="mt-8 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col sm:flex-row items-center gap-4 justify-between">
            <div class="text-sm text-gray-500 font-semibold">
                <span id="answered-count" class="text-navy font-black">0</span>/<?= $total ?> <?= __('quizzes.show.answered_count') ?>
            </div>
            <button type="submit" id="submit-btn"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-brand-500 to-blue-600 hover:from-brand-600 hover:to-blue-700 text-white font-black px-8 py-3.5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/30 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:translate-y-0">
                <?= __('quizzes.show.submit') ?>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php if (!$cerrado): /* el script maneja el formulario; sin él sobra */ ?>
<script>
const totalQ = <?= $total ?>;

function updateProgress() {
    const answered = document.querySelectorAll('input[type=radio]:checked').length;
    const pct = Math.round((answered / totalQ) * 100);
    document.getElementById('quiz-progress-bar').style.width = pct + '%';
    document.getElementById('quiz-progress-text').textContent = answered + '/' + totalQ;
    document.getElementById('answered-count').textContent = answered;
}

<?php if ($timeLimit > 0): ?>
// Timer countdown
let seconds = <?= $timeLimit * 60 ?>;
const timerEl = document.getElementById('timer');
const interval = setInterval(() => {
    seconds--;
    if (seconds <= 0) {
        clearInterval(interval);
        document.getElementById('quiz-form').submit();
        return;
    }
    const m = String(Math.floor(seconds / 60)).padStart(2,'0');
    const s = String(seconds % 60).padStart(2,'0');
    timerEl.textContent = m + ':' + s;
    if (seconds <= 60) {
        timerEl.classList.remove('text-amber-600','bg-amber-50');
        timerEl.classList.add('text-red-600','bg-red-50');
    }
}, 1000);
<?php endif; ?>

// Confirmar envío si quedan sin responder
const unansweredConfirmTpl = <?= json_encode(__('quizzes.show.unanswered_confirm', ['count' => '__COUNT__'])) ?>;
document.getElementById('quiz-form').addEventListener('submit', function(e) {
    const answered = document.querySelectorAll('input[type=radio]:checked').length;
    if (answered < totalQ) {
        if (!confirm(unansweredConfirmTpl.replace('__COUNT__', totalQ - answered))) {
            e.preventDefault();
        }
    }
});
</script>
<?php endif; ?>
