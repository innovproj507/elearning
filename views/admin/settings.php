<?php
$groups = [
    'general'      => ['label' => __('admin.settings.tabGeneral'),      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
    'appearance'   => ['label' => __('admin.settings.tabAppearance'),   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>'],
    'registration' => ['label' => __('admin.settings.tabRegistration'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>'],
    'courses'      => ['label' => __('admin.settings.tabCourses'),      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'],
];
$activeTab = $_GET['tab'] ?? 'general';
if (!array_key_exists($activeTab, $groups)) $activeTab = 'general';
?>

<div class="mb-6">
    <h2 class="text-2xl font-black text-navy"><?= __('admin.settings.title') ?></h2>
    <p class="text-gray-500 text-sm mt-1"><?= __('admin.settings.subtitle') ?></p>
</div>

<div class="flex gap-6 items-start">

    <!-- ── Tabs lateral ────────────────────────────────── -->
    <aside class="w-52 flex-shrink-0">
        <nav class="bg-white rounded-2xl border border-gray-100 shadow-sm p-2 space-y-0.5">
            <?php foreach ($groups as $key => $g): ?>
            <a href="?tab=<?= $key ?>"
               class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all
                      <?= $activeTab === $key ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/25' : 'text-gray-600 hover:bg-slate-50' ?>">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $g['icon'] ?></svg>
                <?= $g['label'] ?>
            </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- ── Formulario ──────────────────────────────────── -->
    <div class="flex-1 min-w-0">
        <form method="POST" action="/admin/configuracion">
            <?= \App\Core\CSRF::field() ?>

            <!-- Campos del tab activo -->
            <?php if (!empty($settings[$activeTab])): ?>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-50">

                <div class="px-6 py-4 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $groups[$activeTab]['icon'] ?></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-navy text-sm"><?= $groups[$activeTab]['label'] ?></h3>
                        <p class="text-xs text-gray-400"><?= __('admin.settings.sectionHint') ?></p>
                    </div>
                </div>

                <?php foreach ($settings[$activeTab] as $key => $row): ?>
                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-start gap-4">
                    <div class="sm:w-56 flex-shrink-0">
                        <label for="<?= $key ?>" class="text-sm font-bold text-gray-700 block">
                            <?= htmlspecialchars($row['label']) ?>
                        </label>
                        <p class="text-xs text-gray-400 mt-0.5 font-mono"><?= $key ?></p>
                    </div>
                    <div class="flex-1">
                        <?php
                        $val = htmlspecialchars($row['value'] ?? '');
                        switch ($row['type']):
                            case 'boolean': ?>
                            <label class="inline-flex items-center gap-3 cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" id="<?= $key ?>" name="<?= $key ?>" value="1"
                                           class="sr-only peer" <?= !empty($row['value']) ? 'checked' : '' ?>>
                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-brand-500 transition-colors"></div>
                                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-600 peer-checked:text-brand-600">
                                    <?= !empty($row['value']) ? __('admin.settings.on') : __('admin.settings.off') ?>
                                </span>
                            </label>
                            <?php break;
                            case 'textarea': ?>
                            <textarea id="<?= $key ?>" name="<?= $key ?>" rows="3"
                                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all resize-none"><?= $val ?></textarea>
                            <?php break;
                            case 'select':
                                $options = $key === 'default_role' ? ['student' => __('admin.settings.roleStudent'), 'teacher' => __('admin.settings.roleTeacher')] : [];
                            ?>
                            <select id="<?= $key ?>" name="<?= $key ?>"
                                    class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 bg-white font-semibold text-gray-700 w-full sm:w-auto">
                                <?php foreach ($options as $optVal => $optLabel): ?>
                                <option value="<?= $optVal ?>" <?= $row['value'] === $optVal ? 'selected' : '' ?>><?= $optLabel ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php break;
                            case 'number': ?>
                            <input type="number" id="<?= $key ?>" name="<?= $key ?>" value="<?= $val ?>"
                                   class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all w-32">
                            <?php break;
                            case 'email': ?>
                            <input type="email" id="<?= $key ?>" name="<?= $key ?>" value="<?= $val ?>"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
                            <?php break;
                            default: ?>
                            <input type="text" id="<?= $key ?>" name="<?= $key ?>" value="<?= $val ?>"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
                            <?php break; endswitch; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Botones -->
            <div class="flex items-center justify-end gap-3 mt-5">
                <a href="/admin/configuracion?tab=<?= $activeTab ?>"
                   class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
                    <?= __('admin.settings.cancel') ?>
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold bg-brand-500 hover:bg-brand-600 text-white transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <?= __('admin.settings.saveChanges') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
/* Actualiza el label del toggle en tiempo real */
document.querySelectorAll('input[type=checkbox]').forEach(cb => {
    const label = cb.closest('label')?.querySelector('span');
    if (!label) return;
    cb.addEventListener('change', () => {
        label.textContent = cb.checked ? <?= json_encode(__('admin.settings.on')) ?> : <?= json_encode(__('admin.settings.off')) ?>;
    });
});
</script>
