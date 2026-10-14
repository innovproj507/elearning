<?php
$currentIndex   = array_search($lesson['id'], array_column($allLessons, 'id'));
$prevLesson     = $allLessons[$currentIndex - 1] ?? null;
$nextLesson     = $allLessons[$currentIndex + 1] ?? null;
$totalActive    = count(array_filter($allLessons, fn($l) => $l['is_active']));
$wordCount      = str_word_count(strip_tags($lesson['content'] ?? ''));
$readingMinutes = max(1, (int) ceil($wordCount / 200));
?>

<!-- Barra de progreso de lectura (sticky top) -->
<div id="reading-bar" class="fixed top-0 left-0 right-0 z-50 h-1 bg-gray-200">
    <div id="reading-progress" class="h-1 bg-gradient-to-r from-brand-500 to-blue-400 transition-all duration-100" style="width:0%"></div>
</div>

<style>
/* Tipografía del contenido de la lección */
.lesson-body h1,.lesson-body h2{font-size:1.35rem;font-weight:900;color:#0f172a;margin:2rem 0 .75rem;padding-bottom:.5rem;border-bottom:2px solid #e2e8f0;}
.lesson-body h3{font-size:1.1rem;font-weight:800;color:#1e40af;margin:1.75rem 0 .5rem;}
.lesson-body h4{font-size:.95rem;font-weight:700;color:#374151;margin:1.5rem 0 .4rem;}
.lesson-body p{margin:.85rem 0;line-height:1.85;color:#374151;font-size:.95rem;}
.lesson-body strong,.lesson-body b{color:#0f172a;font-weight:700;}
.lesson-body ul{list-style:none;padding:0;margin:.75rem 0;}
.lesson-body ul li{padding:.35rem 0 .35rem 1.75rem;position:relative;color:#374151;font-size:.93rem;}
.lesson-body ul li::before{content:"";position:absolute;left:0;top:.85rem;width:8px;height:8px;border-radius:50%;background:#1a56db;}
.lesson-body ol{counter-reset:ol-counter;padding:0;margin:.75rem 0;}
.lesson-body ol li{counter-increment:ol-counter;padding:.35rem 0 .35rem 2rem;position:relative;color:#374151;font-size:.93rem;}
.lesson-body ol li::before{content:counter(ol-counter);position:absolute;left:0;top:.2rem;width:1.4rem;height:1.4rem;border-radius:50%;background:#1a56db;color:#fff;font-size:.7rem;font-weight:900;display:flex;align-items:center;justify-content:center;}
.lesson-body blockquote{border-left:4px solid #1a56db;background:#eff6ff;padding:1rem 1.25rem;border-radius:0 .75rem .75rem 0;margin:1.25rem 0;color:#1e40af;font-style:italic;}
.lesson-body a{color:#1a56db;font-weight:600;text-decoration:underline;}
.lesson-body table{width:100%;border-collapse:collapse;margin:1.25rem 0;font-size:.88rem;border-radius:.75rem;overflow:hidden;}
.lesson-body th{background:#1a3a6b;color:#fff;padding:.65rem 1rem;text-align:left;font-weight:700;}
.lesson-body td{padding:.6rem 1rem;border-bottom:1px solid #e2e8f0;color:#374151;}
.lesson-body tr:nth-child(even) td{background:#f8fafc;}
.lesson-body img{max-width:100%;height:auto;border-radius:.75rem;margin:1rem auto;display:block;cursor:zoom-in;box-shadow:0 4px 20px rgba(0,0,0,.1);transition:transform .2s;}
.lesson-body img:hover{transform:scale(1.01);}
.lesson-body hr{border:none;border-top:2px solid #e2e8f0;margin:2rem 0;}
/* Callout highlight */
.lesson-body .callout{background:#fefce8;border:1px solid #fde68a;border-radius:.75rem;padding:1rem 1.25rem;margin:1.25rem 0;}

/* Lightbox */
#lightbox{display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:9999;align-items:center;justify-content:center;cursor:zoom-out;}
#lightbox.active{display:flex;}
#lightbox img{max-width:90vw;max-height:90vh;border-radius:.75rem;box-shadow:0 25px 60px rgba(0,0,0,.5);}
</style>

<div class="max-w-7xl mx-auto px-4 py-8">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="/cursos" class="hover:text-brand-500 font-semibold transition-colors"><?= __('lessons.show.breadcrumb_catalog') ?></a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>" class="hover:text-brand-500 font-semibold transition-colors truncate max-w-xs"><?= htmlspecialchars($course['title']) ?></a>
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-navy font-semibold truncate"><?= htmlspecialchars($lesson['title']) ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- ─── Columna principal ──────────────────────────── -->
        <div class="lg:col-span-2 space-y-5">

            <!-- Banner hero -->
            <div class="rounded-2xl overflow-hidden shadow-lg"
                 style="background:linear-gradient(135deg,#0a1628 0%,#1a3a6b 60%,#1a56db 100%);">
                <div class="p-7">
                    <p class="text-blue-300 text-xs font-bold uppercase tracking-widest mb-2">
                        <?= htmlspecialchars($course['title']) ?>
                    </p>
                    <h1 class="text-2xl font-black text-white leading-snug mb-4">
                        <?= htmlspecialchars($lesson['title']) ?>
                    </h1>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-white/15 text-white backdrop-blur-sm">
                            <?php if ($lesson['type'] === 'video'): ?>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <?= __('lessons.show.type_video') ?>
                            <?php elseif ($lesson['type'] === 'file'): ?>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <?= __('lessons.show.type_file') ?>
                            <?php else: ?>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <?= __('lessons.show.type_reading') ?>
                            <?php endif; ?>
                        </span>
                        <?php if ($lesson['duration']): ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-white/15 text-white backdrop-blur-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <?= $lesson['duration'] ?> min
                        </span>
                        <?php elseif ($lesson['type'] === 'text' && $wordCount > 0): ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-white/15 text-white backdrop-blur-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            ~<?= __('lessons.show.reading_minutes', ['minutes' => $readingMinutes]) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($isCompleted): ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-emerald-500/40 text-emerald-100">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <?= __('lessons.show.completed_badge') ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Vídeo -->
            <?php if ($lesson['type'] === 'video' && $lesson['video_url']): ?>
            <div class="aspect-video rounded-2xl overflow-hidden bg-navy shadow-xl">
                <?php
                $url = $lesson['video_url'];
                if (preg_match('/youtube\.com\/watch\?v=([^&]+)|youtu\.be\/([^?]+)/', $url, $m)) {
                    $vid = $m[1] ?: $m[2];
                    echo "<iframe src='https://www.youtube.com/embed/{$vid}?rel=0' class='w-full h-full' allowfullscreen></iframe>";
                } elseif (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
                    echo "<iframe src='https://player.vimeo.com/video/{$m[1]}' class='w-full h-full' allowfullscreen></iframe>";
                } else {
                    echo "<a href='" . htmlspecialchars($url) . "' class='flex items-center justify-center h-full text-white gap-2 font-bold hover:text-blue-300 transition-colors'>" . __('lessons.show.view_external_video') . " →</a>";
                }
                ?>
            </div>
            <?php endif; ?>

            <!-- Archivo descargable -->
            <?php if ($lesson['type'] === 'file' && $lesson['file_path']): ?>
            <div class="flex items-center gap-4 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl shadow-sm">
                <div class="w-14 h-14 rounded-2xl bg-blue-500 flex items-center justify-center text-white text-3xl flex-shrink-0 shadow-lg">📄</div>
                <div class="flex-1">
                    <p class="font-black text-navy text-sm mb-0.5"><?= __('lessons.show.downloadable_material') ?></p>
                    <p class="text-xs text-gray-500 mb-3"><?= __('lessons.show.downloadable_material_help') ?></p>
                    <?php /*
                        cms_asset y no la ruta tal cual: el archivo vive en el
                        CMS, que es otro sitio. Una ruta relativa se resolvía
                        contra el dominio del portal y daba 404 al estudiante.
                    */ ?>
                    <a href="<?= htmlspecialchars(cms_asset($lesson['file_path'])) ?>" download
                       class="inline-flex items-center gap-2 text-sm font-bold bg-blue-500 text-white px-5 py-2.5 rounded-xl hover:bg-blue-600 transition-all shadow-md shadow-blue-500/25 hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <?= __('lessons.show.download_file') ?>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Contenido de la lección -->
            <?php if ($lesson['content']): ?>
            <div id="lesson-content" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                <!-- Barra superior del artículo -->
                <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                    </div>
                    <?php if ($wordCount > 0): ?>
                    <span class="text-xs text-gray-400 font-semibold"><?= __('lessons.show.word_count_summary', ['count' => number_format($wordCount), 'minutes' => $readingMinutes]) ?></span>
                    <?php endif; ?>
                    <div class="flex items-center gap-1.5">
                        <button onclick="changeFontSize(-1)" class="w-7 h-7 rounded-lg bg-white border border-gray-200 text-xs font-bold text-gray-500 hover:bg-gray-100 flex items-center justify-center transition-colors" title="<?= htmlspecialchars(__('lessons.show.decrease_text'), ENT_QUOTES) ?>">A-</button>
                        <button onclick="changeFontSize(1)"  class="w-7 h-7 rounded-lg bg-white border border-gray-200 text-sm font-bold text-gray-500 hover:bg-gray-100 flex items-center justify-center transition-colors" title="<?= htmlspecialchars(__('lessons.show.increase_text'), ENT_QUOTES) ?>">A+</button>
                    </div>
                </div>

                <!-- Cuerpo del artículo -->
                <div class="lesson-body px-8 py-7" id="lesson-body">
                    <?= $lesson['content'] ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Banner de completar (aparece al llegar al final) -->
            <?php if (!$isCompleted): ?>
            <div id="complete-cta" class="hidden">
                <?php if (!empty($lessonQuiz) && !$lessonQuizPassed && !$isManager): ?>
                <div class="bg-gradient-to-r from-amber-500 to-yellow-500 rounded-2xl p-6 flex flex-col sm:flex-row items-center gap-4 shadow-xl shadow-amber-500/25">
                    <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center flex-shrink-0 text-3xl">📝</div>
                    <div class="flex-1 text-center sm:text-left">
                        <p class="font-black text-white text-lg"><?= __('lessons.show.quiz_ready_title') ?></p>
                        <p class="text-amber-100 text-sm mt-0.5"><?= __('lessons.show.quiz_ready_help') ?></p>
                    </div>
                    <a href="/cursos/<?= $course['id'] ?>/quizzes/<?= $lessonQuiz['id'] ?>"
                       class="flex-shrink-0 bg-white text-amber-600 font-black px-6 py-3 rounded-xl text-sm hover:bg-amber-50 transition-all shadow-lg">
                        <?= __('lessons.show.take_quiz') ?> →
                    </a>
                </div>
                <?php else: ?>
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl p-6 flex flex-col sm:flex-row items-center gap-4 shadow-xl shadow-emerald-500/25">
                    <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center flex-shrink-0 text-3xl">🎉</div>
                    <div class="flex-1 text-center sm:text-left">
                        <p class="font-black text-white text-lg"><?= __('lessons.show.reached_end_title') ?></p>
                        <p class="text-emerald-100 text-sm mt-0.5"><?= __('lessons.show.reached_end_help') ?></p>
                    </div>
                    <form method="POST" action="/cursos/<?= $course['id'] ?>/lecciones/<?= $lesson['id'] ?>/completar">
                        <?= \App\Core\CSRF::field() ?>
                        <button type="submit" class="flex-shrink-0 bg-white text-emerald-600 font-black px-6 py-3 rounded-xl text-sm hover:bg-emerald-50 transition-all shadow-lg">
                            <?= __('lessons.show.mark_complete_check') ?>
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Quiz de la lección -->
            <?php if (!empty($lessonQuiz)): ?>
            <div id="lesson-quiz-section" class="bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-200 rounded-2xl p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white text-2xl shadow-md flex-shrink-0">📝</div>
                        <div>
                            <p class="text-xs font-bold text-amber-600 uppercase tracking-wider mb-0.5"><?= __('lessons.show.lesson_quiz_label') ?></p>
                            <h3 class="font-black text-navy text-base"><?= htmlspecialchars($lessonQuiz['title']) ?></h3>
                            <?php if (!empty($lessonQuiz['description'])): ?>
                            <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($lessonQuiz['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($isManager): ?>
                    <button type="button" onclick="document.getElementById('lesson-quiz-edit').classList.toggle('hidden')"
                        class="flex-shrink-0 text-xs font-bold text-amber-600 hover:text-amber-800 bg-amber-100 hover:bg-amber-200 px-3 py-1.5 rounded-lg transition-colors">
                        <?= __('common.edit') ?>
                    </button>
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-4 mt-4 pt-4 border-t border-amber-200">
                    <span class="text-xs text-amber-700 font-semibold"><?= __('lessons.show.pass_percentage_label', ['percent' => $lessonQuiz['pass_percentage']]) ?></span>
                    <?php if ($lessonQuiz['time_limit'] > 0): ?>
                    <span class="text-xs text-amber-700 font-semibold">⏱ <?= __('lessons.show.minutes_short', ['minutes' => $lessonQuiz['time_limit']]) ?></span>
                    <?php endif; ?>
                    <span class="text-xs text-amber-700 font-semibold"><?= __('lessons.show.questions_count', ['count' => count($lessonQuiz['questions'] ?? [])]) ?></span>

                    <?php if ($isManager || $isCompleted): ?>
                    <a href="/cursos/<?= $course['id'] ?>/quizzes/<?= $lessonQuiz['id'] ?>"
                       class="ml-auto inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-all shadow-md shadow-amber-500/25 hover:-translate-y-0.5">
                        <?= $isManager ? __('lessons.show.view_quiz') : __('lessons.show.take_quiz') ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <?php else: ?>
                    <div class="ml-auto flex items-center gap-2 text-xs text-amber-600 font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <?= __('lessons.show.available_on_complete') ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Panel edición inline (managers) -->
                <?php if ($isManager): ?>
                <div id="lesson-quiz-edit" class="hidden mt-4 pt-4 border-t border-amber-200">
                    <form method="POST" action="/cursos/<?= $course['id'] ?>/quizzes/<?= $lessonQuiz['id'] ?>/editar"
                          id="lesson-quiz-edit-form" class="space-y-4">
                        <?= \App\Core\CSRF::field() ?>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('lessons.show.title_label') ?></label>
                                <input type="text" name="title" required value="<?= htmlspecialchars($lessonQuiz['title'], ENT_QUOTES) ?>"
                                    class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-amber-400 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('lessons.show.pass_percentage_short') ?></label>
                                <input type="number" name="pass_percentage" min="0" max="100" value="<?= $lessonQuiz['pass_percentage'] ?>"
                                    class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-amber-400 bg-white">
                            </div>
                        </div>
                        <!-- Agregar nuevas preguntas -->
                        <div class="pt-3 border-t border-amber-100">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-bold text-gray-700"><?= __('lessons.show.add_questions') ?></p>
                                <button type="button" onclick="addLQQuestion()"
                                    class="text-xs font-bold bg-amber-100 hover:bg-amber-200 text-amber-700 px-3 py-1.5 rounded-lg transition-colors">
                                    + <?= __('lessons.show.new_question') ?>
                                </button>
                            </div>
                            <div id="lq-questions-container" class="space-y-3"></div>
                        </div>
                        <div class="flex gap-3 pt-2 border-t border-amber-100">
                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-all">
                                <?= __('common.save') ?>
                            </button>
                            <button type="button" onclick="document.getElementById('lesson-quiz-edit').classList.add('hidden')"
                                class="bg-white hover:bg-gray-100 text-gray-700 font-bold px-5 py-2.5 rounded-xl text-sm border border-gray-200 transition-all">
                                <?= __('common.cancel') ?>
                            </button>
                        </div>
                    </form>
                    <!-- Preguntas existentes -->
                    <?php $lqQuestions = $lessonQuiz['questions'] ?? []; ?>
                    <?php if (!empty($lqQuestions)): ?>
                    <div class="mt-4 pt-4 border-t border-amber-100">
                        <p class="text-xs font-bold text-gray-700 mb-3"><?= __('lessons.show.questions_with_count', ['count' => count($lqQuestions)]) ?></p>
                        <div class="space-y-2">
                            <?php foreach ($lqQuestions as $qi => $eq): ?>
                            <div class="bg-white rounded-xl border border-amber-100 p-3 flex items-start gap-3">
                                <span class="w-5 h-5 rounded-md bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-black flex-shrink-0 mt-0.5"><?= $qi + 1 ?></span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($eq['question'] ?? '') ?></p>
                                    <ul class="mt-1 space-y-0.5">
                                        <?php foreach ($eq['options'] ?? [] as $opt): ?>
                                        <li class="text-xs flex items-center gap-1.5 <?= $opt['is_correct'] ? 'text-emerald-700 font-bold' : 'text-gray-400' ?>">
                                            <?php if ($opt['is_correct']): ?>
                                            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            <?php else: ?>
                                            <span class="w-3 h-3 rounded-full border border-gray-300 flex-shrink-0"></span>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($opt['option_text'] ?? '') ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <form method="POST" action="/cursos/<?= $course['id'] ?>/quizzes/<?= $lessonQuiz['id'] ?>/preguntas/<?= $eq['id'] ?>/eliminar"
                                      onsubmit="return confirm('<?= __('common.confirm_delete') ?>')" class="flex-shrink-0">
                                    <?= \App\Core\CSRF::field() ?>
                                    <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php elseif ($isManager): ?>
            <!-- Managers: crear quiz para esta lección -->
            <div class="border-2 border-dashed border-amber-200 rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-xl">📝</div>
                        <div>
                            <p class="font-bold text-navy text-sm"><?= __('lessons.show.no_quiz_title') ?></p>
                            <p class="text-xs text-gray-400"><?= __('lessons.show.no_quiz_help') ?></p>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('create-lesson-quiz').classList.toggle('hidden')"
                        class="text-sm font-bold text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 px-4 py-2 rounded-xl transition-colors">
                        + <?= __('lessons.show.add_quiz') ?>
                    </button>
                </div>
                <div id="create-lesson-quiz" class="hidden mt-4 pt-4 border-t border-amber-100">
                    <form method="POST" action="/cursos/<?= $course['id'] ?>/quizzes/crear" id="create-lq-form" class="space-y-4">
                        <?= \App\Core\CSRF::field() ?>
                        <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('lessons.show.title_label') ?> <span class="text-red-500">*</span></label>
                                <input type="text" name="title" required placeholder="<?= htmlspecialchars(__('lessons.show.quiz_title_placeholder'), ENT_QUOTES) ?>"
                                    class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-amber-400 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('lessons.show.pass_percentage_short') ?></label>
                                <input type="number" name="pass_percentage" value="70" min="0" max="100"
                                    class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-amber-400 bg-white">
                            </div>
                        </div>
                        <!-- Agregar preguntas al crear -->
                        <div class="pt-3 border-t border-amber-100">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-bold text-gray-700"><?= __('lessons.show.questions_optional') ?></p>
                                <button type="button" onclick="addCreateLQQuestion()"
                                    class="text-xs font-bold bg-amber-100 hover:bg-amber-200 text-amber-700 px-3 py-1.5 rounded-lg transition-colors">
                                    + <?= __('common.add') ?>
                                </button>
                            </div>
                            <div id="create-lq-container" class="space-y-3"></div>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-5 py-2.5 rounded-xl text-sm">
                                <?= __('lessons.show.create_quiz') ?>
                            </button>
                            <button type="button" onclick="document.getElementById('create-lesson-quiz').classList.add('hidden')"
                                class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold px-5 py-2.5 rounded-xl text-sm">
                                <?= __('common.cancel') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Navegación anterior / siguiente -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center justify-between gap-4">
                <?php if ($prevLesson): ?>
                <a href="/cursos/<?= $course['id'] ?>/lecciones/<?= $prevLesson['id'] ?>"
                   class="flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-brand-500 transition-colors min-w-0">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span class="truncate hidden sm:block"><?= htmlspecialchars($prevLesson['title']) ?></span>
                    <span class="sm:hidden"><?= __('lessons.show.previous') ?></span>
                </a>
                <?php else: ?><div></div><?php endif; ?>

                <?php if ($isCompleted): ?>
                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 font-bold px-4 py-2 rounded-xl text-xs border border-emerald-200 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <?= __('lessons.show.completed_badge') ?>
                </span>
                <?php elseif (!empty($lessonQuiz) && !$lessonQuizPassed && !$isManager): ?>
                <a href="/cursos/<?= $course['id'] ?>/quizzes/<?= $lessonQuiz['id'] ?>"
                   class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold px-4 py-2 rounded-xl text-xs transition-all shadow-md shadow-amber-500/25 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <?= __('lessons.show.take_quiz') ?>
                </a>
                <?php else: ?>
                <form method="POST" action="/cursos/<?= $course['id'] ?>/lecciones/<?= $lesson['id'] ?>/completar" class="flex-shrink-0">
                    <?= \App\Core\CSRF::field() ?>
                    <button type="submit" class="inline-flex items-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold px-4 py-2 rounded-xl text-xs transition-all shadow-md shadow-emerald-500/25">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?= __('lessons.show.mark_complete') ?>
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($nextLesson): ?>
                <a href="/cursos/<?= $course['id'] ?>/lecciones/<?= $nextLesson['id'] ?>"
                   class="flex items-center gap-2 text-sm font-bold text-brand-500 hover:text-brand-600 transition-colors min-w-0">
                    <span class="truncate hidden sm:block"><?= htmlspecialchars($nextLesson['title']) ?></span>
                    <span class="sm:hidden"><?= __('lessons.show.next') ?></span>
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <?php else: ?><div></div><?php endif; ?>
            </div>

            <?php
            // Mostrar banner de satisfacción en la última lección si está completada y habilitada
            $allActive      = array_filter($allLessons, fn($l) => $l['is_active']);
            $allCompletedNow = count(array_intersect(
                array_column(array_values($allActive), 'id'),
                $completedLessonIds ?? []
            ));
            $isLastLesson   = !$nextLesson;
            $courseFullDone = count($allActive) > 0 && $allCompletedNow >= count($allActive);
            if ($isLastLesson && $courseFullDone && !empty($course['satisfaction_enabled'])): ?>
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-6 shadow-xl shadow-purple-500/20">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-3xl flex-shrink-0">⭐</div>
                    <div>
                        <p class="font-black text-white text-lg mb-1"><?= __('lessons.show.course_completed_title') ?></p>
                        <p class="text-purple-200 text-sm mb-4 leading-relaxed">
                            <?= __('lessons.show.satisfaction_help') ?>
                        </p>
                        <a href="<?= cms_url() ?>/formulario-de-satisfaccion"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 bg-white text-purple-700 font-black text-sm px-5 py-3 rounded-xl hover:bg-purple-50 transition-all shadow-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            <?= __('lessons.show.fill_satisfaction_form') ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- ─── Sidebar derecho ────────────────────────────── -->
        <div class="lg:col-span-1">
            <div class="sticky top-8 space-y-4">

                <!-- Progreso del curso -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-lg p-5">
                    <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>"
                       class="flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-brand-500 transition-colors mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <?= __('lessons.show.back_to_course') ?>
                    </a>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-wider mb-1"><?= __('lessons.show.course_label') ?></p>
                    <p class="font-black text-navy text-sm leading-snug mb-4"><?= htmlspecialchars($course['title']) ?></p>
                    <?php
                    $completedCount = count(array_intersect(
                        array_column(array_filter($allLessons, fn($l) => $l['is_active']), 'id'),
                        $completedLessonIds ?? []
                    ));
                    $progressPct = $totalActive > 0 ? round(($completedCount / $totalActive) * 100) : 0;
                    ?>
                    <div class="flex items-center justify-between text-xs font-semibold text-gray-400 mb-1.5">
                        <span><?= __('lessons.show.progress_label') ?></span>
                        <span class="font-black text-brand-500"><?= $progressPct ?>%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2 mb-1">
                        <div class="bg-gradient-to-r from-brand-500 to-blue-400 h-2 rounded-full transition-all" style="width:<?= $progressPct ?>%"></div>
                    </div>
                    <p class="text-xs text-gray-400"><?= __('lessons.show.lessons_completed_count', ['done' => $completedCount, 'total' => $totalActive]) ?></p>
                </div>

                <!-- Lista de módulos -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h3 class="font-black text-navy text-sm"><?= __('lessons.show.course_modules') ?></h3>
                        <p class="text-xs text-gray-400 mt-0.5"><?= __('lessons.show.lessons_count', ['count' => $totalActive]) ?><?= !empty($quizzes) ? ' · ' . __('lessons.show.evaluations_count', ['count' => count($quizzes)]) : '' ?></p>
                    </div>
                    <ul class="divide-y divide-gray-50 max-h-96 overflow-y-auto">
                        <?php
                        $prevDone = true;
                        foreach ($allLessons as $i => $ls):
                            $lsDone   = in_array((int)$ls['id'], $completedLessonIds ?? []);
                            $lsLocked = !$isManager && $ls['is_active'] && !$prevDone;
                            $lsActive = $ls['id'] == $lesson['id'];
                            $lsDis    = !$ls['is_active'];
                            if ($ls['is_active']) $prevDone = $lsDone;
                        ?>
                        <li>
                            <?php if ($lsLocked || $lsDis): ?>
                            <div class="flex items-center gap-3 px-4 py-2.5 opacity-40 cursor-not-allowed">
                            <?php elseif ($lsActive): ?>
                            <div class="flex items-center gap-3 px-4 py-2.5 bg-brand-50 border-l-[3px] border-brand-500">
                            <?php else: ?>
                            <a href="/cursos/<?= $course['id'] ?>/lecciones/<?= $ls['id'] ?>"
                               class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 border-l-[3px] border-transparent transition-colors">
                            <?php endif; ?>
                                <span class="w-6 h-6 rounded-lg text-xs font-black flex items-center justify-center flex-shrink-0
                                    <?= $lsDone && !$lsActive ? 'bg-emerald-100 text-emerald-600' : ($lsActive ? 'bg-brand-500 text-white shadow-md shadow-brand-500/30' : ($lsLocked || $lsDis ? 'bg-gray-100 text-gray-300' : 'bg-gray-100 text-gray-500')) ?>">
                                    <?php if ($lsDone && !$lsActive): ?>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <?php elseif ($lsLocked): ?>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <?php else: ?>
                                        <?= $i + 1 ?>
                                    <?php endif; ?>
                                </span>
                                <span class="text-xs font-semibold truncate leading-snug
                                    <?= $lsActive ? 'text-brand-600' : ($lsDone ? 'text-emerald-600' : ($lsLocked || $lsDis ? 'text-gray-300' : 'text-gray-600')) ?>">
                                    <?= htmlspecialchars($ls['title']) ?>
                                </span>
                            <?php if ($lsLocked || $lsDis || $lsActive): ?></div><?php else: ?></a><?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if (!empty($quizzes)): ?>
                    <div class="border-t border-gray-100 px-4 py-3 bg-amber-50/50">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-wider mb-2"><?= __('lessons.show.evaluations_label') ?></p>
                        <ul class="space-y-1">
                            <?php foreach ($quizzes as $quiz):
                                $allActive = array_filter($allLessons, fn($l) => $l['is_active']);
                                $allDone   = count($allActive) === count(array_intersect(
                                    array_column(array_values($allActive), 'id'),
                                    $completedLessonIds ?? []
                                ));
                            ?>
                            <li>
                                <?php if (!$isManager && !$allDone): ?>
                                <div class="flex items-center gap-2.5 opacity-40 cursor-not-allowed px-1 py-1">
                                    <span class="w-6 h-6 rounded-lg bg-gray-100 flex items-center justify-center text-xs flex-shrink-0">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </span>
                                    <span class="text-xs font-semibold text-gray-400 truncate"><?= htmlspecialchars($quiz['title']) ?></span>
                                </div>
                                <?php else: ?>
                                <a href="/cursos/<?= $course['id'] ?>/quizzes/<?= $quiz['id'] ?>"
                                   class="flex items-center gap-2.5 hover:bg-amber-100 rounded-xl px-2 py-1.5 transition-colors">
                                    <span class="w-6 h-6 rounded-lg bg-amber-200 flex items-center justify-center text-xs flex-shrink-0">📝</span>
                                    <span class="text-xs font-semibold text-navy hover:text-amber-700 truncate"><?= htmlspecialchars($quiz['title']) ?></span>
                                </a>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Atajos de teclado -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                    <p class="text-xs font-black text-gray-400 uppercase tracking-wider mb-3"><?= __('lessons.show.quick_navigation') ?></p>
                    <div class="space-y-2">
                        <?php if ($prevLesson): ?>
                        <a href="/cursos/<?= $course['id'] ?>/lecciones/<?= $prevLesson['id'] ?>"
                           class="flex items-center gap-2 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors">
                            <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-500 font-mono text-xs">←</kbd>
                            <?= __('lessons.show.previous_lesson') ?>
                        </a>
                        <?php endif; ?>
                        <?php if ($nextLesson): ?>
                        <a href="/cursos/<?= $course['id'] ?>/lecciones/<?= $nextLesson['id'] ?>"
                           class="flex items-center gap-2 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors">
                            <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-500 font-mono text-xs">→</kbd>
                            <?= __('lessons.show.next_lesson') ?>
                        </a>
                        <?php endif; ?>
                        <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>"
                           class="flex items-center gap-2 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors">
                            <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-500 font-mono text-xs">↑</kbd>
                            <?= __('lessons.show.back_to_course') ?>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Lightbox para imágenes -->
<div id="lightbox" onclick="closeLightbox()">
    <img id="lightbox-img" src="" alt="">
</div>

<script>
// ── Barra de progreso de lectura ──────────────────────────────
window.addEventListener('scroll', () => {
    const doc  = document.documentElement;
    const pct  = (doc.scrollTop / (doc.scrollHeight - doc.clientHeight)) * 100;
    document.getElementById('reading-progress').style.width = Math.min(pct, 100) + '%';

    // Mostrar CTA de completar al llegar al 85%
    const cta = document.getElementById('complete-cta');
    if (cta && pct >= 85) cta.classList.remove('hidden');
});

// ── Tamaño de fuente ──────────────────────────────────────────
let fontSize = 15;
function changeFontSize(delta) {
    fontSize = Math.min(20, Math.max(13, fontSize + delta));
    document.getElementById('lesson-body').style.fontSize = fontSize + 'px';
}

// ── Lightbox de imágenes ──────────────────────────────────────
document.querySelectorAll('.lesson-body img').forEach(img => {
    img.addEventListener('click', () => {
        document.getElementById('lightbox-img').src = img.src;
        document.getElementById('lightbox').classList.add('active');
        document.body.style.overflow = 'hidden';
    });
});
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// ── Navegación con teclado ────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    <?php if ($prevLesson): ?>
    if (e.key === 'ArrowLeft')  location.href = '/cursos/<?= $course['id'] ?>/lecciones/<?= $prevLesson['id'] ?>';
    <?php endif; ?>
    <?php if ($nextLesson): ?>
    if (e.key === 'ArrowRight') location.href = '/cursos/<?= $course['id'] ?>/lecciones/<?= $nextLesson['id'] ?>';
    <?php endif; ?>
});

<?php if ($isManager): ?>
// ── Preguntas para quiz de lección (edición) ──────────────────
var lqCount = 0;
function addLQQuestion() {
    var i = lqCount++;
    var c = document.getElementById('lq-questions-container');
    c.insertAdjacentHTML('beforeend', lqBlockHTML('lq', i, 'lesson-quiz-edit-form'));
    syncLQRadios('lesson-quiz-edit-form');
}
// ── Preguntas para crear quiz de lección ──────────────────────
var clqCount = 0;
function addCreateLQQuestion() {
    var i = clqCount++;
    var c = document.getElementById('create-lq-container');
    c.insertAdjacentHTML('beforeend', lqBlockHTML('clq', i, 'create-lq-form'));
    syncLQRadios('create-lq-form');
}
function lqBlockHTML(pfx, i, formId) {
    return '<div class="border-2 border-gray-100 rounded-xl p-4 bg-white" id="' + pfx + 'block_' + i + '">'
        + '<div class="flex items-center justify-between mb-3"><span class="text-xs font-black text-navy"><?= __('lessons.show.question_word') ?> ' + (i+1) + '</span>'
        + '<button type="button" onclick="document.getElementById(\'' + pfx + 'block_' + i + '\').remove()" class="text-xs text-red-400 hover:text-red-600 font-bold px-2 py-1 rounded-lg hover:bg-red-50"><?= __('common.remove') ?></button></div>'
        + '<div class="space-y-3">'
        + '<input type="text" name="questions[' + i + '][text]" required placeholder="<?= __('lessons.show.question_placeholder') ?>" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-amber-400">'
        + '<div class="flex gap-3">'
        + '<select name="questions[' + i + '][type]" onchange="lqToggleType(\'' + pfx + '\',' + i + ',this.value,\'' + formId + '\')" class="px-3 py-2 border-2 border-gray-200 rounded-xl text-xs focus:outline-none focus:border-amber-400 bg-white font-semibold">'
        + '<option value="multiple_choice"><?= __('lessons.show.multiple_choice') ?></option><option value="true_false"><?= __('lessons.show.true_false_short') ?></option></select>'
        + '<input type="number" name="questions[' + i + '][points]" value="1" min="1" class="w-16 px-2 py-2 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-amber-400 text-center font-bold">'
        + '<span class="text-xs text-gray-400 self-center font-semibold"><?= __('lessons.show.points_abbr') ?></span></div>'
        + '<div id="' + pfx + 'opts_' + i + '" class="space-y-2">'
        + lqOptHTML(pfx, i, 0) + lqOptHTML(pfx, i, 1) + lqOptHTML(pfx, i, 2) + lqOptHTML(pfx, i, 3)
        + '</div>'
        + '<button type="button" onclick="lqAddOpt(\'' + pfx + '\',' + i + ')" class="text-xs font-bold text-amber-600 hover:underline">+ <?= __('lessons.show.option_word') ?></button>'
        + '</div></div>';
}
function lqOptHTML(pfx, i, oi) {
    return '<div class="flex items-center gap-2 p-2.5 border-2 border-gray-100 rounded-xl">'
        + '<input type="radio" name="questions[' + i + '][correct]" value="' + oi + '" class="text-amber-500 w-4 h-4">'
        + '<input type="text" name="questions[' + i + '][options][' + oi + '][text]" required placeholder="<?= __('lessons.show.option_word') ?> ' + (oi+1) + '" class="flex-1 text-sm font-semibold text-gray-700 bg-transparent outline-none placeholder-gray-300">'
        + '<input type="hidden" name="questions[' + i + '][options][' + oi + '][is_correct]" value="0" id="' + pfx + 'hid_' + i + '_' + oi + '">'
        + '</div>';
}
var lqOptCounts = {};
function lqAddOpt(pfx, i) {
    var key = pfx + '_' + i;
    lqOptCounts[key] = (lqOptCounts[key] ?? 4);
    var oi = lqOptCounts[key]++;
    document.getElementById(pfx + 'opts_' + i).insertAdjacentHTML('beforeend', lqOptHTML(pfx, i, oi));
}
function lqToggleType(pfx, i, type, formId) {
    var c = document.getElementById(pfx + 'opts_' + i);
    if (type === 'true_false') {
        c.innerHTML = '<div class="flex items-center gap-2 p-2.5 border-2 border-gray-100 rounded-xl"><input type="radio" name="questions[' + i + '][correct]" value="0" class="text-amber-500 w-4 h-4"><span class="flex-1 text-sm font-semibold text-gray-700"><?= __('lessons.show.true_word') ?></span><input type="hidden" name="questions[' + i + '][options][0][text]" value="<?= __('lessons.show.true_word') ?>"><input type="hidden" name="questions[' + i + '][options][0][is_correct]" value="0" id="' + pfx + 'hid_' + i + '_0"></div>'
            + '<div class="flex items-center gap-2 p-2.5 border-2 border-gray-100 rounded-xl"><input type="radio" name="questions[' + i + '][correct]" value="1" class="text-amber-500 w-4 h-4"><span class="flex-1 text-sm font-semibold text-gray-700"><?= __('lessons.show.false_word') ?></span><input type="hidden" name="questions[' + i + '][options][1][text]" value="<?= __('lessons.show.false_word') ?>"><input type="hidden" name="questions[' + i + '][options][1][is_correct]" value="0" id="' + pfx + 'hid_' + i + '_1"></div>';
    }
}
function syncLQRadios(formId) {
    var form = document.getElementById(formId);
    if (!form) return;
    form.addEventListener('change', function(e) {
        if (e.target.type !== 'radio' || !e.target.name.includes('[correct]')) return;
        var m = e.target.name.match(/questions\[(\d+)\]/);
        if (!m) return;
        var qi = m[1];
        form.querySelectorAll('input[type="hidden"]').forEach(function(el) {
            if (el.name.includes('questions[' + qi + ']') && el.name.includes('[is_correct]')) el.value = '0';
        });
        var pfx = formId === 'lesson-quiz-edit-form' ? 'lq' : 'clq';
        var hid = document.getElementById(pfx + 'hid_' + qi + '_' + e.target.value);
        if (hid) hid.value = '1';
    }, {once: false});
}
<?php endif; ?>
</script>
