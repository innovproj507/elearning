<div class="max-w-3xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-3">
            <a href="/dashboard" class="hover:text-brand-500 font-semibold transition-colors"><?= __('courses.create.dashboard') ?></a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>" class="hover:text-brand-500 font-semibold transition-colors truncate max-w-xs"><?= htmlspecialchars($course['title']) ?></a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-navy font-semibold"><?= __('common.edit') ?></span>
        </div>
        <h2 class="text-2xl font-black text-navy"><?= __('courses.edit.heading') ?></h2>
        <p class="text-gray-500 text-sm mt-1"><?= __('courses.edit.intro') ?></p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
        <form method="POST" action="/cursos/<?= $course['id'] ?>/editar" enctype="multipart/form-data" class="space-y-6">
            <?= \App\Core\CSRF::field() ?>

            <!-- Título -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('courses.create.title_label') ?> <span class="text-red-500">*</span></label>
                <input type="text" name="title" required
                    value="<?= htmlspecialchars($course['title'], ENT_QUOTES) ?>"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                    placeholder="<?= __('courses.create.title_placeholder') ?>">
            </div>

            <!-- Descripción -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('courses.create.description_label') ?></label>
                <textarea name="description" rows="4"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all resize-none"
                    placeholder="<?= __('courses.create.description_placeholder') ?>"><?= htmlspecialchars($course['description'] ?? '', ENT_QUOTES) ?></textarea>
            </div>

            <!-- Categoría + Nivel -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
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
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
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

            <!-- Gratuito toggle -->
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                <input type="checkbox" name="is_free" value="1" id="is_free"
                    <?= !empty($course['is_free']) ? 'checked' : '' ?>
                    class="w-4 h-4 text-emerald-500 border-gray-300 rounded focus:ring-emerald-500">
                <label for="is_free" class="text-sm font-bold text-emerald-700">
                    <?= __('courses.create.is_free_label') ?>
                </label>
            </div>

            <!-- Formulario de satisfacción -->
            <div class="p-4 bg-purple-50 border border-purple-200 rounded-xl space-y-3">
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="satisfaction_enabled" value="1" id="satisfaction_enabled"
                        <?= !empty($course['satisfaction_enabled']) ? 'checked' : '' ?>
                        class="w-4 h-4 text-purple-500 border-gray-300 rounded focus:ring-purple-500">
                    <label for="satisfaction_enabled" class="text-sm font-bold text-purple-700 cursor-pointer">
                        <?= __('courses.edit.satisfaction_enable_label') ?>
                    </label>
                </div>
                <p class="text-xs text-purple-500 pl-7">
                    <?= __('courses.edit.satisfaction_hint') ?>
                    <strong><?= cms_url() ?>/formulario-de-satisfaccion</strong> <?= __('courses.edit.satisfaction_hint_suffix') ?>
                </p>
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
                         class="h-32 w-auto rounded-xl object-cover border border-gray-200">
                    <p class="text-xs text-gray-400 mt-1"><?= __('courses.edit.current_image_hint') ?></p>
                </div>
                <?php endif; ?>
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-brand-300 transition-colors">
                    <div class="text-3xl mb-2">🖼️</div>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                        class="text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-brand-50 file:text-brand-500 hover:file:bg-brand-100 transition-all cursor-pointer">
                    <p class="text-xs text-gray-400 mt-2"><?= __('courses.create.image_hint') ?></p>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                    class="flex-1 sm:flex-none bg-brand-500 hover:bg-brand-600 text-white font-bold px-8 py-3.5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5">
                    <?= __('courses.edit.save_changes') ?>
                </button>
                <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>"
                    class="flex-1 sm:flex-none text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-8 py-3.5 rounded-xl text-sm transition-all">
                    <?= __('common.cancel') ?>
                </a>
            </div>
        </form>
    </div>
</div>
