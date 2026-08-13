<?php
$seoTitle = 'About | ASAAS Studio Somalia';
$seoDesc = 'ASAAS Studio is a digital studio based in Mogadishu, Somalia, designing and building websites, custom web systems, and UI/UX design for businesses and organizations.';
$seoKeywords = 'about ASAAS studio, digital studio Somalia, web design company Somalia, web development Mogadishu, ASAAS studio Mogadishu';
require __DIR__ . '/../includes/header.php'; ?>

<main class="page-transition">
    <section style="padding-top:calc(var(--header-height) + 60px);background:var(--bg-light);overflow:hidden">
        <div class="container">
            <div class="grid grid-2" style="align-items:center;gap:60px;padding:60px 0">
                <div class="fade-in-up">
                    <div class="section-tag"><i data-lucide="info" size="16"></i>About</div>
                    <h1 style="font-size:clamp(2rem,4vw,3.5rem);margin-bottom:16px">We Are <span class="gradient-text">ASAAS</span></h1>
                    <p style="color:var(--text-secondary);font-size:18px;line-height:1.8;margin-bottom:24px">ASAAS is a digital studio based in Mogadishu, Somalia, designing and building websites, web systems, and digital experiences for businesses and organizations.</p>
                    <p style="color:var(--text-secondary);line-height:1.8">We focus on the areas where we can create the most value: websites, web systems, UI/UX, and digital support. We take on projects we can genuinely do well.</p>
                    <div style="display:flex;gap:24px;margin-top:32px">
                        <div>
                            <div style="font-size:36px;font-weight:800;color:var(--primary)">Mogadishu</div>
                            <div style="font-size:14px;color:var(--text-muted)">Based in</div>
                        </div>
                        <div>
                            <div style="font-size:36px;font-weight:800;color:var(--primary)">End-to-End</div>
                            <div style="font-size:14px;color:var(--text-muted)">Delivery</div>
                        </div>
                        <div>
                            <div style="font-size:36px;font-weight:800;color:var(--primary)">$99</div>
                            <div style="font-size:14px;color:var(--text-muted)">Starting price</div>
                        </div>
                    </div>
                </div>
                <div class="fade-in-right">
                    <div style="width:100%;aspect-ratio:1/1;border-radius:var(--radius-xl);box-shadow:var(--shadow-lg);background:linear-gradient(135deg,#14141f,#1a1a2e);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:20px;padding:40px;text-align:center">
                        <img src="<?= BASE_URL ?>assets/images/logo1_whitebackground.png" alt="ASAAS" style="height:72px;width:auto;border-radius:12px">
                        <p style="color:#aab;font-size:15px;max-width:260px;margin:0">Designing and building digital products for businesses and organizations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="padding-top:80px">
        <div class="container" style="max-width:820px">
            <div class="reveal" style="background:var(--bg-white);border-radius:var(--radius-xl);border:1px solid var(--border);padding:40px;box-shadow:var(--shadow-sm);margin-bottom:24px">
                <div class="section-tag"><i data-lucide="compass" size="16"></i>Why ASAAS Exists</div>
                <h2 style="font-size:clamp(1.5rem,2.5vw,2.25rem);margin:16px 0 12px">Good software should not be hard to get</h2>
                <p style="color:var(--text-secondary);font-size:16px;line-height:1.8;margin:0">Many businesses and organizations need digital products that are practical, well-built, and genuinely useful. Too often they end up with over-engineered solutions or generic templates that do not fit. We design and build what is actually needed, and we keep it focused on serving a real purpose.</p>
            </div>
            <div class="reveal" style="background:var(--bg-white);border-radius:var(--radius-xl);border:1px solid var(--border);padding:40px;box-shadow:var(--shadow-sm)">
                <div class="section-tag"><i data-lucide="cpu" size="16"></i>Technology Should Have a Purpose</div>
                <h2 style="font-size:clamp(1.5rem,2.5vw,2.25rem);margin:16px 0 12px">Built for the job, not for the trend</h2>
                <p style="color:var(--text-secondary);font-size:16px;line-height:1.8;margin:0">A tool is only good if it solves a problem. We use the simplest technology that does the job, we avoid unnecessary complexity, and we tell you honestly when something is not worth building.</p>
            </div>
        </div>
    </section>

    <section class="section" style="text-align:center;padding-top:0">
        <div class="container">
            <div class="section-header reveal">
                <h2 class="section-title">What We <span class="gradient-text">Stand For</span></h2>
                <p class="section-desc">The principles that guide every project we take on.</p>
            </div>
            <div class="grid grid-3 stagger-children" style="margin-top:40px">
                <?php $values = [
                    ['icon' => 'wrench', 'title' => 'Practicality', 'desc' => 'We build things that solve real problems, not things that look impressive and gather dust.'],
                    ['icon' => 'badge-check', 'title' => 'Quality', 'desc' => 'We would rather do the job properly than rush it. Details matter to us.'],
                    ['icon' => 'align-left', 'title' => 'Clarity', 'desc' => 'We explain technical things in plain language, so you always know where things stand.'],
                    ['icon' => 'handshake', 'title' => 'Honesty', 'desc' => 'If something is not worth doing, we will say so before you pay for it.'],
                    ['icon' => 'book-open', 'title' => 'Learning', 'desc' => 'We continuously learn and evolve as technology and digital products change, so our work stays sharp and current.'],
                    ['icon' => 'users', 'title' => 'Partnership', 'desc' => 'We work with our clients as collaborators, not as a vendor handing over a finished box.'],
                ];
                foreach ($values as $v): ?>
                    <div class="card hover-lift" style="padding:32px;text-align:center">
                        <div style="width:56px;height:56px;border-radius:50%;background:var(--primary-alpha);display:flex;align-items:center;justify-content:center;color:var(--primary);margin:0 auto 20px">
                            <i data-lucide="<?= $v['icon'] ?>" size="28"></i>
                        </div>
                        <h5 style="margin-bottom:8px"><?= $v['title'] ?></h5>
                        <p style="font-size:14px;color:var(--text-secondary)"><?= $v['desc'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section style="background:var(--bg-light);padding:80px 0">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-tag"><i data-lucide="users" size="16"></i>Leadership</div>
                <h2 class="section-title">The People <span class="gradient-text">Behind ASAAS</span></h2>
            </div>
            <?php
            $teamJson = getSetting('team_members', '');
            $team = $teamJson ? json_decode($teamJson, true) : [];
            if (!is_array($team)) $team = [];
            $realTeam = array_filter($team, function ($m) { return !empty(trim($m['name'] ?? '')); });
            ?>
            <?php if (empty($realTeam)): ?>
                <div class="reveal" style="max-width:720px;margin:0 auto;text-align:center;background:var(--bg-white);border-radius:var(--radius-xl);border:1px solid var(--border);padding:40px">
                    <p style="color:var(--text-secondary);font-size:17px;line-height:1.8;margin:0">The founders and team behind ASAAS design and build every project together, with direct collaboration from the people doing the work. Their profiles will be added here once confirmed.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-2 stagger-children" style="max-width:760px;margin:0 auto">
                    <?php foreach ($realTeam as $t): ?>
                        <div class="card hover-lift" style="text-align:center;padding:24px">
                            <?php if (!empty($t['img'])): ?>
                                <img src="<?= strpos($t['img'], 'uploads/') === 0 ? BASE_URL . $t['img'] : htmlspecialchars($t['img']) ?>" alt="<?= htmlspecialchars($t['name']) ?>" style="width:120px;height:120px;border-radius:50%;object-fit:cover;margin:0 auto 16px">
                            <?php endif; ?>
                            <h6 style="font-weight:700"><?= htmlspecialchars($t['name']) ?></h6>
                            <p style="font-size:13px;color:var(--text-muted)"><?= htmlspecialchars($t['role'] ?? '') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section style="background:var(--bg-dark);padding:80px 0;text-align:center">
        <div class="container">
            <div class="reveal">
                <h2 style="color:white;margin-bottom:16px">Have Something You Want to Build?</h2>
                <p style="color:var(--text-light);font-size:18px;max-width:520px;margin:0 auto 32px">Tell us what you have in mind and we will get back to you with an honest answer.</p>
                <a href="<?= BASE_URL ?>contact" class="btn btn-primary btn-lg">Start a Project <i data-lucide="arrow-right" size="20"></i></a>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
