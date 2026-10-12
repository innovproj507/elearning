<?php
$isStudents  = $role === 'student';
$total       = count($users);
$active      = count(array_filter($users, fn($u) => !empty($u['is_active'])));
$blocked     = $total - $active;
$roleLabel   = $isStudents ? __('admin.users.roleStudents') : __('admin.users.roleTeachers');
$roleColor   = $isStudents ? 'blue' : 'purple';
?>

<!-- Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-black text-navy"><?= $roleLabel ?></h2>
        <p class="text-gray-500 text-sm mt-0.5"><?= __('admin.users.subtitleCount', ['count' => $total]) ?></p>
    </div>
    <div class="flex items-center gap-2">
        <a href="/admin/usuarios/nuevo"
           class="inline-flex items-center gap-2 text-sm font-bold px-4 py-2.5 rounded-xl bg-brand-500 text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600 transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <?= __('admin.users.createUser') ?>
        </a>
        <a href="/admin/usuarios/students"
           class="inline-flex items-center gap-2 text-sm font-bold px-4 py-2.5 rounded-xl transition-all <?= $role === 'student' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/25' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
            <?= __('admin.users.students') ?>
        </a>
        <a href="/admin/usuarios/teachers"
           class="inline-flex items-center gap-2 text-sm font-bold px-4 py-2.5 rounded-xl transition-all <?= $role === 'teacher' ? 'bg-purple-500 text-white shadow-lg shadow-purple-500/25' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            <?= __('admin.users.teachers') ?>
        </a>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <p class="text-3xl font-black text-navy"><?= $total ?></p>
        <p class="text-xs text-gray-500 font-semibold mt-1"><?= __('admin.users.totalLabel', ['role' => $roleLabel]) ?></p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <p class="text-3xl font-black text-emerald-600"><?= $active ?></p>
        <p class="text-xs text-gray-500 font-semibold mt-1"><?= __('admin.users.active') ?></p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <p class="text-3xl font-black text-red-500"><?= $blocked ?></p>
        <p class="text-xs text-gray-500 font-semibold mt-1"><?= __('admin.users.blocked') ?></p>
    </div>
</div>

<!-- Búsqueda -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-2">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
        <div class="relative flex-1">
            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="searchInput" placeholder="<?= __('admin.users.searchPlaceholder') ?>"
                   class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
        </div>
        <select id="filterStatus" class="border border-gray-200 rounded-xl text-sm px-3 py-2.5 focus:outline-none focus:border-brand-500 bg-white font-semibold text-gray-600">
            <option value=""><?= __('admin.users.filterAll') ?></option>
            <option value="active"><?= __('admin.users.filterActive') ?></option>
            <option value="blocked"><?= __('admin.users.filterBlocked') ?></option>
        </select>
    </div>

    <?php if (empty($users)): ?>
    <div class="py-16 text-center">
        <div class="text-5xl mb-3">👤</div>
        <p class="font-semibold text-gray-500"><?= __('admin.users.emptyRegistered', ['role' => strtolower($roleLabel)]) ?></p>
    </div>
    <?php else: ?>

    <!-- Tabla -->
    <div class="overflow-x-auto">
        <table class="w-full" id="usersTable">
            <thead>
                <tr class="text-left border-b border-gray-100">
                    <th class="px-5 py-3 text-xs font-black text-gray-400 uppercase tracking-wider"><?= __('admin.users.thUser') ?></th>
                    <th class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider hidden md:table-cell"><?= __('admin.users.thEmail') ?></th>
                    <?php if ($isStudents): ?>
                    <th class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider hidden lg:table-cell text-center"><?= __('admin.users.thCourses') ?></th>
                    <?php endif; ?>
                    <th class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider hidden sm:table-cell"><?= __('admin.users.thStatus') ?></th>
                    <th class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider hidden sm:table-cell"><?= __('admin.users.thRegistered') ?></th>
                    <th class="px-5 py-3 text-xs font-black text-gray-400 uppercase tracking-wider text-right"><?= __('admin.users.thActions') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50" id="usersBody">
                <?php
                $avatarColors = [
                    'from-blue-500 to-blue-700',
                    'from-purple-500 to-purple-700',
                    'from-emerald-500 to-emerald-700',
                    'from-amber-500 to-amber-600',
                    'from-rose-500 to-rose-700',
                    'from-cyan-500 to-cyan-700',
                    'from-indigo-500 to-indigo-700',
                ];
                foreach ($users as $i => $u):
                    $color = $avatarColors[$i % count($avatarColors)];
                    $isActive = !empty($u['is_active']);
                ?>
                <tr class="hover:bg-slate-50 transition-colors user-row"
                    data-name="<?= strtolower(htmlspecialchars($u['name'])) ?>"
                    data-email="<?= strtolower(htmlspecialchars($u['email'])) ?>"
                    data-status="<?= $isActive ? 'active' : 'blocked' ?>">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br <?= $color ?> flex items-center justify-center text-white font-black text-sm flex-shrink-0 shadow-sm">
                                <?= mb_strtoupper(mb_substr($u['name'], 0, 2)) ?>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-navy truncate"><?= htmlspecialchars($u['name']) ?></p>
                                <p class="text-xs text-gray-400 md:hidden truncate"><?= htmlspecialchars($u['email']) ?></p>
                                <?php if (!empty($u['bio'])): ?>
                                <p class="text-xs text-gray-400 truncate max-w-[180px] hidden xl:block"><?= htmlspecialchars($u['bio']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 hidden md:table-cell">
                        <span class="text-sm text-gray-600"><?= htmlspecialchars($u['email']) ?></span>
                    </td>
                    <?php if ($isStudents): ?>
                    <td class="px-4 py-4 hidden lg:table-cell text-center">
                        <span class="inline-flex items-center gap-1 text-sm font-bold text-brand-500 bg-brand-50 px-2.5 py-1 rounded-lg">
                            <?= (int)($u['total_enrollments'] ?? 0) ?>
                        </span>
                    </td>
                    <?php endif; ?>
                    <td class="px-4 py-4 hidden sm:table-cell">
                        <?php if ($isActive): ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1.5 rounded-full bg-emerald-100 text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span><?= __('admin.users.active') ?>
                        </span>
                        <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1.5 rounded-full bg-red-100 text-red-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span><?= __('admin.users.blocked') ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-4 hidden sm:table-cell">
                        <span class="text-xs text-gray-400 font-medium">
                            <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2 justify-end">
                            <a href="/admin/usuarios/<?= $u['id'] ?>/editar"
                               class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <?= __('admin.users.edit') ?>
                            </a>
                            <form method="POST" action="/admin/usuarios/<?= $u['id'] ?>/bloquear" class="inline"
                                  onsubmit="return confirm('<?= $isActive ? addslashes(__('admin.users.confirmBlock')) : addslashes(__('admin.users.confirmUnblock')) ?>')">
                                <?= \App\Core\CSRF::field() ?>
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg transition-colors
                                        <?= $isActive ? 'text-red-600 bg-red-50 hover:bg-red-100' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' ?>">
                                    <?php if ($isActive): ?>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    <?= __('admin.users.block') ?>
                                    <?php else: ?>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <?= __('admin.users.unblock') ?>
                                    <?php endif; ?>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Resultado búsqueda -->
    <div id="noResults" class="hidden py-12 text-center text-gray-400">
        <div class="text-4xl mb-2">🔍</div>
        <p class="font-semibold"><?= __('admin.users.noResults') ?></p>
    </div>

    <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-400 font-medium" id="resultCount"><?= $total ?> <?= __('admin.users.userPlural') ?></p>
    </div>
    <?php endif; ?>
</div>

<script>
const searchInput  = document.getElementById('searchInput');
const filterStatus = document.getElementById('filterStatus');
const rows         = document.querySelectorAll('.user-row');
const noResults    = document.getElementById('noResults');
const resultCount  = document.getElementById('resultCount');

function filterUsers() {
    const q      = searchInput.value.toLowerCase();
    const status = filterStatus.value;
    let visible  = 0;

    rows.forEach(row => {
        const name    = row.dataset.name;
        const email   = row.dataset.email;
        const rowStat = row.dataset.status;
        const matchQ  = !q || name.includes(q) || email.includes(q);
        const matchS  = !status || rowStat === status;
        const show    = matchQ && matchS;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    noResults.classList.toggle('hidden', visible > 0);
    document.querySelector('#usersTable tbody') && (document.querySelector('#usersTable').style.display = visible > 0 ? '' : 'none');
    if (resultCount) resultCount.textContent = visible + ' ' + (visible !== 1 ? <?= json_encode(__('admin.users.userPlural')) ?> : <?= json_encode(__('admin.users.userSingular')) ?>);
}

searchInput?.addEventListener('input', filterUsers);
filterStatus?.addEventListener('change', filterUsers);
</script>
