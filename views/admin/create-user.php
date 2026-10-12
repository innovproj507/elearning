<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-400 mb-3">
        <a href="/admin/usuarios/students" class="hover:text-brand-500 font-semibold transition-colors"><?= __('admin.createUser.breadcrumbUsers') ?></a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-navy font-semibold"><?= __('admin.createUser.breadcrumbCreate') ?></span>
    </div>
    <h2 class="text-2xl font-black text-navy"><?= __('admin.createUser.title') ?></h2>
    <p class="text-gray-500 text-sm mt-1"><?= __('admin.createUser.subtitle') ?></p>
</div>

<div class="max-w-xl">
    <form method="POST" action="/admin/usuarios/nuevo" class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-50">

        <div class="p-6 space-y-5">

            <!-- Nombre -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('admin.createUser.fullName') ?> <span class="text-red-500">*</span></label>
                <input type="text" name="name" required autofocus
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                       placeholder="<?= __('admin.createUser.namePlaceholder') ?>">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('admin.createUser.email') ?> <span class="text-red-500">*</span></label>
                <input type="email" name="email" required
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                       placeholder="<?= __('admin.createUser.emailPlaceholder') ?>">
            </div>

            <!-- Contraseña -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('admin.createUser.password') ?> <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="password" name="password" id="password" required minlength="8"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-11 text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                           placeholder="<?= __('admin.createUser.passwordPlaceholder') ?>">
                    <button type="button" onclick="togglePwd()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1.5"><?= __('admin.createUser.passwordHint') ?></p>
            </div>

            <!-- Rol -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('admin.createUser.role') ?> <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    <?php foreach ([
                        ['value'=>'student', 'label'=>__('admin.createUser.roleStudent'), 'icon'=>'👨‍🎓', 'color'=>'blue'],
                        ['value'=>'teacher', 'label'=>__('admin.createUser.roleTeacher'), 'icon'=>'👨‍🏫', 'color'=>'purple'],
                        ['value'=>'admin',   'label'=>__('admin.createUser.roleAdmin'),   'icon'=>'⚙️',  'color'=>'slate'],
                    ] as $r): ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="<?= $r['value'] ?>" class="sr-only peer"
                               <?= $r['value'] === 'student' ? 'checked' : '' ?>>
                        <div class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200
                                    peer-checked:border-brand-500 peer-checked:bg-brand-50 transition-all hover:border-gray-300 text-center">
                            <span class="text-2xl"><?= $r['icon'] ?></span>
                            <span class="text-xs font-bold text-gray-700 peer-checked:text-brand-600"><?= $r['label'] ?></span>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div class="px-6 py-4 flex items-center justify-between bg-slate-50 rounded-b-2xl">
            <a href="/admin/usuarios/students"
               class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors">
                <?= __('common.cancel') ?>
            </a>
            <?= \App\Core\CSRF::field() ?>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold px-6 py-2.5 rounded-xl transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <?= __('admin.createUser.createUser') ?>
            </button>
        </div>
    </form>
</div>

<script>
function togglePwd() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
