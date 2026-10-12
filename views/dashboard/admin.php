<!-- Bienvenida Admin -->
<div class="mb-8">
    <h2 class="text-2xl font-black text-navy"><?= __('dashboard.admin.title') ?></h2>
    <p class="text-gray-500 text-sm mt-1"><?= __('dashboard.admin.subtitle') ?></p>
</div>

<!-- Stats globales -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <?php
    $cards = [
        ['label'=>__('dashboard.admin.stat_students'),    'value'=>$stats['students'],    'icon'=>'👨‍🎓', 'from'=>'from-blue-500',   'to'=>'to-blue-700',    'href'=>'/admin/usuarios/students'],
        ['label'=>__('dashboard.admin.stat_teachers'),     'value'=>$stats['teachers'],    'icon'=>'👨‍🏫', 'from'=>'from-purple-500', 'to'=>'to-purple-700',  'href'=>'/admin/usuarios/teachers'],
        ['label'=>__('dashboard.admin.stat_total_courses'),   'value'=>$stats['courses'],     'icon'=>'📚', 'from'=>'from-slate-600',  'to'=>'to-slate-800',   'href'=>'/admin/cursos'],
        ['label'=>__('dashboard.admin.stat_published'),     'value'=>$stats['published'],   'icon'=>'✅', 'from'=>'from-emerald-500','to'=>'to-emerald-700', 'href'=>'/admin/cursos'],
        ['label'=>__('dashboard.admin.stat_enrollments'),  'value'=>$stats['enrollments'], 'icon'=>'🎓', 'from'=>'from-amber-500',  'to'=>'to-amber-700',   'href'=>'/admin/cursos'],
    ];
    foreach ($cards as $c): ?>
    <a href="<?= $c['href'] ?>"
       class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all block">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br <?= $c['from'] ?> <?= $c['to'] ?> flex items-center justify-center text-xl mb-4 shadow-lg">
            <?= $c['icon'] ?>
        </div>
        <p class="text-3xl font-black text-navy"><?= number_format($c['value']) ?></p>
        <p class="text-xs text-gray-500 font-semibold mt-1"><?= $c['label'] ?></p>
    </a>
    <?php endforeach; ?>
</div>

<!-- Accesos rápidos -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <a href="/admin/cursos"
       class="flex items-center gap-4 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all p-5">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center text-2xl shadow-lg flex-shrink-0">📚</div>
        <div class="min-w-0">
            <p class="font-black text-navy"><?= __('dashboard.admin.quick_access_courses_title') ?></p>
            <p class="text-xs text-gray-400 mt-0.5"><?= __('dashboard.admin.quick_access_courses_desc') ?></p>
        </div>
        <svg class="w-5 h-5 text-gray-300 ml-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
    <a href="/admin/usuarios/students"
       class="flex items-center gap-4 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all p-5">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-2xl shadow-lg flex-shrink-0">👨‍🎓</div>
        <div class="min-w-0">
            <p class="font-black text-navy"><?= __('dashboard.admin.quick_access_students_title') ?></p>
            <p class="text-xs text-gray-400 mt-0.5"><?= __('dashboard.admin.quick_access_students_desc') ?></p>
        </div>
        <svg class="w-5 h-5 text-gray-300 ml-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
    <a href="/admin/usuarios/teachers"
       class="flex items-center gap-4 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all p-5">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center text-2xl shadow-lg flex-shrink-0">👨‍🏫</div>
        <div class="min-w-0">
            <p class="font-black text-navy"><?= __('dashboard.admin.quick_access_teachers_title') ?></p>
            <p class="text-xs text-gray-400 mt-0.5"><?= __('dashboard.admin.quick_access_teachers_desc') ?></p>
        </div>
        <svg class="w-5 h-5 text-gray-300 ml-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
</div>
