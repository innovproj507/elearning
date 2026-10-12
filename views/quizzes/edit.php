<div class="max-w-4xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-3">
            <a href="/dashboard" class="hover:text-brand-500 font-semibold transition-colors">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>" class="hover:text-brand-500 font-semibold transition-colors truncate max-w-xs">
                <?= htmlspecialchars($course['title']) ?>
            </a>
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-navy font-semibold flex-shrink-0"><?= __('quizzes.edit.breadcrumb_edit') ?></span>
        </div>
        <h2 class="text-2xl font-black text-navy"><?= __('quizzes.edit.heading') ?></h2>
        <p class="text-gray-500 text-sm mt-1"><?= __('quizzes.edit.subheading') ?></p>
    </div>

    <!-- ── Info general ── -->
    <form method="POST" action="/cursos/<?= $course['id'] ?>/quizzes/<?= $quiz['id'] ?>/editar" id="quiz-edit-form" class="space-y-6">
        <?= \App\Core\CSRF::field() ?>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
            <h3 class="font-black text-navy mb-5"><?= __('quizzes.edit.general_info') ?></h3>
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('quizzes.edit.quiz_title_label') ?> <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required
                        value="<?= htmlspecialchars($quiz['title'], ENT_QUOTES) ?>"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('quizzes.edit.description_label') ?></label>
                    <input type="text" name="description"
                        value="<?= htmlspecialchars($quiz['description'] ?? '', ENT_QUOTES) ?>"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                        placeholder="<?= __('quizzes.edit.description_placeholder') ?>">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('quizzes.edit.pass_percentage_label') ?></label>
                        <input type="number" name="pass_percentage" min="0" max="100"
                            value="<?= htmlspecialchars((string)($quiz['pass_percentage'] ?? 70), ENT_QUOTES) ?>"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('quizzes.edit.time_limit_label') ?></label>
                        <input type="number" name="time_limit" min="0"
                            value="<?= htmlspecialchars((string)($quiz['time_limit'] ?? 0), ENT_QUOTES) ?>"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Agregar nuevas preguntas ── -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-black text-navy"><?= __('quizzes.edit.add_questions') ?></h3>
                    <p class="text-xs text-gray-400 mt-0.5"><?= __('quizzes.edit.add_questions_hint') ?></p>
                </div>
                <button type="button" onclick="addQuestion()"
                    class="inline-flex items-center gap-2 text-sm font-bold bg-brand-50 hover:bg-brand-100 text-brand-500 px-4 py-2 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <?= __('quizzes.edit.new_question') ?>
                </button>
            </div>
            <div id="questions-container" class="space-y-5"></div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit"
                class="flex-1 sm:flex-none bg-brand-500 hover:bg-brand-600 text-white font-bold px-8 py-3.5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5">
                <?= __('quizzes.edit.save_changes') ?>
            </button>
            <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>"
                class="flex-1 sm:flex-none text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-8 py-3.5 rounded-xl text-sm transition-all">
                <?= __('common.cancel') ?>
            </a>
        </div>
    </form>

    <!-- ── Preguntas existentes ── -->
    <?php $questions = $quiz['questions'] ?? []; ?>
    <div class="mt-8 bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-black text-navy"><?= __('quizzes.edit.existing_questions') ?></h3>
                <p class="text-xs text-gray-400 mt-0.5"><?= count($questions) !== 1 ? __('quizzes.edit.question_count_plural', ['count' => count($questions)]) : __('quizzes.edit.question_count', ['count' => count($questions)]) ?></p>
            </div>
        </div>

        <?php if (empty($questions)): ?>
        <p class="text-sm text-gray-400 text-center py-6"><?= __('quizzes.edit.no_questions') ?></p>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($questions as $i => $q): ?>
            <div class="border-2 border-gray-100 rounded-xl p-5 hover:border-gray-200 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-6 h-6 rounded-lg bg-brand-500 text-white flex items-center justify-center text-xs font-black flex-shrink-0"><?= $i + 1 ?></span>
                            <span class="text-xs font-bold text-gray-400 uppercase"><?= $q['type'] === 'true_false' ? __('quizzes.edit.type_true_false') : __('quizzes.edit.type_multiple') ?> · <?= $q['points'] ?? 1 ?> <?= ($q['points'] ?? 1) !== 1 ? __('quizzes.edit.points_abbr_plural') : __('quizzes.edit.points_abbr') ?></span>
                        </div>
                        <p class="font-semibold text-gray-800 text-sm mb-3"><?= htmlspecialchars($q['question'] ?? '') ?></p>
                        <ul class="space-y-1.5">
                            <?php foreach ($q['options'] ?? [] as $opt): ?>
                            <li class="flex items-center gap-2 text-sm <?= $opt['is_correct'] ? 'text-emerald-700 font-bold' : 'text-gray-500' ?>">
                                <?php if ($opt['is_correct']): ?>
                                <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                <?php else: ?>
                                <span class="w-3.5 h-3.5 rounded-full border-2 border-gray-300 flex-shrink-0"></span>
                                <?php endif; ?>
                                <?= htmlspecialchars($opt['option_text'] ?? '') ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <form method="POST" action="/cursos/<?= $course['id'] ?>/quizzes/<?= $quiz['id'] ?>/preguntas/<?= $q['id'] ?>/eliminar"
                          onsubmit="return confirm('<?= addslashes(__('quizzes.edit.delete_question_confirm')) ?>')" class="flex-shrink-0">
                        <?= \App\Core\CSRF::field() ?>
                        <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center transition-colors" title="<?= __('quizzes.edit.delete_title') ?>">
                            <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
let questionCount = 0;

function addQuestion() {
    const i = questionCount++;
    const container = document.getElementById('questions-container');
    const html = `
    <div class="border-2 border-gray-100 rounded-xl p-5 hover:border-brand-200 transition-colors" id="question_${i}">
        <div class="flex items-center justify-between mb-4">
            <span class="flex items-center gap-2 font-black text-navy text-sm">
                <span class="w-6 h-6 rounded-lg bg-brand-500 text-white flex items-center justify-center text-xs">${i + 1}</span>
                <?= __('quizzes.edit.new_question') ?>
            </span>
            <button type="button" onclick="removeQuestion(${i})"
                class="text-xs font-bold text-red-400 hover:text-red-600 hover:bg-red-50 px-3 py-1 rounded-lg transition-colors">
                <?= __('quizzes.edit.remove') ?>
            </button>
        </div>
        <div class="space-y-4">
            <input type="text" name="questions[${i}][text]" required
                placeholder="<?= __('quizzes.edit.question_placeholder') ?>"
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
            <div class="flex items-center gap-3">
                <select name="questions[${i}][type]" onchange="toggleQuestionType(${i}, this.value)"
                    class="px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 transition-all bg-white font-semibold">
                    <option value="multiple_choice"><?= __('quizzes.edit.multiple_choice') ?></option>
                    <option value="true_false"><?= __('quizzes.edit.true_false') ?></option>
                </select>
                <div class="flex items-center gap-2">
                    <input type="number" name="questions[${i}][points]" value="1" min="1"
                        class="w-20 px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 transition-all text-center font-bold">
                    <span class="text-xs text-gray-400 font-semibold"><?= __('quizzes.edit.points_label') ?></span>
                </div>
            </div>
            <div id="options_${i}" class="space-y-2">
                ${addOptionHTML(i, 0)}${addOptionHTML(i, 1)}${addOptionHTML(i, 2)}${addOptionHTML(i, 3)}
            </div>
            <button type="button" onclick="addOption(${i})"
                class="text-xs font-bold text-brand-500 hover:text-brand-600 hover:underline transition-colors">
                <?= __('quizzes.edit.add_option') ?>
            </button>
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
}

const optionPlaceholderTpl = <?= json_encode(__('quizzes.edit.option_placeholder', ['num' => '__NUM__'])) ?>;
function addOptionHTML(qi, oi) {
    return `
    <div class="flex items-center gap-3 p-3 border-2 border-gray-100 rounded-xl hover:border-brand-200 transition-colors" id="option_${qi}_${oi}">
        <input type="radio" name="questions[${qi}][correct]" value="${oi}"
            class="text-brand-500 focus:ring-brand-500 w-4 h-4 flex-shrink-0">
        <input type="text" name="questions[${qi}][options][${oi}][text]" required
            placeholder="${optionPlaceholderTpl.replace('__NUM__', oi + 1)}"
            class="flex-1 text-sm font-semibold text-gray-700 bg-transparent outline-none placeholder-gray-300">
        <input type="hidden" name="questions[${qi}][options][${oi}][is_correct]" value="0" id="is_correct_${qi}_${oi}">
    </div>`;
}

let optionCounts = {};
function addOption(qi) {
    optionCounts[qi] = (optionCounts[qi] ?? 4);
    const oi = optionCounts[qi]++;
    document.getElementById(`options_${qi}`).insertAdjacentHTML('beforeend', addOptionHTML(qi, oi));
}

const trueLabel = <?= json_encode(__('quizzes.edit.true_label')) ?>;
const falseLabel = <?= json_encode(__('quizzes.edit.false_label')) ?>;
function toggleQuestionType(qi, type) {
    const container = document.getElementById(`options_${qi}`);
    if (type === 'true_false') {
        container.innerHTML = `
            <div class="flex items-center gap-3 p-3 border-2 border-gray-100 rounded-xl" id="option_${qi}_0">
                <input type="radio" name="questions[${qi}][correct]" value="0" class="text-brand-500 w-4 h-4 flex-shrink-0">
                <span class="flex-1 text-sm font-semibold text-gray-700">${trueLabel}</span>
                <input type="hidden" name="questions[${qi}][options][0][text]" value="${trueLabel}">
                <input type="hidden" name="questions[${qi}][options][0][is_correct]" value="0" id="is_correct_${qi}_0">
            </div>
            <div class="flex items-center gap-3 p-3 border-2 border-gray-100 rounded-xl" id="option_${qi}_1">
                <input type="radio" name="questions[${qi}][correct]" value="1" class="text-brand-500 w-4 h-4 flex-shrink-0">
                <span class="flex-1 text-sm font-semibold text-gray-700">${falseLabel}</span>
                <input type="hidden" name="questions[${qi}][options][1][text]" value="${falseLabel}">
                <input type="hidden" name="questions[${qi}][options][1][is_correct]" value="0" id="is_correct_${qi}_1">
            </div>`;
    }
}

function removeQuestion(i) {
    document.getElementById(`question_${i}`)?.remove();
}

document.getElementById('quiz-edit-form').addEventListener('change', function(e) {
    if (e.target.type === 'radio' && e.target.name.includes('[correct]')) {
        const qi = e.target.name.match(/questions\[(\d+)\]/)[1];
        document.querySelectorAll(`input[id^="is_correct_${qi}_"]`).forEach(el => el.value = '0');
        const hidden = document.getElementById(`is_correct_${qi}_${e.target.value}`);
        if (hidden) hidden.value = '1';
    }
});
</script>
