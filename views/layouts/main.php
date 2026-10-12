<!DOCTYPE html>
<html lang="<?= \App\Core\Language::getLocale() ?>" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'LMS', ENT_QUOTES) ?> | <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'E-Learning') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            400: '#60a5fa',
                            500: '#1a56db',
                            600: '#1447c0',
                            700: '#1040b0',
                            900: '#0c2d6b',
                        },
                        gold: { DEFAULT: '#f59e0b', light: '#fef3c7', dark: '#b45309' },
                        navy: '#0a1628',
                    },
                    backgroundImage: {
                        'hero-gradient': 'linear-gradient(135deg, #0a1628 0%, #1a3a6b 50%, #0f4c8a 100%)',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .nav-link { position: relative; }
        .nav-link::after { content:''; position:absolute; bottom:-2px; left:0; width:0; height:2px; background:#1a56db; transition:width .25s; }
        .nav-link:hover::after { width:100%; }
        .card-hover { transition: transform .2s, box-shadow .2s; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(26,86,219,.15); }
        .badge { display:inline-flex; align-items:center; gap:4px; font-size:.7rem; font-weight:600; padding:2px 10px; border-radius:999px; letter-spacing:.04em; text-transform:uppercase; }
        .shine { background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.08) 50%, transparent 100%); background-size: 200% 100%; animation: shine 2.5s infinite; }
        @keyframes shine { 0%{background-position:200%} 100%{background-position:-200%} }
    </style>
</head>
<body class="bg-slate-50 text-gray-800 antialiased">

<!-- ─── Navbar ─────────────────────────────────────────────────── -->
<nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <a href="/" class="flex items-center gap-2.5 group">
                <img src="/assets/img/logo.png" alt="PAMEL" class="h-10 w-auto object-contain group-hover:scale-105 transition-transform"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div class="hidden w-10 h-10 rounded-xl bg-brand-500 items-center justify-center shadow-lg shadow-brand-500/30 group-hover:scale-105 transition-transform">
                    <span class="text-white font-black text-base">P</span>
                </div>
                <div>
                    <span class="font-black text-navy text-lg leading-none">PAMEL</span>
                    <p class="text-xs text-gray-500 leading-none font-medium">Maritime Training Center</p>
                </div>
            </a>

            <!-- Nav links -->
            <div class="hidden md:flex items-center gap-7">
                <a href="/" class="nav-link text-sm font-semibold text-gray-600 hover:text-brand-500 transition-colors pb-0.5"><?= __('layout.home') ?></a>
                <a href="/cursos" class="nav-link text-sm font-semibold text-gray-600 hover:text-brand-500 transition-colors pb-0.5"><?= __('layout.courses') ?></a>
                <?php if (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') !== 'admin'): ?>
                    <a href="/dashboard" class="nav-link text-sm font-semibold text-gray-600 hover:text-brand-500 transition-colors pb-0.5"><?= __('layout.my_portal') ?></a>
                <?php endif; ?>
            </div>

            <!-- Auth -->
            <div class="flex items-center gap-3">
                <!-- Language Switcher -->
                <div class="flex items-center gap-2 mr-1 border-r pr-3 border-gray-200">
                    <a href="?lang=en" class="text-xs font-extrabold <?= \App\Core\Language::getLocale() === 'en' ? 'text-brand-500 underline' : 'text-gray-400 hover:text-brand-500'; ?> transition">EN</a>
                    <span class="text-gray-300 text-xs">|</span>
                    <a href="?lang=es" class="text-xs font-extrabold <?= \App\Core\Language::getLocale() === 'es' ? 'text-brand-500 underline' : 'text-gray-400 hover:text-brand-500'; ?> transition">ES</a>
                </div>
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="hidden md:flex items-center gap-2 bg-slate-100 rounded-full px-3 py-1.5">
                        <div class="w-6 h-6 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold">
                            <?= mb_strtoupper(mb_substr($_SESSION['user']['name'], 0, 1)) ?>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 max-w-[120px] truncate">
                            <?= htmlspecialchars($_SESSION['user']['name']) ?>
                        </span>
                    </div>
                    <form method="POST" action="/logout">
                        <?= \App\Core\CSRF::field() ?>
                        <button type="submit" class="text-xs font-semibold text-gray-500 hover:text-red-600 bg-red-50 hover:bg-red-100 px-3 py-2 rounded-lg transition-colors">
                            <?= __('layout.logout') ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="/login" class="text-sm font-semibold text-gray-600 hover:text-brand-500 transition-colors">
                        <?= __('layout.login') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- ─── Flash Messages ─────────────────────────────────────────── -->
<?php
$flashSuccess = \App\Core\Session::flash('success');
$flashError   = \App\Core\Session::flash('error');
?>
<?php if ($flashSuccess): ?>
<div class="max-w-7xl mx-auto px-4 mt-4">
    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium">
        <span class="flex-shrink-0 w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center text-white text-xs">✓</span>
        <?= htmlspecialchars($flashSuccess) ?>
    </div>
</div>
<?php endif; ?>
<?php if ($flashError): ?>
<div class="max-w-7xl mx-auto px-4 mt-4">
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm font-medium">
        <span class="flex-shrink-0 w-6 h-6 bg-red-500 rounded-full flex items-center justify-center text-white text-xs">✕</span>
        <?= htmlspecialchars($flashError) ?>
    </div>
</div>
<?php endif; ?>

<main><?= $content ?></main>

<!-- ─── Footer ────────────────────────────────────────────────── -->
<footer class="bg-gray-900 text-white py-12 mt-20">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10 items-start mb-10">

            <!-- Logo + Social -->
            <div>
                <a href="/" class="inline-block mb-4">
                    <img src="/assets/img/LogoSelloPamel-EditedWhite.png"
                         alt="PAMEL"
                         class="h-48 w-auto object-contain"
                         onerror="this.style.display='none'">
                </a>
                <h3 class="text-lg font-bold mb-3"><?= __('layout.footer_training') ?></h3>
                <div class="flex gap-3">
                    <a href="https://www.instagram.com/pamel_elearning/" target="_blank"
                       class="w-9 h-9 rounded flex items-center justify-center bg-gray-700 hover:bg-gray-600 transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Information -->
            <div>
                <h3 class="text-lg font-bold mb-4"><?= __('layout.footer_information') ?></h3>
                <ul class="space-y-2.5 text-sm text-gray-300">
                    <li><a href="/cursos"            class="hover:text-white transition-colors"><?= __('layout.footer_courses') ?></a></li>
                    <li><a href="<?= cms_url() ?>/payment-methods" target="_blank" class="hover:text-white transition-colors"><?= __('layout.footer_payment') ?></a></li>
                    <li><a href="<?= cms_url() ?>/our-company"     target="_blank" class="hover:text-white transition-colors"><?= __('layout.footer_about') ?></a></li>
                    <li><a href="<?= cms_url() ?>/contact"         target="_blank" class="hover:text-white transition-colors"><?= __('layout.footer_contact') ?></a></li>
                </ul>
            </div>

            <!-- Legal Links -->
            <div>
                <h3 class="text-lg font-bold mb-4"><?= __('layout.footer_legal') ?></h3>
                <ul class="space-y-2.5 text-sm text-gray-300 mb-5">
                    <li><a href="<?= cms_url() ?>/privacy-policy"      target="_blank" class="hover:text-white transition-colors"><?= __('layout.footer_privacy') ?></a></li>
                    <li><a href="<?= cms_url() ?>/cookie-policy"        target="_blank" class="hover:text-white transition-colors"><?= __('layout.footer_cookies') ?></a></li>
                    <li><a href="<?= cms_url() ?>/terms-and-conditions" target="_blank" class="hover:text-white transition-colors"><?= __('layout.footer_terms') ?></a></li>
                </ul>
                <img src="/assets/img/ISO-9001_160x128px.png"
                     alt="ISO 9001 Certification"
                     class="bg-white p-2 rounded w-28"
                     onerror="this.style.display='none'">
            </div>

            <!-- Maritime Authority -->
            <div>
                <p class="text-sm leading-relaxed text-gray-300 mb-4">
                    <?= __('layout.footer_authority') ?>
                </p>
                <img src="/assets/img/AMP-LOGO-2-01-resized-trans.png"
                     alt="Autoridad Marítima de Panamá"
                     class="w-36 object-contain"
                     onerror="this.style.display='none'">
            </div>

        </div>

        <div class="border-t border-white/10 pt-6 text-center text-xs text-gray-500">
            &copy; <?= date('Y') ?> PAMEL Maritime Training Center. <?= __('layout.footer_rights') ?>
        </div>
    </div>
</footer>

<script src="/assets/js/api.js"></script>
</body>
</html>
