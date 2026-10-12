<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-black text-navy"><?= __('admin.courses.title') ?></h2>
        <p class="text-gray-500 text-sm mt-1"><?= __('admin.courses.subtitleCount', ['count' => count($courses)]) ?></p>
    </div>
    <a href="/cursos/crear"
       class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-bold px-4 py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <?= __('admin.courses.newCourse') ?>
    </a>
</div>

<!-- Búsqueda -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-2">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
        <div class="relative flex-1">
            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="searchInput" placeholder="<?= __('admin.courses.searchPlaceholder') ?>"
                   class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
        </div>
        <select id="filterStatus" class="border border-gray-200 rounded-xl text-sm px-3 py-2.5 focus:outline-none focus:border-brand-500 bg-white font-semibold text-gray-600">
            <option value=""><?= __('admin.courses.filterAll') ?></option>
            <option value="published"><?= __('admin.courses.filterPublished') ?></option>
            <option value="draft"><?= __('admin.courses.filterDraft') ?></option>
        </select>
    </div>

    <?php if (empty($courses)): ?>
    <div class="py-16 text-center">
        <div class="text-5xl mb-3">📚</div>
        <p class="text-gray-500 font-semibold mb-4"><?= __('admin.courses.emptyTitle') ?></p>
        <a href="/cursos/crear" class="inline-flex items-center gap-2 bg-brand-500 text-white font-bold px-5 py-2.5 rounded-xl text-sm">
            <?= __('admin.courses.createFirst') ?>
        </a>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full" id="coursesTable">
            <thead>
                <tr class="text-left border-b border-gray-100">
                    <th class="px-6 py-3 text-xs font-black text-gray-400 uppercase tracking-wider"><?= __('admin.courses.thCourse') ?></th>
                    <th class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider hidden md:table-cell"><?= __('admin.courses.thStatus') ?></th>
                    <th class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider hidden lg:table-cell"><?= __('admin.courses.thContent') ?></th>
                    <th class="px-6 py-3 text-xs font-black text-gray-400 uppercase tracking-wider text-right"><?= __('admin.courses.thActions') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50" id="coursesBody">
                <?php foreach ($courses as $c): ?>
                <tr class="hover:bg-slate-50 transition-colors course-row"
                    data-title="<?= strtolower(htmlspecialchars($c['title'])) ?>"
                    data-cat="<?= strtolower(htmlspecialchars($c['category_name'] ?? '')) ?>"
                    data-status="<?= $c['status'] ?>">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-sm flex-shrink-0 shadow"
                                 style="background:linear-gradient(135deg,#1a56db,#0f4c8a);">
                                <?= mb_strtoupper(mb_substr($c['title'], 0, 1)) ?>
                            </div>
                            <div class="min-w-0">
                                <a href="/cursos/<?= htmlspecialchars($c['slug']) ?>"
                                   class="text-sm font-bold text-navy hover:text-brand-500 transition-colors truncate block max-w-xs">
                                    <?= htmlspecialchars($c['title']) ?>
                                </a>
                                <?php if (!empty($c['category_name'])): ?>
                                <p class="text-xs text-gray-400 font-semibold"><?= htmlspecialchars($c['category_name']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 hidden md:table-cell">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full
                            <?= $c['status'] === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?= $c['status'] === 'published' ? 'bg-emerald-500' : 'bg-amber-500' ?>"></span>
                            <?= $c['status'] === 'published' ? __('admin.courses.published') : __('admin.courses.draft') ?>
                        </span>
                    </td>
                    <td class="px-4 py-4 hidden lg:table-cell">
                        <div class="flex items-center gap-3 text-xs text-gray-500 font-semibold">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                                <?= $c['total_lessons'] ?? 0 ?> <?= __('admin.courses.lessonsAbbr') ?>
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                                <?= $c['total_quizzes'] ?? 0 ?> <?= __('admin.courses.quizAbbr') ?>
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <?= $c['total_students'] ?? 0 ?> <?= __('admin.courses.studentsAbbr') ?>
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2 justify-end">
                            <a href="/cursos/<?= $c['id'] ?>/editar"
                               class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <?= __('admin.courses.edit') ?>
                            </a>
                            <a href="/admin/cursos/<?= $c['id'] ?>/estudiantes"
                               class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <?= __('admin.courses.students') ?>
                            </a>
                            <a href="/cursos/<?= $c['id'] ?>/lecciones/crear"
                               class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition-colors">
                                <?= __('admin.courses.addLesson') ?>
                            </a>
                            <a href="/cursos/<?= $c['id'] ?>/quizzes/crear"
                               class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-lg transition-colors">
                                <?= __('admin.courses.addQuiz') ?>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="noResults" class="hidden py-12 text-center text-gray-400">
        <div class="text-4xl mb-2">🔍</div>
        <p class="font-semibold"><?= __('admin.courses.noResults') ?></p>
    </div>

    <div class="px-5 py-3 border-t border-gray-100">
        <p class="text-xs text-gray-400 font-medium" id="resultCount"><?= count($courses) ?> <?= __('admin.courses.coursePlural') ?></p>
    </div>
    <?php endif; ?>
</div>

<script>
const searchInput  = document.getElementById('searchInput');
const filterStatus = document.getElementById('filterStatus');
const rows         = document.querySelectorAll('.course-row');
const noResults    = document.getElementById('noResults');
const resultCount  = document.getElementById('resultCount');

function filterCourses() {
    const q      = searchInput.value.toLowerCase();
    const status = filterStatus.value;
    let visible  = 0;
    rows.forEach(row => {
        const matchQ = !q || row.dataset.title.includes(q) || row.dataset.cat.includes(q);
        const matchS = !status || row.dataset.status === status;
        const show   = matchQ && matchS;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    noResults.classList.toggle('hidden', visible > 0);
    document.getElementById('coursesTable').style.display = visible > 0 ? '' : 'none';
    if (resultCount) resultCount.textContent = visible + ' ' + (visible !== 1 ? <?= json_encode(__('admin.courses.coursePlural')) ?> : <?= json_encode(__('admin.courses.courseSingular')) ?>);
}

searchInput?.addEventListener('input', filterCourses);
filterStatus?.addEventListener('change', filterCourses);
</script>
