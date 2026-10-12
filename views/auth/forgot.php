<div class="bg-white rounded-3xl shadow-2xl p-8 lg:p-10">
    <div class="mb-8">
        <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center mb-5">
            <svg class="w-7 h-7 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-black text-navy"><?= __('auth.forgot.title') ?></h1>
        <p class="text-gray-500 mt-1 text-sm"><?= __('auth.forgot.subtitle') ?></p>
    </div>

    <form method="POST" action="/recuperar-contrasena" class="space-y-5">
        <?= \App\Core\CSRF::field() ?>

        <div>
            <label for="email" class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('auth.forgot.email_label') ?></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input type="email" id="email" name="email" required autocomplete="email"
                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                    placeholder="tu@email.com">
            </div>
        </div>

        <button type="submit"
            class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5 text-sm">
            <?= __('auth.forgot.submit') ?>
        </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
        <a href="/login" class="text-brand-500 hover:text-brand-600 font-bold"><?= __('auth.forgot.back_to_login') ?></a>
    </p>
</div>
