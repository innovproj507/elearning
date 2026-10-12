<?php $user = $_SESSION['user'] ?? null; ?>

<!-- Hero -->
<div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-12 px-6 mb-10">
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl font-extrabold mb-2"><?= __('forum.index.heroTitle') ?></h1>
        <p class="text-blue-200 text-sm"><?= __('forum.index.heroSubtitle') ?></p>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 pb-16">

    <?php if (empty($forums)): ?>
        <div class="text-center py-20 text-gray-400">
            <i class="fas fa-comments text-5xl mb-4 block"></i>
            <p class="text-lg"><?= __('forum.index.empty') ?></p>
        </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($forums as $forum): ?>
        <a href="/foro/<?= htmlspecialchars($forum['slug']) ?>"
           class="flex items-center gap-5 bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-blue-200 transition group">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition">
                <i class="fas fa-comments text-blue-500 text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="font-bold text-gray-800 text-base group-hover:text-blue-700 transition"><?= htmlspecialchars($forum['title']) ?></h2>
                <?php if ($forum['description']): ?>
                <p class="text-sm text-gray-500 mt-0.5 truncate"><?= htmlspecialchars($forum['description']) ?></p>
                <?php endif; ?>
            </div>
            <div class="text-right flex-shrink-0">
                <span class="text-2xl font-extrabold text-blue-600"><?= (int) $forum['topics_count'] ?></span>
                <p class="text-xs text-gray-400"><?= __('forum.index.topicsCount') ?></p>
            </div>
            <i class="fas fa-chevron-right text-gray-300 group-hover:text-blue-400 transition"></i>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
