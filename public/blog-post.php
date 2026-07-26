<?php
$slug = $_GET['slug'] ?? '';
$db = Database::getInstance()->getConnection();

if ($slug) {
    $stmt = $db->prepare("SELECT p.*, c.name as category_name, u.name as author_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN users u ON p.author_id = u.id WHERE p.slug = ? AND p.status = 'published'");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
}

if (empty($post)) {
    $seoTitle = 'Blog Post Not Found';
    require __DIR__ . '/../includes/header.php';
    ?>
    <main class="page-transition">
        <section style="padding-top:calc(var(--header-height) + 120px);padding-bottom:80px;text-align:center">
            <div class="container">
                <h1 style="font-size:clamp(2rem,4vw,3rem);margin-bottom:16px">Post Not Found</h1>
                <p style="color:var(--text-secondary);margin-bottom:32px">The blog post you are looking for does not exist or has been removed.</p>
                <a href="<?= BASE_URL ?>blog" class="btn btn-primary"><i data-lucide="arrow-left" size="16"></i> Back to Blog</a>
            </div>
        </section>
    </main>
    <?php require __DIR__ . '/../includes/footer.php'; exit;
}

$seoTitle = htmlspecialchars($post['title']) . ' — ASAAS Studio Blog';
$seoDesc = htmlspecialchars($post['excerpt'] ?? mb_substr(strip_tags($post['content']), 0, 160));
$seoKeywords = $post['tags'] ?? 'blog, asaas studio';
$readTime = max(1, round(str_word_count(strip_tags($post['content'])) / 200));
require __DIR__ . '/../includes/header.php';
?>

<main class="page-transition">
    <section style="padding-top:calc(var(--header-height) + 60px);background:var(--bg-light)">
        <div class="container" style="padding:40px 24px;max-width:800px;margin:0 auto">
            <div class="fade-in-up">
                <a href="<?= BASE_URL ?>blog" class="btn btn-ghost btn-sm" style="margin-bottom:24px"><i data-lucide="arrow-left" size="16"></i> Back to Blog</a>
                <?php if ($post['category_name']): ?>
                <span class="badge badge-primary" style="margin-bottom:16px"><?= htmlspecialchars($post['category_name']) ?></span>
                <?php endif; ?>
                <h1 style="font-size:clamp(1.75rem,3vw,2.75rem);margin-bottom:16px"><?= htmlspecialchars($post['title']) ?></h1>
                <div style="display:flex;align-items:center;gap:16px;margin-bottom:32px;font-size:14px;color:var(--text-muted)">
                    <span>By <?= htmlspecialchars($post['author_name'] ?? 'Unknown') ?></span>
                    <span><?= date('M j, Y', strtotime($post['published_at'] ?: $post['created_at'])) ?></span>
                    <span><i data-lucide="clock" size="14" style="display:inline;vertical-align:middle"></i> <?= $readTime ?> min read</span>
                </div>
                <?php if ($post['featured_image']): ?>
                <img src="<?= BASE_URL . $post['featured_image'] ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="width:100%;border-radius:var(--radius-xl);margin-bottom:40px;box-shadow:var(--shadow-lg)">
                <?php endif; ?>
            </div>

            <div class="reveal" style="font-size:16px;line-height:1.9;color:var(--text-secondary)">
                <?= $post['content'] ?>
            </div>

            <?php if ($post['tags']): ?>
            <div class="reveal" style="margin-top:32px;display:flex;gap:8px;flex-wrap:wrap">
                <?php foreach (array_filter(array_map('trim', explode(',', $post['tags']))) as $tag): ?>
                    <span class="badge" style="background:var(--bg-gray)"><?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="reveal" style="margin-top:48px;padding-top:32px;border-top:1px solid var(--border)">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:48px;height:48px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700"><?= strtoupper(substr($post['author_name'] ?? 'A', 0, 1)) ?></div>
                        <div>
                            <h6 style="font-weight:700"><?= htmlspecialchars($post['author_name'] ?? 'Unknown') ?></h6>
                            <p style="font-size:13px;color:var(--text-muted)">ASAAS Studio</p>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px">
                        <button class="btn btn-secondary btn-sm" onclick="navigator.share({title:'<?= htmlspecialchars(addslashes($post['title'])) ?>',url:window.location.href})"><i data-lucide="share-2" size="14"></i> Share</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
