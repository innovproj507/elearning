<?php
$isManager   = $user && (
    $user['role'] === 'admin' ||
    $user['role'] === 'teacher' && (int)$course['teacher_id'] === (int)$user['id']
);
$lessons     = is_array($lessons  ?? null) ? $lessons  : [];
$quizzes     = is_array($quizzes  ?? null) ? $quizzes  : [];
$lessonCount = count($lessons);
$quizCount   = count($quizzes);

// Normaliza una URL del CMS reemplazando cualquier hostname local
$_cmsBase = cms_url();
$_normCmsUrl = function(string $url) use ($_cmsBase): string {
    if ($url === '') return '';
    if (strpos($url, 'http') !== 0) {
        return $_cmsBase . '/' . ltrim($url, '/');
    }
    return preg_replace('#https?://cms\.test#', $_cmsBase, $url);
};

// Foro del curso — se carga aquí para que el sidebar pueda mostrar el contador
$_forumModel  = new \App\Models\Forum();
$_forumTopics = $_forumModel->courseTopics((int) $course['id']);
$_csrfToken   = \App\Core\CSRF::generate();
?>
<div class="max-w-7xl mx-auto px-4 py-10">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8">
        <a href="/cursos" class="hover:text-brand-500 font-semibold transition-colors"><?= __('courses.show.catalog') ?></a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-navy font-semibold truncate"><?= htmlspecialchars($course['title']) ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- ─── Columna principal ──────────────────────────── -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Cabecera del curso -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <!-- Banner -->
                <div class="h-52 relative overflow-hidden"
                     style="background:linear-gradient(135deg,#0a1628 0%,#1a3a6b 60%,#1a56db 100%);">
                    <?php
                    $_img = $course['image'] ?? '';
                    if ($_img !== '' && strpos($_img, 'http') !== 0) {
                        $_img = cms_asset($_img);
                    }
                    ?>
                    <?php if ($_img): ?>
                    <img src="<?= htmlspecialchars($_img) ?>"
                         alt="<?= htmlspecialchars($course['title']) ?>"
                         class="w-full h-full object-cover opacity-40">
                    <?php endif; ?>
                    <div class="absolute inset-0 flex flex-col justify-end p-6">
                        <?php if (!empty($course['category_name'])): ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold bg-white/20 text-white border border-white/30 px-3 py-1 rounded-full w-fit mb-3 backdrop-blur-sm">
                            <?= htmlspecialchars($course['category_name']) ?>
                        </span>
                        <?php endif; ?>
                        <h1 class="text-2xl font-black text-white leading-tight">
                            <?= htmlspecialchars($course['title']) ?>
                        </h1>
                    </div>
                </div>

                <!-- Meta info -->
                <div class="px-6 py-4 flex flex-wrap items-center gap-4 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-blue-700 flex items-center justify-center text-white font-black text-xs">
                            <?= mb_strtoupper(mb_substr($course['teacher_name'] ?? 'P', 0, 1)) ?>
                        </div>
                        <span class="text-sm text-gray-600">
                            <?= __('courses.show.by') ?> <strong class="text-navy"><?= htmlspecialchars($course['teacher_name'] ?? 'PAMEL') ?></strong>
                        </span>
                    </div>
                    <span class="text-xs text-gray-400 font-semibold ml-auto">
                        <?= __('courses.show.lessons_count', ['count' => $lessonCount]) ?> &bull; <?= __('courses.show.quizzes_count', ['count' => $quizCount]) ?>
                    </span>
                </div>
            </div>

            <!-- Panel de edición inline (solo managers) -->
            <?php if ($isManager): ?>
            <div id="edit-panel" class="hidden bg-white rounded-2xl border-2 border-brand-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-black text-navy text-lg flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-brand-50 flex items-center justify-center text-brand-500 text-sm">✏️</span>
                        <?= __('courses.edit.heading') ?>
                    </h2>
                    <button type="button" onclick="toggleEditPanel()"
                            class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors" title="<?= __('common.close') ?>">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="/cursos/<?= $course['id'] ?>/editar" enctype="multipart/form-data" class="space-y-5">
                    <?= \App\Core\CSRF::field() ?>

                    <!-- Título -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('courses.show.title_label') ?> <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required
                            value="<?= htmlspecialchars($course['title'], ENT_QUOTES) ?>"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('courses.create.description_label') ?></label>
                        <textarea name="description" rows="4"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all resize-none"><?= htmlspecialchars($course['description'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>

                    <!-- Categoría + Nivel -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('courses.create.category_label') ?></label>
                            <select name="category_id"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all bg-white">
                                <option value=""><?= __('courses.create.no_category') ?></option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ((int)($course['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('courses.create.level_label') ?></label>
                            <select name="level"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all bg-white">
                                <option value="beginner"     <?= ($course['level'] === 'beginner')     ? 'selected' : '' ?>><?= __('courses.create.level_beginner') ?></option>
                                <option value="intermediate" <?= ($course['level'] === 'intermediate') ? 'selected' : '' ?>><?= __('courses.create.level_intermediate') ?></option>
                                <option value="advanced"     <?= ($course['level'] === 'advanced')     ? 'selected' : '' ?>><?= __('courses.create.level_advanced') ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Precio + Estado -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('courses.create.price_label') ?></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 font-bold text-sm">$</span>
                                <input type="number" name="price" min="0" step="0.01"
                                    value="<?= htmlspecialchars((string)($course['price'] ?? '0'), ENT_QUOTES) ?>"
                                    class="w-full pl-8 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('common.status') ?></label>
                            <select name="status"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all bg-white">
                                <option value="draft"     <?= ($course['status'] === 'draft')     ? 'selected' : '' ?>>📝 <?= __('courses.create.status_draft') ?></option>
                                <option value="published" <?= ($course['status'] === 'published') ? 'selected' : '' ?>>✅ <?= __('courses.create.status_published') ?></option>
                                <option value="archived"  <?= ($course['status'] === 'archived')  ? 'selected' : '' ?>>🗄️ <?= __('courses.edit.status_archived') ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Gratuito -->
                    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                        <input type="checkbox" name="is_free" value="1" id="edit_is_free"
                            <?= !empty($course['is_free']) ? 'checked' : '' ?>
                            class="w-4 h-4 text-emerald-500 border-gray-300 rounded focus:ring-emerald-500">
                        <label for="edit_is_free" class="text-sm font-bold text-emerald-700 cursor-pointer">
                            <?= __('courses.show.is_free_short_label') ?>
                        </label>
                    </div>

                    <!-- Satisfacción -->
                    <div class="flex items-center gap-3 p-4 bg-purple-50 border border-purple-200 rounded-xl">
                        <input type="checkbox" name="satisfaction_enabled" value="1" id="edit_satisfaction_enabled"
                            <?= !empty($course['satisfaction_enabled']) ? 'checked' : '' ?>
                            class="w-4 h-4 text-purple-500 border-gray-300 rounded focus:ring-purple-500">
                        <label for="edit_satisfaction_enabled" class="text-sm font-bold text-purple-700 cursor-pointer">
                            <?= __('courses.show.satisfaction_enable_short_label') ?>
                        </label>
                    </div>

                    <!-- Imagen -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('courses.create.image_label') ?></label>
                        <?php
                        $_editImg = $course['image'] ?? '';
                        if ($_editImg !== '' && strpos($_editImg, 'http') !== 0) {
                            $_editImg = cms_asset($_editImg);
                        }
                        ?>
                        <?php if (!empty($_editImg)): ?>
                        <div class="mb-3">
                            <img src="<?= htmlspecialchars($_editImg) ?>" alt="<?= __('courses.edit.current_cover_alt') ?>"
                                 class="h-24 w-auto rounded-xl object-cover border border-gray-200">
                            <p class="text-xs text-gray-400 mt-1"><?= __('courses.edit.current_image_hint') ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-brand-300 transition-colors">
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                class="text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-brand-50 file:text-brand-500 hover:file:bg-brand-100 cursor-pointer">
                            <p class="text-xs text-gray-400 mt-2"><?= __('courses.show.image_formats_short') ?></p>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3 pt-2 border-t border-gray-100">
                        <button type="submit"
                            class="bg-brand-500 hover:bg-brand-600 text-white font-bold px-6 py-3 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/25">
                            <?= __('courses.edit.save_changes') ?>
                        </button>
                        <button type="button" onclick="toggleEditPanel()"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-6 py-3 rounded-xl text-sm transition-all">
                            <?= __('common.cancel') ?>
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Descripción -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="font-black text-navy text-lg mb-4 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-brand-50 flex items-center justify-center text-brand-500 text-sm">📄</span>
                    <?= __('courses.show.about_course') ?>
                </h2>
                <div class="text-gray-600 text-sm leading-relaxed space-y-3">
                    <?= nl2br(htmlspecialchars($course['description'] ?? '')) ?>
                </div>

                <!-- Stats visuales -->
                <div class="grid grid-cols-3 gap-3 mt-6 pt-5 border-t border-gray-100">
                    <div class="text-center p-3 bg-blue-50 rounded-xl">
                        <p class="text-2xl font-black text-brand-600"><?= $lessonCount ?></p>
                        <p class="text-xs text-gray-500 font-semibold mt-0.5"><?= __('courses.show.lessons_label') ?></p>
                    </div>
                    <div class="text-center p-3 bg-amber-50 rounded-xl">
                        <p class="text-2xl font-black text-amber-600"><?= $quizCount ?></p>
                        <p class="text-xs text-gray-500 font-semibold mt-0.5"><?= __('courses.show.quizzes_label') ?></p>
                    </div>
                </div>
            </div>

            <?php
            // Competencias del curso, guardadas como JSON en
            // learning_objectives. Ninguna vista las leía: el dato entraba
            // en la base y no se veía en ninguna parte.
            $_objetivos = json_decode((string) ($course['learning_objectives'] ?? ''), true);
            $_objetivos = is_array($_objetivos)
                ? array_values(array_filter(array_map('strval', $_objetivos), fn($o) => trim($o) !== ''))
                : [];
            ?>

            <!-- Contenido del curso: competencias y lecciones -->
            <?php if (!empty($lessons) || $_objetivos): ?>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="font-black text-navy text-lg mb-5">
                    <?= __('courses.show.course_content') ?>
                    <?php if (!empty($lessons)): ?>
                    <span class="text-sm font-semibold text-gray-400 ml-2"><?= __('courses.show.lessons_count', ['count' => $lessonCount]) ?></span>
                    <?php endif; ?>
                </h2>

                <?php if ($_objetivos): ?>
                <?php /*
                    Las entradas que empiezan por "F" son el nombre de una
                    función y se pintan como cabecera; las "C" son sus
                    competencias y van numeradas dentro de cada función.
                */ ?>
                <div class="<?= !empty($lessons) ? 'mb-5 pb-5 border-b border-gray-100' : '' ?>">
                    <?php $_n = 0; foreach ($_objetivos as $_o): ?>
                        <?php if (preg_match('/^F\d+\./', $_o)): $_n = 0; ?>
                        <h3 class="font-black text-navy text-sm mt-5 first:mt-0 mb-2 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-brand-50 text-brand-500 text-[10px] font-black flex items-center justify-center flex-shrink-0">
                                <?= htmlspecialchars(strtok($_o, '.')) ?>
                            </span>
                            <?= htmlspecialchars(trim(substr($_o, strpos($_o, '.') + 1))) ?>
                        </h3>
                        <?php else: $_n++; ?>
                        <div class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 mb-2">
                            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 text-[11px] font-black flex items-center justify-center flex-shrink-0">
                                <?= $_n ?>
                            </span>
                            <span class="flex-1 text-sm text-gray-700 leading-snug pt-1.5"><?= htmlspecialchars($_o) ?></span>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($lessons)): ?>
                <ul class="space-y-2">
                    <?php
                    $prevCompleted = true;
                    foreach ($lessons as $i => $lesson):
                        $isCompleted  = \in_array((int)$lesson['id'], $completedLessonIds ?? []);
                        $isDisabled   = !$lesson['is_active'];
                        $isLocked     = !$isManager && $enrolled && !$isDisabled && !$prevCompleted;
                        $canAccess    = ($enrolled || $isManager) && !$isLocked && !$isDisabled;
                        if ($lesson['is_active']) $prevCompleted = $isCompleted;
                    ?>
                    <li class="flex items-center gap-3 p-3 rounded-xl border transition-all
                        <?= $isDisabled && $isManager ? 'border-red-100 bg-red-50/50 opacity-70' : ($isCompleted ? 'border-emerald-100 bg-emerald-50/30' : ($isLocked ? 'border-gray-100 bg-gray-50/50' : 'border-gray-100 hover:bg-slate-50 hover:border-brand-100')) ?>">
                        <span class="w-8 h-8 rounded-xl text-xs font-black flex items-center justify-center flex-shrink-0
                            <?= $isCompleted ? 'bg-emerald-100 text-emerald-600' : ($isLocked || $isDisabled ? 'bg-gray-100 text-gray-400' : 'bg-brand-50 text-brand-500') ?>">
                            <?php if ($isCompleted): ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <?php elseif ($isLocked): ?>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <?php else: ?>
                            <?= $i + 1 ?>
                            <?php endif; ?>
                        </span>
                        <?php if ($canAccess): ?>
                        <a href="/cursos/<?= $course['id'] ?>/lecciones/<?= $lesson['id'] ?>"
                           class="flex-1 text-sm font-semibold min-w-0 truncate transition-colors
                               <?= $isCompleted ? 'text-emerald-700 hover:text-emerald-600' : 'text-navy hover:text-brand-500' ?>">
                            <?= htmlspecialchars($lesson['title']) ?>
                        </a>
                        <?php else: ?>
                        <span class="flex-1 text-sm font-semibold text-gray-400 min-w-0 truncate <?= $isDisabled && $isManager ? 'line-through' : '' ?>">
                            <?= htmlspecialchars($lesson['title']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($isDisabled && $isManager): ?>
                        <span class="text-xs font-bold text-red-500 bg-red-50 px-2 py-0.5 rounded-full flex-shrink-0"><?= __('courses.show.lesson_inactive') ?></span>
                        <?php elseif ($isLocked): ?>
                        <span class="text-xs text-gray-400 flex-shrink-0"><?= __('courses.show.lesson_locked') ?></span>
                        <?php elseif ($lesson['duration']): ?>
                        <span class="text-xs text-gray-400 flex-shrink-0 font-semibold"><?= $lesson['duration'] ?><?= __('courses.show.minutes_abbr') ?></span>
                        <?php endif; ?>
                        <?php if ($isManager): ?>
                        <div class="flex items-center gap-1 flex-shrink-0 ml-1">
                            <button type="button" onclick="toggleLessonEditPanel(<?= $lesson['id'] ?>)"
                               class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors" title="<?= __('common.edit') ?>">
                                <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form method="POST" action="/cursos/<?= $course['id'] ?>/lecciones/<?= $lesson['id'] ?>/toggle" class="inline">
                                <?= \App\Core\CSRF::field() ?>
                                <button type="submit" title="<?= $lesson['is_active'] ? __('courses.show.deactivate') : __('courses.show.activate') ?>"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors
                                        <?= $lesson['is_active'] ? 'bg-amber-50 hover:bg-amber-100' : 'bg-emerald-50 hover:bg-emerald-100' ?>">
                                    <?php if ($lesson['is_active']): ?>
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    <?php else: ?>
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <?php endif; ?>
                                </button>
                            </form>
                            <form method="POST" action="/cursos/<?= $course['id'] ?>/lecciones/<?= $lesson['id'] ?>/eliminar"
                                  onsubmit="return confirm('<?= addslashes(__('courses.show.confirm_delete_lesson')) ?>')" class="inline">
                                <?= \App\Core\CSRF::field() ?>
                                <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center transition-colors" title="<?= __('common.delete') ?>">
                                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <!-- Paneles de edición inline por lección -->
                <?php if ($isManager && !empty($lessons)): ?>
                <?php foreach ($lessons as $lesson): ?>
                <div id="lesson-edit-panel-<?= $lesson['id'] ?>" class="hidden mt-3 p-5 rounded-xl border-2 border-brand-200 bg-brand-50/30">
                    <div class="flex items-center justify-between mb-4">
                        <p class="font-black text-navy text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <?= __('courses.show.editing_prefix') ?> <?= htmlspecialchars($lesson['title']) ?>
                        </p>
                        <button type="button" onclick="toggleLessonEditPanel(<?= $lesson['id'] ?>)"
                                class="w-7 h-7 rounded-lg bg-white hover:bg-gray-100 flex items-center justify-center transition-colors" title="<?= __('common.close') ?>">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" action="/cursos/<?= $course['id'] ?>/lecciones/<?= $lesson['id'] ?>/editar" enctype="multipart/form-data" class="space-y-4">
                        <?= \App\Core\CSRF::field() ?>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('courses.show.title_label') ?> <span class="text-red-500">*</span></label>
                            <input type="text" name="title" required
                                value="<?= htmlspecialchars($lesson['title'], ENT_QUOTES) ?>"
                                class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all bg-white">
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('courses.show.type_label') ?></label>
                                <select name="type" id="ltype_<?= $lesson['id'] ?>" onchange="toggleLessonTypeFields(<?= $lesson['id'] ?>)"
                                    class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-xs focus:outline-none focus:border-brand-500 transition-all bg-white">
                                    <option value="text"  <?= $lesson['type'] === 'text'  ? 'selected' : '' ?>>📄 <?= __('courses.show.type_text') ?></option>
                                    <option value="video" <?= $lesson['type'] === 'video' ? 'selected' : '' ?>>🎬 <?= __('courses.show.type_video') ?></option>
                                    <option value="file"  <?= $lesson['type'] === 'file'  ? 'selected' : '' ?>>📎 <?= __('courses.show.type_file') ?></option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('courses.show.duration_label') ?></label>
                                <input type="number" name="duration" min="0"
                                    value="<?= htmlspecialchars((string)($lesson['duration'] ?? 0), ENT_QUOTES) ?>"
                                    class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 transition-all bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('courses.show.order_label') ?></label>
                                <input type="number" name="order_num" min="0"
                                    value="<?= htmlspecialchars((string)($lesson['order_num'] ?? 0), ENT_QUOTES) ?>"
                                    class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 transition-all bg-white">
                            </div>
                        </div>

                        <div id="lfield_video_<?= $lesson['id'] ?>" class="<?= $lesson['type'] !== 'video' ? 'hidden' : '' ?>">
                            <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('courses.show.video_url_label') ?></label>
                            <input type="url" name="video_url"
                                value="<?= htmlspecialchars($lesson['video_url'] ?? '', ENT_QUOTES) ?>"
                                class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 transition-all bg-white"
                                placeholder="https://youtube.com/watch?v=...">
                        </div>

                        <div id="lfield_content_<?= $lesson['id'] ?>" class="<?= $lesson['type'] === 'file' ? 'hidden' : '' ?>">
                            <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('courses.show.content_label') ?></label>
                            <textarea name="content" id="editor_lesson_<?= $lesson['id'] ?>" class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl text-sm bg-white"><?= htmlspecialchars($lesson['content'] ?? '', ENT_QUOTES) ?></textarea>
                        </div>

                        <div class="flex gap-3 pt-2 border-t border-gray-200">
                            <button type="submit"
                                class="bg-brand-500 hover:bg-brand-600 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/25">
                                <?= __('common.save') ?>
                            </button>
                            <button type="button" onclick="toggleLessonEditPanel(<?= $lesson['id'] ?>)"
                                class="bg-white hover:bg-gray-100 text-gray-700 font-bold px-5 py-2.5 rounded-xl text-sm transition-all border border-gray-200">
                                <?= __('common.cancel') ?>
                            </button>
                        </div>
                    </form>

                    <!-- Quiz de la Lección -->
                    <?php $lq = ($lessonQuizzes ?? [])[$lesson['id']] ?? null; ?>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs font-black text-gray-500 uppercase tracking-wider mb-3"><?= __('courses.show.lesson_quiz_title') ?></p>
                        <?php if ($lq): ?>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">📝</span>
                                    <div>
                                        <p class="font-bold text-navy text-sm"><?= htmlspecialchars($lq['title']) ?></p>
                                        <p class="text-xs text-amber-600"><?= __('courses.show.questions_abbr_count', ['count' => count($lq['questions'] ?? [])]) ?> · <?= $lq['pass_percentage'] ?>% <?= __('courses.show.pass_percentage_short') ?></p>
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" onclick="toggleLessonQuizPanel(<?= $lesson['id'] ?>)"
                                        class="text-xs font-bold text-amber-600 bg-amber-100 hover:bg-amber-200 px-3 py-1.5 rounded-lg transition-colors">
                                        <?= __('common.edit') ?>
                                    </button>
                                    <form method="POST" action="/cursos/<?= $course['id'] ?>/quizzes/<?= $lq['id'] ?>/eliminar"
                                          onsubmit="return confirm('<?= addslashes(__('courses.show.confirm_delete_lesson_quiz')) ?>')" class="inline">
                                        <?= \App\Core\CSRF::field() ?>
                                        <button type="submit" class="text-xs font-bold text-red-500 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">
                                            <?= __('common.delete') ?>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Panel edición inline del quiz de la lección -->
                            <div id="lesson-quiz-panel-<?= $lesson['id'] ?>" class="hidden mt-3 pt-3 border-t border-amber-200">
                                <form method="POST" action="/cursos/<?= $course['id'] ?>/quizzes/<?= $lq['id'] ?>/editar"
                                      id="lq-edit-form-<?= $lesson['id'] ?>" class="space-y-3">
                                    <?= \App\Core\CSRF::field() ?>
                                    <div class="grid grid-cols-2 gap-3">
                                        <input type="text" name="title" required value="<?= htmlspecialchars($lq['title'], ENT_QUOTES) ?>"
                                            class="px-3 py-2 border-2 border-gray-200 rounded-xl text-xs focus:outline-none focus:border-amber-400 bg-white"
                                            placeholder="<?= __('courses.show.title_label') ?>">
                                        <input type="number" name="pass_percentage" min="0" max="100" value="<?= $lq['pass_percentage'] ?>"
                                            class="px-3 py-2 border-2 border-gray-200 rounded-xl text-xs focus:outline-none focus:border-amber-400 bg-white"
                                            placeholder="<?= __('courses.show.pass_percentage_placeholder') ?>">
                                        <input type="hidden" name="description" value="<?= htmlspecialchars($lq['description'] ?? '', ENT_QUOTES) ?>">
                                        <input type="hidden" name="time_limit" value="<?= (int)($lq['time_limit'] ?? 0) ?>">
                                    </div>
                                    <!-- Agregar nuevas preguntas -->
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-xs font-bold text-gray-600"><?= __('courses.show.add_questions') ?></p>
                                            <button type="button" onclick="addCLQQuestion(<?= $lesson['id'] ?>)"
                                                class="text-xs font-bold text-amber-600 bg-amber-100 hover:bg-amber-200 px-2 py-1 rounded-lg transition-colors">
                                                + <?= __('courses.show.new_short') ?>
                                            </button>
                                        </div>
                                        <div id="clq-container-<?= $lesson['id'] ?>" class="space-y-2"></div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-4 py-2 rounded-xl text-xs"><?= __('common.save') ?></button>
                                        <button type="button" onclick="toggleLessonQuizPanel(<?= $lesson['id'] ?>)" class="bg-white border border-gray-200 text-gray-700 font-bold px-4 py-2 rounded-xl text-xs hover:bg-gray-50"><?= __('common.cancel') ?></button>
                                    </div>
                                </form>
                                <!-- Preguntas existentes -->
                                <?php if (!empty($lq['questions'])): ?>
                                <div class="mt-3 pt-3 border-t border-amber-100 space-y-2">
                                    <?php foreach ($lq['questions'] as $qi => $eq): ?>
                                    <div class="bg-white rounded-lg border border-amber-100 p-3 flex items-start gap-2">
                                        <span class="w-5 h-5 rounded-md bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-black flex-shrink-0"><?= $qi + 1 ?></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-gray-800"><?= htmlspecialchars($eq['question'] ?? '') ?></p>
                                            <ul class="mt-1 space-y-0.5">
                                                <?php foreach ($eq['options'] ?? [] as $opt): ?>
                                                <li class="text-xs flex items-center gap-1 <?= $opt['is_correct'] ? 'text-emerald-700 font-bold' : 'text-gray-400' ?>">
                                                    <?php if ($opt['is_correct']): ?><svg class="w-2.5 h-2.5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg><?php else: ?><span class="w-2.5 h-2.5 rounded-full border border-gray-300 flex-shrink-0"></span><?php endif; ?>
                                                    <?= htmlspecialchars($opt['option_text'] ?? '') ?>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <form method="POST" action="/cursos/<?= $course['id'] ?>/quizzes/<?= $lq['id'] ?>/preguntas/<?= $eq['id'] ?>/eliminar"
                                              onsubmit="return confirm('<?= addslashes(__('courses.show.confirm_delete_generic')) ?>')" class="flex-shrink-0">
                                            <?= \App\Core\CSRF::field() ?>
                                            <button type="submit" class="w-6 h-6 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center">
                                                <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Sin quiz: crear inline -->
                        <div class="border-2 border-dashed border-amber-100 rounded-xl p-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-gray-400 font-semibold"><?= __('courses.show.no_quiz_assigned') ?></p>
                                <button type="button" onclick="toggleCreateLessonQuiz(<?= $lesson['id'] ?>)"
                                    class="text-xs font-bold text-amber-600 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-lg transition-colors">
                                    + <?= __('courses.show.create_quiz_short') ?>
                                </button>
                            </div>
                            <div id="create-lq-panel-<?= $lesson['id'] ?>" class="hidden mt-3 pt-3 border-t border-amber-100">
                                <form method="POST" action="/cursos/<?= $course['id'] ?>/quizzes/crear"
                                      id="create-lq-form-<?= $lesson['id'] ?>" class="space-y-3">
                                    <?= \App\Core\CSRF::field() ?>
                                    <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" name="title" required placeholder="<?= __('courses.show.quiz_title_placeholder') ?>"
                                            class="px-3 py-2 border-2 border-gray-200 rounded-xl text-xs focus:outline-none focus:border-amber-400 bg-white">
                                        <input type="number" name="pass_percentage" value="70" min="0" max="100"
                                            class="px-3 py-2 border-2 border-gray-200 rounded-xl text-xs focus:outline-none focus:border-amber-400 bg-white"
                                            placeholder="<?= __('courses.show.pass_percentage_placeholder') ?>">
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-xs font-bold text-gray-600"><?= __('courses.show.questions_label') ?></p>
                                            <button type="button" onclick="addNewLQQuestion(<?= $lesson['id'] ?>)"
                                                class="text-xs font-bold text-amber-600 bg-amber-100 hover:bg-amber-200 px-2 py-1 rounded-lg">
                                                + <?= __('common.add') ?>
                                            </button>
                                        </div>
                                        <div id="new-lq-container-<?= $lesson['id'] ?>" class="space-y-2"></div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-4 py-2 rounded-xl text-xs"><?= __('common.create') ?></button>
                                        <button type="button" onclick="toggleCreateLessonQuiz(<?= $lesson['id'] ?>)" class="bg-white border border-gray-200 text-gray-700 font-bold px-4 py-2 rounded-xl text-xs hover:bg-gray-50"><?= __('common.cancel') ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

            </div>
            <?php endif; ?>

            <!-- Evaluaciones -->
            <?php if (!empty($quizzes)): ?>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="font-black text-navy text-lg mb-5"><?= __('courses.show.evaluations') ?></h2>
                <ul class="space-y-3">
                    <?php foreach ($quizzes as $quiz): ?>
                    <li class="flex items-center gap-4 p-4 rounded-xl bg-amber-50 border border-amber-100">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white text-xl shadow-md flex-shrink-0">📝</div>
                        <div class="flex-1 min-w-0">
                            <?php if ($enrolled || $isManager): ?>
                            <a href="/cursos/<?= $course['id'] ?>/quizzes/<?= $quiz['id'] ?>"
                               class="font-bold text-navy hover:text-brand-500 transition-colors text-sm">
                                <?= htmlspecialchars($quiz['title']) ?>
                            </a>
                            <?php else: ?>
                            <p class="font-bold text-navy text-sm"><?= htmlspecialchars($quiz['title']) ?></p>
                            <?php endif; ?>
                            <p class="text-xs text-amber-600 font-semibold mt-0.5"><?= __('courses.show.min_pass') ?>: <?= $quiz['pass_percentage'] ?>%</p>
                        </div>
                        <?php if ($isManager): ?>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button type="button" onclick="toggleQuizEditPanel(<?= $quiz['id'] ?>)"
                               class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 flex items-center justify-center transition-colors border border-amber-200" title="<?= __('courses.show.edit_quiz') ?>">
                                <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form method="POST" action="/cursos/<?= $course['id'] ?>/quizzes/<?= $quiz['id'] ?>/eliminar"
                                  onsubmit="return confirm('<?= addslashes(__('courses.show.confirm_delete_quiz')) ?>')" class="inline">
                                <?= \App\Core\CSRF::field() ?>
                                <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center transition-colors border border-red-200" title="<?= __('courses.show.delete_quiz') ?>">
                                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                        <?php elseif (!$enrolled): ?>
                        <svg class="w-5 h-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Paneles de edición inline por quiz -->
                <?php if ($isManager): ?>
                <?php foreach (($quizzesWithQuestions ?? []) as $qwq): ?>
                <div id="quiz-edit-panel-<?= $qwq['id'] ?>" class="hidden mt-3 p-5 rounded-xl border-2 border-amber-200 bg-amber-50/30">
                    <div class="flex items-center justify-between mb-4">
                        <p class="font-black text-navy text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <?= __('courses.show.editing_prefix') ?> <?= htmlspecialchars($qwq['title']) ?>
                        </p>
                        <button type="button" onclick="toggleQuizEditPanel(<?= $qwq['id'] ?>)"
                                class="w-7 h-7 rounded-lg bg-white hover:bg-gray-100 flex items-center justify-center transition-colors" title="<?= __('common.close') ?>">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Form info + nuevas preguntas -->
                    <form method="POST" action="/cursos/<?= $course['id'] ?>/quizzes/<?= $qwq['id'] ?>/editar"
                          id="quiz-update-form-<?= $qwq['id'] ?>" class="space-y-4">
                        <?= \App\Core\CSRF::field() ?>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('courses.show.title_label') ?> <span class="text-red-500">*</span></label>
                                <input type="text" name="title" required
                                    value="<?= htmlspecialchars($qwq['title'], ENT_QUOTES) ?>"
                                    class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-amber-400 transition-all bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('courses.create.description_label') ?></label>
                                <input type="text" name="description"
                                    value="<?= htmlspecialchars($qwq['description'] ?? '', ENT_QUOTES) ?>"
                                    class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-amber-400 transition-all bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('courses.show.min_pass_percentage_label') ?></label>
                                <input type="number" name="pass_percentage" min="0" max="100"
                                    value="<?= htmlspecialchars((string)($qwq['pass_percentage'] ?? 70), ENT_QUOTES) ?>"
                                    class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-amber-400 transition-all bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1"><?= __('courses.show.time_limit_label') ?></label>
                                <input type="number" name="time_limit" min="0"
                                    value="<?= htmlspecialchars((string)($qwq['time_limit'] ?? 0), ENT_QUOTES) ?>"
                                    class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-amber-400 transition-all bg-white">
                            </div>
                        </div>

                        <!-- Agregar nuevas preguntas -->
                        <div class="pt-3 border-t border-amber-200">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-bold text-gray-700"><?= __('courses.show.add_new_questions') ?></p>
                                <button type="button" onclick="addQuizQuestion(<?= $qwq['id'] ?>)"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold bg-amber-100 hover:bg-amber-200 text-amber-700 px-3 py-1.5 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <?= __('courses.show.new_question') ?>
                                </button>
                            </div>
                            <div id="quiz-questions-container-<?= $qwq['id'] ?>" class="space-y-3"></div>
                        </div>

                        <div class="flex gap-3 pt-2 border-t border-amber-200">
                            <button type="submit"
                                class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-all shadow-md shadow-amber-500/25">
                                <?= __('common.save') ?>
                            </button>
                            <button type="button" onclick="toggleQuizEditPanel(<?= $qwq['id'] ?>)"
                                class="bg-white hover:bg-gray-100 text-gray-700 font-bold px-5 py-2.5 rounded-xl text-sm transition-all border border-gray-200">
                                <?= __('common.cancel') ?>
                            </button>
                        </div>
                    </form>

                    <!-- Preguntas existentes -->
                    <?php $existingQs = $qwq['questions'] ?? []; ?>
                    <?php if (!empty($existingQs)): ?>
                    <div class="mt-4 pt-4 border-t border-amber-200">
                        <p class="text-xs font-bold text-gray-700 mb-3"><?= __('courses.show.existing_questions_count', ['count' => count($existingQs)]) ?></p>
                        <div class="space-y-3">
                            <?php foreach ($existingQs as $qi => $eq): ?>
                            <div class="bg-white rounded-xl border border-amber-100 p-4">
                                <div class="flex items-start gap-3">
                                    <span class="w-5 h-5 rounded-md bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-black flex-shrink-0 mt-0.5"><?= $qi + 1 ?></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-800 text-sm mb-2"><?= htmlspecialchars($eq['question'] ?? '') ?></p>
                                        <ul class="space-y-1">
                                            <?php foreach ($eq['options'] ?? [] as $opt): ?>
                                            <li class="flex items-center gap-2 text-xs <?= $opt['is_correct'] ? 'text-emerald-700 font-bold' : 'text-gray-500' ?>">
                                                <?php if ($opt['is_correct']): ?>
                                                <svg class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                <?php else: ?>
                                                <span class="w-3 h-3 rounded-full border border-gray-300 flex-shrink-0"></span>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($opt['option_text'] ?? '') ?>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <form method="POST"
                                          action="/cursos/<?= $course['id'] ?>/quizzes/<?= $qwq['id'] ?>/preguntas/<?= $eq['id'] ?>/eliminar"
                                          onsubmit="return confirm('<?= addslashes(__('courses.show.confirm_delete_question')) ?>')"
                                          class="flex-shrink-0">
                                        <?= \App\Core\CSRF::field() ?>
                                        <button type="submit"
                                            class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center transition-colors" title="<?= __('common.delete') ?>">
                                            <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="mt-4 pt-4 border-t border-amber-200 text-xs text-gray-400 text-center py-2"><?= __('courses.show.no_questions_yet') ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>

        <!-- ─── Sidebar ─────────────────────────────────────── -->
        <?php
        ?>
        <div class="lg:col-span-1">
            <div class="sticky top-6 space-y-4">

            <?php if ($isManager): ?>
            <!-- ── Panel de gestión (admin / teacher propietario) ── -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-lg p-6">
                <div class="flex items-center gap-2 mb-5">
                    <span class="w-8 h-8 rounded-xl bg-purple-100 flex items-center justify-center text-base">🛠️</span>
                    <h3 class="font-black text-navy text-sm"><?= __('courses.show.course_management') ?></h3>
                </div>

                <!-- Estado -->
                <div class="flex items-center justify-between mb-5 pb-5 border-b border-gray-100">
                    <span class="text-xs font-bold text-gray-500"><?= __('common.status') ?></span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full
                        <?= $course['status'] === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?= $course['status'] === 'published' ? 'bg-emerald-500' : 'bg-amber-500' ?>"></span>
                        <?= $course['status'] === 'published' ? __('courses.create.status_published') : __('courses.create.status_draft') ?>
                    </span>
                </div>

                <!-- Acciones -->
                <div class="space-y-2">
                    <button type="button" onclick="toggleEditPanel()"
                       id="btn-edit-curso"
                       class="flex items-center gap-3 w-full px-4 py-3 rounded-xl bg-slate-50 hover:bg-slate-100 text-navy font-bold text-sm transition-all text-left">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <?= __('courses.edit.heading') ?>
                    </button>
                    <a href="/cursos/<?= $course['id'] ?>/lecciones/crear"
                       class="flex items-center gap-3 w-full px-4 py-3 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-600 font-bold text-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <?= __('courses.show.add_lesson') ?>
                    </a>
                    <a href="/cursos/<?= $course['id'] ?>/quizzes/crear"
                       class="flex items-center gap-3 w-full px-4 py-3 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <?= __('courses.show.add_quiz') ?>
                    </a>
                </div>

                <!-- Stats del curso -->
                <div class="mt-5 pt-5 border-t border-gray-100 grid grid-cols-2 gap-3">
                    <div class="text-center p-3 bg-slate-50 rounded-xl">
                        <p class="text-2xl font-black text-navy"><?= $lessonCount ?></p>
                        <p class="text-xs text-gray-500 font-semibold"><?= __('courses.show.lessons_label') ?></p>
                    </div>
                    <div class="text-center p-3 bg-slate-50 rounded-xl">
                        <p class="text-2xl font-black text-navy"><?= $quizCount ?></p>
                        <p class="text-xs text-gray-500 font-semibold"><?= __('courses.show.quizzes_label') ?></p>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <?php
            // Calcular si el estudiante completó todas las lecciones activas
            $activeL      = array_filter($lessons, fn($l) => $l['is_active']);
            $doneCount    = count(array_intersect(
                array_column(array_values($activeL), 'id'),
                $completedLessonIds ?? []
            ));
            $courseIsDone = $enrolled && count($activeL) > 0 && $doneCount >= count($activeL);
            ?>
            <!-- ── Panel de inscripción (estudiantes) ── -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-lg p-6">

                <!-- CTA -->
                <div class="py-5 border-b border-gray-100">
                    <?php if ($enrolled): ?>
                    <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3 rounded-xl mb-4 font-bold">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?= __('courses.show.enrolled_message') ?>
                    </div>
                    <?php if (!empty($lessons)): ?>
                    <a href="/cursos/<?= $course['id'] ?>/lecciones/<?= $lessons[0]['id'] ?>"
                       class="block w-full text-center bg-brand-500 hover:bg-brand-600 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5">
                        <?= __('courses.show.continue_course') ?> →
                    </a>
                    <?php endif; ?>
                    <?php else: ?>
                    <a href="https://pamel.edu.pa/formulario-de-admision"
                       target="_blank" rel="noopener noreferrer"
                       class="block w-full text-center bg-brand-500 hover:bg-brand-600 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5 mb-3">
                        <?= __('courses.show.request_admission') ?>
                    </a>
                    <p class="text-xs text-center text-gray-400 mt-2"><?= __('courses.show.request_admission_hint') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Beneficios -->
                <ul class="pt-5 space-y-3">
                    <?php foreach ([
                        [__('courses.show.benefit_lessons_included', ['count' => $lessonCount]),      '📚'],
                        [__('courses.show.benefit_certified_evaluations', ['count' => $quizCount]),   '📝'],
                        [__('courses.show.benefit_imo_certificate'),              '🏆'],
                        [__('courses.show.benefit_stcw_compatible'),              '⚓'],
                    ] as $b): ?>
                    <li class="flex items-center gap-3 text-sm text-gray-600 font-semibold">
                        <span class="text-base w-6 text-center"><?= $b[1] ?></span>
                        <?= $b[0] ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Foro del curso -->
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="#foro-curso"
                       class="flex items-center justify-between w-full bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-sm px-4 py-3 rounded-xl transition group">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-comments text-blue-500"></i>
                            <?= __('courses.show.forum_title') ?>
                        </span>
                        <span class="text-xs bg-blue-100 group-hover:bg-blue-200 text-blue-600 rounded-full px-2 py-0.5 transition font-bold">
                            <?= count($_forumTopics ?? []) ?>
                        </span>
                    </a>
                </div>

                <!-- Formulario de satisfacción (siempre visible si habilitado) -->
                <?php if ($enrolled && !empty($course['satisfaction_enabled'])): ?>
                <div class="mt-5 pt-5 border-t border-gray-100">
                    <?php if ($courseIsDone): ?>
                    <a href="<?= cms_url() ?>/formulario-de-satisfaccion"
                       target="_blank" rel="noopener noreferrer"
                       class="flex items-center justify-center gap-2 w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 rounded-xl text-sm transition-all shadow-lg shadow-purple-500/25 hover:-translate-y-0.5">
                        ⭐ <?= __('courses.show.satisfaction_form_cta') ?>
                    </a>
                    <p class="text-xs text-center text-purple-400 mt-2 font-semibold"><?= __('courses.show.satisfaction_thanks') ?></p>
                    <?php else: ?>
                    <div class="relative">
                        <div class="flex items-center justify-center gap-2 w-full bg-gray-100 text-gray-400 font-bold py-3 rounded-xl text-sm cursor-not-allowed border-2 border-dashed border-gray-200 select-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <?= __('courses.show.satisfaction_form_cta') ?>
                        </div>
                        <p class="text-xs text-center text-gray-400 mt-2">
                            <?= __('courses.show.satisfaction_locked_hint') ?>
                            <strong class="text-navy"><?= $doneCount ?>/<?= count($activeL) ?></strong> <?= __('courses.show.satisfaction_locked_hint_suffix') ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>


            </div><!-- /sticky -->
        </div>
    </div>
</div>

<div id="foro-curso" class="max-w-7xl mx-auto px-4 pb-16 mt-10">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center gap-3">
                <i class="fas fa-comments text-blue-500 text-lg"></i>
                <h2 class="font-extrabold text-gray-800 text-base"><?= __('courses.show.forum_title') ?></h2>
                <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 text-xs font-bold"><?= count($_forumTopics) ?></span>
            </div>
            <?php if ($user): ?>
            <button onclick="document.getElementById('forum-new-topic').classList.toggle('hidden')"
                    class="text-sm font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                <i class="fas fa-plus text-xs"></i> <?= __('courses.show.forum_new_topic') ?>
            </button>
            <?php endif; ?>
        </div>

        <?php if ($user): ?>
        <div id="forum-new-topic" class="hidden border-b border-gray-100 p-5 bg-blue-50">
            <form method="POST" action="/foro/curso/<?= (int) $course['id'] ?>/nuevo" class="space-y-3">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_csrfToken) ?>">
                <input type="text" name="title" required maxlength="200"
                       class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 outline-none text-sm transition"
                       placeholder="<?= __('courses.show.forum_title_placeholder') ?>">
                <textarea name="content" rows="3" required
                          class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 outline-none text-sm resize-none transition"
                          placeholder="<?= __('courses.show.forum_content_placeholder') ?>"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('forum-new-topic').classList.add('hidden')"
                            class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 transition"><?= __('common.cancel') ?></button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-5 py-2 rounded-lg transition">
                        <?= __('courses.show.forum_publish') ?>
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <?php if (empty($_forumTopics)): ?>
        <div class="py-12 text-center text-gray-400 text-sm">
            <i class="fas fa-comment-slash text-3xl mb-3 block"></i>
            <?= __('courses.show.forum_no_topics') ?> <?= $user ? __('courses.show.forum_be_first') : '' ?>
        </div>
        <?php else: ?>
        <div class="divide-y divide-gray-50">
            <?php foreach ($_forumTopics as $_topic): ?>
            <a href="/foro/tema/<?= $_topic['id'] ?>"
               class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition group">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 text-blue-600 font-bold text-sm">
                    <?= mb_strtoupper(mb_substr($_topic['user_name'], 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm group-hover:text-blue-600 transition truncate">
                        <?= htmlspecialchars($_topic['title']) ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <?= htmlspecialchars($_topic['user_name']) ?>
                        · <?= date('d/m/Y', strtotime($_topic['created_at'])) ?>
                    </p>
                </div>
                <div class="flex-shrink-0 text-right">
                    <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-bold">
                        <?= (int)$_topic['replies_count'] ?> <?= __('courses.show.forum_replies_abbr') ?>
                    </span>
                </div>
                <i class="fas fa-chevron-right text-gray-300 group-hover:text-blue-400 transition flex-shrink-0"></i>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($isManager): ?>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<?php endif; ?>
<script>
function toggleEditPanel() {
    var panel = document.getElementById('edit-panel');
    var btn   = document.getElementById('btn-edit-curso');
    if (!panel) return;
    panel.classList.toggle('hidden');
    var isOpen = !panel.classList.contains('hidden');
    btn.classList.toggle('bg-brand-50', isOpen);
    btn.classList.toggle('text-brand-600', isOpen);
    btn.classList.toggle('bg-slate-50', !isOpen);
    if (isOpen) {
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

var lessonEditors = {};

function toggleLessonEditPanel(lessonId) {
    var panel   = document.getElementById('lesson-edit-panel-' + lessonId);
    if (!panel) return;
    var isHidden = panel.classList.contains('hidden');

    // Cierra todos los paneles de lección abiertos
    document.querySelectorAll('[id^="lesson-edit-panel-"]').forEach(function(p) {
        p.classList.add('hidden');
    });

    if (isHidden) {
        panel.classList.remove('hidden');
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        // Inicializa CKEditor la primera vez que se abre
        var editorEl = document.getElementById('editor_lesson_' + lessonId);
        if (editorEl && !lessonEditors[lessonId] && typeof ClassicEditor !== 'undefined') {
            ClassicEditor.create(editorEl, {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', '|',
                    'bulletedList', 'numberedList', '|',
                    'outdent', 'indent', '|',
                    'blockQuote', 'insertTable', '|',
                    'link', 'uploadImage', '|',
                    'undo', 'redo'
                ],
                heading: {
                    options: [
                        { model: 'paragraph', title: '<?= addslashes(__('courses.show.ck_paragraph')) ?>',   class: 'ck-heading_paragraph' },
                        { model: 'heading2',  view: 'h2', title: '<?= addslashes(__('courses.show.ck_heading2')) ?>', class: 'ck-heading_heading2' },
                        { model: 'heading3',  view: 'h3', title: '<?= addslashes(__('courses.show.ck_heading3')) ?>', class: 'ck-heading_heading3' },
                        { model: 'heading4',  view: 'h4', title: '<?= addslashes(__('courses.show.ck_heading4')) ?>', class: 'ck-heading_heading4' },
                    ]
                },
                simpleUpload: {
                    uploadUrl: '/upload/imagen',
                    headers: {
                        'X-CSRF-Token': (document.querySelector('input[name="_csrf"]') || {}).value || ''
                    }
                },
                image: {
                    toolbar: ['imageTextAlternative', '|', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side']
                }
            }).then(function(editor) {
                lessonEditors[lessonId] = editor;
                // Sincronizar contenido del editor al textarea antes de enviar el form
                panel.querySelector('form').addEventListener('submit', function() {
                    editorEl.value = editor.getData();
                });
            }).catch(console.error);
        }
    }
}

function toggleLessonTypeFields(lessonId) {
    var type = document.getElementById('ltype_' + lessonId).value;
    var videoField   = document.getElementById('lfield_video_' + lessonId);
    var contentField = document.getElementById('lfield_content_' + lessonId);
    if (videoField)   videoField.classList.toggle('hidden', type !== 'video');
    if (contentField) contentField.classList.toggle('hidden', type === 'file');
}

// ── Lesson quiz panels ────────────────────────────────────────
function toggleLessonQuizPanel(lessonId) {
    var panel = document.getElementById('lesson-quiz-panel-' + lessonId);
    if (panel) panel.classList.toggle('hidden');
}
function toggleCreateLessonQuiz(lessonId) {
    var panel = document.getElementById('create-lq-panel-' + lessonId);
    if (panel) panel.classList.toggle('hidden');
}

var clqCounts = {}, newLqCounts = {};

function addCLQQuestion(lessonId) {
    clqCounts[lessonId] = (clqCounts[lessonId] ?? 0);
    var i = clqCounts[lessonId]++;
    document.getElementById('clq-container-' + lessonId)
        .insertAdjacentHTML('beforeend', lqInlineBlock('clq' + lessonId, i, 'lq-edit-form-' + lessonId));
}
function addNewLQQuestion(lessonId) {
    newLqCounts[lessonId] = (newLqCounts[lessonId] ?? 0);
    var i = newLqCounts[lessonId]++;
    document.getElementById('new-lq-container-' + lessonId)
        .insertAdjacentHTML('beforeend', lqInlineBlock('nlq' + lessonId, i, 'create-lq-form-' + lessonId));
}
function lqInlineBlock(pfx, i, formId) {
    return '<div class="border border-gray-100 rounded-xl p-3 bg-white" id="' + pfx + '_b' + i + '">'
        + '<div class="flex justify-between mb-2"><span class="text-xs font-black text-navy"><?= addslashes(__('courses.show.js_question_short_label')) ?>' + (i+1) + '</span>'
        + '<button type="button" onclick="document.getElementById(\'' + pfx + '_b' + i + '\').remove()" class="text-xs text-red-400 hover:text-red-600 font-bold">✕</button></div>'
        + '<input type="text" name="questions[' + i + '][text]" required placeholder="<?= addslashes(__('courses.show.js_question_placeholder')) ?>" class="w-full px-2 py-2 border border-gray-200 rounded-lg text-xs mb-2 focus:outline-none focus:border-amber-400">'
        + '<div class="flex gap-2 mb-2">'
        + '<select name="questions[' + i + '][type]" onchange="lqInlineToggle(\'' + pfx + '\',' + i + ',this.value)" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white focus:outline-none focus:border-amber-400">'
        + '<option value="multiple_choice"><?= addslashes(__('courses.show.js_type_multiple_short')) ?></option><option value="true_false"><?= addslashes(__('courses.show.js_type_truefalse_short')) ?></option></select>'
        + '<input type="number" name="questions[' + i + '][points]" value="1" min="1" class="w-12 text-xs border border-gray-200 rounded-lg px-2 py-1.5 text-center focus:outline-none focus:border-amber-400">'
        + '</div>'
        + '<div id="' + pfx + '_o' + i + '" class="space-y-1">'
        + lqInlineOpt(pfx, i, 0) + lqInlineOpt(pfx, i, 1) + lqInlineOpt(pfx, i, 2) + lqInlineOpt(pfx, i, 3)
        + '</div>'
        + '<button type="button" onclick="lqInlineAddOpt(\'' + pfx + '\',' + i + ')" class="text-xs text-amber-600 hover:underline mt-1 font-bold"><?= addslashes(__('courses.show.js_add_option_short')) ?></button>'
        + '</div>';
}
function lqInlineOpt(pfx, i, oi) {
    return '<div class="flex items-center gap-1.5 p-1.5 border border-gray-100 rounded-lg">'
        + '<input type="radio" name="questions[' + i + '][correct]" value="' + oi + '" class="text-amber-500 w-3.5 h-3.5">'
        + '<input type="text" name="questions[' + i + '][options][' + oi + '][text]" required placeholder="<?= addslashes(__('courses.show.js_option_placeholder')) ?>' + (oi+1) + '" class="flex-1 text-xs text-gray-700 bg-transparent outline-none">'
        + '<input type="hidden" name="questions[' + i + '][options][' + oi + '][is_correct]" value="0" id="' + pfx + '_h' + i + '_' + oi + '">'
        + '</div>';
}
var lqInlineOptCounts = {};
function lqInlineAddOpt(pfx, i) {
    var key = pfx + '_' + i;
    lqInlineOptCounts[key] = (lqInlineOptCounts[key] ?? 4);
    var oi = lqInlineOptCounts[key]++;
    document.getElementById(pfx + '_o' + i).insertAdjacentHTML('beforeend', lqInlineOpt(pfx, i, oi));
}
function lqInlineToggle(pfx, i, type) {
    var c = document.getElementById(pfx + '_o' + i);
    if (type === 'true_false') {
        c.innerHTML = '<div class="flex items-center gap-1.5 p-1.5 border border-gray-100 rounded-lg"><input type="radio" name="questions[' + i + '][correct]" value="0" class="w-3.5 h-3.5"><span class="flex-1 text-xs text-gray-700"><?= addslashes(__('courses.show.js_true')) ?></span><input type="hidden" name="questions[' + i + '][options][0][text]" value="<?= addslashes(__('courses.show.js_true')) ?>"><input type="hidden" name="questions[' + i + '][options][0][is_correct]" value="0" id="' + pfx + '_h' + i + '_0"></div>'
            + '<div class="flex items-center gap-1.5 p-1.5 border border-gray-100 rounded-lg"><input type="radio" name="questions[' + i + '][correct]" value="1" class="w-3.5 h-3.5"><span class="flex-1 text-xs text-gray-700"><?= addslashes(__('courses.show.js_false')) ?></span><input type="hidden" name="questions[' + i + '][options][1][text]" value="<?= addslashes(__('courses.show.js_false')) ?>"><input type="hidden" name="questions[' + i + '][options][1][is_correct]" value="0" id="' + pfx + '_h' + i + '_1"></div>';
    }
}
// Sync radio → hidden is_correct for lesson quiz forms
document.addEventListener('change', function(e) {
    if (e.target.type !== 'radio' || !e.target.name.includes('[correct]')) return;
    var form = e.target.closest('form[id^="lq-edit-form-"], form[id^="create-lq-form-"]');
    if (!form) return;
    var m = e.target.name.match(/questions\[(\d+)\]/);
    if (!m) return;
    var qi = m[1];
    form.querySelectorAll('input[type="hidden"]').forEach(function(el) {
        if (el.name.includes('questions[' + qi + ']') && el.name.includes('[is_correct]')) el.value = '0';
    });
    // Find the id from the form prefix
    var pfxMatch = form.id.replace('lq-edit-form-', 'clq').replace('create-lq-form-', 'nlq');
    var hid = document.getElementById(pfxMatch + '_h' + qi + '_' + e.target.value);
    if (hid) hid.value = '1';
});

// ── Quiz inline edit ──────────────────────────────────────────
function toggleQuizEditPanel(quizId) {
    var panel    = document.getElementById('quiz-edit-panel-' + quizId);
    if (!panel) return;
    var isHidden = panel.classList.contains('hidden');

    document.querySelectorAll('[id^="quiz-edit-panel-"]').forEach(function(p) {
        p.classList.add('hidden');
    });

    if (isHidden) {
        panel.classList.remove('hidden');
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

var quizQuestionCounts = {};

function addQuizQuestion(quizId) {
    quizQuestionCounts[quizId] = (quizQuestionCounts[quizId] ?? 0);
    var i = quizQuestionCounts[quizId]++;
    var container = document.getElementById('quiz-questions-container-' + quizId);
    var html = '<div class="border-2 border-gray-100 rounded-xl p-4 bg-white" id="qqblock_' + quizId + '_' + i + '">'
        + '<div class="flex items-center justify-between mb-3">'
        + '<span class="text-xs font-black text-navy"><?= addslashes(__('courses.show.js_question_short_label')) ?> ' + (i + 1) + '</span>'
        + '<button type="button" onclick="removeQuizQuestion(\'' + quizId + '\',' + i + ')" class="text-xs text-red-400 hover:text-red-600 hover:bg-red-50 px-2 py-1 rounded-lg transition-colors font-bold"><?= addslashes(__('courses.show.js_remove')) ?></button>'
        + '</div>'
        + '<div class="space-y-3">'
        + '<input type="text" name="questions[' + i + '][text]" required placeholder="<?= addslashes(__('courses.show.js_question_text_placeholder')) ?>" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-amber-400 transition-all">'
        + '<div class="flex items-center gap-3">'
        + '<select name="questions[' + i + '][type]" onchange="toggleQType(\'' + quizId + '\',' + i + ',this.value)" class="px-3 py-2 border-2 border-gray-200 rounded-xl text-xs focus:outline-none focus:border-amber-400 transition-all bg-white font-semibold">'
        + '<option value="multiple_choice"><?= addslashes(__('courses.show.js_type_multiple_choice')) ?></option>'
        + '<option value="true_false"><?= addslashes(__('courses.show.js_type_truefalse')) ?></option>'
        + '</select>'
        + '<input type="number" name="questions[' + i + '][points]" value="1" min="1" class="w-16 px-2 py-2 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-amber-400 transition-all text-center font-bold">'
        + '<span class="text-xs text-gray-400 font-semibold"><?= addslashes(__('courses.show.js_points_abbr')) ?></span>'
        + '</div>'
        + '<div id="qqopts_' + quizId + '_' + i + '" class="space-y-2">'
        + qqOptionHTML(quizId, i, 0) + qqOptionHTML(quizId, i, 1) + qqOptionHTML(quizId, i, 2) + qqOptionHTML(quizId, i, 3)
        + '</div>'
        + '<button type="button" onclick="addQQOption(\'' + quizId + '\',' + i + ')" class="text-xs font-bold text-amber-600 hover:underline"><?= addslashes(__('courses.show.js_add_option')) ?></button>'
        + '</div>'
        + '</div>';
    container.insertAdjacentHTML('beforeend', html);
    // radio → hidden sync
    document.getElementById('quiz-update-form-' + quizId).addEventListener('change', qqRadioSync);
}

function qqOptionHTML(quizId, qi, oi) {
    return '<div class="flex items-center gap-2 p-2.5 border-2 border-gray-100 rounded-xl" id="qqopt_' + quizId + '_' + qi + '_' + oi + '">'
        + '<input type="radio" name="questions[' + qi + '][correct]" value="' + oi + '" class="text-amber-500 w-4 h-4 flex-shrink-0">'
        + '<input type="text" name="questions[' + qi + '][options][' + oi + '][text]" required placeholder="<?= addslashes(__('courses.show.js_option_placeholder')) ?>' + (oi + 1) + '" class="flex-1 text-sm font-semibold text-gray-700 bg-transparent outline-none placeholder-gray-300">'
        + '<input type="hidden" name="questions[' + qi + '][options][' + oi + '][is_correct]" value="0" id="qqhid_' + quizId + '_' + qi + '_' + oi + '">'
        + '</div>';
}

var qqOptCounts = {};
function addQQOption(quizId, qi) {
    var key = quizId + '_' + qi;
    qqOptCounts[key] = (qqOptCounts[key] ?? 4);
    var oi = qqOptCounts[key]++;
    document.getElementById('qqopts_' + quizId + '_' + qi).insertAdjacentHTML('beforeend', qqOptionHTML(quizId, qi, oi));
}

function toggleQType(quizId, qi, type) {
    var container = document.getElementById('qqopts_' + quizId + '_' + qi);
    if (type === 'true_false') {
        container.innerHTML =
            '<div class="flex items-center gap-2 p-2.5 border-2 border-gray-100 rounded-xl">'
            + '<input type="radio" name="questions[' + qi + '][correct]" value="0" class="text-amber-500 w-4 h-4 flex-shrink-0">'
            + '<span class="flex-1 text-sm font-semibold text-gray-700"><?= addslashes(__('courses.show.js_true')) ?></span>'
            + '<input type="hidden" name="questions[' + qi + '][options][0][text]" value="<?= addslashes(__('courses.show.js_true')) ?>">'
            + '<input type="hidden" name="questions[' + qi + '][options][0][is_correct]" value="0" id="qqhid_' + quizId + '_' + qi + '_0">'
            + '</div>'
            + '<div class="flex items-center gap-2 p-2.5 border-2 border-gray-100 rounded-xl">'
            + '<input type="radio" name="questions[' + qi + '][correct]" value="1" class="text-amber-500 w-4 h-4 flex-shrink-0">'
            + '<span class="flex-1 text-sm font-semibold text-gray-700"><?= addslashes(__('courses.show.js_false')) ?></span>'
            + '<input type="hidden" name="questions[' + qi + '][options][1][text]" value="<?= addslashes(__('courses.show.js_false')) ?>">'
            + '<input type="hidden" name="questions[' + qi + '][options][1][is_correct]" value="0" id="qqhid_' + quizId + '_' + qi + '_1">'
            + '</div>';
    }
}

function removeQuizQuestion(quizId, i) {
    var el = document.getElementById('qqblock_' + quizId + '_' + i);
    if (el) el.remove();
}

function qqRadioSync(e) {
    if (e.target.type !== 'radio' || !e.target.name.includes('[correct]')) return;
    var m = e.target.name.match(/questions\[(\d+)\]/);
    if (!m) return;
    var qi = m[1];
    // Find all hidden is_correct for this question index in this form
    var form = e.target.closest('form');
    form.querySelectorAll('input[id^="qqhid_"]').forEach(function(el) {
        if (el.id.includes('_' + qi + '_')) el.value = '0';
    });
    // Set the selected one
    var formId = form.id; // quiz-update-form-{quizId}
    var quizId = formId.replace('quiz-update-form-', '');
    var hidId = 'qqhid_' + quizId + '_' + qi + '_' + e.target.value;
    var hid = document.getElementById(hidId);
    if (hid) hid.value = '1';
}
</script>

