<?php
$seoTitle = 'Page Not Found | ASAAS Studio Somalia';
$seoDesc = 'The page you are looking for does not exist. Return to the ASAAS Studio Somalia homepage to explore our digital services.';
$seoKeywords = 'page not found, 404 error, ASAAS studio Somalia';
require __DIR__ . '/../includes/header.php'; ?>

<main class="page-transition" style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding-top:var(--header-height)">
    <div class="container text-center">
        <div class="fade-in-up">
            <div style="font-size:120px;font-weight:900;color:var(--primary);line-height:1;margin-bottom:16px">404</div>
            <h2 style="margin-bottom:12px">Page Not Found</h2>
            <p style="color:var(--text-secondary);font-size:18px;margin-bottom:32px;max-width:500px;margin-left:auto;margin-right:auto">
                The page you are looking for doesn't exist or has been moved.
            </p>
            <div style="display:flex;gap:16px;justify-content:center">
                <a href="<?= BASE_URL ?>home" class="btn btn-primary">Go Home <i data-lucide="home" size="18"></i></a>
                <a href="<?= BASE_URL ?>contact" class="btn btn-secondary">Contact Us <i data-lucide="mail" size="18"></i></a>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
