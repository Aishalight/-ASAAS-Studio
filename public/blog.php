<?php
$seoTitle = 'Blog | ASAAS Studio Somalia';
$seoDesc = 'Notes and articles from the ASAAS Studio team on designing and building websites, custom web systems, and digital products.';
$seoKeywords = 'ASAAS studio blog, web design tips Somalia, web development guide Somalia, building websites, ASAAS studio';
require __DIR__ . '/../includes/header.php'; ?>

<main class="page-transition">
    <section style="padding-top:calc(var(--header-height) + 60px);padding-bottom:60px;background:var(--bg-light)">
        <div class="container">
            <div class="section-header fade-in-up">
                <div class="section-tag"><i data-lucide="pen-tool" size="16"></i>Our Blog</div>
                <h1 class="section-title">Notes & <span class="gradient-text">Resources</span></h1>
                <p class="section-desc">Notes and ideas from our work designing and building websites and web systems.</p>
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
                    echo '<div style="grid-column:1/-1;text-align:center;padding:60px 20px;background:var(--bg-white);border-radius:var(--radius-xl);border:1px solid var(--border)">';
                    echo '<div style="font-size:48px;margin-bottom:16px;opacity:0.3"><i data-lucide="pen-tool" size="48" style="color:var(--text-muted)"></i></div>';
                    echo '<h4 style="margin-bottom:8px">No Posts Yet</h4>';
                    echo '<p style="color:var(--text-muted);margin:0">We will publish notes and articles here soon.</p>';
                    echo '</div>';
                }
                foreach ($posts as $p): ?>
                    <article class="card hover-lift">
                        <div style="overflow:hidden;height:220px">
                            <?php if (!empty($p['featured_image'])): ?>
                            <img src="<?= htmlspecialchars($p['featured_image']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.5s ease" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div style="padding:24px">
                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;font-size:13px;color:var(--text-muted)">
                                <span class="badge badge-primary"><?= htmlspecialchars($p['category_name'] ?? 'General') ?></span>
                                <span><?= date('M j, Y', strtotime($p['created_at'])) ?></span>
                            </div>
                            <h5 style="margin-bottom:8px;line-height:1.4"><?= htmlspecialchars($p['title']) ?></h5>
                            <p style="font-size:14px;color:var(--text-secondary);margin-bottom:16px"><?= htmlspecialchars($p['excerpt'] ?? '') ?></p>
                            <div style="display:flex;align-items:center;justify-content:space-between">
                                <span style="font-size:13px;color:var(--text-muted)">By <?= htmlspecialchars($p['author_name'] ?? 'ASAAS') ?></span>
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
