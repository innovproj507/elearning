<?php
/** @var array $conversaciones @var bool $esAlumno @var array $cursos @var array $old */
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<div class="max-w-5xl mx-auto">
    <p class="text-sm text-gray-500 mb-5">
        <?= $esAlumno ? __('chat.subtitle_student') : __('chat.subtitle_staff') ?>
    </p>

    <div class="grid gap-6 <?= $esAlumno ? 'lg:grid-cols-5' : '' ?>">

        <!-- Conversaciones -->
        <section class="<?= $esAlumno ? 'lg:col-span-3' : '' ?> bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <h2 class="px-5 py-3 border-b border-gray-100 text-sm font-black text-navy">
                <?= $esAlumno ? __('chat.your_conversations') : __('chat.conversations') ?>
                <span class="text-gray-400 font-bold">· <?= count($conversaciones) ?></span>
            </h2>

            <?php if (!$conversaciones): ?>
            <p class="px-5 py-10 text-center text-sm text-gray-400">
                <?= $esAlumno ? __('chat.empty_student') : __('chat.empty_staff') ?>
            </p>
            <?php else: ?>
            <ul class="divide-y divide-gray-100">
                <?php foreach ($conversaciones as $c):
                    $sinLeer = (int) ($c['sin_leer'] ?? 0);
                    $fecha = !empty($c['last_message_at']) ? strtotime((string) $c['last_message_at']) : strtotime((string) $c['created_at']);
                ?>
                <li>
                    <a href="/mensajes/<?= (int) $c['id'] ?>" class="flex items-start gap-3 px-5 py-3 hover:bg-gray-50 transition-colors">
                        <span class="mt-1 w-2.5 h-2.5 rounded-full flex-shrink-0 <?= $sinLeer ? 'bg-blue-500' : 'bg-transparent' ?>"></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm truncate <?= $sinLeer ? 'font-black text-navy' : 'font-semibold text-gray-700' ?>">
                                    <?= $e($c['subject']) ?>
                                </p>
                                <?php if ($sinLeer): ?>
                                <span class="text-[10px] font-black bg-blue-500 text-white rounded-full px-2 py-0.5 flex-shrink-0">
                                    <?= $sinLeer ?> <?= __('chat.unread') ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-400 truncate">
                                <?php if (!$esAlumno): ?><?= $e($c['student_name'] ?? '') ?> · <?php endif; ?>
                                <?= !empty($c['course_title']) ? $e($c['course_title']) : $e(__('chat.general')) ?>
                            </p>
                            <p class="text-xs text-gray-500 truncate mt-0.5"><?= $e(mb_strimwidth((string) ($c['ultimo'] ?? ''), 0, 90, '…')) ?></p>
                        </div>
                        <span class="text-[11px] text-gray-400 flex-shrink-0"><?= date('d/m H:i', $fecha ?: time()) ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </section>

        <?php if ($esAlumno): ?>
        <!-- Nueva conversación -->
        <section class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 self-start">
            <h2 class="text-sm font-black text-navy mb-4"><?= __('chat.new_conversation') ?></h2>
            <form method="POST" action="/mensajes/nuevo" class="space-y-3">
                <?= \App\Core\CSRF::field() ?>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1" for="chat-course"><?= __('chat.about_course') ?></label>
                    <select id="chat-course" name="course_id" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                        <option value=""><?= $e(__('chat.general')) ?></option>
                        <?php foreach ($cursos as $k): if (empty($k['id'])) continue; ?>
                        <option value="<?= (int) $k['id'] ?>" <?= (string) $old['course_id'] === (string) $k['id'] ? 'selected' : '' ?>>
                            <?= $e($k['title']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1" for="chat-subject"><?= __('chat.subject') ?></label>
                    <input id="chat-subject" name="subject" type="text" maxlength="150" required
                           placeholder="<?= $e(__('chat.subject_ph')) ?>"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1" for="chat-body"><?= __('chat.message') ?></label>
                    <textarea id="chat-body" name="body" rows="5" maxlength="2000" required
                              placeholder="<?= $e(__('chat.message_ph')) ?>"
                              class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm"></textarea>
                    <p class="text-[11px] text-gray-400 mt-1"><?= __('chat.max_chars', ['max' => 2000]) ?></p>
                </div>
                <button type="submit" class="w-full bg-navy text-white font-bold text-sm rounded-xl py-2.5 hover:opacity-90 transition">
                    <?= __('chat.start') ?>
                </button>
            </form>
        </section>
        <?php endif; ?>
    </div>
</div>

<script>
// Chat en vivo: un mensaje nuevo recarga la bandeja, salvo que se esté escribiendo en el formulario
window.addEventListener('chat-live', () => {
    const escribiendo = Array.from(document.querySelectorAll('textarea, input[name=subject]')).some(el => el.value.trim() !== '');
    if (!escribiendo && !document.hidden) location.reload();
});
</script>
