<?php
// ============================================================
// ASAAS STUDIO - Main Router / Entry Point
// ============================================================

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/functions.php';

startSession();

$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'home';
$url = filter_var($url, FILTER_SANITIZE_URL);
$segments = explode('/', $url);
$page = $segments[0] ?? 'home';

ob_start();

switch ($page) {
    // === PUBLIC PAGES ===
    case 'home':
        require __DIR__ . '/public/home.php';
        break;
    case 'services':
        require __DIR__ . '/public/services.php';
        break;
    case 'portfolio':
        if (isset($segments[1]) && $segments[1] !== '') {
            $_GET['slug'] = $segments[1];
            require __DIR__ . '/public/case-study.php';
        } else {
            require __DIR__ . '/public/portfolio.php';
        }
        break;
    case 'blog':
        if (isset($segments[1]) && $segments[1] !== '') {
            $_GET['slug'] = $segments[1];
            require __DIR__ . '/public/blog-post.php';
        } else {
            require __DIR__ . '/public/blog.php';
        }
        break;
    case 'about':
        require __DIR__ . '/public/about.php';
        break;
    case 'contact':
        require __DIR__ . '/public/contact.php';
        break;
    case 'faq':
        require __DIR__ . '/public/faq.php';
        break;
    case 'privacy-policy':
        require __DIR__ . '/public/privacy-policy.php';
        break;
    case 'terms-of-service':
        require __DIR__ . '/public/terms-of-service.php';
        break;

    // === AUTH PAGES ===
    case 'login':
        require __DIR__ . '/auth/login.php';
        break;
    case 'register':
        require __DIR__ . '/auth/register.php';
        break;
    case 'logout':
        require __DIR__ . '/auth/logout.php';
        break;
    case 'forgot-password':
        require __DIR__ . '/auth/forgot-password.php';
        break;
    case 'reset-password':
        require __DIR__ . '/auth/reset-password.php';
        break;

    // === USER DASHBOARD ===
    case 'dashboard':
        require __DIR__ . '/user/dashboard.php';
        break;
    case 'profile':
        require __DIR__ . '/user/profile.php';
        break;
    case 'messages':
        require __DIR__ . '/user/messages.php';
        break;
    case 'notifications':
        require __DIR__ . '/user/notifications.php';
        break;
    case 'ratings':
        require __DIR__ . '/user/ratings.php';
        break;
    case 'u-settings':
        require __DIR__ . '/user/settings.php';
        break;

    // === ADMIN PAGES ===
    case 'admin':
        require __DIR__ . '/admin/index.php';
        break;
    case 'admin-users':
        require __DIR__ . '/admin/users.php';
        break;
    case 'admin-posts':
        require __DIR__ . '/admin/posts.php';
        break;
    case 'admin-portfolio':
        require __DIR__ . '/admin/portfolio.php';
        break;
    case 'admin-media':
        require __DIR__ . '/admin/files.php';
        break;
    case 'admin-files':
        require __DIR__ . '/admin/files.php';
        break;
    case 'admin-messages':
        require __DIR__ . '/admin/messages.php';
        break;
    case 'admin-notifications':
        require __DIR__ . '/admin/notifications.php';
        break;
    case 'admin-logs':
        require __DIR__ . '/admin/activity-logs.php';
        break;
    case 'admin-settings':
        require __DIR__ . '/admin/settings.php';
        break;
    case 'admin-analysis':
        require __DIR__ . '/admin/analysis.php';
        break;
    case 'admin-services':
        require __DIR__ . '/admin/services.php';
        break;
    case 'admin-testimonials':
        require __DIR__ . '/admin/testimonials.php';
        break;
    case 'admin-faqs':
        require __DIR__ . '/admin/faqs.php';
        break;
    case 'admin-ratings':
        require __DIR__ . '/admin/ratings.php';
        break;
    case 'admin-bookings':
        require __DIR__ . '/admin/bookings.php';
        break;

    // === API ENDPOINTS ===
    case 'api':
        require __DIR__ . '/api/index.php';
        break;

    default:
        http_response_code(404);
        require __DIR__ . '/public/404.php';
        break;
}

$content = ob_get_clean();

// Pages with their own full HTML structure (no layout wrapping)
$fullPages = ['login', 'register', 'forgot-password', 'logout', 'dashboard', 'profile', 'messages', 'notifications', 'ratings', 'u-settings', 'api'];

// Track page visit for analytics
if (!in_array($page, $fullPages) && strpos($page, 'admin') !== 0 && $page !== 'api') {
    trackPageVisit('/' . $page);
}

if (!in_array($page, $fullPages) && strpos($page, 'admin') !== 0) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/animations.css">
        <script src="https://unpkg.com/lucide@0.460.0" integrity="sha256-GyLGwEocabdaQcZMfqmSZX6PYo2r1jJJhP/GHDdhpWo=" crossorigin="anonymous"></script>
        <script>var BASE_URL = '<?= BASE_URL ?>';</script>
        <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>assets/images/favicon_io/favicon.ico">
        <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>assets/images/favicon_io/favicon-16x16.png">
        <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>assets/images/favicon_io/favicon-32x32.png">
        <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>assets/images/favicon_io/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_URL ?>assets/images/favicon_io/android-chrome-192x192.png">
        <link rel="icon" type="image/png" sizes="512x512" href="<?= BASE_URL ?>assets/images/favicon_io/android-chrome-512x512.png">
        <link rel="manifest" href="<?= BASE_URL ?>assets/images/favicon_io/site.webmanifest">
        <?= getSEOTags($seoTitle ?? 'ASAAS STUDIO | Digital Studio in Somalia', $seoDesc ?? '', $seoImage ?? '', $seoUrl ?? '', $seoKeywords ?? '') ?>
    </head>
    <body>
        <?= $content ?>
        <script src="<?= BASE_URL ?>assets/js/animations.js"></script>
        <script src="<?= BASE_URL ?>assets/js/main.js"></script>
    </body>
    </html>
    <?php
} else {
    echo $content;
}
