<?php $csrfField = '<input type="hidden" name="_token" value="' . \App\Core\CSRF::generate() . '">';  ?>

<!-- Header -->
<div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-10 px-6 mb-10">
    <div class="max-w-4xl mx-auto">
        <nav class="text-blue-300 text-sm mb-3">
            <?php if (!empty($forum['slug'])): ?>
            <a href="/foro" class="hover:text-white transition"><?= __('forum.topic.breadcrumbForums') ?></a>
            <span class="mx-2">›</span>
            <a href="/foro/<?= htmlspecialchars($forum['slug']) ?>" class="hover:text-white transition"><?= htmlspecialchars($forum['title']) ?></a>
            <span class="mx-2">›</span>
            <?php else: ?>
            <a href="javascript:history.back()" class="hover:text-white transition"><?= __('forum.topic.backLink') ?></a>
            <span class="mx-2">›</span>
            <?php endif; ?>
            <span class="text-white truncate"><?= htmlspecialchars($topic['title']) ?></span>
        </nav>
        <h1 class="text-xl font-extrabold leading-tight"><?= htmlspecialchars($topic['title']) ?></h1>
        <p class="text-blue-200 text-sm mt-1">
            <?= __('forum.topic.postedBy') ?> <?= htmlspecialchars($topic['user_name']) ?>
            · <?= date('d/m/Y H:i', strtotime($topic['created_at'])) ?>
        </p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 pb-16 space-y-6">

    <!-- Mensaje original -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                <?= mb_strtoupper(mb_substr($topic['user_name'], 0, 1)) ?>
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($topic['user_name']) ?></p>
                <p class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($topic['created_at'])) ?></p>
            </div>
            <?php if ($isManager || ($user && (int)($user['id'] ?? 0) === (int)$topic['user_id'])): ?>
            <div class="ml-auto">
                <form method="POST" action="/foro/tema/<?= $topic['id'] ?>/eliminar"
                      onsubmit="return confirm('<?= addslashes(__('forum.topic.deleteTopicConfirm')) ?>');">
                    <?= $csrfField ?>
                    <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <div class="text-gray-700 text-sm leading-relaxed whitespace-pre-wrap">
            <?= nl2br(htmlspecialchars($topic['content'])) ?>
        </div>
    </div>

    <!-- Respuestas -->
    <?php if (!empty($posts)): ?>
    <div class="space-y-4">
        <h3 class="font-bold text-gray-600 text-sm uppercase tracking-wider px-1">
            <?= count($posts) ?> <?= count($posts) !== 1 ? __('forum.topic.replies') : __('forum.topic.reply') ?>
        </h3>
        <?php foreach ($posts as $post): ?>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-sm flex-shrink-0">
                    <?= mb_strtoupper(mb_substr($post['user_name'], 0, 1)) ?>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($post['user_name']) ?></p>
                    <p class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></p>
                </div>
                <?php if ($isManager || ($user && (int)($user['id'] ?? 0) === (int)$post['user_id'])): ?>
                <div class="ml-auto">
                    <form method="POST" action="/foro/post/<?= $post['id'] ?>/eliminar/<?= $topic['id'] ?>"
                          onsubmit="return confirm('<?= addslashes(__('forum.topic.deletePostConfirm')) ?>');">
                        <?= $csrfField ?>
                        <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <div class="text-gray-700 text-sm leading-relaxed whitespace-pre-wrap pl-11">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Formulario de respuesta -->
    <?php if ($user && !$topic['is_locked']): ?>
    <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-6">
        <h3 class="font-bold text-gray-700 text-sm mb-4"><?= __('forum.topic.writeReply') ?></h3>
        <form method="POST" action="/foro/tema/<?= $topic['id'] ?>/responder" class="space-y-3">
            <?= $csrfField ?>
            <textarea name="content" rows="4" required
                      class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 outline-none text-sm resize-none transition"
                      placeholder="<?= __('forum.topic.replyPlaceholder') ?>"></textarea>
            <div class="flex justify-end">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-6 py-2.5 rounded-lg transition shadow">
                    <?= __('forum.topic.submitReply') ?>
                </button>
            </div>
        </form>
    </div>
    <?php elseif ($topic['is_locked']): ?>
    <div class="bg-gray-50 rounded-xl border border-gray-100 p-5 text-center text-sm text-gray-500">
        <i class="fas fa-lock mr-2"></i><?= __('forum.topic.lockedMessage') ?>
    </div>
    <?php elseif (!$user): ?>
    <div class="bg-blue-50 rounded-xl border border-blue-100 p-5 text-center text-sm text-blue-700">
        <a href="/login" class="font-semibold hover:underline"><?= __('forum.topic.loginPrompt') ?></a> <?= __('forum.topic.loginToParticipate') ?>
    </div>
    <?php endif; ?>

</div>
