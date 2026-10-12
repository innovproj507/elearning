<div class="bg-white rounded-3xl shadow-2xl p-8 lg:p-10">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-6 lg:hidden">
            <div class="w-9 h-9 rounded-xl bg-brand-500 flex items-center justify-center">
                <span class="text-white font-black text-sm">P</span>
            </div>
            <span class="font-black text-navy">PAMEL LMS</span>
        </div>
        <h1 class="text-3xl font-black text-navy"><?= __('auth.register.title') ?></h1>
        <p class="text-gray-500 mt-1 text-sm"><?= __('auth.register.subtitle') ?></p>
    </div>

    <form method="POST" action="/register" class="space-y-4">
        <?= \App\Core\CSRF::field() ?>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('auth.register.name_label') ?></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <input type="text" name="name" required
                    value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES) ?>"
                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                    placeholder="<?= __('auth.register.name_placeholder') ?>">
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('auth.register.email_label') ?></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input type="email" name="email" required
                    value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>"
                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                    placeholder="tu@email.com">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('auth.register.password_label') ?></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input type="password" name="password" required minlength="8"
                        class="w-full pl-10 pr-3 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                        placeholder="<?= __('auth.register.password_placeholder') ?>">
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('auth.register.confirm_label') ?></label>
                <input type="password" name="password_confirm" required
                    class="w-full px-3 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                    placeholder="<?= __('auth.register.confirm_placeholder') ?>">
            </div>
        </div>

        <button type="submit"
            class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40 hover:-translate-y-0.5 text-sm mt-2">
            <?= __('auth.register.submit') ?>
        </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
        <?= __('auth.register.have_account') ?>
        <a href="/login" class="text-brand-500 hover:text-brand-600 font-bold ml-1"><?= __('auth.register.login_link') ?></a>
    </p>
</div>
