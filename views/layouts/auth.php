<!DOCTYPE html>
<html lang="<?= \App\Core\Language::getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? __('layout.login') ?> | PAMEL LMS</title>

    <!-- Tailwind CSS (Intentamos cargar de nuevo, pero con fallback) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background-color: #f9fafb;
            color: #111827;
            margin: 0;
            font-family: sans-serif;
        }
        svg { max-width: 100%; }
    </style>
</head>
<body>
    <div style="position:fixed;top:16px;right:16px;z-index:50;display:flex;align-items:center;gap:8px;background:#fff;padding:6px 12px;border-radius:9999px;box-shadow:0 2px 8px rgba(0,0,0,.08);">
        <a href="?lang=en" style="font-size:.75rem;font-weight:800;text-decoration:<?= \App\Core\Language::getLocale() === 'en' ? 'underline' : 'none'; ?>;color:<?= \App\Core\Language::getLocale() === 'en' ? '#1a56db' : '#9ca3af'; ?>;">EN</a>
        <span style="color:#d1d5db;font-size:.75rem;">|</span>
        <a href="?lang=es" style="font-size:.75rem;font-weight:800;text-decoration:<?= \App\Core\Language::getLocale() === 'es' ? 'underline' : 'none'; ?>;color:<?= \App\Core\Language::getLocale() === 'es' ? '#1a56db' : '#9ca3af'; ?>;">ES</a>
    </div>
    <?= $content ?>
</body>
</html>
