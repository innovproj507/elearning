<?php
$totalEnrolled  = count($enrollments);
$totalCompleted = count(array_filter($enrollments, fn($e) => $e['total_lessons'] > 0 && $e['completed_lessons'] >= $e['total_lessons']));
$totalLessonsOk = array_sum(array_column($enrollments, 'completed_lessons'));
$totalLessons   = array_sum(array_column($enrollments, 'total_lessons'));
$overallPct     = $totalLessons > 0 ? round(($totalLessonsOk / $totalLessons) * 100) : 0;
$inProgress     = $totalEnrolled - $totalCompleted;

// Saludo según la hora local
$_h = (int) date('G');
$saludo = $_h < 12
    ? __('dashboard.student.good_morning')
    : ($_h < 19 ? __('dashboard.student.good_afternoon') : __('dashboard.student.good_evening'));
?>

<div class="max-w-7xl mx-auto px-4 py-8">

    <!-- Saludo -->
    <div class="card card-pad mb-6 relative overflow-hidden"
         style="background:linear-gradient(110deg,#eaf1ff 0%,#f6f9ff 55%,#fff 100%)">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-slate-500"><?= $saludo ?>, 👋</p>
                <h1 class="text-2xl font-black text-navy mt-0.5">
                    <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>
                </h1>
                <p class="text-sm text-slate-500 mt-1.5"><?= __('dashboard.student.subtitle') ?></p>
            </div>
            <a href="/cursos"
               class="inline-flex items-center justify-center gap-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5 flex-shrink-0 self-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <?= __('dashboard.student.explore_courses') ?>
            </a>
        </div>
        <svg class="absolute -right-6 -bottom-8 w-40 h-40 text-brand-500/8 z-0 hidden sm:block" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.42A12 12 0 0112 21a12 12 0 01-6.16-10.42L12 14z"/></svg>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        <!-- ─── Mi Progreso ───────────────────────────────────── -->
        <aside class="w-full lg:w-64 flex-shrink-0">
            <div class="card card-pad sticky top-6">
                <h3 class="font-black text-navy text-sm mb-4 uppercase tracking-wider"><?= __('dashboard.student.my_progress') ?></h3>

                <!-- Círculo de avance -->
                <div class="flex justify-center mb-5">
                    <div class="relative w-32 h-32">
                        <svg class="w-32 h-32 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#eef2f9" stroke-width="3"/>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#2563eb" stroke-width="3"
                                    stroke-dasharray="<?= round($overallPct * 99.9 / 100, 1) ?> 100"
                                    stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-black text-navy"><?= $overallPct ?>%</span>
                            <span class="text-[11px] text-gray-400 font-medium"><?= __('dashboard.student.overall') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Resumen -->
                <ul class="space-y-0.5">
                    <li>
                        <div class="stat-row is-active">
                            <span class="label"><?= __('dashboard.student.enrolled') ?></span>
                            <span class="value text-brand-600"><?= $totalEnrolled ?></span>
                        </div>
                    </li>
                    <li>
                        <div class="stat-row">
                            <span class="label"><?= __('dashboard.student.in_progress') ?></span>
                            <span class="value text-amber-600"><?= $inProgress ?></span>
                        </div>
                    </li>
                    <li>
                        <div class="stat-row">
                            <span class="label"><?= __('dashboard.student.completed') ?></span>
                            <span class="value text-emerald-600"><?= $totalCompleted ?></span>
                        </div>
                    </li>
                    <li>
                        <div class="stat-row">
                            <span class="label"><?= __('dashboard.student.lessons') ?></span>
                            <span class="value text-violet-600"><?= $totalLessonsOk ?>/<?= $totalLessons ?></span>
                        </div>
                    </li>
                </ul>

                <!-- Accesos -->
                <div class="mt-4 pt-4 border-t space-y-0.5" style="border-color:var(--card-border)">
                    <a href="/cursos" class="stat-row">
                        <span class="label"><?= __('dashboard.student.view_catalog') ?></span>
                        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="/dashboard/perfil" class="stat-row">
                        <span class="label"><?= __('dashboard.student.my_profile') ?></span>
                        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </aside>

        <!-- ─── Cursos inscritos ──────────────────────────────── -->
        <div class="flex-1 min-w-0">

            <?php if (empty($enrollments)): ?>
            <div class="card py-20 text-center">
                <div class="text-5xl mb-4">🚢</div>
                <p class="text-gray-500 font-semibold text-lg mb-2"><?= __('dashboard.student.empty_title') ?></p>
                <p class="text-sm text-gray-400 mb-6"><?= __('dashboard.student.empty_desc') ?></p>
                <a href="/cursos" class="inline-flex items-center gap-2 bg-brand-500 text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-brand-600 transition-all shadow-lg shadow-brand-500/25">
                    <?= __('dashboard.student.explore_courses') ?>
                </a>
            </div>
            <?php else: ?>

            <div class="flex items-center justify-between mb-5">
                <p class="text-sm text-gray-500 font-semibold">
                    <span class="text-navy font-black"><?= $totalEnrolled ?></span> <?= __($totalEnrolled !== 1 ? 'dashboard.student.courses_enrolled_many' : 'dashboard.student.courses_enrolled_one') ?>
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                <?php foreach ($enrollments as $e):
                    $total     = (int) $e['total_lessons'];
                    $completed = (int) $e['completed_lessons'];
                    $pct       = $total > 0 ? round(($completed / $total) * 100) : 0;
                    $isDone    = $pct >= 100;
                ?>
                <div class="card overflow-hidden hover:shadow-xl transition-all hover:-translate-y-1 card-hover group flex flex-col">

                    <!-- Banner -->
                    <div class="relative h-40 overflow-hidden flex-shrink-0"
                         style="background:linear-gradient(135deg,#1a3a6b 0%,#0f4c8a 100%);">
                        <?php $_img = cms_asset($e['image'] ?? null); ?>
                        <?php if ($_img !== ''): ?>
                            <img src="<?= htmlspecialchars($_img) ?>" class="w-full h-full object-cover" alt="">
                            <div class="absolute inset-0 bg-navy/30"></div>
                        <?php else: ?>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-16 h-16 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                        <?php endif; ?>

                        <!-- Badge estado -->
                        <div class="absolute top-3 right-3">
                            <?php if ($isDone): ?>
                            <span class="inline-flex items-center gap-1 text-xs font-bold bg-emerald-500 text-white px-2.5 py-1 rounded-full shadow">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <?= __('dashboard.student.status_completed') ?>
                            </span>
                            <?php elseif ($pct > 0): ?>
                            <span class="inline-flex items-center gap-1 text-xs font-bold bg-amber-400 text-amber-900 px-2.5 py-1 rounded-full shadow">
                                <?= __('dashboard.student.status_in_progress') ?>
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center gap-1 text-xs font-bold bg-white/20 text-white px-2.5 py-1 rounded-full backdrop-blur-sm">
                                <?= __('dashboard.student.status_available') ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Barra de progreso sobre imagen -->
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-black/20">
                            <div class="h-1 <?= $isDone ? 'bg-emerald-400' : 'bg-brand-400' ?> transition-all" style="width:<?= $pct ?>%"></div>
                        </div>
                    </div>

                    <!-- Contenido -->
                    <div class="p-5 flex flex-col flex-1">
                        <a href="/cursos/<?= htmlspecialchars($e['slug']) ?>"
                           class="font-black text-navy text-sm leading-snug hover:text-brand-500 transition-colors line-clamp-2 group-hover:text-brand-500 block mb-1">
                            <?= htmlspecialchars($e['title']) ?>
                        </a>
                        <?php /*
                            Sin instructor asignado la línea queda en blanco en
                            vez de un «Por» suelto, y se conserva para que todas
                            las tarjetas midan lo mismo.
                        */ ?>
                        <p class="text-xs text-gray-400 mb-3"><?= !empty($e['teacher_name'])
                            ? __('dashboard.student.by_teacher', ['name' => htmlspecialchars($e['teacher_name'])])
                            : '&nbsp;' ?></p>

                        <div class="flex items-center gap-2 mb-1">
                            <div class="flex-1 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full <?= $isDone ? 'bg-emerald-500' : 'bg-brand-500' ?>" style="width:<?= $pct ?>%"></div>
                            </div>
                            <span class="text-xs font-black <?= $isDone ? 'text-emerald-600' : 'text-brand-500' ?> w-8 text-right"><?= $pct ?>%</span>
                        </div>
                        <p class="text-xs text-gray-400 mb-4"><?= __('dashboard.student.lessons_count', ['completed' => $completed, 'total' => $total]) ?></p>

                        <div class="mt-auto">
                            <a href="/cursos/<?= htmlspecialchars($e['slug']) ?>"
                               class="block w-full text-center py-2.5 rounded-xl text-sm font-bold transition-all
                                      <?= $isDone
                                          ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200'
                                          : 'bg-brand-500 text-white hover:bg-brand-600 shadow-lg shadow-brand-500/25 hover:-translate-y-0.5' ?>">
                                <?= $isDone ? __('dashboard.student.view_course') : __('dashboard.student.continue_course') ?>
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
