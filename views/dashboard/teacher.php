<?php
$totalCourses   = count($courses);
$totalPublished = count(array_filter($courses, fn($c) => $c['status'] === 'published'));
$totalDraft     = $totalCourses - $totalPublished;
?>

<div class="max-w-7xl mx-auto px-4 py-10">

    <!-- Header -->
    <div class="mb-8 flex items-end justify-between">
        <div>
            <h1 class="text-3xl font-black text-navy"><?= __('dashboard.teacher.greeting', ['name' => htmlspecialchars(explode(' ', $user['name'])[0])]) ?></h1>
            <p class="text-gray-500 text-sm mt-1"><?= __('dashboard.teacher.subtitle') ?></p>
        </div>
        <a href="/cursos/crear"
           class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <?= __('dashboard.teacher.new_course') ?>
        </a>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        <!-- ─── Panel izquierdo ──────────────────────────────── -->
        <aside class="w-full lg:w-56 flex-shrink-0 space-y-4">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sticky top-6">
                <h3 class="font-black text-navy text-sm mb-4 uppercase tracking-wider"><?= __('dashboard.teacher.my_courses') ?></h3>

                <ul class="space-y-1">
                    <li>
                        <div class="flex items-center justify-between text-sm px-3 py-2 rounded-xl bg-brand-500 text-white shadow-lg shadow-brand-500/25">
                            <span class="font-semibold"><?= __('dashboard.teacher.total') ?></span>
                            <span class="font-black"><?= $totalCourses ?></span>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center justify-between text-sm px-3 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                            <span class="font-semibold text-gray-600"><?= __('dashboard.teacher.published') ?></span>
                            <span class="font-black text-emerald-600"><?= $totalPublished ?></span>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center justify-between text-sm px-3 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                            <span class="font-semibold text-gray-600"><?= __('dashboard.teacher.drafts') ?></span>
                            <span class="font-black text-amber-600"><?= $totalDraft ?></span>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center justify-between text-sm px-3 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                            <span class="font-semibold text-gray-600"><?= __('dashboard.teacher.students') ?></span>
                            <span class="font-black text-purple-600"><?= number_format($total_students) ?></span>
                        </div>
                    </li>
                </ul>

                <div class="mt-4 pt-4 border-t border-gray-100 space-y-1">
                    <a href="/cursos/crear" class="flex items-center justify-between text-sm px-3 py-2 rounded-xl font-semibold text-gray-600 hover:bg-slate-50 transition-colors">
                        <span><?= __('dashboard.teacher.create_course') ?></span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="/cursos" class="flex items-center justify-between text-sm px-3 py-2 rounded-xl font-semibold text-gray-600 hover:bg-slate-50 transition-colors">
                        <span><?= __('dashboard.teacher.view_catalog') ?></span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="/dashboard/perfil" class="flex items-center justify-between text-sm px-3 py-2 rounded-xl font-semibold text-gray-600 hover:bg-slate-50 transition-colors">
                        <span><?= __('dashboard.teacher.my_profile') ?></span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </aside>

        <!-- ─── Mis cursos ────────────────────────────────────── -->
        <div class="flex-1 min-w-0">

            <?php if (empty($courses)): ?>
            <div class="py-20 text-center bg-white rounded-2xl border border-gray-100 shadow-sm">
                <div class="text-5xl mb-4">🚢</div>
                <p class="text-gray-500 font-semibold text-lg mb-2"><?= __('dashboard.teacher.empty_title') ?></p>
                <p class="text-sm text-gray-400 mb-6"><?= __('dashboard.teacher.empty_desc') ?></p>
                <a href="/cursos/crear"
                   class="inline-flex items-center gap-2 bg-brand-500 text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-brand-600 transition-all shadow-lg shadow-brand-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <?= __('dashboard.teacher.create_first_course') ?>
                </a>
            </div>
            <?php else: ?>

            <div class="flex items-center justify-between mb-6">
                <p class="text-sm text-gray-500 font-semibold">
                    <span class="text-navy font-black"><?= $totalCourses ?></span> <?= __($totalCourses !== 1 ? 'dashboard.teacher.courses_created_many' : 'dashboard.teacher.courses_created_one') ?>
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php foreach ($courses as $c):
                    $isPublished = $c['status'] === 'published';
                ?>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-xl hover:border-brand-100 transition-all hover:-translate-y-1 card-hover group flex flex-col">

                    <!-- Banner -->
                    <div class="relative h-40 overflow-hidden flex-shrink-0"
                         style="background:linear-gradient(135deg,#1a3a6b 0%,#0f4c8a 100%);">
                        <?php $_img = cms_asset($c['image'] ?? null); ?>
                        <?php if ($_img !== ''): ?>
                            <img src="<?= htmlspecialchars($_img) ?>" class="w-full h-full object-cover" alt="">
                            <div class="absolute inset-0 bg-navy/30"></div>
                        <?php else: ?>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-white/10 text-8xl font-black"><?= mb_strtoupper(mb_substr($c['title'], 0, 1)) ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Status -->
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full shadow
                                <?= $isPublished ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-amber-900' ?>">
                                <span class="w-1.5 h-1.5 rounded-full <?= $isPublished ? 'bg-white animate-pulse' : 'bg-amber-700' ?>"></span>
                                <?= $isPublished ? __('dashboard.teacher.status_published') : __('dashboard.teacher.status_draft') ?>
                            </span>
                        </div>

                        <!-- Nivel -->
                        <?php if (!empty($c['level'])): ?>
                        <div class="absolute top-3 right-3">
                            <span class="text-xs font-bold bg-white/20 text-white px-2 py-1 rounded-full backdrop-blur-sm">
                                <?= ucfirst(htmlspecialchars($c['level'])) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Contenido -->
                    <div class="p-5 flex flex-col flex-1">
                        <a href="/cursos/<?= htmlspecialchars($c['slug']) ?>"
                           class="font-black text-navy text-sm leading-snug hover:text-brand-500 transition-colors line-clamp-2 group-hover:text-brand-500 block mb-1">
                            <?= htmlspecialchars($c['title']) ?>
                        </a>
                        <?php if (!empty($c['category_name'])): ?>
                        <p class="text-xs text-gray-400 mb-3"><?= htmlspecialchars($c['category_name']) ?></p>
                        <?php else: ?>
                        <div class="mb-3"></div>
                        <?php endif; ?>

                        <!-- Stats -->
                        <div class="flex items-center gap-4 text-xs text-gray-500 mb-4">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                                <span class="font-bold text-gray-700"><?= (int)($c['total_students'] ?? 0) ?></span> <?= __('dashboard.teacher.students_count_label') ?>
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span class="font-bold text-gray-700"><?= (int)($c['total_lessons'] ?? 0) ?></span> <?= __('dashboard.teacher.lessons_count_label') ?>
                            </span>
                        </div>

                        <!-- Acciones -->
                        <div class="mt-auto flex gap-2">
                            <a href="/cursos/<?= $c['id'] ?>/editar"
                               class="flex-1 text-center py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                <?= __('dashboard.teacher.edit') ?>
                            </a>
                            <a href="/cursos/<?= $c['id'] ?>/lecciones/crear"
                               class="flex-1 text-center py-2 rounded-xl text-xs font-bold bg-brand-50 text-brand-500 hover:bg-brand-100 transition-colors">
                                <?= __('dashboard.teacher.add_lesson') ?>
                            </a>
                            <a href="/cursos/<?= $c['id'] ?>/quizzes/crear"
                               class="flex-1 text-center py-2 rounded-xl text-xs font-bold bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                <?= __('dashboard.teacher.add_quiz') ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
