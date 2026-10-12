<?php $user = $_SESSION['user'] ?? null; ?>

<!-- Header -->
<div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-10 px-6 mb-10">
    <div class="max-w-5xl mx-auto">
        <nav class="text-blue-300 text-sm mb-3">
            <a href="/foro" class="hover:text-white transition"><?= __('forum.show.breadcrumbForums') ?></a>
            <span class="mx-2">›</span>
            <span class="text-white"><?= htmlspecialchars($forum['title']) ?></span>
        </nav>
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold"><?= htmlspecialchars($forum['title']) ?></h1>
                <?php if ($forum['description']): ?>
                <p class="text-blue-200 text-sm mt-1"><?= htmlspecialchars($forum['description']) ?></p>
                <?php endif; ?>
            </div>
            <?php if ($user): ?>
            <a href="/foro/<?= htmlspecialchars($forum['slug']) ?>/nuevo"
               class="flex-shrink-0 bg-white text-blue-700 font-bold text-sm px-5 py-2.5 rounded-lg hover:bg-blue-50 transition shadow">
                <i class="fas fa-plus mr-1"></i> <?= __('forum.show.newTopic') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 pb-16">

    <?php if (empty($topics)): ?>
        <div class="text-center py-16 text-gray-400">
            <i class="fas fa-comment-slash text-4xl mb-3 block"></i>
            <p><?= __('forum.show.emptyTitle') ?></p>
            <?php if ($user): ?>
            <a href="/foro/<?= htmlspecialchars($forum['slug']) ?>/nuevo"
               class="mt-4 inline-block bg-blue-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                <?= __('forum.show.createTopic') ?>
            </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider"><?= __('forum.show.colTopic') ?></th>
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center hidden sm:table-cell"><?= __('forum.show.colReplies') ?></th>
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right hidden md:table-cell"><?= __('forum.show.colDate') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($topics as $topic): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-start gap-3">
                            <?php if ($topic['is_pinned']): ?>
                            <i class="fas fa-thumbtack text-yellow-500 mt-1 flex-shrink-0" title="<?= __('forum.show.pinned') ?>"></i>
                            <?php else: ?>
                            <i class="fas fa-comment-dots text-gray-300 mt-1 flex-shrink-0"></i>
                            <?php endif; ?>
                            <div>
                                <a href="/foro/tema/<?= $topic['id'] ?>"
                                   class="font-semibold text-gray-800 hover:text-blue-600 transition line-clamp-1">
                                    <?= htmlspecialchars($topic['title']) ?>
                                    <?php if ($topic['is_locked']): ?>
                                    <i class="fas fa-lock text-gray-400 text-xs ml-1"></i>
                                    <?php endif; ?>
                                </a>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    <?= __('forum.show.postedBy') ?> <span class="font-medium"><?= htmlspecialchars($topic['user_name']) ?></span>
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center hidden sm:table-cell">
                        <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold">
                            <?= (int) $topic['replies_count'] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-xs text-gray-400 hidden md:table-cell">
                        <?= date('d/m/Y', strtotime($topic['created_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
