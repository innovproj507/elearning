<?php $csrfField = '<input type="hidden" name="_token" value="' . \App\Core\CSRF::generate() . '">';  ?>

<div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-10 px-6 mb-10">
    <div class="max-w-3xl mx-auto">
        <nav class="text-blue-300 text-sm mb-3">
            <a href="/foro" class="hover:text-white transition"><?= __('forum.createTopic.breadcrumbForums') ?></a>
            <span class="mx-2">›</span>
            <a href="/foro/<?= htmlspecialchars($forum['slug']) ?>" class="hover:text-white transition"><?= htmlspecialchars($forum['title']) ?></a>
            <span class="mx-2">›</span>
            <span class="text-white"><?= __('forum.createTopic.breadcrumbCurrent') ?></span>
        </nav>
        <h1 class="text-2xl font-extrabold"><?= __('forum.createTopic.heading') ?></h1>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 pb-16">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-8">
        <form method="POST" action="/foro/<?= htmlspecialchars($forum['slug']) ?>/nuevo" class="space-y-5">
            <?= $csrfField ?>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('forum.createTopic.titleLabel') ?> <span class="text-red-500">*</span></label>
                <input type="text" name="title" required maxlength="200"
                       class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 outline-none text-sm transition"
                       placeholder="<?= __('forum.createTopic.titlePlaceholder') ?>">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('forum.createTopic.contentLabel') ?> <span class="text-red-500">*</span></label>
                <textarea name="content" rows="8" required
                          class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 outline-none text-sm resize-none transition"
                          placeholder="<?= __('forum.createTopic.contentPlaceholder') ?>"></textarea>
            </div>
            <div class="flex items-center justify-between pt-2">
                <a href="/foro/<?= htmlspecialchars($forum['slug']) ?>"
                   class="text-sm text-gray-500 hover:text-gray-700 transition">
                    <?= __('forum.createTopic.backToForum') ?>
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-7 py-2.5 rounded-lg transition shadow">
                    <?= __('forum.createTopic.publish') ?>
                </button>
            </div>
        </form>
    </div>
</div>
