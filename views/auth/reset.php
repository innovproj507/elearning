<div class="bg-white rounded-3xl shadow-2xl p-8 lg:p-10">
    <div class="mb-8">
        <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center mb-5">
            <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-black text-navy"><?= __('auth.reset.title') ?></h1>
        <p class="text-gray-500 mt-1 text-sm"><?= __('auth.reset.subtitle', ['email' => $email]) ?></p>
    </div>

    <!-- Info box -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 mb-6 flex items-start gap-3">
        <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-xs text-blue-700 font-medium"><?= __('auth.reset.code_expires_notice') ?></p>
    </div>

    <form method="POST" action="/recuperar-contrasena/verificar" class="space-y-5">
        <?= \App\Core\CSRF::field() ?>
        <input type="hidden" name="email" value="<?= $email ?>">

        <div>
            <label for="code" class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('auth.reset.code_label') ?></label>
            <input type="text" id="code" name="code" required
                maxlength="6" inputmode="numeric" autocomplete="one-time-code"
                class="w-full text-center text-3xl font-black tracking-[0.5em] py-4 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all placeholder-gray-300"
                placeholder="000000">
        </div>

        <div>
            <label for="password" class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('auth.reset.password_label') ?></label>
            <input type="password" id="password" name="password" required
                minlength="8" autocomplete="new-password"
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                placeholder="<?= __('auth.reset.password_placeholder') ?>">
        </div>

        <div>
            <label for="password_confirm" class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('auth.reset.confirm_label') ?></label>
            <input type="password" id="password_confirm" name="password_confirm" required
                minlength="8" autocomplete="new-password"
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                placeholder="<?= __('auth.reset.confirm_placeholder') ?>">
        </div>

        <button type="submit"
            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-emerald-500/25 hover:-translate-y-0.5 text-sm">
            <?= __('auth.reset.submit') ?>
        </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
        <?= __('auth.reset.no_code') ?>
        <a href="/recuperar-contrasena" class="text-brand-500 hover:text-brand-600 font-bold ml-1"><?= __('auth.reset.request_new_code') ?></a>
    </p>
</div>
