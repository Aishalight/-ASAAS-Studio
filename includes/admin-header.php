<?php updateLastActivity(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> | <?= APP_NAME ?></title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>assets/images/favicon_io/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>assets/images/favicon_io/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>assets/images/favicon_io/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>assets/images/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_URL ?>assets/images/favicon_io/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= BASE_URL ?>assets/images/favicon_io/android-chrome-512x512.png">
    <link rel="manifest" href="<?= BASE_URL ?>assets/images/favicon_io/site.webmanifest">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/animations.css">
    <style>
    :root { --bg-dark: #1a1a2e; --bg-white: #ffffff; --bg-light: #f8f9fb; --bg-gray: #f0f1f5; --border: #e8e8f0; --border-dark: #d0d0e0; --text-primary: #1a1a2e; --text-secondary: #4a4a6a; --text-muted: #8a8aaa; --shadow-sm: 0 2px 8px rgba(0,0,0,0.06); --shadow-md: 0 4px 20px rgba(0,0,0,0.08); --shadow-lg: 0 8px 40px rgba(0,0,0,0.12); --transition: 0.3s ease; }
    :root[data-theme="dark"] { --bg-dark: #0a0a14; --bg-white: #1a1a2e; --bg-light: #12121f; --bg-gray: #1e1e35; --border: #2a2a45; --border-dark: #3a3a55; --text-primary: #e8e8f0; --text-secondary: #a0a0c0; --text-muted: #6a6a8a; --shadow-sm: 0 2px 8px rgba(0,0,0,0.3); --shadow-md: 0 4px 20px rgba(0,0,0,0.4); --shadow-lg: 0 8px 40px rgba(0,0,0,0.5); }
    [data-theme="dark"] body { background: var(--bg-light); color: var(--text-primary) }
    [data-theme="dark"] .admin-sidebar { background: var(--bg-dark) }
    [data-theme="dark"] .admin-main { background: var(--bg-light) }
    [data-theme="dark"] .admin-topbar { background: rgba(26,26,46,0.95); border-color: var(--border) }
    [data-theme="dark"] .stat-card,
    [data-theme="dark"] .chart-container,
    [data-theme="dark"] .activity-feed,
    [data-theme="dark"] .card,
    [data-theme="dark"] .upload-zone-icon { background: var(--bg-white) }
    [data-theme="dark"] .upload-zone { background: var(--bg-light) }
    [data-theme="dark"] .topbar-btn { color: var(--text-secondary) }
    [data-theme="dark"] .topbar-btn:hover { background: var(--bg-light); color: var(--text-primary) }
    [data-theme="dark"] .topbar-dropdown { background: var(--bg-white); border-color: var(--border) }
    [data-theme="dark"] .dropdown-item:hover { background: var(--bg-light) }
    [data-theme="dark"] .topbar-search input { background: var(--bg-light); color: var(--text-primary) }
    [data-theme="dark"] .sidebar-item:hover { background: rgba(255,255,255,0.08) }
    [data-theme="dark"] .sidebar-item.active { background: rgba(255,255,255,0.12) }
    [data-theme="dark"] .form-input, [data-theme="dark"] .form-select, [data-theme="dark"] .form-textarea { background: var(--bg-light); color: var(--text-primary); border-color: var(--border) }
    [data-theme="dark"] .form-input:focus, [data-theme="dark"] .form-select:focus, [data-theme="dark"] .form-textarea:focus { border-color: var(--primary) }
    [data-theme="dark"] .btn-secondary { background: transparent; border-color: var(--border); color: var(--text-primary) }
    [data-theme="dark"] .table-container { background: var(--bg-white) }
    [data-theme="dark"] .pagination a { background: var(--bg-white); border-color: var(--border); color: var(--text-primary) }
    [data-theme="dark"] .pagination a:hover { background: var(--bg-light) }
    [data-theme="dark"] .admin-content .page-header .page-title { color: var(--text-primary) }
    [data-theme="dark"] .topbar-search-icon { color: var(--text-muted) }
    [data-theme="dark"] .theme-icon-sun { display:inline-flex !important }
    [data-theme="dark"] .theme-icon-moon { display:none !important }
    .theme-icon-sun { display:none !important }
    [data-theme="dark"] .logo-light { display:none !important }
    [data-theme="dark"] .logo-dark { display:inline !important }
    .logo-dark { display:none !important }
    body { transition: background .3s ease, color .3s ease }
    .theme-toggle { background:none; border:none; cursor:pointer; font:inherit; color:inherit; display:inline-flex; align-items:center; justify-content:center; padding:8px; border-radius:8px; transition:all .2s }
    .theme-toggle:hover { background:var(--bg-light); color:var(--text-primary) }
    </style>
    <script src="https://unpkg.com/lucide@0.460.0" integrity="sha256-GyLGwEocabdaQcZMfqmSZX6PYo2r1jJJhP/GHDdhpWo=" crossorigin="anonymous"></script>
    <script>var BASE_URL = '<?= BASE_URL ?>';</script>
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
<div class="admin-layout">
    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="sidebar-header">
            <img src="<?= BASE_URL ?>assets/images/logo2_blackbackground.png" alt="ASAAS" style="height:38px;width:auto">
            <span class="sidebar-brand" style="font-size:14px;letter-spacing:0.3px"><?= APP_NAME ?></span>
        </div>
        <?php
        $currentUrl = $_GET['url'] ?? '';
        function navActive($url) {
            global $currentUrl;
            return $currentUrl === $url ? 'active' : '';
        }
        ?>
        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main</div>
                <a href="<?= BASE_URL ?>admin" class="sidebar-item <?= navActive('admin') ?>"><span class="sidebar-item-icon"><i data-lucide="layout-dashboard" size="18"></i></span><span class="sidebar-item-text">Dashboard</span></a>
                <a href="<?= BASE_URL ?>" target="_blank" class="sidebar-item"><span class="sidebar-item-icon"><i data-lucide="globe" size="18"></i></span><span class="sidebar-item-text">Website</span></a>
                <a href="<?= BASE_URL ?>admin-users" class="sidebar-item <?= navActive('admin-users') ?>"><span class="sidebar-item-icon"><i data-lucide="users" size="18"></i></span><span class="sidebar-item-text">Users</span></a>
                <a href="<?= BASE_URL ?>admin-posts" class="sidebar-item <?= navActive('admin-posts') ?>"><span class="sidebar-item-icon"><i data-lucide="file-text" size="18"></i></span><span class="sidebar-item-text">Blog Posts</span></a>
                <a href="<?= BASE_URL ?>admin-analysis" class="sidebar-item <?= navActive('admin-analysis') ?>"><span class="sidebar-item-icon"><i data-lucide="bar-chart-3" size="18"></i></span><span class="sidebar-item-text">Analysis</span></a>
                <a href="<?= BASE_URL ?>admin-services" class="sidebar-item <?= navActive('admin-services') ?>"><span class="sidebar-item-icon"><i data-lucide="briefcase" size="18"></i></span><span class="sidebar-item-text">Services</span></a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-title">Content</div>
                <a href="<?= BASE_URL ?>admin-portfolio" class="sidebar-item <?= navActive('admin-portfolio') ?>"><span class="sidebar-item-icon"><i data-lucide="image" size="18"></i></span><span class="sidebar-item-text">Portfolio</span></a>
                <a href="<?= BASE_URL ?>admin-files" class="sidebar-item <?= navActive('admin-files') || navActive('admin-media') ?>"><span class="sidebar-item-icon"><i data-lucide="folder" size="18"></i></span><span class="sidebar-item-text">Files</span></a>
                <a href="<?= BASE_URL ?>admin-ratings" class="sidebar-item <?= navActive('admin-ratings') ?>"><span class="sidebar-item-icon"><i data-lucide="star" size="18"></i></span><span class="sidebar-item-text">Ratings</span></a>
                <a href="<?= BASE_URL ?>admin-bookings" class="sidebar-item <?= navActive('admin-bookings') ?>"><span class="sidebar-item-icon"><i data-lucide="calendar" size="18"></i></span><span class="sidebar-item-text">Bookings</span></a>
                <a href="<?= BASE_URL ?>admin-testimonials" class="sidebar-item <?= navActive('admin-testimonials') ?>"><span class="sidebar-item-icon"><i data-lucide="message-square" size="18"></i></span><span class="sidebar-item-text">Testimonials</span></a>
                <a href="<?= BASE_URL ?>admin-faqs" class="sidebar-item <?= navActive('admin-faqs') ?>"><span class="sidebar-item-icon"><i data-lucide="help-circle" size="18"></i></span><span class="sidebar-item-text">FAQs</span></a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-title">System</div>
                <?php
$mDb = Database::getInstance()->getConnection();
$mCount = (int)$mDb->query("SELECT COUNT(DISTINCT thread_id) FROM messages WHERE receiver_id IN (SELECT id FROM users WHERE role IN ('admin','superadmin'))")->fetchColumn();
$mBadge = $mCount > 0 ? '<span class="sidebar-item-badge">'.$mCount.'</span>' : '';
?><a href="<?= BASE_URL ?>admin-messages" class="sidebar-item <?= navActive('admin-messages') ?>"><span class="sidebar-item-icon"><i data-lucide="inbox" size="18"></i></span><span class="sidebar-item-text">Messages</span><span class="sidebar-item-badge" data-msg-badge><?= $mCount > 0 ? $mCount : 0 ?></span></a>
                <a href="<?= BASE_URL ?>admin-notifications" class="sidebar-item <?= navActive('admin-notifications') ?>"><span class="sidebar-item-icon"><i data-lucide="bell" size="18"></i></span><span class="sidebar-item-text">Notifications</span></a>
                <a href="<?= BASE_URL ?>admin-logs" class="sidebar-item <?= navActive('admin-logs') ?>"><span class="sidebar-item-icon"><i data-lucide="activity" size="18"></i></span><span class="sidebar-item-text">Activity Logs</span></a>
                <a href="<?= BASE_URL ?>admin-settings" class="sidebar-item <?= navActive('admin-settings') ?>"><span class="sidebar-item-icon"><i data-lucide="settings" size="18"></i></span><span class="sidebar-item-text">Settings</span></a>
            </div>
        </nav>
        <?php
        $adminAvatarLetter = strtoupper(getCurrentUserName()[0]);
        try { $q = Database::getInstance()->getConnection()->prepare("SELECT avatar FROM users WHERE id=?"); $q->execute([getCurrentUserId()]); $adminAvatarPath = $q->fetchColumn(); } catch (Exception $e) { $adminAvatarPath = null; }
        ?>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar" style="<?= $adminAvatarPath ? 'overflow:hidden;background:none' : '' ?>"><?php if ($adminAvatarPath): ?><img src="<?= BASE_URL . $adminAvatarPath ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover"><?php else: ?><?= $adminAvatarLetter ?><?php endif; ?></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?= htmlspecialchars(getCurrentUserName()) ?></div>
                    <div class="sidebar-user-role"><?= ucfirst(getCurrentUserRole()) ?></div>
                </div>
            </div>
        </div>
    </aside>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" id="sidebar-toggle">
                    <i data-lucide="menu" size="20"></i>
                </button>
                <div class="topbar-search">
                    <i data-lucide="search" class="topbar-search-icon" size="16"></i>
                    <input type="text" placeholder="Search anything..." id="globalSearch">
                </div>
            </div>
            <div class="topbar-right">
                <button class="theme-toggle" id="theme-toggle" title="Toggle theme">
                    <i data-lucide="moon" size="18" class="theme-icon-moon"></i>
                    <i data-lucide="sun" size="18" class="theme-icon-sun"></i>
                </button>
                <?php $alertBadgeCount = getUnreadAlertCount(); ?>
                <div style="position:relative">
                    <a href="<?= BASE_URL ?>admin-logs" class="topbar-btn" style="text-decoration:none;position:relative">
                        <i data-lucide="shield" size="20"></i>
                        <?php if ($alertBadgeCount > 0): ?><span class="topbar-badge" style="background:#F44336"><?= $alertBadgeCount ?></span><?php endif; ?>
                    </a>
                </div>
                <div style="position:relative">
                    <button class="topbar-btn" data-dropdown="notifications-dropdown">
                        <i data-lucide="bell" size="20"></i>
                        <span class="topbar-badge" data-notif-badge><?= getUnreadNotificationCount(getCurrentUserId()) ?></span>
                    </button>
                    <div class="topbar-dropdown" id="notifications-dropdown" style="min-width:360px">
                        <div class="dropdown-header">
                            <div class="dropdown-header-title">Notifications</div>
                            <div class="dropdown-header-sub"><a href="<?= BASE_URL ?>admin-notifications" style="font-size:12px">Mark all as read</a></div>
                        </div>
                        <?php
                        $notifList = getNotifications(getCurrentUserId(), 5);
                        if (empty($notifList)): ?>
                            <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:13px">No notifications</div>
                        <?php else: foreach ($notifList as $n): ?>
                        <div class="dropdown-item">
                            <div style="width:36px;height:36px;border-radius:50%;background:var(--primary-alpha);display:flex;align-items:center;justify-content:center;color:var(--primary);min-width:36px">
                                <i data-lucide="<?= htmlspecialchars($n['icon'] ?: 'bell') ?>" size="16"></i>
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($n['title']) ?></div>
                                <div style="font-size:12px;color:var(--text-muted)"><?= formatTimeAgo($n['created_at']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; endif; ?>
                        <a href="<?= BASE_URL ?>admin-notifications" class="dropdown-item" style="justify-content:center;font-weight:600;color:var(--primary)">View All</a>
                    </div>
                </div>
                <div style="position:relative">
                    <button class="topbar-btn" data-dropdown="profile-dropdown">
                        <i data-lucide="settings" size="20"></i>
                    </button>
                    <div class="topbar-dropdown" id="profile-dropdown">
                        <div class="dropdown-header">
                            <div class="dropdown-header-title"><?= htmlspecialchars(getCurrentUserName()) ?></div>
                            <div class="dropdown-header-sub"><?= htmlspecialchars(getCurrentUserEmail()) ?></div>
                        </div>
                        <a href="<?= BASE_URL ?>admin-settings" class="dropdown-item"><i data-lucide="settings" size="16"></i> Settings</a>
                        <a href="<?= BASE_URL ?>dashboard" class="dropdown-item"><i data-lucide="user" size="16"></i> User Dashboard</a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="<?= BASE_URL ?>logout">
                            <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                            <button type="submit" class="dropdown-item" style="color:#F44336;width:100%;text-align:left;border:none;background:none;cursor:pointer;font:inherit;padding:8px 16px"><i data-lucide="log-out" size="16"></i> Sign Out</button>
                        </form>
                    </div>
                </div>
                <div style="position:relative">
                    <div class="topbar-profile" data-dropdown="profile-dropdown">
                        <div class="topbar-profile-avatar"><?= strtoupper(getCurrentUserName()[0]) ?></div>
                        <span class="topbar-profile-name"><?= htmlspecialchars(getCurrentUserName()) ?></span>
                    </div>
                </div>
            </div>
        </header>
        <script>
        var admSidebar = document.querySelector('.admin-sidebar');
        var admToggle = document.querySelector('.topbar-toggle');
        var admOverlay = document.querySelector('.sidebar-overlay');
        if (admToggle && admSidebar) {
            admToggle.onclick = function(e) {
                e.stopPropagation();
                if (window.innerWidth <= 1024) {
                    admSidebar.classList.toggle('active');
                    if (admOverlay) admOverlay.classList.toggle('active');
                } else {
                    admSidebar.classList.toggle('collapsed');
                }
            };
        }
        if (admOverlay) {
            admOverlay.onclick = function() {
                admSidebar.classList.remove('active');
                admOverlay.classList.remove('active');
            };
        }
        </script>
        <div class="admin-content">
