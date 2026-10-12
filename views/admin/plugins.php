<div class="mb-6">
    <h2 class="text-2xl font-black text-navy"><?= __('admin.plugins.title') ?></h2>
    <p class="text-gray-500 text-sm mt-1"><?= __('admin.plugins.subtitle') ?></p>
</div>

<!-- Info -->
<div class="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4 mb-6 flex items-start gap-3">
    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div class="text-sm text-blue-800">
        <p class="font-bold mb-1"><?= __('admin.plugins.howToInstallTitle') ?></p>
        <p class="text-blue-700"><?= __('admin.plugins.howToInstallBody') ?></p>
    </div>
</div>

<?php if (empty($plugins)): ?>
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center">
    <div class="text-5xl mb-3">🔌</div>
    <p class="font-semibold text-gray-500 mb-2"><?= __('admin.plugins.emptyTitle') ?></p>
    <p class="text-sm text-gray-400"><?= __('admin.plugins.emptySubtitle') ?></p>
</div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    <?php foreach ($plugins as $plugin): ?>
    <div class="bg-white rounded-2xl border <?= $plugin['is_active'] ? 'border-brand-200 shadow-md shadow-brand-500/10' : 'border-gray-100 shadow-sm' ?> p-6 flex flex-col gap-4 transition-all">
        <!-- Header -->
        <div class="flex items-start justify-between gap-3">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0
                        <?= $plugin['is_active'] ? 'bg-brand-50 border border-brand-200' : 'bg-gray-100 border border-gray-200' ?>">
                🔌
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full flex-shrink-0
                <?= $plugin['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>">
                <span class="w-1.5 h-1.5 rounded-full <?= $plugin['is_active'] ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' ?>"></span>
                <?= $plugin['is_active'] ? __('admin.plugins.active') : __('admin.plugins.inactive') ?>
            </span>
        </div>

        <!-- Info -->
        <div class="flex-1">
            <h3 class="font-black text-navy text-sm mb-1"><?= htmlspecialchars($plugin['name']) ?></h3>
            <?php if (!empty($plugin['description'])): ?>
            <p class="text-xs text-gray-500 leading-relaxed"><?= htmlspecialchars($plugin['description']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Meta -->
        <div class="flex items-center justify-between text-xs text-gray-400 border-t border-gray-100 pt-3">
            <span class="font-mono bg-gray-100 px-2 py-0.5 rounded">v<?= htmlspecialchars($plugin['version']) ?></span>
            <?php if (!empty($plugin['author'])): ?>
            <span class="font-semibold truncate ml-2"><?= htmlspecialchars($plugin['author']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Toggle -->
        <form method="POST" action="/admin/plugins/<?= $plugin['id'] ?>/toggle">
            <?= \App\Core\CSRF::field() ?>
            <button type="submit"
                class="w-full py-2.5 rounded-xl text-sm font-bold transition-all
                    <?= $plugin['is_active']
                        ? 'bg-red-50 text-red-600 hover:bg-red-100 border border-red-200'
                        : 'bg-brand-500 text-white hover:bg-brand-600 shadow-lg shadow-brand-500/25 hover:-translate-y-0.5' ?>">
                <?= $plugin['is_active'] ? __('admin.plugins.deactivate') : __('admin.plugins.activate') ?>
            </button>
        </form>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Guía de desarrollo -->
<div class="mt-8 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
    <h3 class="font-black text-navy mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        <?= __('admin.plugins.devGuideTitle') ?>
    </h3>
    <pre class="bg-slate-900 text-emerald-400 text-xs rounded-xl p-5 overflow-x-auto font-mono leading-relaxed">plugins/
└── mi-plugin/
    ├── manifest.json          <span class="text-slate-400"><?= __('admin.plugins.manifestComment') ?></span>
    └── plugin.php             <span class="text-slate-400"><?= __('admin.plugins.pluginPhpComment') ?></span>

<span class="text-amber-400">// manifest.json</span>
{
  "name": "<?= __('admin.plugins.samplePluginName') ?>",
  "slug": "mi-plugin",
  "version": "1.0.0",
  "description": "<?= __('admin.plugins.samplePluginDescription') ?>",
  "author": "<?= __('admin.plugins.samplePluginAuthor') ?>"
}

<span class="text-amber-400">// plugin.php</span>
&lt;?php
use App\Core\PluginManager;

PluginManager::addHook('curso.completado', function(array $data): array {
    <span class="text-slate-400">// <?= __('admin.plugins.sampleLogicComment') ?></span>
    return $data;
});</pre>
</div>
