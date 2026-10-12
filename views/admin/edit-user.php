<div class="max-w-2xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-3">
            <a href="/dashboard" class="hover:text-brand-500 font-semibold transition-colors"><?= __('admin.editUser.breadcrumbDashboard') ?></a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="/admin/usuarios/<?= $editUser['role'] ?>s" class="hover:text-brand-500 font-semibold transition-colors capitalize">
                <?= $editUser['role'] === 'teacher' ? __('admin.editUser.breadcrumbTeachers') : __('admin.editUser.breadcrumbStudents') ?>
            </a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-navy font-semibold"><?= __('admin.editUser.breadcrumbEdit') ?></span>
        </div>
        <h2 class="text-2xl font-black text-navy"><?= __('admin.editUser.title') ?></h2>
        <p class="text-gray-500 text-sm mt-1"><?= __('admin.editUser.subtitle', ['name' => '<strong>' . htmlspecialchars($editUser['name']) . '</strong>']) ?></p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
        <form method="POST" action="/admin/usuarios/<?= $editUser['id'] ?>/editar" class="space-y-6">
            <?= \App\Core\CSRF::field() ?>

            <!-- Nombre -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('admin.editUser.fullName') ?> <span class="text-red-500">*</span></label>
                <input type="text" name="name" required
                    value="<?= htmlspecialchars($editUser['name'], ENT_QUOTES) ?>"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('admin.editUser.email') ?> <span class="text-red-500">*</span></label>
                <input type="email" name="email" required
                    value="<?= htmlspecialchars($editUser['email'], ENT_QUOTES) ?>"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
            </div>

            <!-- Rol -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('admin.editUser.role') ?></label>
                <select name="role"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all bg-white">
                    <option value="student" <?= $editUser['role'] === 'student' ? 'selected' : '' ?>><?= __('admin.editUser.roleStudent') ?></option>
                    <option value="teacher" <?= $editUser['role'] === 'teacher' ? 'selected' : '' ?>><?= __('admin.editUser.roleTeacher') ?></option>
                    <option value="admin"   <?= $editUser['role'] === 'admin'   ? 'selected' : '' ?>><?= __('admin.editUser.roleAdmin') ?></option>
                </select>
            </div>

            <!-- Bio -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('admin.editUser.bio') ?></label>
                <textarea name="bio" rows="3"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all resize-none"
                    placeholder="<?= __('admin.editUser.bioPlaceholder') ?>"><?= htmlspecialchars($editUser['bio'] ?? '', ENT_QUOTES) ?></textarea>
            </div>

            <!-- Nueva contraseña (opcional) -->
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl space-y-4">
                <p class="text-xs font-bold text-amber-700 uppercase tracking-wider"><?= __('admin.editUser.passwordChangeTitle') ?></p>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('admin.editUser.newPassword') ?></label>
                    <input type="password" name="new_password" minlength="8"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                        placeholder="<?= __('admin.editUser.newPasswordPlaceholder') ?>">
                </div>
            </div>

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                    class="flex-1 sm:flex-none bg-brand-500 hover:bg-brand-600 text-white font-bold px-8 py-3.5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5">
                    <?= __('admin.editUser.saveChanges') ?>
                </button>
                <a href="/admin/usuarios/<?= $editUser['role'] ?>s"
                    class="flex-1 sm:flex-none text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-8 py-3.5 rounded-xl text-sm transition-all">
                    <?= __('common.cancel') ?>
                </a>
            </div>
        </form>
    </div>
</div>
