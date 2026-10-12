<?php
$passed  = array_filter($students, fn($s) => $s['quiz_passed'] == 1);
$failed  = array_filter($students, fn($s) => $s['quiz_passed'] !== null && $s['quiz_passed'] != 1);
$pending = array_filter($students, fn($s) => $s['quiz_passed'] === null);
?>

<!-- Encabezado -->
<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <a href="/admin/cursos" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand-500 font-semibold mb-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <?= __('admin.courseStudents.backToCourses') ?>
        </a>
        <h2 class="text-2xl font-black text-navy"><?= htmlspecialchars($course['title']) ?></h2>
        <p class="text-gray-500 text-sm mt-1"><?= __('admin.courseStudents.studentManagement') ?></p>
    </div>
</div>

<!-- Tarjetas resumen -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
        <p class="text-3xl font-black text-brand-500"><?= count($students) ?></p>
        <p class="text-xs text-gray-500 font-semibold mt-1"><?= __('admin.courseStudents.totalEnrolled') ?></p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
        <p class="text-3xl font-black text-emerald-500"><?= count($passed) ?></p>
        <p class="text-xs text-gray-500 font-semibold mt-1"><?= __('admin.courseStudents.quizPassed') ?></p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
        <p class="text-3xl font-black text-red-500"><?= count($failed) ?></p>
        <p class="text-xs text-gray-500 font-semibold mt-1"><?= __('admin.courseStudents.quizFailed') ?></p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
        <p class="text-3xl font-black text-amber-500"><?= count($pending) ?></p>
        <p class="text-xs text-gray-500 font-semibold mt-1"><?= __('admin.courseStudents.noEvaluation') ?></p>
    </div>
</div>

<!-- Formulario de inscripción -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
    <h3 class="text-base font-black text-navy mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        <?= __('admin.courseStudents.enrollStudent') ?>
    </h3>

    <?php if (empty($available)): ?>
    <p class="text-sm text-gray-400 font-semibold"><?= __('admin.courseStudents.allEnrolled') ?></p>
    <?php else: ?>
    <form method="POST" action="/admin/cursos/<?= $course['id'] ?>/estudiantes/inscribir"
          class="flex flex-wrap gap-3 items-end" id="enrollForm">
        <?= \App\Core\CSRF::field() ?>
        <input type="hidden" name="student_id" id="selectedStudentId">
        <div class="flex-1 min-w-64 relative">
            <label class="block text-xs font-bold text-gray-600 mb-1.5"><?= __('admin.courseStudents.searchStudentLabel') ?></label>
            <div class="relative">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="studentAutocomplete" autocomplete="off"
                       placeholder="<?= __('admin.courseStudents.searchPlaceholder') ?>"
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
            </div>
            <!-- Dropdown de sugerencias -->
            <ul id="autocompleteList"
                class="hidden absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-xl mt-1 max-h-60 overflow-y-auto">
            </ul>
        </div>
        <button type="submit" id="enrollBtn" disabled
                class="inline-flex items-center gap-2 bg-brand-500 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5 whitespace-nowrap disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:translate-y-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <?= __('admin.courseStudents.enroll') ?>
        </button>
    </form>

    <script>
    const students = <?= json_encode(array_values(array_map(fn($u) => [
        'id'    => (int) $u['id'],
        'name'  => $u['name'],
        'email' => $u['email'],
    ], $available))) ?>;

    const input     = document.getElementById('studentAutocomplete');
    const list      = document.getElementById('autocompleteList');
    const hiddenId  = document.getElementById('selectedStudentId');
    const enrollBtn = document.getElementById('enrollBtn');
    const form      = document.getElementById('enrollForm');

    function clearSelection() {
        hiddenId.value = '';
        enrollBtn.disabled = true;
    }

    function selectStudent(s) {
        input.value    = s.name + ' — ' + s.email;
        hiddenId.value = s.id;
        enrollBtn.disabled = false;
        list.classList.add('hidden');
    }

    function renderList(matches) {
        list.innerHTML = '';
        if (!matches.length) {
            list.innerHTML = '<li class="px-4 py-3 text-sm text-gray-400"><?= __('admin.courseStudents.noResults') ?></li>';
            list.classList.remove('hidden');
            return;
        }
        matches.forEach(s => {
            const li = document.createElement('li');
            li.className = 'px-4 py-3 text-sm cursor-pointer hover:bg-brand-50 flex flex-col border-b border-gray-50 last:border-0';
            li.innerHTML = `<span class="font-bold text-navy">${s.name}</span>
                            <span class="text-gray-400 text-xs">${s.email}</span>`;
            li.addEventListener('mousedown', e => { e.preventDefault(); selectStudent(s); });
            list.appendChild(li);
        });
        list.classList.remove('hidden');
    }

    input.addEventListener('input', function () {
        clearSelection();
        const q = this.value.trim().toLowerCase();
        if (q.length < 1) { list.classList.add('hidden'); return; }
        const matches = students.filter(s =>
            s.name.toLowerCase().includes(q) || s.email.toLowerCase().includes(q)
        );
        renderList(matches);
    });

    input.addEventListener('blur', () => setTimeout(() => list.classList.add('hidden'), 150));
    input.addEventListener('focus', function () {
        if (this.value.trim().length >= 1 && !hiddenId.value) this.dispatchEvent(new Event('input'));
    });

    form.addEventListener('submit', function (e) {
        if (!hiddenId.value) { e.preventDefault(); input.focus(); }
    });
    </script>
    <?php endif; ?>
</div>

<!-- Tabla de estudiantes -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-black text-navy text-base"><?= __('admin.courseStudents.enrolledStudents') ?></h3>
        <div class="relative">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="studentSearch" placeholder="<?= __('admin.courseStudents.searchStudentPh') ?>"
                   class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all w-52">
        </div>
    </div>

    <?php if (empty($students)): ?>
    <div class="py-16 text-center">
        <div class="text-5xl mb-3">👨‍🎓</div>
        <p class="text-gray-500 font-semibold"><?= __('admin.courseStudents.emptyTitle') ?></p>
        <p class="text-gray-400 text-sm mt-1"><?= __('admin.courseStudents.emptySubtitle') ?></p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left border-b border-gray-100">
                    <th class="px-6 py-3 text-xs font-black text-gray-400 uppercase tracking-wider"><?= __('admin.courseStudents.thStudent') ?></th>
                    <th class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider hidden md:table-cell"><?= __('admin.courseStudents.thEnrolled') ?></th>
                    <th class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider hidden lg:table-cell"><?= __('admin.courseStudents.thProgress') ?></th>
                    <th class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider"><?= __('admin.courseStudents.thQuiz') ?></th>
                    <th class="px-6 py-3 text-xs font-black text-gray-400 uppercase tracking-wider text-right"><?= __('admin.courseStudents.thActions') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50" id="studentsBody">
                <?php foreach ($students as $s):
                    $progress = $s['total_lessons'] > 0
                        ? round(($s['completed_lessons'] / $s['total_lessons']) * 100)
                        : 0;
                ?>
                <tr class="hover:bg-slate-50 transition-colors student-row"
                    data-name="<?= strtolower(htmlspecialchars($s['name'])) ?>"
                    data-email="<?= strtolower(htmlspecialchars($s['email'])) ?>">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <?php if (!empty($s['avatar'])): ?>
                            <img src="<?= htmlspecialchars($s['avatar']) ?>" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">
                            <?php else: ?>
                            <div class="w-9 h-9 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-black text-sm flex-shrink-0">
                                <?= mb_strtoupper(mb_substr($s['name'], 0, 1)) ?>
                            </div>
                            <?php endif; ?>
                            <div>
                                <p class="text-sm font-bold text-navy"><?= htmlspecialchars($s['name']) ?></p>
                                <p class="text-xs text-gray-400"><?= htmlspecialchars($s['email']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 hidden md:table-cell">
                        <p class="text-xs text-gray-500 font-semibold"><?= date('d/m/Y', strtotime($s['enrolled_at'])) ?></p>
                    </td>
                    <td class="px-4 py-4 hidden lg:table-cell">
                        <div class="flex items-center gap-2 min-w-28">
                            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full <?= $progress >= 100 ? 'bg-emerald-400' : 'bg-brand-400' ?>"
                                     style="width:<?= $progress ?>%"></div>
                            </div>
                            <span class="text-xs font-black text-gray-600 w-8 text-right"><?= $progress ?>%</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1"><?= $s['completed_lessons'] ?>/<?= $s['total_lessons'] ?> <?= __('admin.courseStudents.lessonsShort') ?></p>
                    </td>
                    <td class="px-4 py-4">
                        <?php if ($s['quiz_passed'] === null): ?>
                        <span class="inline-flex items-center gap-1 text-xs font-bold bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">
                            <?= __('admin.courseStudents.notEvaluated') ?>
                        </span>
                        <?php elseif ($s['quiz_passed'] == 1): ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <?= __('admin.courseStudents.passed') ?> <?= $s['best_score'] !== null ? '(' . number_format($s['best_score'], 1) . '%)' : '' ?>
                        </span>
                        <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold bg-red-100 text-red-700 px-2.5 py-1 rounded-full">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <?= __('admin.courseStudents.failed') ?> <?= $s['best_score'] !== null ? '(' . number_format($s['best_score'], 1) . '%)' : '' ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2 justify-end">
                            <form method="POST"
                                  action="/admin/cursos/<?= $course['id'] ?>/estudiantes/<?= $s['enrollment_id'] ?>/eliminar"
                                  onsubmit="return confirm('<?= addslashes(__('admin.courseStudents.confirmUnenroll', ['name' => htmlspecialchars($s['name'])])) ?>')">
                                <?= \App\Core\CSRF::field() ?>
                                <button type="submit"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <?= __('admin.courseStudents.unenroll') ?>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="px-6 py-3 border-t border-gray-100">
        <p class="text-xs text-gray-400 font-medium" id="studentCount"><?= count($students) ?> <?= count($students) !== 1 ? __('admin.courseStudents.studentPlural') : __('admin.courseStudents.studentSingular') ?></p>
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('studentSearch')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    let visible = 0;
    document.querySelectorAll('.student-row').forEach(row => {
        const match = !q || row.dataset.name.includes(q) || row.dataset.email.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    const counter = document.getElementById('studentCount');
    if (counter) counter.textContent = visible + ' ' + (visible !== 1 ? <?= json_encode(__('admin.courseStudents.studentPlural')) ?> : <?= json_encode(__('admin.courseStudents.studentSingular')) ?>);
});
</script>
