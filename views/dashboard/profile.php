<div class="max-w-2xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-black text-navy"><?= __('dashboard.profile.title') ?></h2>
        <p class="text-gray-500 text-sm mt-1"><?= __('dashboard.profile.subtitle') ?></p>
    </div>

    <!-- Avatar + info -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <div class="flex items-center gap-5">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-2xl font-black text-white shadow-xl flex-shrink-0"
                 style="background:linear-gradient(135deg,#1a56db,#0f4c8a);">
                <?= mb_strtoupper(mb_substr($user['name'], 0, 2)) ?>
            </div>
            <div>
                <h3 class="text-xl font-black text-navy"><?= htmlspecialchars($user['name']) ?></h3>
                <div class="flex items-center gap-2 mt-1">
                    <?php
                    $roleColors = ['admin'=>'bg-purple-100 text-purple-700','teacher'=>'bg-amber-100 text-amber-700','student'=>'bg-blue-100 text-blue-700'];
                    $roleColor  = $roleColors[$user['role']] ?? 'bg-gray-100 text-gray-700';
                    ?>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full <?= $roleColor ?> capitalize">
                        <?= htmlspecialchars($user['role']) ?>
                    </span>
                    <span class="text-sm text-gray-400"><?= htmlspecialchars($user['email']) ?></span>
                </div>
                <?php if (!empty($user['bio'])): ?>
                <p class="text-sm text-gray-500 mt-2"><?= htmlspecialchars($user['bio']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
        <form method="POST" action="/dashboard/perfil" class="space-y-6">
            <?= \App\Core\CSRF::field() ?>

            <!-- Nombre -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('dashboard.profile.full_name') ?></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <input type="text" name="name" required
                        value="<?= htmlspecialchars($user['name']) ?>"
                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
                </div>
            </div>

            <!-- Biografía -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('dashboard.profile.bio') ?></label>
                <textarea name="bio" rows="3"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all resize-none"
                    placeholder="<?= __('dashboard.profile.bio_placeholder') ?>"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>

            <!-- Separador contraseña -->
            <div class="pt-2 border-t border-gray-100">
                <h3 class="font-black text-navy mb-1"><?= __('dashboard.profile.change_password_title') ?></h3>
                <p class="text-xs text-gray-400 mb-5"><?= __('dashboard.profile.change_password_desc') ?></p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('dashboard.profile.current_password') ?></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input type="password" name="current_password"
                                class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                                placeholder="<?= __('dashboard.profile.current_password_placeholder') ?>">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('dashboard.profile.new_password') ?></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <input type="password" name="new_password" minlength="8"
                                class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                                placeholder="<?= __('dashboard.profile.new_password_placeholder') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="bg-brand-500 hover:bg-brand-600 text-white font-bold px-8 py-3.5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5">
                    <?= __('dashboard.profile.save_changes') ?>
                </button>
                <a href="/dashboard"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-8 py-3.5 rounded-xl text-sm transition-all">
                    <?= __('common.cancel') ?>
                </a>
            </div>
        </form>
    </div>
</div>
