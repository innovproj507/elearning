<?php
$_img = cms_asset($course['image'] ?? null);

$levelLabel = ['beginner' => __('courses.card.level_beginner'), 'intermediate' => __('courses.card.level_intermediate'), 'advanced' => __('courses.card.level_advanced')];
$levelColor = [
    'beginner'     => 'bg-emerald-100 text-emerald-700',
    'intermediate' => 'bg-blue-100 text-blue-700',
    'advanced'     => 'bg-purple-100 text-purple-700',
];
$catColors = [
    0 => 'from-blue-700 to-blue-900',
    1 => 'from-purple-700 to-purple-900',
    2 => 'from-amber-600 to-amber-800',
    3 => 'from-red-600 to-red-900',
    4 => 'from-emerald-600 to-emerald-900',
    5 => 'from-teal-600 to-teal-900',
];
$gradient   = $catColors[($course['category_id'] ?? 0) % 6];
$lessons    = (int)($course['lesson_count']  ?? $course['lessons_count']  ?? 0);
$quizCount  = (int)($course['quiz_count']    ?? $course['quizzes_count']  ?? 0);
?>
<div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300 hover:-translate-y-1.5 flex flex-col border border-gray-100">

    <!-- Imagen / banner -->
    <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>" class="block relative overflow-hidden h-44 flex-shrink-0">
        <!-- Gradiente siempre presente como fondo -->
        <div class="card-img-fallback w-full h-full bg-gradient-to-br <?= $gradient ?> flex items-center justify-center absolute inset-0">
            <div class="absolute inset-0 opacity-10" style="background-image:repeating-linear-gradient(0deg,#fff 0,#fff 1px,transparent 1px,transparent 40px),repeating-linear-gradient(90deg,#fff 0,#fff 1px,transparent 1px,transparent 40px)"></div>
            <svg class="w-16 h-16 text-white/25 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <!-- Imagen encima del gradiente; si falla, se elimina y queda el gradiente -->
        <?php if ($_img !== ''): ?>
            <img src="<?= htmlspecialchars($_img) ?>"
                 alt="<?= htmlspecialchars($course['title']) ?>"
                 class="w-full h-full object-cover absolute inset-0 group-hover:scale-105 transition-transform duration-500"
                 onerror="this.remove()">
        <?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>

        <!-- Stats sobre imagen -->
        <div class="absolute bottom-3 left-3 flex items-center gap-2">
            <?php if ($lessons > 0): ?>
            <span class="inline-flex items-center gap-1 text-xs font-bold bg-black/40 backdrop-blur-sm text-white px-2 py-1 rounded-full">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"/></svg>
                <?= $lessons ?>
            </span>
            <?php endif; ?>
            <?php if ($quizCount > 0): ?>
            <span class="inline-flex items-center gap-1 text-xs font-bold bg-black/40 backdrop-blur-sm text-white px-2 py-1 rounded-full">
                📝 <?= $quizCount ?>
            </span>
            <?php endif; ?>
        </div>
    </a>

    <!-- Contenido -->
    <div class="p-5 flex flex-col flex-1">
        <?php if (!empty($course['category_name'])): ?>
        <span class="text-xs font-bold text-brand-500 uppercase tracking-wider mb-1.5">
            <?= htmlspecialchars($course['category_name']) ?>
        </span>
        <?php endif; ?>

        <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>">
            <h3 class="font-black text-navy text-sm leading-snug hover:text-brand-500 transition-colors line-clamp-2 mb-2">
                <?= htmlspecialchars($course['title']) ?>
            </h3>
        </a>

        <?php if (!empty($course['description'])): ?>
        <p class="text-xs text-gray-400 line-clamp-2 mb-3 leading-relaxed">
            <?= htmlspecialchars(mb_substr(strip_tags($course['description']), 0, 100)) ?>
        </p>
        <?php endif; ?>

        <?php if (!empty($course['teacher_name'])): ?>
        <div class="flex items-center gap-2 mb-4">
            <div class="w-5 h-5 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-xs font-black flex-shrink-0">
                <?= mb_strtoupper(mb_substr($course['teacher_name'], 0, 1)) ?>
            </div>
            <p class="text-xs text-gray-500 font-semibold truncate"><?= htmlspecialchars($course['teacher_name']) ?></p>
        </div>
        <?php endif; ?>

        <div class="mt-auto pt-3 border-t border-gray-100">
            <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>"
               class="flex items-center justify-center gap-2 w-full bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all hover:-translate-y-0.5 shadow-md shadow-brand-500/20 group-hover:shadow-brand-500/40">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <?= __('courses.card.view_course') ?>
            </a>
        </div>
    </div>
</div>
