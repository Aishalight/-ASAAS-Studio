<?php
$seoTitle = 'About — Premium Digital Agency in Somalia';
$seoDesc = 'Learn about ASAAS Studio — our mission, values, and the team behind Somalia premier digital agency specializing in web design, development, branding, and digital strategy.';
$seoKeywords = 'about ASAAS studio, digital agency Somalia, web design company Somalia, branding agency Mogadishu, digital agency team';
require __DIR__ . '/../includes/header.php'; ?>

<main class="page-transition">
    <section style="padding-top:calc(var(--header-height) + 60px);background:var(--bg-light);overflow:hidden">
        <div class="container">
            <div class="grid grid-2" style="align-items:center;gap:60px;padding:60px 0">
                <div class="fade-in-up">
                    <div class="section-tag"><i data-lucide="info" size="16"></i>About</div>
                    <h1 style="font-size:clamp(2rem,4vw,3.5rem);margin-bottom:16px">We Are <span class="gradient-text">ASAAS</span></h1>
                    <p style="color:var(--text-secondary);font-size:18px;line-height:1.8;margin-bottom:24px">We design and engineer digital products for brands that aim higher. A team of strategists, designers, and engineers — we've been turning ambitious ideas into scalable realities since 2014.</p>
                    <p style="color:var(--text-secondary);line-height:1.8">Our philosophy is straightforward: every line of code, every pixel, every interaction should serve a purpose. Great digital experiences don't happen by accident — they are designed, tested, and refined with relentless attention to detail.</p>
                    <div style="display:flex;gap:24px;margin-top:32px">
                        <div>
                            <div style="font-size:36px;font-weight:800;color:var(--primary)">150+</div>
                            <div style="font-size:14px;color:var(--text-muted)">Projects</div>
                        </div>
                        <div>
                            <div style="font-size:36px;font-weight:800;color:var(--primary)">50+</div>
                            <div style="font-size:14px;color:var(--text-muted)">Team Members</div>
                        </div>
                        <div>
                            <div style="font-size:36px;font-weight:800;color:var(--primary)">12+</div>
                            <div style="font-size:14px;color:var(--text-muted)">Years</div>
                        </div>
                    </div>
                </div>
                <div class="fade-in-right">
                    <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&h=600&fit=crop" alt="ASAAS Studio Team" style="width:100%;border-radius:var(--radius-xl);box-shadow:var(--shadow-lg)">
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="text-align:center">
        <div class="container">
            <div class="section-header reveal">
                <h2 class="section-title">What We <span class="gradient-text">Stand For</span></h2>
                <p class="section-desc">The principles that guide every decision, every design, and every line of code.</p>
            </div>
            <div class="grid grid-3 stagger-children" style="margin-top:40px">
                <?php $values = [
                    ['icon' => 'target', 'title' => 'Excellence', 'desc' => 'We never settle for good enough. Every project receives our full dedication to quality and craftsmanship.'],
                    ['icon' => 'lightbulb', 'title' => 'Innovation', 'desc' => 'We stay ahead of the curve, embracing new technologies and creative approaches to solve complex problems.'],
                    ['icon' => 'heart', 'title' => 'Passion', 'desc' => 'We genuinely love what we do. Our passion drives us to create extraordinary digital experiences.'],
                    ['icon' => 'users', 'title' => 'Collaboration', 'desc' => 'We believe in the power of teamwork. Great results come from diverse perspectives working together.'],
                    ['icon' => 'shield', 'title' => 'Integrity', 'desc' => 'Honesty and transparency are at the core of every relationship we build with our clients and partners.'],
                    ['icon' => 'trending-up', 'title' => 'Growth', 'desc' => 'We are committed to continuous learning and improvement, both for ourselves and for the businesses we serve.'],
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
                <h2 class="section-title">Meet Our <span class="gradient-text">Team</span></h2>
            </div>
            <div class="grid grid-4 stagger-children">
                <?php $team = [
                    ['name' => 'Alex Mercer', 'role' => 'CEO & Founder', 'img' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=300&h=300&fit=crop'],
                    ['name' => 'Sarah Chen', 'role' => 'Creative Director', 'img' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=300&h=300&fit=crop'],
                    ['name' => 'Marcus Williams', 'role' => 'Technical Lead', 'img' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=300&fit=crop'],
                    ['name' => 'Emily Zhao', 'role' => 'Head of Marketing', 'img' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=300&h=300&fit=crop'],
                ];
                foreach ($team as $t): ?>
                    <div class="card hover-lift" style="text-align:center;padding:24px">
                        <img src="<?= $t['img'] ?>" alt="<?= $t['name'] ?>" style="width:120px;height:120px;border-radius:50%;object-fit:cover;margin:0 auto 16px">
                        <h6 style="font-weight:700"><?= $t['name'] ?></h6>
                        <p style="font-size:13px;color:var(--text-muted)"><?= $t['role'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section style="background:var(--bg-dark);padding:80px 0;text-align:center">
        <div class="container">
            <div class="reveal">
                <h2 style="color:white;margin-bottom:16px">Help Us Shape the Future</h2>
                <p style="color:var(--text-light);font-size:18px;max-width:500px;margin:0 auto 32px">We're always looking for sharp minds who care deeply about their craft.</p>
                <a href="<?= BASE_URL ?>contact" class="btn btn-primary btn-lg">View Open Positions <i data-lucide="arrow-right" size="20"></i></a>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
