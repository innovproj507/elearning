<style>
.card-entry { animation: cardIn .4s ease both; }
@keyframes cardIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
.card-entry:nth-child(2) { animation-delay:.05s; }
.card-entry:nth-child(3) { animation-delay:.1s; }
.card-entry:nth-child(4) { animation-delay:.15s; }
.card-entry:nth-child(5) { animation-delay:.2s; }
.card-entry:nth-child(6) { animation-delay:.25s; }
</style>

<div class="max-w-7xl mx-auto px-4 py-10">

    <!-- Hero header -->
    <div class="relative rounded-3xl overflow-hidden mb-10 shadow-xl"
         style="background:linear-gradient(135deg,#0a1628 0%,#1a3a6b 55%,#1a56db 100%);">
        <div class="absolute inset-0 opacity-10" style="background-image:repeating-linear-gradient(0deg,#fff 0,#fff 1px,transparent 1px,transparent 50px),repeating-linear-gradient(90deg,#fff 0,#fff 1px,transparent 1px,transparent 50px)"></div>
        <div class="relative z-10 px-8 py-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div>
                <p class="text-blue-300 text-xs font-bold uppercase tracking-widest mb-2">PAMEL Maritime Training Center</p>
                <h1 class="text-3xl font-black text-white mb-2"><?= __('courses.index.title') ?></h1>
                <p class="text-blue-200 text-sm"><?= __('courses.index.subtitle') ?></p>
                <div class="flex flex-wrap gap-4 mt-4">
                    <div class="flex items-center gap-2 text-white/80 text-xs font-semibold">
                        <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"/></svg>
                        <?= __('courses.index.hero_count', ['count' => $courses['total']]) ?>
                    </div>
                    <div class="flex items-center gap-2 text-white/80 text-xs font-semibold">
                        <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?= __('courses.index.imo_badge') ?>
                    </div>
                    <div class="flex items-center gap-2 text-white/80 text-xs font-semibold">
                        <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064"/></svg>
                        <?= __('courses.index.stcw_badge') ?>
                    </div>
                </div>
            </div>
            <div class="flex-shrink-0">
                <div class="w-20 h-20 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-10 h-10 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        <!-- ─── Sidebar filtros ──────────────────────────────── -->
        <aside class="w-full lg:w-60 flex-shrink-0">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sticky top-6">
                <h3 class="font-black text-navy text-xs uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <?= __('courses.index.categories') ?>
                </h3>
                <ul class="space-y-1">
                    <li>
                        <a href="/cursos"
                           class="flex items-center justify-between text-sm px-3 py-2 rounded-xl font-semibold transition-all
                                  <?= !$currentCat ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/25' : 'text-gray-600 hover:bg-slate-50' ?>">
                            <span><?= __('courses.index.all_courses') ?></span>
                            <span class="text-xs <?= !$currentCat ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' ?> font-bold px-1.5 py-0.5 rounded-md"><?= $courses['total'] ?></span>
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="/cursos?categoria=<?= $cat['id'] ?>"
                           class="flex items-center justify-between text-sm px-3 py-2 rounded-xl font-semibold transition-all
                                  <?= $currentCat == $cat['id'] ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/25' : 'text-gray-600 hover:bg-slate-50' ?>">
                            <span class="truncate"><?= htmlspecialchars($cat['name']) ?></span>
                            <span class="text-xs <?= $currentCat == $cat['id'] ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' ?> font-bold px-1.5 py-0.5 rounded-md flex-shrink-0 ml-1"><?= $cat['course_count'] ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <!-- ─── Listado de cursos ────────────────────────────── -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-5">
                <p class="text-sm text-gray-500 font-semibold">
                    <span class="text-navy font-black"><?= $courses['total'] ?></span> <?= __('courses.index.available') ?>
                    <?php if ($currentCat): ?>
                    <span class="ml-2 text-brand-500">· <?= __('courses.index.filtered') ?></span>
                    <?php endif; ?>
                </p>
            </div>

            <?php if (empty($courses['data'])): ?>
            <div class="py-20 text-center bg-white rounded-2xl border border-gray-100 shadow-sm">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center text-3xl mx-auto mb-4">🔍</div>
                <p class="text-gray-500 font-bold mb-4"><?= __('courses.index.empty') ?></p>
                <a href="/cursos" class="text-brand-500 hover:text-brand-600 font-bold text-sm"><?= __('courses.index.view_all') ?> →</a>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php foreach ($courses['data'] as $course): ?>
                <div class="card-entry">
                    <?php include BASE_PATH . '/views/courses/_card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Paginación -->
            <?php if ($courses['last_page'] > 1): ?>
            <div class="flex justify-center gap-2 mt-10">
                <?php for ($p = 1; $p <= $courses['last_page']; $p++): ?>
                <a href="/cursos?page=<?= $p ?><?= $currentCat ? '&categoria=' . $currentCat : '' ?>"
                   class="w-9 h-9 flex items-center justify-center rounded-xl text-sm font-bold transition-all
                          <?= $p === $courses['current_page']
                              ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/25'
                              : 'bg-white text-gray-600 hover:bg-slate-50 border border-gray-200' ?>">
                    <?= $p ?>
                </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
