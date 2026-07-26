<?php
$seoTitle = 'Blog Somalia — Insights on Design, Development & Digital Strategy';
$seoDesc = 'Read the ASAAS Studio Somalia blog for expert insights on web design, web development, branding, UI/UX, digital marketing, and mobile development.';
$seoKeywords = 'digital agency Somalia blog, web design tips Somalia, web development guide Somalia, branding insights Somalia, digital marketing strategies Somalia';
require __DIR__ . '/../includes/header.php'; ?>

<main class="page-transition">
    <section style="padding-top:calc(var(--header-height) + 60px);padding-bottom:60px;background:var(--bg-light)">
        <div class="container">
            <div class="section-header fade-in-up">
                <div class="section-tag"><i data-lucide="pen-tool" size="16"></i>Our Blog</div>
                <h1 class="section-title">Insights & <span class="gradient-text">Resources</span></h1>
                <p class="section-desc">Thoughts on design, development, branding, and digital strategy from the ASAAS team.</p>
            </div>
        </div>
    </section>

    <section class="section" style="padding-top:40px">
        <div class="container">
            <div class="grid grid-auto stagger-children">
                <?php
                $db = Database::getInstance()->getConnection();
                $posts = $db->query("SELECT p.*, c.name as category_name, u.name as author_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN users u ON p.author_id = u.id WHERE p.status = 'published' ORDER BY p.published_at DESC, p.created_at DESC LIMIT 12")->fetchAll();
                if (empty($posts)) {
                    $posts = [
                        ['id' => 1, 'title' => 'The Future of Web Design: Trends to Watch in 2026', 'category_name' => 'Web Design', 'created_at' => '2026-05-28', 'author_name' => 'Alex Mercer', 'featured_image' => 'https://images.unsplash.com/photo-1487017159836-4e23ece2e4cf?w=600&h=400&fit=crop', 'excerpt' => 'Explore the cutting-edge trends shaping the future of web design and how they can transform your digital presence.', 'slug' => 'future-web-design-2026'],
                        ['id' => 2, 'title' => 'Building Scalable Web Applications: A Complete Guide', 'category_name' => 'Development', 'created_at' => '2026-05-25', 'author_name' => 'Jordan Lee', 'featured_image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&h=400&fit=crop', 'excerpt' => 'Learn the principles and practices for building web applications that scale from hundreds to millions of users.', 'slug' => 'scalable-web-apps-guide'],
                        ['id' => 3, 'title' => 'Brand Identity in the Digital Age', 'category_name' => 'Branding', 'created_at' => '2026-05-20', 'author_name' => 'Sarah Chen', 'featured_image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600&h=400&fit=crop', 'excerpt' => 'How to create a brand identity that resonates across digital channels and builds lasting connections.', 'slug' => 'brand-identity-digital-age'],
                        ['id' => 4, 'title' => 'UI/UX Principles That Drive Conversions', 'category_name' => 'UI/UX', 'created_at' => '2026-05-15', 'author_name' => 'Marcus Williams', 'featured_image' => 'https://images.unsplash.com/photo-1586717791821-3f44a563fa4c?w=600&h=400&fit=crop', 'excerpt' => 'Discover the user experience principles that can significantly improve your website conversion rates.', 'slug' => 'uiux-principles-conversions'],
                        ['id' => 5, 'title' => 'SEO Strategies for 2026: What Actually Works', 'category_name' => 'Marketing', 'created_at' => '2026-05-10', 'author_name' => 'Emily Zhao', 'featured_image' => 'https://images.unsplash.com/photo-1432889821006-3149409b1c1f?w=600&h=400&fit=crop', 'excerpt' => 'A practical guide to SEO strategies that deliver real results in today competitive digital landscape.', 'slug' => 'seo-strategies-2026'],
                        ['id' => 6, 'title' => 'The Complete Guide to Mobile App Development', 'category_name' => 'Mobile', 'created_at' => '2026-05-05', 'author_name' => 'David Park', 'featured_image' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=600&h=400&fit=crop', 'excerpt' => 'Everything you need to know about mobile app development, from concept to App Store launch.', 'slug' => 'mobile-app-dev-guide'],
                    ];
                }
                foreach ($posts as $p): ?>
                    <article class="card hover-lift">
                        <div style="overflow:hidden;height:220px">
                            <img src="<?= $p['featured_image'] ?? 'https://images.unsplash.com/photo-1487017159836-4e23ece2e4cf?w=600&h=400&fit=crop' ?>" alt="<?= htmlspecialchars($p['title']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.5s ease" loading="lazy">
                        </div>
                        <div style="padding:24px">
                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;font-size:13px;color:var(--text-muted)">
                                <span class="badge badge-primary"><?= htmlspecialchars($p['category_name'] ?? 'General') ?></span>
                                <span><?= date('M j, Y', strtotime($p['created_at'])) ?></span>
                            </div>
                            <h5 style="margin-bottom:8px;line-height:1.4"><?= htmlspecialchars($p['title']) ?></h5>
                            <p style="font-size:14px;color:var(--text-secondary);margin-bottom:16px"><?= htmlspecialchars($p['excerpt'] ?? '') ?></p>
                            <div style="display:flex;align-items:center;justify-content:space-between">
                                <span style="font-size:13px;color:var(--text-muted)">By <?= htmlspecialchars($p['author_name'] ?? 'Unknown') ?></span>
                                <a href="<?= BASE_URL ?>blog/<?= htmlspecialchars($p['slug'] ?? 'post') ?>" class="btn btn-ghost btn-sm">Read More <i data-lucide="arrow-right" size="14"></i></a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-8 reveal">
                <button class="btn btn-secondary">Load More Posts <i data-lucide="refresh-cw" size="16"></i></button>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
