<!DOCTYPE html>
<html lang="<?= \App\Core\Language::getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Dashboard') ?> | PAMEL LMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: { sans: ['Inter','sans-serif'] },
                colors: {
                    brand: { 50:'#eff6ff', 100:'#dbeafe', 400:'#60a5fa', 500:'#2563eb', 600:'#1d4ed8' },
                    gold:  { DEFAULT:'#f59e0b', light:'#fef3c7', dark:'#b45309' },
                    navy:  '#132a5c',
                }
            }}
        }
    </script>
    <style>
        body { font-family:'Inter',sans-serif; color: var(--ink); }

        /* ── Navegación lateral ── */
        .sidebar { background: linear-gradient(180deg, var(--navy-top) 0%, var(--navy-bottom) 100%); }
        .sidebar-link { display:flex; align-items:center; gap:11px; padding:9px 13px; margin:1px 0; border-radius:10px; font-size:.82rem; font-weight:600; transition:background .15s, color .15s; color:#a9bbdd; white-space:nowrap; }
        .sidebar-link:hover { background:rgba(255,255,255,.08); color:#fff; }
        .sidebar-link.active { background:#2563eb; color:#fff; box-shadow:0 4px 12px rgba(37,99,235,.35); }
        .sidebar-link .icon { width:17px; height:17px; flex-shrink:0; opacity:.9; }
        .sidebar-section { font-size:.65rem; font-weight:800; color:#6f86b8; text-transform:uppercase; letter-spacing:.16em; padding:4px 13px; margin-top:14px; }

    </style>
</head>
<body class="antialiased app-canvas">

<div class="flex h-screen overflow-hidden">

    <!-- ─── SIDEBAR ─────────────────────────────────────────────── -->
    <aside class="sidebar w-60 flex-shrink-0 flex flex-col overflow-y-auto">

        <!-- Logo -->
        <div class="px-6 pt-7 pb-6 text-center flex-shrink-0">
            <a href="/" class="block">
                <img src="/assets/img/logo.png" alt="PAMEL" class="h-14 w-auto object-contain mx-auto mb-3"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div class="hidden w-12 h-12 mx-auto rounded-2xl bg-white/12 items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l6.16-3.42A12 12 0 0112 21a12 12 0 01-6.16-10.42L12 14z"/></svg>
                </div>
                <p class="text-white font-black text-lg leading-none tracking-wide">PAMEL</p>
                <p class="text-[9px] text-blue-200/70 font-bold uppercase tracking-[.2em] mt-1.5">Maritime Training</p>
            </a>
        </div>

        <!-- User card -->
        <?php $authUser = $_SESSION['user'] ?? []; ?>
        <div class="px-3 py-3 border-b border-white/5 flex-shrink-0">
            <div class="flex items-center gap-3 bg-white/5 hover:bg-white/8 rounded-xl px-3 py-2.5 transition-colors">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-black text-white flex-shrink-0 shadow"
                     style="background:linear-gradient(135deg,#1a56db,#0f4c8a);">
                    <?= mb_strtoupper(mb_substr($authUser['name'] ?? 'U', 0, 2)) ?>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-white text-xs font-bold truncate"><?= htmlspecialchars($authUser['name'] ?? '') ?></p>
                    <span class="inline-flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full <?= $authUser['role'] === 'admin' ? 'bg-purple-400' : ($authUser['role'] === 'teacher' ? 'bg-amber-400' : 'bg-emerald-400') ?>"></span>
                        <span class="text-slate-400 text-xs capitalize"><?= htmlspecialchars($authUser['role'] ?? '') ?></span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto">
            <?php
            $role    = $authUser['role'] ?? 'student';
            $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            $nav = [
                ['href'=>'/dashboard',        'label'=>__('layout.dashboard'),   'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
                ['href'=>'/cursos',           'label'=>__('layout.catalog'),     'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'],
                ['href'=>'/dashboard/perfil', 'label'=>__('layout.my_profile'),  'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'],
            ];

            if (in_array($role, ['teacher','admin'])) {
                $nav[] = ['href'=>'/cursos/crear', 'label'=>__('layout.create_course'), 'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>'];
            }

            foreach ($nav as $item):
                $isActive = str_starts_with($current, $item['href']) && ($item['href'] === '/dashboard' ? $current === '/dashboard' : true);
            ?>
            <a href="<?= $item['href'] ?>" class="sidebar-link <?= $isActive ? 'active' : '' ?>">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $item['icon'] ?></svg>
                <?= $item['label'] ?>
            </a>
            <?php endforeach; ?>

            <?php if ($role === 'admin'): ?>
            <div class="sidebar-section mt-4"><?= __('layout.administration') ?></div>
            <a href="/admin/cursos" class="sidebar-link <?= str_starts_with($current, '/admin/cursos') ? 'active' : '' ?>">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <?= __('layout.courses') ?>
            </a>
            <a href="/admin/usuarios/students" class="sidebar-link <?= $current === '/admin/usuarios/students' ? 'active' : '' ?>">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                <?= __('layout.students') ?>
            </a>
            <a href="/admin/usuarios/teachers" class="sidebar-link <?= $current === '/admin/usuarios/teachers' ? 'active' : '' ?>">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                <?= __('layout.teachers') ?>
            </a>
            <a href="/admin/categorias" class="sidebar-link <?= str_starts_with($current, '/admin/categorias') ? 'active' : '' ?>">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <?= __('layout.categories') ?>
            </a>
            <a href="/admin/plugins" class="sidebar-link <?= str_starts_with($current, '/admin/plugins') ? 'active' : '' ?>">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                <?= __('layout.plugins') ?>
            </a>
            <a href="/admin/configuracion" class="sidebar-link <?= str_starts_with($current, '/admin/configuracion') ? 'active' : '' ?>">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <?= __('layout.settings') ?>
            </a>
            <?php endif; ?>

            <div class="sidebar-section mt-4"><?= __('layout.system') ?></div>
            <a href="/" class="sidebar-link">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                <?= __('layout.website') ?>
            </a>
        </nav>

        <!-- Logout -->
        <div class="px-2 py-3 border-t border-white/5 flex-shrink-0">
            <form method="POST" action="/logout">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="sidebar-link w-full hover:bg-red-500/10 hover:text-red-400">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <?= __('layout.logout_btn') ?>
                </button>
            </form>
        </div>
    </aside>

    <!-- ─── CONTENIDO PRINCIPAL ──────────────────────────────────── -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Topbar -->
        <header class="bg-white border-b h-16 flex items-center justify-between px-6 flex-shrink-0" style="border-color:var(--card-border)">
            <div class="min-w-0">
                <h1 class="text-sm font-black text-navy truncate"><?= htmlspecialchars($title ?? 'Dashboard') ?></h1>
                <p class="text-xs text-gray-400"><?= \App\Core\Language::formatDate() ?></p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative hidden md:block mr-1">
                    <svg class="w-3.5 h-3.5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" class="topbar-search" placeholder="<?= __('common.search') ?>">
                </div>
                <div class="flex items-center gap-2 mr-1 border-r pr-3 border-gray-200">
                    <a href="?lang=en" class="text-xs font-extrabold <?= \App\Core\Language::getLocale() === 'en' ? 'text-brand-500 underline' : 'text-gray-400 hover:text-brand-500'; ?> transition">EN</a>
                    <span class="text-gray-300 text-xs">|</span>
                    <a href="?lang=es" class="text-xs font-extrabold <?= \App\Core\Language::getLocale() === 'es' ? 'text-brand-500 underline' : 'text-gray-400 hover:text-brand-500'; ?> transition">ES</a>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full
                    <?= $role === 'admin' ? 'bg-purple-100 text-purple-700' : ($role === 'teacher' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') ?>">
                    <span class="w-1.5 h-1.5 rounded-full animate-pulse
                        <?= $role === 'admin' ? 'bg-purple-500' : ($role === 'teacher' ? 'bg-amber-500' : 'bg-blue-500') ?>"></span>
                    <?= ucfirst($role) ?>
                </span>
            </div>
        </header>

        <!-- Flash -->
        <?php
        $flashSuccess = \App\Core\Session::flash('success');
        $flashError   = \App\Core\Session::flash('error');
        ?>
        <?php if ($flashSuccess): ?>
        <div class="mx-5 mt-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium shadow-sm">
            <span class="w-5 h-5 bg-emerald-500 rounded-full flex items-center justify-center text-white text-xs flex-shrink-0">✓</span>
            <?= htmlspecialchars($flashSuccess) ?>
        </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
        <div class="mx-5 mt-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm font-medium shadow-sm">
            <span class="w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-white text-xs flex-shrink-0">✕</span>
            <?= htmlspecialchars($flashError) ?>
        </div>
        <?php endif; ?>

        <!-- Page content -->
        <main class="flex-1 overflow-y-auto p-6">
            <?= $content ?>
        </main>
    </div>
</div>

<script src="/assets/js/api.js"></script>
</body>
</html>
