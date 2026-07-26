<?php
updateLastActivity();
if (!isset($pageTitle)) $pageTitle = 'Dashboard';
$currentPage = $_GET['url'] ?? 'dashboard';
$userId = getCurrentUserId();
$userName = getCurrentUserName();
$userEmail = getCurrentUserEmail();
$userAvatarLetter = strtoupper($userName[0] ?? 'U');
try {
    $aDb = Database::getInstance()->getConnection();
    $aStmt = $aDb->prepare("SELECT avatar FROM users WHERE id = ?");
    $aStmt->execute([$userId]);
    $userAvatarPath = $aStmt->fetchColumn();
} catch (Exception $e) { $userAvatarPath = null; }
$userAvatar = $userAvatarPath ? BASE_URL . $userAvatarPath : $userAvatarLetter;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    $unreadCount = (int)$stmt->fetch()['count'];
} catch (Exception $e) {
    $unreadCount = 0;
}

$navItems = [
    'dashboard'    => ['label' => 'Dashboard',     'icon' => 'layout-dashboard'],
    'profile'      => ['label' => 'Profile',       'icon' => 'user'],
    'messages'     => ['label' => 'Messages',      'icon' => 'message-square'],
    'notifications'=> ['label' => 'Notifications', 'icon' => 'bell'],
    'ratings'      => ['label' => 'My Ratings',    'icon' => 'star'],
    'u-settings'   => ['label' => 'Settings',      'icon' => 'settings'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | <?= APP_NAME ?></title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>assets/images/favicon_io/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>assets/images/favicon_io/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>assets/images/favicon_io/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>assets/images/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_URL ?>assets/images/favicon_io/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= BASE_URL ?>assets/images/favicon_io/android-chrome-512x512.png">
    <link rel="manifest" href="<?= BASE_URL ?>assets/images/favicon_io/site.webmanifest">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/animations.css">
    <style>
    :root { --bg-dark: #1a1a2e; --bg-white: #ffffff; --bg-light: #f8f9fb; --bg-gray: #f0f1f5; --border: #e8e8f0; --border-dark: #d0d0e0; --text-primary: #1a1a2e; --text-secondary: #4a4a6a; --text-muted: #8a8aaa; --shadow-sm: 0 2px 8px rgba(0,0,0,0.06); --shadow-md: 0 4px 20px rgba(0,0,0,0.08); --shadow-lg: 0 8px 40px rgba(0,0,0,0.12); --transition: 0.3s ease; }
    :root[data-theme="dark"] { --bg-dark: #0a0a14; --bg-white: #1a1a2e; --bg-light: #12121f; --bg-gray: #1e1e35; --border: #2a2a45; --border-dark: #3a3a55; --text-primary: #e8e8f0; --text-secondary: #a0a0c0; --text-muted: #6a6a8a; --shadow-sm: 0 2px 8px rgba(0,0,0,0.3); --shadow-md: 0 4px 20px rgba(0,0,0,0.4); --shadow-lg: 0 8px 40px rgba(0,0,0,0.5); }
    [data-theme="dark"] .user-sidebar { background: var(--bg-dark); }
    [data-theme="dark"] .user-topbar { background: rgba(26,26,46,0.95); }
    [data-theme="dark"] body { background: var(--bg-light); color: var(--text-primary); }
    [data-theme="dark"] .dashboard-layout { background: var(--bg-light); }
    [data-theme="dark"] .dash-stat, [data-theme="dark"] .profile-header, [data-theme="dark"] .profile-body, [data-theme="dark"] .settings-section, [data-theme="dark"] .notifications-card, [data-theme="dark"] .ratings-card, [data-theme="dark"] .messages-layout { background: var(--bg-white); }
    [data-theme="dark"] .dash-action, [data-theme="dark"] .dash-activity { background: var(--bg-white); }
    [data-theme="dark"] .chat-messages { background: #0e0e1a; }
    [data-theme="dark"] .chat-bubble.received { background: var(--bg-gray); color: var(--text-primary); }
    [data-theme="dark"] .form-input, [data-theme="dark"] .form-textarea { background: var(--bg-light); color: var(--text-primary); border-color: var(--border); }
    [data-theme="dark"] .form-input:focus, [data-theme="dark"] .form-textarea:focus { border-color: var(--primary); }
    [data-theme="dark"] .btn-secondary { background: transparent; border-color: var(--border); color: var(--text-primary); }
    [data-theme="dark"] .empty-state-icon { background: var(--bg-gray); }
    [data-theme="dark"] .alert-success { background: rgba(76,175,80,0.06); border-color: rgba(76,175,80,0.15); }
    [data-theme="dark"] .alert-error { background: rgba(244,67,54,0.06); border-color: rgba(244,67,54,0.15); }
    body { transition: background .3s ease, color .3s ease }
    .theme-toggle { background:none; border:none; cursor:pointer; font:inherit; color:inherit; display:inline-flex; align-items:center; justify-content:center; padding:8px; border-radius:8px; transition:all .2s }
    .theme-toggle:hover { background:var(--bg-light); color:var(--text-primary) }
    [data-theme="dark"] .theme-icon-sun { display:inline-flex !important }
    [data-theme="dark"] .theme-icon-moon { display:none !important }
    .theme-icon-sun { display:none !important }
    [data-theme="dark"] .logo-light { display:none !important }
    [data-theme="dark"] .logo-dark { display:inline !important }
    .logo-dark { display:none !important }
    </style>
    <script>var BASE_URL = '<?= BASE_URL ?>';</script>
    <script src="https://unpkg.com/lucide@0.460.0" integrity="sha256-GyLGwEocabdaQcZMfqmSZX6PYo2r1jJJhP/GHDdhpWo=" crossorigin="anonymous"></script>
    <script>
    (function(){
        var h=document.documentElement;
        if(localStorage.getItem('theme')==='dark') h.setAttribute('data-theme','dark');
        document.addEventListener('DOMContentLoaded',function(){
            var b=document.getElementById('theme-toggle');
            if(b){
                b.addEventListener('click',function(){
                    if(h.getAttribute('data-theme')==='dark'){
                        h.removeAttribute('data-theme');
                        localStorage.setItem('theme','light');
                    }else{
                        h.setAttribute('data-theme','dark');
                        localStorage.setItem('theme','dark');
                    }
                });
            }
        });
    })();
    </script>
</head>
<body>
<div class="dashboard-layout">
    <aside class="user-sidebar" id="user-sidebar">
        <div class="user-sidebar-brand">
            <img src="<?= BASE_URL ?>uploads/logo2_blackbackground.png" alt="ASAAS" style="height:46px;width:auto">
            <span class="user-sidebar-name">ASAAS</span>
        </div>

        <div class="user-sidebar-user">
            <div class="user-sidebar-avatar" style="<?= $userAvatarPath ? 'overflow:hidden;background:none' : '' ?>"><?php if ($userAvatarPath): ?><img src="<?= $userAvatar ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover"><?php else: ?><?= $userAvatar ?><?php endif; ?></div>
            <div class="user-sidebar-user-info">
                <div class="user-sidebar-user-name"><?= htmlspecialchars($userName) ?></div>
                <div class="user-sidebar-user-role"><?= ucfirst(getCurrentUserRole()) ?></div>
            </div>
        </div>

        <nav class="user-sidebar-nav">
            <div class="user-sidebar-section-label">Main Menu</div>
            <?php foreach ($navItems as $route => $item):
                $isActive = $currentPage === $route;
                $badge = ($route === 'notifications' && $unreadCount > 0) ? '<span class="user-sidebar-badge">' . $unreadCount . '</span>' : '';
            ?>
                <a href="<?= BASE_URL . $route ?>" class="user-sidebar-item <?= $isActive ? 'active' : '' ?>">
                    <span class="user-sidebar-item-icon"><i data-lucide="<?= $item['icon'] ?>" size="18"></i></span>
                    <span class="user-sidebar-item-text"><?= $item['label'] ?></span>
                    <?= $badge ?>
                </a>
            <?php endforeach; ?>

            <div class="user-sidebar-section-label" style="margin-top:16px">Links</div>
            <a href="<?= BASE_URL ?>home" class="user-sidebar-item">
                <span class="user-sidebar-item-icon"><i data-lucide="globe" size="18"></i></span>
                <span class="user-sidebar-item-text">Website</span>
            </a>
            <form method="POST" action="<?= BASE_URL ?>logout" class="user-sidebar-item user-sidebar-danger" style="margin:0;padding:0;border:none;background:none;width:100%">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <button type="submit" style="display:flex;align-items:center;gap:10px;width:100%;padding:10px 16px;border:none;background:none;cursor:pointer;color:inherit;font:inherit;text-align:left">
                    <span class="user-sidebar-item-icon"><i data-lucide="log-out" size="18"></i></span>
                    <span class="user-sidebar-item-text">Sign Out</span>
                </button>
            </form>
        </nav>
    </aside>
    <div class="user-sidebar-overlay" id="user-sidebar-overlay"></div>

    <main class="user-main">
        <header class="user-topbar">
            <div class="user-topbar-left">
                <button class="user-sidebar-toggle" id="user-sidebar-toggle">
                    <i data-lucide="menu" size="20"></i>
                </button>
                <h3 class="user-topbar-title"><?= $pageTitle ?></h3>
            </div>
            <div class="user-topbar-right">
                <button class="user-topbar-icon-wrap theme-toggle" id="theme-toggle" title="Toggle theme">
                    <i data-lucide="moon" size="18" class="theme-icon-moon"></i>
                    <i data-lucide="sun" size="18" class="theme-icon-sun"></i>
                </button>
                <a href="<?= BASE_URL ?>notifications" class="user-topbar-icon-wrap">
                    <i data-lucide="bell" size="20"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="user-topbar-badge badge-pulse" data-notif-badge><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>profile" class="user-topbar-avatar" style="<?= $userAvatarPath ? 'overflow:hidden;background:none' : '' ?>"><?php if ($userAvatarPath): ?><img src="<?= $userAvatar ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover"><?php else: ?><?= $userAvatar ?><?php endif; ?></a>
            </div>
        </header>
        <script>
        var toggleBtn = document.getElementById('user-sidebar-toggle');
        var sideBar = document.getElementById('user-sidebar');
        var sideOverlay = document.getElementById('user-sidebar-overlay');
        if (toggleBtn && sideBar) {
            toggleBtn.onclick = function() {
                sideBar.classList.toggle('active');
                if (sideOverlay) sideOverlay.classList.toggle('active');
            };
        }
        if (sideOverlay) {
            sideOverlay.onclick = function() {
                sideBar.classList.remove('active');
                sideOverlay.classList.remove('active');
            };
        }
        if (sideBar) {
            var links = sideBar.querySelectorAll('.user-sidebar-item');
            for (var i = 0; i < links.length; i++) {
                links[i].onclick = function() {
                    if (window.innerWidth <= 1024) {
                        sideBar.classList.remove('active');
                        if (sideOverlay) sideOverlay.classList.remove('active');
                    }
                };
            }
        }
        </script>
        <div class="user-content">
