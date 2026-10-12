<?php
$passed   = (bool)$attempt['passed'];
$score    = (float)$attempt['score'];
$correct  = count(array_filter($attempt['answers'], fn($a) => $a['is_correct']));
$total    = count($attempt['answers']);
$wrong    = $total - $correct;
?>
<style>
.confetti-piece { position:fixed; width:10px; height:10px; top:-10px; animation:fall linear forwards; pointer-events:none; z-index:9999; border-radius:2px; }
@keyframes fall { to { transform:translateY(110vh) rotate(720deg); opacity:0; } }
.score-ring { transition: stroke-dashoffset 1.5s cubic-bezier(.4,0,.2,1); }
.ans-card { animation: ansIn .3s ease both; }
@keyframes ansIn { from { opacity:0; transform:translateX(-8px); } to { opacity:1; transform:translateX(0); } }
</style>

<div class="max-w-3xl mx-auto py-8">

    <!-- Resultado principal -->
    <div class="rounded-3xl overflow-hidden shadow-2xl mb-8">

        <!-- Banner -->
        <div class="relative p-10 text-center overflow-hidden"
             style="background:linear-gradient(135deg,<?= $passed ? '#064e3b,#059669,#34d399' : '#7f1d1d,#b91c1c,#ef4444' ?>);">
            <div class="absolute inset-0 opacity-10" style="background-image:repeating-linear-gradient(45deg,#fff 0,#fff 1px,transparent 1px,transparent 20px)"></div>
            <div class="relative z-10">
                <div class="text-7xl mb-4" id="result-emoji"><?= $passed ? '🏆' : '📖' ?></div>
                <h1 class="text-3xl font-black text-white mb-2">
                    <?= $passed ? __('quizzes.results.congrats_passed') : __('quizzes.results.keep_practicing') ?>
                </h1>
                <p class="text-white/70 text-sm font-semibold"><?= htmlspecialchars($quiz['title']) ?></p>
            </div>
        </div>

        <!-- Cuerpo de resultado -->
        <div class="bg-white p-8">

            <!-- Score ring + stats -->
            <div class="flex flex-col sm:flex-row items-center gap-8 mb-8">

                <!-- Círculo SVG animado -->
                <div class="relative w-40 h-40 flex-shrink-0">
                    <svg class="w-40 h-40 -rotate-90" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                        <circle cx="60" cy="60" r="50" fill="none"
                                stroke="<?= $passed ? '#10b981' : '#ef4444' ?>"
                                stroke-width="10"
                                stroke-linecap="round"
                                stroke-dasharray="314"
                                stroke-dashoffset="314"
                                id="score-ring"
                                class="score-ring"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span id="score-display" class="text-4xl font-black <?= $passed ? 'text-emerald-600' : 'text-red-500' ?>">0%</span>
                        <span class="text-xs text-gray-400 font-semibold"><?= __('quizzes.results.score_label') ?></span>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="flex-1 w-full space-y-4">
                    <div class="flex items-center gap-3 p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                        <span class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center text-white font-black text-lg flex-shrink-0">✓</span>
                        <div>
                            <p class="text-2xl font-black text-emerald-700"><?= $correct ?></p>
                            <p class="text-xs text-emerald-600 font-semibold"><?= __('quizzes.results.correct') ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-red-50 rounded-2xl border border-red-100">
                        <span class="w-10 h-10 rounded-xl bg-red-400 flex items-center justify-center text-white font-black text-lg flex-shrink-0">✗</span>
                        <div>
                            <p class="text-2xl font-black text-red-600"><?= $wrong ?></p>
                            <p class="text-xs text-red-500 font-semibold"><?= __('quizzes.results.incorrect') ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                        <span class="w-10 h-10 rounded-xl bg-gray-200 flex items-center justify-center text-gray-600 font-black text-sm flex-shrink-0"><?= __('quizzes.results.min_abbr') ?></span>
                        <div>
                            <p class="text-2xl font-black text-gray-600"><?= $quiz['pass_percentage'] ?>%</p>
                            <p class="text-xs text-gray-400 font-semibold"><?= __('quizzes.results.to_pass') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra progreso -->
            <div class="mb-6">
                <div class="flex justify-between text-xs font-semibold text-gray-400 mb-1.5">
                    <span>0%</span>
                    <span class="<?= $passed ? 'text-emerald-600' : 'text-red-500' ?> font-black"><?= $score ?>%</span>
                    <span>100%</span>
                </div>
                <div class="bg-gray-100 rounded-full h-4 overflow-hidden relative">
                    <!-- Línea de mínimo -->
                    <div class="absolute top-0 bottom-0 border-l-2 border-dashed border-gray-400 z-10"
                         style="left:<?= $quiz['pass_percentage'] ?>%"></div>
                    <div class="h-4 rounded-full transition-all duration-1000 delay-300"
                         style="width:0%; background:linear-gradient(90deg,<?= $passed ? '#10b981,#34d399' : '#ef4444,#f87171' ?>)"
                         id="score-bar"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1 text-right font-semibold"><?= __('quizzes.results.minimum_pct', ['pct' => $quiz['pass_percentage']]) ?></p>
            </div>

            <!-- Acciones -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="/cursos/<?= $course['id'] ?>/quizzes/<?= $quiz['id'] ?>"
                   class="flex-1 inline-flex items-center justify-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-bold px-6 py-3.5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <?= __('quizzes.results.try_again') ?>
                </a>
                <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>"
                   class="flex-1 inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-6 py-3.5 rounded-xl text-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <?= __('quizzes.results.back_to_course') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Revisión de respuestas -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
            <span class="w-8 h-8 rounded-xl bg-navy flex items-center justify-center text-white text-sm">📋</span>
            <h2 class="font-black text-navy"><?= __('quizzes.results.answer_review') ?></h2>
            <span class="ml-auto text-xs font-bold text-gray-400"><?= __('quizzes.results.correct_count', ['correct' => $correct, 'total' => $total]) ?></span>
        </div>
        <div class="p-5 space-y-3">
            <?php foreach ($attempt['answers'] as $i => $ans): ?>
            <div class="ans-card rounded-xl border-2 overflow-hidden <?= $ans['is_correct'] ? 'border-emerald-200' : 'border-red-200' ?>"
                 style="animation-delay:<?= $i * 0.04 ?>s">
                <div class="flex items-center gap-3 px-4 py-3 <?= $ans['is_correct'] ? 'bg-emerald-50' : 'bg-red-50' ?>">
                    <span class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-black flex-shrink-0 text-white
                        <?= $ans['is_correct'] ? 'bg-emerald-500' : 'bg-red-500' ?>">
                        <?= $ans['is_correct'] ? '✓' : '✗' ?>
                    </span>
                    <p class="font-bold text-navy text-sm leading-snug flex-1">
                        <span class="text-gray-400 font-semibold mr-1"><?= $i + 1 ?>.</span>
                        <?= htmlspecialchars($ans['question']) ?>
                    </p>
                </div>
                <div class="px-4 py-3 bg-white space-y-1.5">
                    <div class="flex items-start gap-2 text-sm">
                        <span class="text-xs font-bold <?= $ans['is_correct'] ? 'text-emerald-600' : 'text-red-600' ?> flex-shrink-0 mt-0.5"><?= __('quizzes.results.your_answer') ?></span>
                        <span class="font-bold <?= $ans['is_correct'] ? 'text-emerald-700' : 'text-red-700' ?>">
                            <?= htmlspecialchars($ans['selected_text'] ?? __('quizzes.results.no_answer')) ?>
                        </span>
                    </div>
                    <?php if (!$ans['is_correct'] && !empty($ans['correct_text'])): ?>
                    <div class="flex items-start gap-2 text-sm">
                        <span class="text-xs font-bold text-emerald-600 flex-shrink-0 mt-0.5"><?= __('quizzes.results.correct_answer') ?></span>
                        <span class="font-bold text-emerald-700"><?= htmlspecialchars($ans['correct_text']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
const score = <?= $score ?>;
const passed = <?= $passed ? 'true' : 'false' ?>;

// Animar el marcador
const ring = document.getElementById('score-ring');
const bar  = document.getElementById('score-bar');
const disp = document.getElementById('score-display');
const circumference = 314;

setTimeout(() => {
    const offset = circumference - (score / 100) * circumference;
    ring.style.strokeDashoffset = offset;
    bar.style.width = Math.min(score, 100) + '%';

    // Contador numérico
    let current = 0;
    const step  = score / 60;
    const timer = setInterval(() => {
        current = Math.min(current + step, score);
        disp.textContent = Math.round(current) + '%';
        if (current >= score) clearInterval(timer);
    }, 16);
}, 300);

// Confetti si aprobó
if (passed) {
    const colors = ['#1a56db','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
    for (let i = 0; i < 80; i++) {
        const el = document.createElement('div');
        el.className = 'confetti-piece';
        el.style.cssText = `
            left:${Math.random() * 100}vw;
            background:${colors[Math.floor(Math.random() * colors.length)]};
            animation-duration:${1.5 + Math.random() * 2}s;
            animation-delay:${Math.random() * .8}s;
            width:${6 + Math.random() * 8}px;
            height:${6 + Math.random() * 8}px;
            border-radius:${Math.random() > .5 ? '50%' : '2px'};
        `;
        document.body.appendChild(el);
        el.addEventListener('animationend', () => el.remove());
    }
}
</script>
