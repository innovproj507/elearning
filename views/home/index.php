<?php
$heroStats  = $heroStats  ?? ['courses' => 0, 'students' => 0, 'categories' => 0];
$featured   = $featured   ?? [];
$categories = $categories ?? [];
$isLoggedIn = isset($_SESSION['user']);
?>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- HERO                                                           -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<section class="relative overflow-hidden" style="background:#071424;min-height:88vh;display:flex;align-items:center">

    <?php
    $slideFiles = ['slide1.jpg','slide2.jpg','slide3.jpg','slide4.jpg','slide5.jpg'];
    $slides = [];
    foreach ($slideFiles as $f) {
        if (file_exists(BASE_PATH . '/public/assets/img/slides/' . $f)) {
            $slides[] = '/assets/img/slides/' . $f;
        }
    }
    ?>

    <!-- Slides de fondo -->
    <?php if (!empty($slides)): ?>
    <div id="hero-slides" class="absolute inset-0">
        <?php foreach ($slides as $i => $src): ?>
        <div class="hero-slide absolute inset-0 bg-cover bg-center transition-opacity duration-1000"
             style="background-image:url('<?= htmlspecialchars($src) ?>');opacity:<?= $i === 0 ? '1' : '0' ?>">
        </div>
        <?php endforeach; ?>
    </div>
    <div class="absolute inset-0" style="background:linear-gradient(160deg,rgba(5,14,26,.90) 0%,rgba(7,25,55,.78) 50%,rgba(10,35,75,.65) 100%)"></div>
    <?php else: ?>
    <div class="absolute inset-0" style="background:linear-gradient(160deg,#071424 0%,#0d2347 55%,#103060 100%)"></div>
    <?php endif; ?>

    <!-- Grid overlay -->
    <div class="absolute inset-0 opacity-[0.03]" style="background-image:linear-gradient(rgba(255,255,255,.7) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.7) 1px,transparent 1px);background-size:48px 48px"></div>

    <!-- Glow accents -->
    <div class="absolute top-0 right-0 w-[700px] h-[700px] rounded-full opacity-[0.07]" style="background:radial-gradient(circle,#3b82f6,transparent);transform:translate(25%,-25%)"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] rounded-full opacity-[0.05]" style="background:radial-gradient(circle,#f59e0b,transparent);transform:translate(-30%,30%)"></div>

    <div class="relative w-full max-w-6xl mx-auto px-6 py-28 lg:py-36">
        <div class="grid lg:grid-cols-2 gap-14 items-center">

            <!-- Left: copy -->
            <div>
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 mb-7 px-3.5 py-2 rounded-full border border-blue-400/25 bg-blue-500/10 backdrop-blur-sm">
                    <svg class="w-3.5 h-3.5 text-blue-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span class="text-blue-300 text-[11px] font-bold tracking-widest uppercase"><?= __('home.hero.badge') ?></span>
                </div>

                <!-- Headline -->
                <h1 class="text-5xl sm:text-6xl lg:text-[4rem] font-black text-white leading-[1.05] mb-6 tracking-tight">
                    <?= __('home.hero.title_line1') ?><br>
                    <?= __('home.hero.title_line2') ?><br>
                    <span style="color:#f59e0b"><?= __('home.hero.title_highlight') ?></span>
                </h1>

                <p class="text-blue-100/75 text-lg leading-relaxed mb-10 max-w-md">
                    <?= __('home.hero.subtitle') ?>
                </p>

                <!-- CTAs -->
                <div class="flex flex-wrap gap-3.5">
                    <a href="/cursos" class="inline-flex items-center gap-2.5 font-bold text-sm px-7 py-3.5 rounded-xl transition-all shadow-xl shadow-yellow-500/20 hover:-translate-y-0.5" style="background:#f59e0b;color:#fff">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <?= __('home.hero.cta_explore') ?>
                    </a>
                    <a href="<?= $isLoggedIn ? '/dashboard' : '/login' ?>" class="inline-flex items-center gap-2.5 bg-white/10 hover:bg-white/18 border border-white/20 text-white font-semibold text-sm px-7 py-3.5 rounded-xl transition-all backdrop-blur-sm">
                        <?= $isLoggedIn ? __('home.hero.cta_dashboard') : __('home.hero.cta_login') ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <!-- Trust pills -->
                <div class="flex flex-wrap gap-2.5 mt-10">
                    <?php foreach ([__('home.hero.pill_stcw'), __('home.hero.pill_imo'), __('home.hero.pill_authority'), __('home.hero.pill_online')] as $pill): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-white/55 text-xs font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
                        <?= $pill ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Bottom fade -->
    <div class="absolute bottom-0 left-0 right-0 h-24" style="background:linear-gradient(to bottom,transparent,#f8fafc)"></div>

    <!-- Slide indicators -->
    <?php if (count($slides) > 1): ?>
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-2 z-10">
        <?php foreach ($slides as $i => $_): ?>
        <button onclick="heroGoTo(<?= $i ?>)"
                class="hero-dot transition-all duration-300 rounded-full <?= $i === 0 ? 'bg-white w-6 h-2' : 'bg-white/35 w-2 h-2' ?>">
        </button>
        <?php endforeach; ?>
    </div>
    <script>
    (function(){
        const slides = document.querySelectorAll('.hero-slide');
        const dots   = document.querySelectorAll('.hero-dot');
        if (slides.length < 2) return;
        let current = 0, timer;
        function show(n) {
            slides[current].style.opacity = '0';
            dots[current].className = 'hero-dot transition-all duration-300 rounded-full bg-white/35 w-2 h-2';
            current = (n + slides.length) % slides.length;
            slides[current].style.opacity = '1';
            dots[current].className = 'hero-dot transition-all duration-300 rounded-full bg-white w-6 h-2';
        }
        function next() { show(current + 1); }
        window.heroGoTo = function(n) { clearInterval(timer); show(n); timer = setInterval(next, 5500); };
        timer = setInterval(next, 5500);
    })();
    </script>
    <?php endif; ?>
</section>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- CATEGORÍAS                                                     -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<?php if (!empty($categories)): ?>
<section class="py-20 bg-slate-50">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex items-end justify-between mb-10">
            <div>
                <p class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-2"><?= __('home.categories.eyebrow') ?></p>
                <h2 class="text-2xl font-black text-[#0d2347]"><?= __('home.categories.title') ?></h2>
            </div>
            <a href="/cursos" class="text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors hidden sm:block"><?= __('home.categories.view_all') ?></a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-<?= min(5, count($categories)) ?> gap-3">
            <?php
            $catPalette = [
                ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'hborder' => '#3b82f6', 'text' => '#1d4ed8'],
                ['bg' => '#fffbeb', 'border' => '#fde68a', 'hborder' => '#f59e0b', 'text' => '#b45309'],
                ['bg' => '#f0fdf4', 'border' => '#bbf7d0', 'hborder' => '#10b981', 'text' => '#047857'],
                ['bg' => '#faf5ff', 'border' => '#e9d5ff', 'hborder' => '#8b5cf6', 'text' => '#6d28d9'],
                ['bg' => '#fff1f2', 'border' => '#fecdd3', 'hborder' => '#f43f5e', 'text' => '#be123c'],
            ];
            foreach ($categories as $i => $cat):
                $p = $catPalette[$i % 5];
            ?>
            <a href="/cursos?categoria=<?= $cat['id'] ?>"
               class="group rounded-xl p-5 text-center transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5"
               style="background:<?= $p['bg'] ?>;border:1px solid <?= $p['border'] ?>"
               onmouseover="this.style.borderColor='<?= $p['hborder'] ?>'" onmouseout="this.style.borderColor='<?= $p['border'] ?>'">
                <div class="w-12 h-12 rounded-xl mx-auto mb-3 flex items-center justify-center" style="background:<?= $p['bg'] ?>;border:1px solid <?= $p['border'] ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:<?= $p['text'] ?>">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <p class="font-bold text-gray-800 text-sm leading-snug mb-1"><?= htmlspecialchars($cat['name']) ?></p>
                <p class="text-xs font-medium" style="color:<?= $p['text'] ?>"><?= (int)$cat['course_count'] ?> <?= $cat['course_count'] != 1 ? __('home.categories.course_plural') : __('home.categories.course_singular') ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- CURSOS DESTACADOS                                              -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-6">

        <div class="flex items-end justify-between mb-10">
            <div>
                <p class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-2"><?= __('home.featured.eyebrow') ?></p>
                <h2 class="text-2xl font-black text-[#0d2347]"><?= __('home.featured.title') ?></h2>
            </div>
            <a href="/cursos" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                <?= __('home.featured.view_all') ?>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <?php if (empty($featured)): ?>
        <div class="text-center py-24 border border-dashed border-gray-200 rounded-2xl bg-slate-50">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p class="text-gray-400 text-sm font-medium"><?= __('home.featured.empty') ?></p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            <?php foreach ($featured as $course):
                include BASE_PATH . '/views/courses/_card.php';
            endforeach; ?>
        </div>
        <div class="text-center mt-10">
            <a href="/cursos" class="inline-flex items-center gap-2 font-semibold text-sm px-8 py-3.5 rounded-xl border-2 border-[#0d2347] text-[#0d2347] hover:bg-[#0d2347] hover:text-white transition-all">
                <?= __('home.featured.view_full_catalog') ?>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- POR QUÉ PAMEL                                                  -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<section class="py-20 bg-slate-50 border-t border-gray-100">
    <div class="max-w-6xl mx-auto px-6">

        <div class="text-center mb-14">
            <p class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-2"><?= __('home.why.eyebrow') ?></p>
            <h2 class="text-2xl font-black text-[#0d2347] mb-3"><?= __('home.why.title') ?></h2>
            <p class="text-gray-500 max-w-xl mx-auto text-sm leading-relaxed">
                <?= __('home.why.subtitle') ?>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php
            $features = [
                [
                    'title' => __('home.why.feature_certification_title'),
                    'desc'  => __('home.why.feature_certification_desc'),
                    'icon'  => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'color' => '#3b82f6', 'bg' => '#eff6ff',
                ],
                [
                    'title' => __('home.why.feature_instructors_title'),
                    'desc'  => __('home.why.feature_instructors_desc'),
                    'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'color' => '#f59e0b', 'bg' => '#fffbeb',
                ],
                [
                    'title' => __('home.why.feature_pace_title'),
                    'desc'  => __('home.why.feature_pace_desc'),
                    'icon'  => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    'color' => '#10b981', 'bg' => '#f0fdf4',
                ],
            ];
            foreach ($features as $f): ?>
            <div class="bg-white rounded-2xl border border-gray-100 p-7 hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-5" style="background:<?= $f['bg'] ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:<?= $f['color'] ?>">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="<?= $f['icon'] ?>"/>
                    </svg>
                </div>
                <h3 class="font-black text-gray-900 mb-2.5 text-base"><?= $f['title'] ?></h3>
                <p class="text-gray-500 text-sm leading-relaxed"><?= $f['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
