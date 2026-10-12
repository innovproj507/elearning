<!DOCTYPE html>
<html lang="<?= \App\Core\Language::getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('errors.500.page_title') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="text-center max-w-md">
        <p class="text-8xl font-extrabold text-gray-200 mb-4">500</p>
        <h1 class="text-2xl font-bold text-gray-800 mb-2"><?= __('errors.500.heading') ?></h1>
        <p class="text-gray-500 mb-8"><?= __('errors.500.message') ?></p>
        <a href="/" class="bg-sky-500 hover:bg-sky-600 text-white font-semibold px-6 py-3 rounded-xl transition-colors">
            <?= __('errors.500.cta') ?>
        </a>
    </div>
</body>
</html>
