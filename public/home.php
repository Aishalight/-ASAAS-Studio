<?php
$seoTitle = 'Digital Agency Somalia — Web Design, Branding & Development';
$seoDesc = 'ASAAS Studio is a premium digital agency in Somalia specializing in web design, web development, branding, UI/UX, and digital marketing. We engineer digital growth from vision to reality.';
$seoKeywords = 'digital agency Somalia, web design agency Somalia, branding agency Somalia, web development Somalia, UI UX design Somalia, digital marketing Somalia, ASAAS studio';
require __DIR__ . '/../includes/header.php';
$db = Database::getInstance()->getConnection();
$totalProjectsCount = (int)$db->query("SELECT COUNT(*) FROM portfolio_projects WHERE status='published'")->fetchColumn() ?: 0;
$avgRatingVal = $db->query("SELECT ROUND(AVG(rating),1) FROM ratings WHERE is_approved=1")->fetchColumn() ?: 0;
$totalRatingsCount = (int)$db->query("SELECT COUNT(*) FROM ratings WHERE is_approved=1")->fetchColumn() ?: 0;
$totalUsersCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn() ?: 0;
?>

<main class="page-transition">
    <!-- ============================================================
    HERO SECTION
    ============================================================ -->
    <section class="hero" id="hero">
        <div class="hero-bg">
            <div class="hero-bg-gradient"></div>
            <div class="hero-shapes">
                <div class="hero-shape"></div>
                <div class="hero-shape"></div>
                <div class="hero-shape"></div>
            </div>
        </div>
        <div class="container">
            <div style="display:flex;align-items:flex-start;gap:60px;width:100%;padding-top:20px">
                <div class="hero-content fade-in-up" style="flex:1;max-width:600px">
                    <div class="hero-tag">
                        <span class="hero-tag-dot"></span>
                        Digital Agency
                    </div>
                    <h1 class="hero-title">
                        We Engineer Your<br>
                        <span class="gradient-text">Digital Growth</span><br>
                        From Vision to Reality
                    </h1>
                    <p class="hero-desc">
                        Strategy-first digital agency. We turn your brand into a lead-generating machine — through high-performance websites, conversion-focused design, and marketing that actually moves the needle.
                    </p>
                    <div class="hero-actions">
                        <a href="<?= BASE_URL ?>contact#booking" class="btn btn-primary btn-lg">
                            Book a Call
                            <i data-lucide="calendar" size="20"></i>
                        </a>
                        <a href="<?= BASE_URL ?>portfolio" class="btn btn-secondary btn-lg">
                            Our Work
                            <i data-lucide="eye" size="20"></i>
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div>
                            <div class="hero-stat-value"><span class="counter" data-target="<?= max($totalProjectsCount, 1) ?>" data-duration="2000">0</span><span class="hero-stat-suffix">+</span></div>
                            <div class="hero-stat-label">Projects</div>
                        </div>
                        <div class="hero-stat-divider"></div>
                        <div>
                            <div class="hero-stat-value"><span class="counter" data-target="<?= max(round($avgRatingVal * 20), 1) ?>" data-duration="2000">0</span><span class="hero-stat-suffix">%</span></div>
                            <div class="hero-stat-label">Satisfaction</div>
                        </div>
                        <div class="hero-stat-divider"></div>
                        <div>
                            <div class="hero-stat-value"><span class="counter" data-target="12" data-duration="2000">0</span><span class="hero-stat-suffix">+</span></div>
                            <div class="hero-stat-label">Years</div>
                        </div>
                        <div class="hero-stat-divider"></div>
                        <div>
                            <div class="hero-stat-value"><span class="counter" data-target="<?= max($totalUsersCount, 2) ?>" data-duration="2000">0</span><span class="hero-stat-suffix">+</span></div>
                            <div class="hero-stat-label">Team</div>
                        </div>
                    </div>
                </div>
                <div class="fade-in-up" style="flex:1;display:none;justify-content:center;align-items:center;position:relative" id="hero-visual">
                    <div style="position:relative;width:100%;max-width:640px">
                        <?php
                        $heroProjects = $db->query("SELECT id, title, slug, featured_image FROM portfolio_projects WHERE status='published' AND featured_image IS NOT NULL AND featured_image != '' ORDER BY featured DESC, created_at DESC LIMIT 6")->fetchAll();
                        if (empty($heroProjects)) {
                            $heroProjects = [
                                ['id' => 1, 'title' => 'TechVolve Platform', 'slug' => 'techvolve-platform', 'featured_image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&h=500&fit=crop'],
                                ['id' => 2, 'title' => 'FinFlow Dashboard', 'slug' => 'finflow-dashboard', 'featured_image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&h=500&fit=crop'],
                                ['id' => 3, 'title' => 'Bloom E-Commerce', 'slug' => 'bloom-ecommerce', 'featured_image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800&h=500&fit=crop'],
                                ['id' => 4, 'title' => 'CloudBase SaaS', 'slug' => 'cloudbase-saas', 'featured_image' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&h=500&fit=crop'],
                            ];
                        }
                        ?>
                        <div class="hero-carousel">
                            <div class="hero-carousel-viewport">
                                <?php foreach ($heroProjects as $hi => $hp):
                                    $hImg = strpos($hp['featured_image'], 'uploads/') === 0 ? BASE_URL . $hp['featured_image'] : $hp['featured_image'];
                                    $hUrl = BASE_URL . 'portfolio/' . htmlspecialchars($hp['slug'] ?? 'project'); ?>
                                    <a class="hero-slide<?= $hi === 0 ? ' active' : '' ?>" href="<?= $hUrl ?>" data-title="<?= htmlspecialchars($hp['title']) ?>" data-url="<?= $hUrl ?>" style="background-image:url('<?= $hImg ?>')" aria-label="<?= htmlspecialchars($hp['title']) ?>"></a>
                                <?php endforeach; ?>
                                <div class="hero-slide-overlay">
                                    <span class="hero-slide-tag">Featured Work</span>
                                    <h3 class="hero-slide-title"><?= htmlspecialchars($heroProjects[0]['title']) ?></h3>
                                    <a class="hero-slide-link" href="<?= BASE_URL . 'portfolio/' . htmlspecialchars($heroProjects[0]['slug'] ?? 'project') ?>">View Case Study <span>&rarr;</span></a>
                                </div>
                                <?php if (count($heroProjects) > 1): ?>
                                <span class="hero-slide-counter">01 / <?= count($heroProjects) ?></span>
                                <div class="hero-carousel-dots">
                                    <?php foreach ($heroProjects as $hi => $hp): ?>
                                        <button class="hero-slide-dot<?= $hi === 0 ? ' active' : '' ?>" data-slide="<?= $hi ?>" aria-label="Slide <?= $hi + 1 ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                                <button class="hero-arrow hero-arrow-prev" aria-label="Previous slide">&lsaquo;</button>
                                <button class="hero-arrow hero-arrow-next" aria-label="Next slide">&rsaquo;</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
        @media (min-width:1024px) { #hero-visual { display:flex !important } }
        .hero-carousel { position:relative; width:100% }
        .hero-carousel-viewport { position:relative; height:420px; border-radius:20px; overflow:hidden; box-shadow:0 40px 100px rgba(0,0,0,0.35); background:#0d0d1a }
        .hero-slide { position:absolute; inset:0; background-size:cover; background-position:center; opacity:0; transform:scale(1.06); transition:opacity .7s ease, transform .7s ease; display:block }
        .hero-slide.active { opacity:1; transform:scale(1); z-index:1 }
        .hero-slide::after { content:''; position:absolute; inset:0; background:linear-gradient(180deg, rgba(13,13,26,0.05) 45%, rgba(13,13,26,0.85) 100%) }
        .hero-slide-overlay { position:absolute; left:0; right:0; bottom:0; padding:32px; z-index:2; display:flex; flex-direction:column; align-items:flex-start; gap:8px }
        .hero-slide-tag { font-size:11px; letter-spacing:2px; text-transform:uppercase; font-weight:600; color:#E8632A; background:rgba(232,99,42,0.12); border:1px solid rgba(232,99,42,0.25); padding:4px 12px; border-radius:20px }
        .hero-slide-title { color:#fff; font-size:26px; font-weight:700; line-height:1.2; margin:0 }
        .hero-slide-link { display:inline-flex; align-items:center; gap:8px; color:#fff; font-size:14px; font-weight:600; text-decoration:none; transition:color .2s }
        .hero-slide-link span { transition:transform .2s }
        .hero-slide-link:hover { color:#E8632A }
        .hero-slide-link:hover span { transform:translateX(4px) }
        .hero-slide-counter { position:absolute; top:20px; right:20px; z-index:3; color:#fff; font-size:13px; font-weight:600; font-family:monospace; background:rgba(13,13,26,0.5); backdrop-filter:blur(6px); padding:6px 12px; border-radius:20px; letter-spacing:1px }
        .hero-carousel-dots { position:absolute; bottom:20px; right:20px; z-index:3; display:flex; gap:8px }
        .hero-slide-dot { width:8px; height:8px; border-radius:50%; border:none; background:rgba(255,255,255,0.4); cursor:pointer; padding:0; transition:background .2s, transform .2s }
        .hero-slide-dot.active { background:#E8632A; transform:scale(1.25) }
        .hero-arrow { position:absolute; top:50%; transform:translateY(-50%); z-index:3; width:44px; height:44px; border-radius:50%; border:1px solid rgba(255,255,255,0.15); background:rgba(13,13,26,0.45); backdrop-filter:blur(6px); color:#fff; font-size:26px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s, border-color .2s }
        .hero-arrow:hover { background:#E8632A; border-color:#E8632A }
        .hero-arrow-prev { left:16px }
        .hero-arrow-next { right:16px }
        [data-theme="dark"] .hero-carousel-viewport { background:#0d0d1a }
        </style>
        <script>
        (function(){
            var slides = document.querySelectorAll('#hero-visual .hero-slide');
            if (slides.length < 1) return;
            var dots = document.querySelectorAll('#hero-visual .hero-slide-dot');
            var counter = document.querySelector('#hero-visual .hero-slide-counter');
            var title = document.querySelector('#hero-visual .hero-slide-title');
            var link = document.querySelector('#hero-visual .hero-slide-link');
            var current = 0, timer = null;
            function goTo(i) {
                slides[current].classList.remove('active');
                if (dots[current]) dots[current].classList.remove('active');
                current = (i + slides.length) % slides.length;
                slides[current].classList.add('active');
                if (dots[current]) dots[current].classList.add('active');
                if (title) title.textContent = slides[current].getAttribute('data-title');
                if (link) link.setAttribute('href', slides[current].getAttribute('data-url'));
                if (counter) counter.textContent = String(current + 1).padStart(2, '0') + ' / ' + String(slides.length).padStart(2, '0');
            }
            function next() { goTo(current + 1); }
            function prev() { goTo(current - 1); }
            function startTimer() { stopTimer(); timer = setInterval(next, 5000); }
            function stopTimer() { if (timer) { clearInterval(timer); timer = null; } }
            var viewport = document.querySelector('#hero-visual .hero-carousel-viewport');
            if (viewport) {
                viewport.addEventListener('mouseenter', stopTimer);
                viewport.addEventListener('mouseleave', startTimer);
            }
            var prevBtn = document.querySelector('#hero-visual .hero-arrow-prev');
            var nextBtn = document.querySelector('#hero-visual .hero-arrow-next');
            if (prevBtn) prevBtn.addEventListener('click', function(){ stopTimer(); prev(); startTimer(); });
            if (nextBtn) nextBtn.addEventListener('click', function(){ stopTimer(); next(); startTimer(); });
            dots.forEach(function(d, i) {
                d.addEventListener('click', function(){ stopTimer(); goTo(i); startTimer(); });
            });
            startTimer();
        })();
        </script>
    </section>

    <!-- ============================================================
    TRUSTED BY
    ============================================================ -->
    <section class="section-sm" style="background:var(--bg-light)">
        <div class="container">
            <div class="reveal" style="text-align:center;margin-bottom:48px">
                <p style="font-size:12px;color:var(--text-muted);letter-spacing:2.5px;text-transform:uppercase;font-weight:600">Trusted by teams at</p>
            </div>
            <div class="reveal" style="display:flex;flex-wrap:wrap;justify-content:center;align-items:center;gap:24px 64px">
                <?php $clients = array_filter(array_map('trim', explode(',', getSetting('trusted_clients', 'TechVolve,GreenLeaf,Pulse,FinFlow,Bloom,CloudBase')))); foreach ($clients as $c): ?>
                    <span style="font-size:20px;font-weight:700;color:var(--text-muted);letter-spacing:0.5px;opacity:0.4;transition:opacity var(--transition);white-space:nowrap" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='0.4'"><?= htmlspecialchars($c) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============================================================
    SERVICES PREVIEW
    ============================================================ -->
    <section class="section" id="services">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-tag">
                    <i data-lucide="sparkles" size="16"></i>
                    Our Capabilities
                </div>
                <h2 class="section-title">Full-Spectrum <span class="gradient-text">Digital Services</span></h2>
                <p class="section-desc">From brand identity to scalable platforms — every capability designed to move your business forward.</p>
            </div>

            <div class="grid grid-3 stagger-children">
                <?php
                $services = [
                    ['icon' => 'palette', 'title' => 'Web Design', 'desc' => 'Beautiful, responsive websites that captivate your audience and drive conversions.', 'features' => ['Custom UI/UX', 'Responsive Design', 'Wireframing']],
                    ['icon' => 'code', 'title' => 'Web Development', 'desc' => 'Powerful web applications built with cutting-edge technologies and best practices.', 'features' => ['Full-Stack Dev', 'API Development', 'Cloud Solutions']],
                    ['icon' => 'award', 'title' => 'Branding', 'desc' => 'Strategic brand identity that tells your story and connects with your audience.', 'features' => ['Brand Strategy', 'Logo Design', 'Visual Identity']],
                    ['icon' => 'layers', 'title' => 'UI/UX Design', 'desc' => 'Intuitive user experiences that delight users and achieve business goals.', 'features' => ['User Research', 'Prototyping', 'Usability Testing']],
                    ['icon' => 'trending-up', 'title' => 'Digital Marketing', 'desc' => 'Data-driven marketing strategies that grow your brand and increase revenue.', 'features' => ['SEO Strategy', 'Content Marketing', 'Analytics']],
                    ['icon' => 'smartphone', 'title' => 'Mobile Development', 'desc' => 'Native and cross-platform mobile apps that deliver exceptional experiences.', 'features' => ['iOS & Android', 'Cross-Platform', 'App Store SEO']]
                ];
                foreach ($services as $s): ?>
                    <div class="card hover-lift" style="padding:32px">
                        <div style="width:56px;height:56px;border-radius:var(--radius-md);background:var(--primary-alpha);display:flex;align-items:center;justify-content:center;color:var(--primary);margin-bottom:20px">
                            <i data-lucide="<?= $s['icon'] ?>" size="28"></i>
                        </div>
                        <h4 style="margin-bottom:12px"><?= $s['title'] ?></h4>
                        <p style="font-size:14px;margin-bottom:16px"><?= $s['desc'] ?></p>
                        <ul style="display:flex;flex-direction:column;gap:8px">
                            <?php foreach ($s['features'] as $f): ?>
                                <li style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-secondary)">
                                    <i data-lucide="check" size="14" style="color:var(--primary);min-width:14px"></i>
                                    <?= $f ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= BASE_URL ?>services" class="btn btn-ghost btn-sm" style="margin-top:20px">
                            Learn More <i data-lucide="arrow-right" size="14"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============================================================
    WHY CHOOSE US
    ============================================================ -->
    <section class="section" style="background:var(--bg-light)" id="why-us">
        <div class="container">
            <div class="grid grid-2" style="align-items:center;gap:60px">
                <div class="reveal">
                    <div class="section-tag">
                        <i data-lucide="star" size="16"></i>
                        Why Choose Us
                    </div>
                    <h2 style="font-size:clamp(1.75rem,3vw,2.75rem);margin-bottom:20px">
                        We Don't Just Build Websites — We Build <span class="gradient-text">Business Growth</span>
                    </h2>
                    <p style="color:var(--text-secondary);margin-bottom:32px;font-size:17px">
                        Every engagement begins with understanding your business deeply. Here's why clients trust us with their most ambitious projects.
                    </p>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                        <?php $features = [
                            ['icon' => 'zap', 'title' => 'Fast Delivery', 'desc' => 'Agile sprints with zero compromise on quality'],
                            ['icon' => 'shield', 'title' => 'Quality Assured', 'desc' => 'Every deliverable tested, reviewed, and refined'],
                            ['icon' => 'users', 'title' => 'Dedicated Team', 'desc' => 'A focused squad aligned with your goals'],
                            ['icon' => 'headphones', 'title' => '24/7 Support', 'desc' => 'We are there when you need us, always']
                        ];
                        foreach ($features as $f): ?>
                            <div style="display:flex;gap:16px">
                                <div style="width:44px;height:44px;min-width:44px;border-radius:var(--radius-md);background:var(--primary-alpha);display:flex;align-items:center;justify-content:center;color:var(--primary)">
                                    <i data-lucide="<?= $f['icon'] ?>" size="22"></i>
                                </div>
                                <div>
                                    <h6 style="font-weight:700;margin-bottom:4px"><?= $f['title'] ?></h6>
                                    <p style="font-size:13px;color:var(--text-muted)"><?= $f['desc'] ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="reveal">
                    <div style="background:var(--bg-white);border-radius:var(--radius-xl);padding:40px;box-shadow:var(--shadow-lg);border:1px solid var(--border)">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
                            <div style="text-align:center;padding:24px;background:var(--bg-light);border-radius:var(--radius-md)">
                                <div style="font-size:36px;font-weight:800;color:var(--primary)"><?= max(round($avgRatingVal * 20), 1) ?>%</div>
                                <div style="font-size:14px;color:var(--text-muted)">Client Satisfaction</div>
                            </div>
                            <div style="text-align:center;padding:24px;background:var(--bg-light);border-radius:var(--radius-md)">
                                <div style="font-size:36px;font-weight:800;color:var(--primary)"><?= $avgRatingVal > 0 ? number_format($avgRatingVal, 1) : '0.0' ?></div>
                                <div style="font-size:14px;color:var(--text-muted)">Average Rating</div>
                            </div>
                            <div style="text-align:center;padding:24px;background:var(--bg-light);border-radius:var(--radius-md)">
                                <div style="font-size:36px;font-weight:800;color:var(--primary)"><?= $totalProjectsCount ?>+</div>
                                <div style="font-size:14px;color:var(--text-muted)">Projects Done</div>
                            </div>
                            <div style="text-align:center;padding:24px;background:var(--bg-light);border-radius:var(--radius-md)">
                                <div style="font-size:36px;font-weight:800;color:var(--primary)">12+</div>
                                <div style="font-size:14px;color:var(--text-muted)">Years Experience</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    PROCESS TIMELINE
    ============================================================ -->
    <section class="section" id="process">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-tag">
                    <i data-lucide="route" size="16"></i>
                    Our Methodology
                </div>
                <h2 class="section-title">How We Bring <span class="gradient-text">Ideas to Life</span></h2>
                <p class="section-desc">A proven, iterative framework that takes your project from concept to launch with precision.</p>
            </div>

            <?php $steps = [
                ['num' => '01', 'title' => 'Discovery', 'desc' => 'We immerse ourselves in your business landscape — understanding goals, audience, and competitive edge to define a clear roadmap.', 'icon' => 'search'],
                ['num' => '02', 'title' => 'Strategy', 'desc' => 'A tailored blueprint emerges — aligning creative vision with measurable outcomes, budget, and timeline.', 'icon' => 'map'],
                ['num' => '03', 'title' => 'Design', 'desc' => 'Every pixel is intentional. Our designers craft interfaces that are as intuitive as they are beautiful.', 'icon' => 'palette'],
                ['num' => '04', 'title' => 'Development', 'desc' => 'Clean code, modern stacks, and rigorous architecture — built for speed, security, and scale.', 'icon' => 'code'],
                ['num' => '05', 'title' => 'Testing', 'desc' => 'Every edge case, every interaction. We QA obsessively so your users experience zero friction.', 'icon' => 'shield'],
                ['num' => '06', 'title' => 'Launch & Support', 'desc' => 'A seamless go-live backed by ongoing monitoring, maintenance, and a team that has your back 24/7.', 'icon' => 'rocket']
            ];
            foreach ($steps as $i => $s): ?>
                <div class="reveal" style="display:grid;grid-template-columns:80px 1fr;gap:24px;margin-bottom:32px;padding:24px;background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);transition:all var(--transition)">
                    <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#ff6b35);display:flex;align-items:center;justify-content:center;color:white;font-size:24px;font-weight:900">
                        <?= $s['num'] ?>
                    </div>
                    <div>
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
                            <div style="width:36px;height:36px;border-radius:var(--radius-sm);background:var(--primary-alpha);display:flex;align-items:center;justify-content:center;color:var(--primary)">
                                <i data-lucide="<?= $s['icon'] ?>" size="18"></i>
                            </div>
                            <h4><?= $s['title'] ?></h4>
                        </div>
                        <p style="color:var(--text-secondary);font-size:15px"><?= $s['desc'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============================================================
    FEATURED PROJECTS
    ============================================================ -->
    <section class="section" style="background:var(--bg-light)" id="projects">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-tag">
                    <i data-lucide="briefcase" size="16"></i>
                    Portfolio
                </div>
                <h2 class="section-title">Selected <span class="gradient-text">Work</span></h2>
                <p class="section-desc">A glimpse into the products and platforms we have designed and engineered for ambitious brands.</p>
            </div>

            <div class="grid grid-3 stagger-children">
                <?php
                $homeProjects = $db->query("SELECT p.*, c.name as category_name FROM portfolio_projects p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'published' ORDER BY p.featured DESC, p.created_at DESC LIMIT 6")->fetchAll();
                $homeColorMap = ['#2196F3','#4CAF50','#E8632A','#9C27B0','#FF9800','#00BCD4'];
                if (empty($homeProjects)) {
                    $homeProjects = [
                        ['title' => 'TechVolve Platform', 'category_name' => 'Web Development', 'featured_image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&h=400&fit=crop', 'slug' => 'techvolve-platform'],
                        ['title' => 'GreenLeaf Brand', 'category_name' => 'Branding', 'featured_image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=600&h=400&fit=crop', 'slug' => 'greenleaf-brand-identity'],
                        ['title' => 'Pulse Fitness App', 'category_name' => 'Mobile Dev', 'featured_image' => 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=600&h=400&fit=crop', 'slug' => 'pulse-fitness-app'],
                    ];
                }
                foreach ($homeProjects as $idx => $hp):
                    $hcolor = $homeColorMap[$idx % count($homeColorMap)];
                ?>
                    <div class="card hover-lift">
                        <div style="position:relative;overflow:hidden;height:220px">
                            <img src="<?= $hp['featured_image'] ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&h=400&fit=crop' ?>" alt="<?= htmlspecialchars($hp['title']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.5s ease">
                            <div style="position:absolute;top:12px;left:12px">
                                <span class="badge" style="background:<?= $hcolor ?>;color:white"><?= htmlspecialchars($hp['category_name'] ?? 'General') ?></span>
                            </div>
                        </div>
                        <div style="padding:20px 24px">
                            <h5 style="margin-bottom:4px"><?= htmlspecialchars($hp['title']) ?></h5>
                            <a href="<?= BASE_URL ?>portfolio/<?= htmlspecialchars($hp['slug'] ?? 'project') ?>" class="btn btn-ghost btn-sm" style="padding-left:0">
                                View Case Study <i data-lucide="arrow-right" size="14"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-8 reveal">
                <a href="<?= BASE_URL ?>portfolio" class="btn btn-primary btn-lg">
                    View All Projects
                    <i data-lucide="arrow-right" size="20"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================
    TESTIMONIALS / RATINGS
    ============================================================ -->
    <style>
    .rating-modal{position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);opacity:0;visibility:hidden;transition:all .3s ease}
    .rating-modal.active{opacity:1;visibility:visible}
    .rating-modal-content{background:var(--bg-white);border-radius:var(--radius-lg);padding:32px;max-width:460px;width:90%;box-shadow:var(--shadow-xl);transform:scale(.9);transition:transform .3s ease}
    .rating-modal.active .rating-modal-content{transform:scale(1)}
    .rating-modal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
    .rating-modal-header h3{font-size:18px;font-weight:600}
    .rating-modal-close{background:none;border:none;font-size:24px;cursor:pointer;color:var(--text-muted);padding:4px;line-height:1;border-radius:6px;transition:background .2s}
    .rating-modal-close:hover{background:var(--bg-light)}
    .rating-item-name{font-size:14px;color:var(--text-muted);margin-bottom:20px}
    .rating-star-row{display:flex;gap:6px;justify-content:center;margin-bottom:20px}
    .rating-star-row .star-btn{background:none;border:none;cursor:pointer;padding:4px;color:#c0c4cc;transition:transform .15s ease,color .15s ease;line-height:0}
    .rating-star-row .star-btn:hover{transform:scale(1.2)}
    .rating-star-row .star-btn.active,.rating-star-row .star-btn.hovered{color:#FFC107}
    .rating-review-input{width:100%;min-height:80px;padding:12px;border:1px solid var(--border);border-radius:var(--radius-sm);font-family:var(--font-family);font-size:14px;resize:vertical;margin-bottom:16px;transition:border-color .2s}
    .rating-review-input:focus{outline:none;border-color:var(--primary)}
    .rating-submit-btn{width:100%;padding:12px;background:var(--primary);color:white;border:none;border-radius:var(--radius-sm);font-size:15px;font-weight:600;cursor:pointer;transition:background .2s,transform .15s}
    .rating-submit-btn:hover{background:var(--primary-dark);transform:translateY(-1px)}
    .rating-submit-btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
    .rating-feedback{text-align:center;font-size:14px;margin-top:12px;min-height:20px}
    .rating-feedback.success{color:#4CAF50}
    .rating-feedback.error{color:#F44336}
    .rate-btn{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:var(--bg-gray);border:1px solid var(--border);border-radius:20px;font-size:13px;color:var(--text-secondary);cursor:pointer;transition:all .2s;font-family:var(--font-family)}
    .rate-btn:hover{background:var(--primary-alpha);border-color:var(--primary);color:var(--primary)}
    .rate-btn.rated{background:#FFF8E1;border-color:#FFC107;color:#F57F17}
    [data-theme="dark"] .rating-modal-content{background:#1e1e32;border:1px solid #2a2a44}
    [data-theme="dark"] .rating-review-input{background:#16162a;border-color:#2a2a44;color:var(--text-white)}
    [data-theme="dark"] .rate-btn{background:#2a2a44;border-color:#3a3a54;color:var(--text-light)}
    [data-theme="dark"] .rate-btn:hover{background:rgba(232,99,42,0.15);border-color:var(--primary)}
    </style>
    <section class="section" id="testimonials">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-tag">
                    <i data-lucide="message-square" size="16"></i>
                    Client Voices
                </div>
                <h2 class="section-title">Trusted by <span class="gradient-text">Ambitious Brands</span></h2>
                <p class="section-desc">Real feedback from the teams we have partnered with to build and scale their digital presence.</p>
            </div>

            <?php
            $db = Database::getInstance()->getConnection();
            $reviews = $db->prepare("SELECT r.*, COALESCE(u.name, r.guest_name, 'Guest') as user_name FROM ratings r LEFT JOIN users u ON r.user_id = u.id WHERE r.item_type = 'business' AND r.is_approved = 1 ORDER BY r.created_at DESC LIMIT 10");
            $reviews->execute();
            $reviews = $reviews->fetchAll();
            $s = $db->prepare("SELECT COUNT(*) as total, ROUND(AVG(rating),1) as avg FROM ratings WHERE item_type = 'business' AND is_approved = 1");
            $s->execute();
            $s = $s->fetch();
            $avgRating = $s['avg'] ?? 0;
            $totalRatings = (int)($s['total'] ?? 0);
            ?>

            <div class="reveal" style="text-align:center;margin-bottom:32px">
                <div style="display:inline-flex;align-items:center;gap:16px;padding:16px 32px;background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);box-shadow:var(--shadow-sm);flex-wrap:wrap;justify-content:center">
                    <div style="text-align:center">
                        <div style="font-size:32px;font-weight:800;color:var(--primary)"><?= $avgRating ?: '&mdash;' ?></div>
                        <div style="font-size:12px;color:var(--text-muted)">Average</div>
                    </div>
                    <div style="width:1px;height:40px;background:var(--border)"></div>
                    <div style="text-align:center">
                        <div style="font-size:32px;font-weight:800;color:var(--primary)"><?= $totalRatings ?></div>
                        <div style="font-size:12px;color:var(--text-muted)">Ratings</div>
                    </div>
                    <div style="width:1px;height:40px;background:var(--border)"></div>
                    <div style="text-align:center">
                        <button class="rate-btn" data-rate-item data-item-id="1" data-item-type="business" data-item-name="ASAAS STUDIO">
                            <i data-lucide="star" size="14"></i>
                            Rate Our Work
                        </button>
                    </div>
                </div>
            </div>

            <div class="testimonial-slider reveal" style="position:relative;overflow:hidden">
                <?php if (empty($reviews)): ?>
                    <div style="text-align:center;padding:60px 20px;background:var(--bg-white);border-radius:var(--radius-xl);border:1px solid var(--border)">
                        <div style="font-size:48px;margin-bottom:16px;opacity:0.3"><i data-lucide="message-square" size="48" style="color:var(--text-muted)"></i></div>
                        <h4 style="margin-bottom:8px">No Reviews Yet</h4>
                        <p style="color:var(--text-muted);margin-bottom:20px">Share your experience — your feedback helps us keep raising the bar.</p>
                        <button class="btn btn-primary" data-rate-item data-item-id="1" data-item-type="business" data-item-name="ASAAS STUDIO">
                            <i data-lucide="star" size="16"></i> Write a Review
                        </button>
                    </div>
                <?php else: ?>
                <div class="slider-track" style="display:flex;transition:transform 0.5s ease">
                    <?php foreach ($reviews as $t): ?>
                        <div style="min-width:100%;padding:0 40px">
                            <div style="max-width:700px;margin:0 auto;text-align:center;padding:40px;background:var(--bg-white);border-radius:var(--radius-xl);border:1px solid var(--border);box-shadow:var(--shadow-md)">
                                <div style="width:64px;height:64px;border-radius:50%;background:var(--primary);margin:0 auto 20px;display:flex;align-items:center;justify-content:center;color:white;font-size:28px;font-weight:700"><?= strtoupper($t['user_name'][0]) ?></div>
                                <div style="display:flex;justify-content:center;gap:4px;margin-bottom:16px;color:#FFC107">
                                    <?php for ($i = 0; $i < $t['rating']; $i++): ?>
                                        <i data-lucide="star" width="20" height="20" fill="currentColor"></i>
                                    <?php endfor; ?>
                                </div>
                                <?php if ($t['review'] && trim($t['review'])): ?>
                                    <p style="font-size:17px;font-style:italic;color:var(--text-secondary);margin-bottom:20px;line-height:1.8">&ldquo;<?= htmlspecialchars($t['review']) ?>&rdquo;</p>
                                <?php else: ?>
                                    <p style="font-size:14px;color:var(--text-muted);margin-bottom:20px">Rated <?= $t['rating'] ?>/5</p>
                                <?php endif; ?>
                                <h6 style="font-weight:700"><?= htmlspecialchars($t['user_name']) ?></h6>
                                <p style="font-size:13px;color:var(--text-muted)"><?= date('M Y', strtotime($t['created_at'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex;justify-content:center;gap:12px;margin-top:24px">
                    <button class="slider-prev btn btn-secondary btn-sm"><i data-lucide="chevron-left" size="18"></i></button>
                    <div class="slider-dots" style="display:flex;align-items:center;gap:8px"></div>
                    <button class="slider-next btn btn-secondary btn-sm"><i data-lucide="chevron-right" size="18"></i></button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ============================================================
    FAQ
    ============================================================ -->
    <section class="section" style="background:var(--bg-light)" id="faq">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-tag">
                    <i data-lucide="help-circle" size="16"></i>
                    Common Questions
                </div>
                <h2 class="section-title">Everything You Need <span class="gradient-text">to Know</span></h2>
                <p class="section-desc">Straight answers about how we work, what we deliver, and what to expect when we partner together.</p>
            </div>

            <div style="max-width:800px;margin:0 auto">
                <?php $faqs = [
                    ['q' => 'What is the typical timeline for a web development project?', 'a' => 'Timelines depend on scope. A standard website typically ships in 4-8 weeks; larger platforms may take 3-6 months. We provide a detailed roadmap during the proposal phase — no surprises.'],
                    ['q' => 'How much does a website cost?', 'a' => 'Projects start at $5,000 for polished brochure sites and scale to $50,000+ for complex web applications. Every estimate is tailored to your specific needs — you only pay for what moves your business forward.'],
                    ['q' => 'Do you offer ongoing maintenance and support?', 'a' => 'Absolutely. We offer flexible retainer plans to keep your digital products secure, up-to-date, and performing optimally. Our team is available around the clock for critical issues.'],
                    ['q' => 'What technologies do you use?', 'a' => 'We select the optimal stack for each project — whether that is React, Node.js, PHP, Python, or cloud-native architectures. The technology serves the solution, not the other way around.'],
                    ['q' => 'How do we get started?', 'a' => 'Reach out through our contact form or schedule a discovery call. We will discuss your goals, challenges, and timeline — and deliver a tailored proposal within 48 hours. No pressure, just clarity.']
                ];
                foreach ($faqs as $i => $f): ?>
                    <div style="margin-bottom:12px;background:var(--bg-white);border-radius:var(--radius-md);border:1px solid var(--border)">
                        <div onclick="toggleHomeFaq(<?= $i ?>)" style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;cursor:pointer;transition:all .3s ease">
                            <h6 style="font-weight:600;font-size:15px;margin:0;padding-right:24px"><?= $f['q'] ?></h6>
                            <svg id="home-faq-icon-<?= $i ?>" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;transition:transform .3s ease"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </div>
                        <div id="home-faq-content-<?= $i ?>" style="max-height:0;overflow:hidden;transition:max-height .4s ease,opacity .3s ease;opacity:0">
                            <div style="padding:0 24px 20px">
                                <p style="font-size:14px;color:var(--text-secondary);line-height:1.7;margin:0"><?= $f['a'] ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <script>
                var homeFaqOpen = 0;
                function toggleHomeFaq(i) {
                    var contents = document.querySelectorAll('[id^="home-faq-content-"]');
                    var icons = document.querySelectorAll('[id^="home-faq-icon-"]');
                    contents.forEach(function(c, idx) {
                        if (idx === i) {
                            var isOpen = c.style.maxHeight && c.style.maxHeight !== '0px';
                            if (isOpen) {
                                c.style.maxHeight = '0px';
                                c.style.opacity = '0';
                                icons[idx].style.transform = 'rotate(0deg)';
                                homeFaqOpen = -1;
                            } else {
                                c.style.maxHeight = c.scrollHeight + 40 + 'px';
                                c.style.opacity = '1';
                                icons[idx].style.transform = 'rotate(45deg)';
                                homeFaqOpen = i;
                            }
                        } else {
                            c.style.maxHeight = '0px';
                            c.style.opacity = '0';
                            icons[idx].style.transform = 'rotate(0deg)';
                        }
                    });
                }
                document.addEventListener('DOMContentLoaded', function() { toggleHomeFaq(0); });
                </script>
            </div>
        </div>
    </section>

    <!-- ============================================================
    FINAL CTA
    ============================================================ -->
    <section class="section" style="background:linear-gradient(135deg, var(--bg-dark), #1a1a2e);position:relative;overflow:hidden">
        <div style="position:absolute;inset:0;opacity:0.05">
            <div style="position:absolute;width:400px;height:400px;border-radius:50%;background:var(--primary);top:-100px;right:-100px"></div>
            <div style="position:absolute;width:300px;height:300px;border-radius:50%;background:var(--primary);bottom:-50px;left:-50px"></div>
        </div>
        <div class="container" style="position:relative;z-index:1">
            <div class="text-center reveal" style="max-width:700px;margin:0 auto">
                <div class="section-tag" style="background:rgba(232,99,42,0.15);color:#f07840">Start a Conversation</div>
                <h2 style="color:white;margin-bottom:16px">Ready to Build Your <span class="gradient-text">Next Big Idea</span>?</h2>
                <p style="color:var(--text-light);font-size:18px;margin-bottom:32px">
                    Let's talk about your vision. No obligation, just a focused conversation about what's possible.
                </p>
                <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
                    <a href="<?= BASE_URL ?>contact#booking" class="btn btn-primary btn-lg">
                        Schedule a Call
                        <i data-lucide="calendar" size="20"></i>
                    </a>
                    <a href="<?= BASE_URL ?>contact" class="btn btn-light btn-lg">
                        Send a Message
                        <i data-lucide="send" size="20"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<div class="rating-modal" id="ratingModal">
    <div class="rating-modal-content">
        <div class="rating-modal-header">
            <h3>Rate ASAAS STUDIO</h3>
            <button class="rating-modal-close">&times;</button>
        </div>
        <p class="rating-item-name">ASAAS STUDIO</p>
        <input type="hidden" id="rateItemId" value="1">
        <input type="hidden" id="rateItemType" value="business">
        <input type="hidden" id="ratingValue" value="0">
        <div id="guestFields" style="display:none;margin-bottom:16px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                <div class="form-group" style="margin:0"><label class="form-label">Your Name *</label><input type="text" id="guestName" class="form-input" placeholder="John Doe"></div>
                <div class="form-group" style="margin:0"><label class="form-label">Email (optional)</label><input type="email" id="guestEmail" class="form-input" placeholder="john@example.com"></div>
            </div>
        </div>
        <div class="rating-star-row">
            <button class="star-btn" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg></button>
            <button class="star-btn" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg></button>
            <button class="star-btn" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg></button>
            <button class="star-btn" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg></button>
            <button class="star-btn" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg></button>
        </div>
        <textarea class="rating-review-input" placeholder="Tell us about your experience (optional)..."></textarea>
        <button class="rating-submit-btn" type="button">Submit Rating</button>
        <div class="rating-feedback"></div>
    </div>
</div>
<script>
(function(){
var isLoggedIn = <?= json_encode(isLoggedIn()) ?>;
var m=document.querySelector('.rating-modal');if(!m)return;
var row=m.querySelector('.rating-star-row'),stars=row.querySelectorAll('.star-btn'),hInput=document.getElementById('ratingValue');
stars.forEach(function(s,i){s.addEventListener('mouseenter',function(){stars.forEach(function(x,j){x.classList.toggle('hovered',j<=i)})});
s.addEventListener('mouseleave',function(){stars.forEach(function(x){x.classList.remove('hovered')})});
s.addEventListener('click',function(){var v=i+1;stars.forEach(function(x,j){x.classList.toggle('active',j<v)});hInput.value=v})});
document.querySelector('.rating-submit-btn').addEventListener('click',function(){var btn=this,fb=m.querySelector('.rating-feedback'),iid=document.getElementById('rateItemId').value,itype=document.getElementById('rateItemType').value,rtg=parseInt(hInput.value),rev=m.querySelector('.rating-review-input').value.trim();if(!rtg||rtg<1){fb.textContent='Please select a rating';fb.className='rating-feedback error';return}
var fd=new FormData();fd.append('item_id',iid);fd.append('item_type',itype);fd.append('rating',rtg);fd.append('review',rev);
if(!isLoggedIn){var gn=document.getElementById('guestName').value.trim(),ge=document.getElementById('guestEmail').value.trim();if(!gn){fb.textContent='Please enter your name';fb.className='rating-feedback error';return}fd.append('guest_name',gn);fd.append('guest_email',ge)}
btn.disabled=true;btn.textContent='Submitting...';
fetch(BASE_URL+'api/index.php?action=submit_rating',{method:'POST',body:fd}).then(function(r){return r.json()}).then(function(d){if(d.success){fb.textContent='Thank you! Your rating will appear after review.';fb.className='rating-feedback success';btn.textContent='Done';setTimeout(function(){m.classList.remove('active');document.body.style.overflow=''},2000)}else{fb.textContent=d.error||'Failed to submit rating';fb.className='rating-feedback error';btn.disabled=false;btn.textContent='Try Again'}}).catch(function(){fb.textContent='Network error. Please try again.';fb.className='rating-feedback error';btn.disabled=false;btn.textContent='Submit Rating'})});
document.addEventListener('click',function(e){var b=e.target.closest('[data-rate-item]');if(b){var iid=b.getAttribute('data-item-id'),itype=b.getAttribute('data-item-type'),iname=b.getAttribute('data-item-name');m.querySelector('.rating-item-name').textContent=iname;document.getElementById('rateItemId').value=iid;document.getElementById('rateItemType').value=itype;hInput.value=0;m.querySelector('.rating-review-input').value='';m.querySelector('.rating-feedback').textContent='';m.querySelector('.rating-submit-btn').disabled=false;m.querySelector('.rating-submit-btn').textContent='Submit Rating';stars.forEach(function(s){s.classList.remove('active','hovered')});var gf=document.getElementById('guestFields');if(gf)gf.style.display=isLoggedIn?'none':'block';m.classList.add('active');document.body.style.overflow='hidden'}
var cb=e.target.closest('.rating-modal-close,[data-rating-close]');if(cb){m.classList.remove('active');document.body.style.overflow=''}
if(e.target===m){m.classList.remove('active');document.body.style.overflow=''}})
})();
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
