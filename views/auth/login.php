<div class="min-h-screen flex flex-col md:flex-row bg-white font-sans">

    <!-- Lado Izquierdo: Imagen con overlay -->
    <div class="hidden md:flex md:w-5/12 flex-col relative overflow-hidden">
        <!-- Imagen de fondo -->
        <img src="/assets/img/slides/slide1.jpg" alt=""
             class="absolute inset-0 w-full h-full object-cover object-center">
        <!-- Overlay degradado: transparente arriba, oscuro abajo -->
        <div class="absolute inset-0 bg-gradient-to-b from-[#0a2463]/60 via-[#0a2463]/40 to-[#0a1a3d]/90"></div>

        <!-- Logo arriba -->
        <div class="relative z-10 flex justify-center pt-10">
            <img src="/assets/img/LogoSelloPamel-EditedWhite.png" alt="PAMEL"
                 class="h-48 object-contain drop-shadow-xl">
        </div>

        <!-- Texto al centro -->
        <div class="relative z-10 px-12 lg:px-16 flex-1 flex flex-col justify-center py-10">
            <h1 class="text-4xl lg:text-5xl font-bold text-white leading-tight mb-5 drop-shadow-md">
                <?= __('auth.login.hero_title_line1') ?><br>
                <span class="text-blue-300"><?= __('auth.login.hero_title_highlight') ?></span>
            </h1>

            <p class="text-white/80 text-base mb-10 max-w-sm leading-relaxed">
                <?= __('auth.login.hero_subtitle') ?>
            </p>

            <ul class="space-y-4 text-white/90">
                <li class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-white/25 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <?= __('auth.login.feature_certified') ?>
                </li>
                <li class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-white/25 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <?= __('auth.login.feature_access') ?>
                </li>
                <li class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-white/25 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <?= __('auth.login.feature_feedback') ?>
                </li>
            </ul>
        </div>

        <!-- Copyright al pie -->
        <div class="relative z-10 px-12 lg:px-16 pb-8">
            <p class="text-white/30 text-xs"><?= __('auth.login.copyright', ['year' => date('Y')]) ?></p>
        </div>
    </div>

    <!-- Lado Derecho: Formulario -->
    <div class="flex-1 flex items-center justify-center px-6 py-12 bg-gray-50">
        <div class="w-full max-w-md">

            <!-- Logo en mobile -->
            <div class="flex justify-center mb-8 md:hidden">
                <img src="/assets/img/logo.png" alt="PAMEL" class="h-16 object-contain">
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 lg:p-10">
                <h2 class="text-2xl font-bold text-gray-900 mb-1"><?= __('auth.login.welcome') ?></h2>
                <p class="text-gray-500 text-sm mb-7"><?= __('auth.login.subtitle') ?></p>

                <?php if (!empty($_SESSION['flash']['error'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?= htmlspecialchars($_SESSION['flash']['error']) ?>
                    </div>
                <?php endif; ?>

                <form action="/login" method="POST" class="space-y-5">
                    <?= \App\Core\CSRF::field(); ?>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5"><?= __('auth.login.email_label') ?></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            </span>
                            <input type="email" name="email" required placeholder="tu@email.com"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 placeholder-gray-400 text-sm transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5"><?= __('auth.login.password_label') ?></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                            <input type="password" name="password" required placeholder="••••••••"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 placeholder-gray-400 text-sm transition">
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-gray-600"><?= __('auth.login.remember_me') ?></span>
                        </label>
                        <a href="#" class="text-blue-600 hover:text-blue-700 font-medium transition"><?= __('auth.login.forgot_password') ?></a>
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-sm transition-all active:scale-95">
                        <?= __('auth.login.submit') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
