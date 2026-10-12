<?php
$flash        = \App\Core\Session::flash('success');
$flashError   = \App\Core\Session::flash('error');
$editId       = (int) ($_GET['editar'] ?? 0);
?>

<div class="max-w-3xl mx-auto">

    <div class="mb-8">
        <h2 class="text-2xl font-black text-navy"><?= __('admin.categories.title') ?></h2>
        <p class="text-gray-500 text-sm mt-1"><?= __('admin.categories.subtitle') ?></p>
    </div>

    <?php if ($flash): ?>
    <div class="mb-5 flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-semibold">
        <span>✓</span> <?= htmlspecialchars($flash) ?>
    </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
    <div class="mb-5 flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-semibold">
        <span>⚠</span> <?= htmlspecialchars($flashError) ?>
    </div>
    <?php endif; ?>

    <!-- Formulario crear -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <h3 class="font-black text-navy text-sm mb-4"><?= __('admin.categories.newCategory') ?></h3>
        <form method="POST" action="/admin/categorias" class="flex gap-3">
            <?= \App\Core\CSRF::field() ?>
            <input type="text" name="name" required placeholder="<?= __('admin.categories.placeholderName') ?>"
                class="flex-1 px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
            <button type="submit"
                class="bg-brand-500 hover:bg-brand-600 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/25">
                <?= __('admin.categories.add') ?>
            </button>
        </form>
    </div>

    <!-- Lista de categorías -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
            <span class="font-black text-navy text-sm"><?= __('admin.categories.existing') ?></span>
            <span class="ml-auto text-xs text-gray-400 font-semibold"><?= __('admin.categories.totalCount', ['count' => count($categories)]) ?></span>
        </div>

        <?php if (empty($categories)): ?>
        <div class="p-12 text-center text-gray-400">
            <div class="text-4xl mb-3">🗂️</div>
            <p class="font-semibold"><?= __('admin.categories.emptyTitle') ?></p>
            <p class="text-sm mt-1"><?= __('admin.categories.emptySubtitle') ?></p>
        </div>
        <?php else: ?>
        <ul class="divide-y divide-gray-100">
            <?php foreach ($categories as $cat): ?>
            <li class="px-6 py-4">
                <?php if ($editId === (int)$cat['id']): ?>
                <!-- Modo edición inline -->
                <form method="POST" action="/admin/categorias/<?= $cat['id'] ?>/editar" class="flex gap-3 items-center">
                    <?= \App\Core\CSRF::field() ?>
                    <input type="text" name="name" required value="<?= htmlspecialchars($cat['name']) ?>"
                        class="flex-1 px-3 py-2 border-2 border-brand-500 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-brand-500/10 transition-all">
                    <button type="submit"
                        class="bg-brand-500 hover:bg-brand-600 text-white font-bold px-4 py-2 rounded-lg text-xs transition-all">
                        <?= __('common.save') ?>
                    </button>
                    <a href="/admin/categorias"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-4 py-2 rounded-lg text-xs transition-all">
                        <?= __('common.cancel') ?>
                    </a>
                </form>
                <?php else: ?>
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <p class="font-bold text-navy text-sm"><?= htmlspecialchars($cat['name']) ?></p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <?= (int)($cat['course_count'] ?? 0) ?> <?= (int)($cat['course_count'] ?? 0) !== 1 ? __('admin.categories.coursesPublished') : __('admin.categories.coursePublished') ?>
                        </p>
                    </div>
                    <a href="/admin/categorias?editar=<?= $cat['id'] ?>"
                        class="text-xs font-bold text-brand-500 hover:text-brand-600 px-3 py-1.5 rounded-lg hover:bg-brand-50 transition-all">
                        <?= __('common.edit') ?>
                    </a>
                    <form method="POST" action="/admin/categorias/<?= $cat['id'] ?>/eliminar"
                        onsubmit="return confirm('<?= addslashes(__('admin.categories.confirmDelete', ['name' => htmlspecialchars($cat['name'], ENT_QUOTES)])) ?>')">
                        <?= \App\Core\CSRF::field() ?>
                        <button type="submit"
                            class="text-xs font-bold text-red-500 hover:text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-all">
                            <?= __('common.delete') ?>
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
