<div class="max-w-3xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-3">
            <a href="/dashboard" class="hover:text-brand-500 font-semibold transition-colors"><?= __('lessons.edit.breadcrumb_dashboard') ?></a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>" class="hover:text-brand-500 font-semibold transition-colors truncate max-w-xs">
                <?= htmlspecialchars($course['title']) ?>
            </a>
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-navy font-semibold flex-shrink-0"><?= __('lessons.edit.breadcrumb_current') ?></span>
        </div>
        <h2 class="text-2xl font-black text-navy"><?= __('lessons.edit.heading') ?></h2>
        <p class="text-gray-500 text-sm mt-1"><?= __('lessons.edit.subheading') ?></p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
        <form method="POST" action="/cursos/<?= $course['id'] ?>/lecciones/<?= $lesson['id'] ?>/editar" enctype="multipart/form-data" class="space-y-6">
            <?= \App\Core\CSRF::field() ?>

            <!-- Título -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('lessons.edit.title_label') ?> <span class="text-red-500">*</span></label>
                <input type="text" name="title" required
                    value="<?= htmlspecialchars($lesson['title'], ENT_QUOTES) ?>"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
            </div>

            <!-- Tipo + Duración + Orden -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('lessons.edit.content_type') ?></label>
                    <select name="type" id="lesson_type" onchange="toggleLessonFields()"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all bg-white">
                        <option value="text"  <?= $lesson['type'] === 'text'  ? 'selected' : '' ?>>📄 <?= __('lessons.edit.type_text') ?></option>
                        <option value="video" <?= $lesson['type'] === 'video' ? 'selected' : '' ?>>🎬 <?= __('lessons.edit.type_video') ?></option>
                        <option value="file"  <?= $lesson['type'] === 'file'  ? 'selected' : '' ?>>📎 <?= __('lessons.edit.type_file') ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('lessons.edit.duration_label') ?></label>
                    <input type="number" name="duration" min="0"
                        value="<?= htmlspecialchars((string)($lesson['duration'] ?? 0), ENT_QUOTES) ?>"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('lessons.edit.order_label') ?></label>
                    <input type="number" name="order_num" min="0"
                        value="<?= htmlspecialchars((string)($lesson['order_num'] ?? 0), ENT_QUOTES) ?>"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all">
                </div>
            </div>

            <!-- URL de vídeo -->
            <div id="field_video" class="<?= $lesson['type'] !== 'video' ? 'hidden' : '' ?>">
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('lessons.edit.video_url_label') ?></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <input type="url" name="video_url"
                        value="<?= htmlspecialchars($lesson['video_url'] ?? '', ENT_QUOTES) ?>"
                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all"
                        placeholder="https://youtube.com/watch?v=...">
                </div>
                <p class="text-xs text-gray-400 mt-1.5 font-medium"><?= __('lessons.edit.video_url_help') ?></p>
            </div>

            <!-- Archivo -->
            <div id="field_file" class="<?= $lesson['type'] !== 'file' ? 'hidden' : '' ?>">
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('lessons.edit.file_label') ?></label>
                <?php if (!empty($lesson['file_path'])): ?>
                <div class="mb-3 flex items-center gap-2 text-sm text-gray-600 bg-gray-50 px-4 py-2.5 rounded-xl border border-gray-200">
                    <svg class="w-4 h-4 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span class="font-semibold truncate"><?= __('lessons.edit.current_file_label') ?></span>
                    <a href="<?= htmlspecialchars(cms_asset($lesson['file_path'])) ?>" target="_blank" class="text-brand-500 hover:underline truncate"><?= htmlspecialchars(basename($lesson['file_path'])) ?></a>
                </div>
                <?php endif; ?>
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-brand-300 transition-colors">
                    <div class="text-3xl mb-2">📎</div>
                    <input type="file" name="lesson_file"
                        class="text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-brand-50 file:text-brand-500 hover:file:bg-brand-100 transition-all cursor-pointer">
                    <p class="text-xs text-gray-400 mt-2"><?= __('lessons.edit.file_replace_help') ?></p>
                </div>
            </div>

            <!-- Contenido texto — Editor WYSIWYG -->
            <div id="field_content" class="<?= $lesson['type'] === 'file' ? 'hidden' : '' ?>">
                <label class="block text-sm font-bold text-gray-700 mb-1.5"><?= __('lessons.edit.content_label') ?></label>
                <textarea name="content" id="editor_content"><?= htmlspecialchars($lesson['content'] ?? '', ENT_QUOTES) ?></textarea>
            </div>

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                    class="flex-1 sm:flex-none bg-brand-500 hover:bg-brand-600 text-white font-bold px-8 py-3.5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/25 hover:-translate-y-0.5">
                    <?= __('lessons.edit.submit') ?>
                </button>
                <a href="/cursos/<?= htmlspecialchars($course['slug']) ?>"
                    class="flex-1 sm:flex-none text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-8 py-3.5 rounded-xl text-sm transition-all">
                    <?= __('common.cancel') ?>
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
ClassicEditor.create(document.getElementById('editor_content'), {
    toolbar: [
        'heading', '|',
        'bold', 'italic', '|',
        'bulletedList', 'numberedList', '|',
        'outdent', 'indent', '|',
        'blockQuote', 'insertTable', '|',
        'link', 'uploadImage', '|',
        'undo', 'redo'
    ],
    heading: {
        options: [
            { model: 'paragraph', title: '<?= __('lessons.edit.editor_paragraph') ?>',   class: 'ck-heading_paragraph' },
            { model: 'heading2',  view: 'h2', title: '<?= __('lessons.edit.editor_heading2') ?>', class: 'ck-heading_heading2' },
            { model: 'heading3',  view: 'h3', title: '<?= __('lessons.edit.editor_heading3') ?>', class: 'ck-heading_heading3' },
            { model: 'heading4',  view: 'h4', title: '<?= __('lessons.edit.editor_heading4') ?>', class: 'ck-heading_heading4' },
        ]
    },
    simpleUpload: {
        uploadUrl: '/upload/imagen',
        headers: {
            'X-CSRF-Token': document.querySelector('input[name="_csrf"]')?.value ?? ''
        }
    },
    image: {
        toolbar: ['imageTextAlternative', '|', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side']
    }
}).catch(console.error);

function toggleLessonFields() {
    const type = document.getElementById('lesson_type').value;
    document.getElementById('field_video').classList.toggle('hidden', type !== 'video');
    document.getElementById('field_file').classList.toggle('hidden', type !== 'file');
    document.getElementById('field_content').classList.toggle('hidden', type === 'file');
}
</script>
