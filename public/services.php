<?php
$seoTitle = 'Our Services — Website Development, SEO, Social Media & Maintenance';
$seoDesc = 'Explore ASAAS Studio services: website development from starter to custom systems, SEO, social media management, website maintenance, and domain & hosting assistance. Transparent pricing.';
$seoKeywords = 'website development Somalia, web design Somalia, SEO services Somalia, social media management, website maintenance, domain hosting';
require __DIR__ . '/../includes/header.php'; ?>

<main class="page-transition">
    <section style="padding-top:calc(var(--header-height) + 60px);padding-bottom:60px;background:var(--bg-light)">
        <div class="container">
            <div class="section-header fade-in-up">
                <div class="section-tag"><i data-lucide="sparkles" size="16"></i>Our Capabilities</div>
                <h1 class="section-title">Our <span class="gradient-text">Services</span></h1>
                <p class="section-desc">From websites to full web systems — transparent pricing, premium quality, built to solve real business problems.</p>
            </div>
        </div>
    </section>

    <section class="section" style="padding-top:40px">
        <div class="container">

            <h3 class="fade-in-up" style="font-size:24px;font-weight:700;margin-bottom:8px">Website Development</h3>
            <p class="fade-in-up" style="font-size:16px;color:var(--text-secondary);margin-bottom:40px">From simple landing pages to complete web systems — we build what your business needs.</p>

            <div class="grid grid-3 stagger-children" style="margin-bottom:80px">
                <?php
                $devServices = [
                    [
                        'icon' => 'rocket',
                        'title' => 'Starter Website',
                        'price' => '$99',
                        'desc' => 'Perfect for startups and personal brands.',
                        'features' => ['Landing pages', 'Portfolio websites', 'Business websites', 'Up to 2 pages', 'Fully responsive', 'Contact form'],
                    ],
                    [
                        'icon' => 'building',
                        'title' => 'Business Website',
                        'price' => '$299',
                        'desc' => 'Ideal for growing businesses.',
                        'features' => ['Multi-page website', 'Basic backend functionality', 'Admin login', 'Content management', 'Product or service listings', 'Contact management'],
                    ],
                    [
                        'icon' => 'settings',
                        'title' => 'Custom Web System',
                        'price' => '$999+',
                        'desc' => 'A fully customized solution built around your business.',
                        'features' => ['User authentication', 'Admin dashboard', 'Multiple user roles', 'Database management', 'Reports & analytics', 'Custom features', 'API integrations', 'Scalable architecture'],
                    ],
                ];
                foreach ($devServices as $s): ?>
                    <div class="card card-sticky-footer hover-lift">
                        <div style="padding:32px">
                            <div style="width:56px;height:56px;border-radius:var(--radius-md);background:var(--primary-alpha);display:flex;align-items:center;justify-content:center;color:var(--primary);margin-bottom:20px">
                                <i data-lucide="<?= $s['icon'] ?>" size="28"></i>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                                <h4><?= $s['title'] ?></h4>
                                <span style="font-size:20px;font-weight:800;color:var(--primary)"><?= $s['price'] ?></span>
                            </div>
                            <p style="font-size:14px;color:var(--text-secondary);margin-bottom:20px"><?= $s['desc'] ?></p>
                            <ul style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px">
                                <?php foreach ($s['features'] as $f): ?>
                                    <li style="display:flex;align-items:center;gap:10px;font-size:14px;color:var(--text-secondary)">
                                        <i data-lucide="check-circle" size="16" style="color:var(--primary);min-width:16px"></i>
                                        <?= $f ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="<?= BASE_URL ?>contact" class="btn btn-primary btn-block">
                                Get Started <i data-lucide="arrow-right" size="16"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Other Services -->
            <h3 class="fade-in-up" style="font-size:24px;font-weight:700;margin-bottom:8px">More Services</h3>
            <p class="fade-in-up" style="font-size:16px;color:var(--text-secondary);margin-bottom:40px">We also handle everything else you need to grow online — SEO, social media, maintenance, and hosting.</p>

            <div class="grid grid-2 stagger-children" style="margin-bottom:80px">
                <?php
                $otherServices = [
                    [
                        'icon' => 'trending-up',
                        'title' => 'Search Engine Optimization (SEO)',
                        'price' => '$99/mo',
                        'desc' => 'Help your website rank higher on Google.',
                        'features' => ['Technical SEO', 'On-page SEO', 'Speed optimization', 'SEO audits', 'Keyword optimization', 'Google Search Console setup'],
                    ],
                    [
                        'icon' => 'share-2',
                        'title' => 'Social Media Management',
                        'price' => '$149/mo',
                        'desc' => 'Grow and maintain your online presence.',
                        'features' => ['Content planning', 'Page management', 'Graphic posting', 'Community engagement', 'Performance reports'],
                    ],
                    [
                        'icon' => 'wrench',
                        'title' => 'Website Maintenance',
                        'price' => '$49/mo',
                        'desc' => 'Keep your website secure, updated, and running smoothly.',
                        'features' => ['Bug fixes', 'Security updates', 'Content updates', 'Performance optimization', 'Regular backups', 'Technical support'],
                    ],
                    [
                        'icon' => 'globe',
                        'title' => 'Domain & Hosting Assistance',
                        'price' => 'Custom',
                        'desc' => 'We help clients get their websites online.',
                        'features' => ['Domain registration guidance', 'Free domain options (where suitable)', 'Premium domain registration', 'Web hosting setup', 'Cloud hosting configuration', 'DNS configuration', 'SSL certificate installation', 'Website deployment'],
                    ],
                ];
                foreach ($otherServices as $s): ?>
                    <div class="card card-sticky-footer hover-lift">
                        <div style="padding:32px">
                            <div style="width:56px;height:56px;border-radius:var(--radius-md);background:var(--primary-alpha);display:flex;align-items:center;justify-content:center;color:var(--primary);margin-bottom:20px">
                                <i data-lucide="<?= $s['icon'] ?>" size="28"></i>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                                <h4><?= $s['title'] ?></h4>
                                <span style="font-size:20px;font-weight:800;color:var(--primary)"><?= $s['price'] ?></span>
                            </div>
                            <p style="font-size:14px;color:var(--text-secondary);margin-bottom:20px"><?= $s['desc'] ?></p>
                            <ul style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px">
                                <?php foreach ($s['features'] as $f): ?>
                                    <li style="display:flex;align-items:center;gap:10px;font-size:14px;color:var(--text-secondary)">
                                        <i data-lucide="check-circle" size="16" style="color:var(--primary);min-width:16px"></i>
                                        <?= $f ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="<?= BASE_URL ?>contact" class="btn btn-primary btn-block">
                                Get Started <i data-lucide="arrow-right" size="16"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="background:var(--bg-gray);border-left:4px solid var(--primary);border-radius:0 var(--radius-sm) var(--radius-sm) 0;padding:20px 24px;margin-bottom:40px" class="fade-in-up">
                <p style="font-size:14px;color:var(--text-secondary);margin:0"><strong style="color:var(--text-primary)">Note:</strong> Hosting and domain registration fees are billed separately based on the selected provider and plan.</p>
            </div>

        </div>
    </section>

    <section style="background:var(--bg-dark);padding:80px 0;text-align:center">
        <div class="container">
            <div class="reveal">
                <h2 style="color:white;margin-bottom:16px">Not Sure Where to Start?</h2>
                <p style="color:var(--text-light);font-size:18px;max-width:600px;margin:0 auto 32px">Every project begins with a conversation. Tell us about your goals and we'll map out the best path forward.</p>
                <a href="<?= BASE_URL ?>contact" class="btn btn-primary btn-lg">Let's Talk <i data-lucide="send" size="20"></i></a>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
