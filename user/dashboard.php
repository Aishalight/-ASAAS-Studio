<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAuth();
$pageTitle = 'Dashboard';
$userId = getCurrentUserId();
$userName = getCurrentUserName();
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE sender_id = ? OR receiver_id = ?");
$stmt->execute([$userId, $userId]);
$msgCount = (int)$stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM ratings WHERE user_id = ?");
$stmt->execute([$userId]);
$ratingCount = (int)$stmt->fetchColumn();

$stmt = $db->prepare("SELECT created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$memberSince = $stmt->fetchColumn();

$activities = $db->prepare("SELECT action, description, created_at, severity FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$activities->execute([$userId]);
$activities = $activities->fetchAll();

require __DIR__ . '/../includes/user-header.php';
?>

<style>
:root {
    --primary: #E8632A;
    --primary-dark: #d4551f;
    --bg-dark: #1a1a2e;
    --bg-white: #ffffff;
    --bg-light: #f8f9fb;
    --border: #e8e8f0;
    --text-primary: #1a1a2e;
    --text-secondary: #4a4a6a;
    --text-muted: #8a8aaa;
    --radius-lg: 16px;
    --radius-md: 12px;
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
    --shadow-lg: 0 8px 40px rgba(0,0,0,0.12);
    --sidebar-width: 260px;
    --transition: 0.3s ease;
}

.dashboard-layout { display: flex; min-height: 100vh; background: var(--bg-light); }

/* SIDEBAR */
.user-sidebar {
    width: var(--sidebar-width); background: var(--bg-dark); color: white;
    display: flex; flex-direction: column; position: fixed;
    top: 0; left: 0; bottom: 0; z-index: 100;
    transition: transform var(--transition);
}

.user-sidebar-brand {
    display: flex; align-items: center; gap: 10px;
    padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.08);
}

.user-sidebar-logo {
    width: 34px; height: 34px; min-width: 34px; background: var(--primary);
    border-radius: 8px; display: flex; align-items: center; justify-content: center;
    font-weight: 900; font-size: 16px; color: white;
}

.user-sidebar-name { font-size: 17px; font-weight: 800; letter-spacing: 0.02em; }

.user-sidebar-user {
    display: flex; align-items: center; gap: 12px;
    padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.08);
}

.user-sidebar-avatar {
    width: 38px; height: 38px; border-radius: 50%; min-width: 38px;
    background: var(--primary); display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 15px; color: white;
    box-shadow: 0 2px 8px rgba(232,99,42,0.3);
}

.user-sidebar-user-info { overflow: hidden; }
.user-sidebar-user-name { font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-sidebar-user-role { font-size: 12px; color: rgba(255,255,255,0.5); }

.user-sidebar-nav { flex: 1; padding: 12px; overflow-y: auto; }

.user-sidebar-section-label {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.12em; color: rgba(255,255,255,0.3);
    padding: 0 10px; margin-bottom: 6px;
}

.user-sidebar-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px; border-radius: 8px;
    color: rgba(255,255,255,0.65); text-decoration: none;
    font-size: 14px; font-weight: 500;
    transition: all 0.2s; margin-bottom: 2px;
}

.user-sidebar-item:hover { background: rgba(255,255,255,0.06); color: white; }

.user-sidebar-item.active {
    background: rgba(255,255,255,0.1); color: white; position: relative;
}

.user-sidebar-item.active::before {
    content: ''; position: absolute; left: 0;
    top: 6px; bottom: 6px; width: 3px;
    background: var(--primary); border-radius: 0 3px 3px 0;
}

.user-sidebar-item-icon { width: 20px; height: 20px; min-width: 20px; display: flex; align-items: center; justify-content: center; }
.user-sidebar-item-text { overflow: hidden; white-space: nowrap; }

.user-sidebar-danger { color: rgba(244,67,54,0.7) !important; }
.user-sidebar-danger:hover { background: rgba(244,67,54,0.1) !important; color: #F44336 !important; }

.user-sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; backdrop-filter: blur(4px); }
.user-sidebar-overlay.active { display: block; }

/* MAIN */
.user-main { flex: 1; margin-left: var(--sidebar-width); min-height: 100vh; }

.user-topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 32px; height: 64px;
    background: rgba(255,255,255,0.9); backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 50;
}

.user-topbar-left { display: flex; align-items: center; gap: 12px; }

.user-sidebar-toggle {
    display: none; background: none; border: none; cursor: pointer;
    color: var(--text-secondary); padding: 6px; border-radius: 8px; transition: all 0.2s;
}

.user-sidebar-toggle:hover { background: var(--bg-light); color: var(--text-primary); }
.user-topbar-title { font-size: 17px; font-weight: 700; color: var(--text-primary); }
.user-topbar-right { display: flex; align-items: center; gap: 8px; }

.user-topbar-icon-wrap {
    position: relative; color: var(--text-secondary); text-decoration: none;
    padding: 8px; border-radius: 8px; transition: all 0.2s;
}

.user-topbar-icon-wrap:hover { background: var(--bg-light); color: var(--text-primary); }

.user-topbar-avatar {
    width: 34px; height: 34px; border-radius: 50%; background: var(--primary);
    color: white; display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 13px; text-decoration: none;
    box-shadow: 0 2px 8px rgba(232,99,42,0.3); transition: transform 0.2s; margin-left: 4px;
}

.user-topbar-avatar:hover { transform: scale(1.05); }

.user-content { padding: 28px 32px; max-width: 1120px; }

/* WELCOME BANNER */
.dash-welcome {
    background: linear-gradient(135deg, var(--primary) 0%, #ff6b35 100%);
    border-radius: 16px; padding: 28px 32px; margin-bottom: 28px;
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    color: white; position: relative; overflow: hidden;
}

.dash-welcome::before {
    content: ''; position: absolute; top: -60%; right: -10%;
    width: 500px; height: 500px; border-radius: 50%; background: rgba(255,255,255,0.05);
}

.dash-welcome-left { display: flex; align-items: center; gap: 16px; position: relative; z-index: 1; }

.dash-welcome-avatar {
    width: 52px; height: 52px; border-radius: 50%; background: rgba(255,255,255,0.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 800; color: white;
    border: 2px solid rgba(255,255,255,0.3); flex-shrink: 0;
}

.dash-welcome-text h1 { font-size: 20px; font-weight: 800; color: white; margin-bottom: 2px; }
.dash-welcome-text p { font-size: 13px; color: rgba(255,255,255,0.8); }

.dash-welcome-right { display: flex; gap: 10px; position: relative; z-index: 1; flex-shrink: 0; }

.dash-welcome-right .btn {
    background: rgba(255,255,255,0.2); color: white;
    border: 1px solid rgba(255,255,255,0.25); backdrop-filter: blur(8px);
    padding: 8px 18px; font-size: 13px; border-radius: 10px;
    display: inline-flex; align-items: center; gap: 6px;
    font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.25s;
}

.dash-welcome-right .btn:hover {
    background: rgba(255,255,255,0.35); transform: translateY(-2px); color: white;
}

.dash-welcome-right .btn-primary {
    background: white; color: var(--primary); border: none;
}

.dash-welcome-right .btn-primary:hover {
    background: rgba(255,255,255,0.9); color: var(--primary-dark);
}

/* STATS */
.dash-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }

.dash-stat {
    background: var(--bg-white); border-radius: 14px; padding: 20px;
    border: 1px solid var(--border); transition: all 0.25s;
}

.dash-stat:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.06); transform: translateY(-2px); }

.dash-stat-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }

.dash-stat-icon {
    width: 42px; height: 42px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 18px;
}

.dash-stat-icon.blue { background: rgba(33,150,243,0.1); color: #2196F3; }
.dash-stat-icon.orange { background: rgba(232,99,42,0.1); color: var(--primary); }
.dash-stat-icon.purple { background: rgba(156,39,176,0.1); color: #9C27B0; }
.dash-stat-icon.green { background: rgba(76,175,80,0.1); color: #4CAF50; }

.dash-stat-label { font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 4px; }
.dash-stat-value { font-size: 28px; font-weight: 800; line-height: 1; color: var(--text-primary); }
.dash-stat-sub { font-size: 12px; color: var(--text-muted); margin-top: 6px; }

.dash-stat-change { font-size: 12px; font-weight: 600; margin-top: 8px; }
.dash-stat-change.positive { color: #4CAF50; }
.dash-stat-change.neutral { color: var(--text-muted); }

.dash-stats.stagger > * {
    opacity: 0; transform: translateY(16px);
    animation: statIn 0.5s ease forwards;
}

.dash-stats.stagger > *:nth-child(1) { animation-delay: 0.06s; }
.dash-stats.stagger > *:nth-child(2) { animation-delay: 0.12s; }
.dash-stats.stagger > *:nth-child(3) { animation-delay: 0.18s; }
.dash-stats.stagger > *:nth-child(4) { animation-delay: 0.24s; }

@keyframes statIn { to { opacity: 1; transform: translateY(0); } }

/* SECTION HEADER */
.dash-section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.dash-section-title { font-size: 15px; font-weight: 700; color: var(--text-primary); }

/* QUICK ACTIONS */
.dash-actions { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 28px; }

.dash-action {
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    padding: 22px 12px; background: var(--bg-white); border-radius: 14px;
    border: 1px solid var(--border); text-decoration: none; color: var(--text-primary);
    transition: all 0.25s;
}

.dash-action:hover {
    transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    border-color: var(--primary);
}

.dash-action-icon {
    width: 44px; height: 44px; border-radius: 10px;
    background: rgba(232,99,42,0.1); display: flex;
    align-items: center; justify-content: center; color: var(--primary);
    transition: all 0.25s;
}

.dash-action:hover .dash-action-icon { background: var(--primary); color: white; }
.dash-action-label { font-size: 13px; font-weight: 600; text-align: center; }

/* ACTIVITY */
.dash-activity {
    background: var(--bg-white); border-radius: 14px;
    border: 1px solid var(--border); overflow: hidden;
}

.dash-activity-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 12px 20px; border-bottom: 1px solid var(--border);
    transition: background 0.15s;
}

.dash-activity-item:last-child { border-bottom: none; }
.dash-activity-item:hover { background: var(--bg-light); }

.dash-activity-dot {
    width: 8px; height: 8px; border-radius: 50%;
    min-width: 8px; margin-top: 6px;
}

.dash-activity-dot.info { background: #2196F3; }
.dash-activity-dot.warning { background: #FF9800; }
.dash-activity-dot.critical { background: #F44336; }
.dash-activity-dot.success { background: #4CAF50; }

.dash-activity-body { flex: 1; min-width: 0; }
.dash-activity-action { font-size: 14px; font-weight: 600; color: var(--text-primary); }
.dash-activity-desc { font-size: 13px; color: var(--text-secondary); margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dash-activity-time { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

/* ANIMATIONS */
.fade-in-up { opacity: 0; transform: translateY(30px); animation: fadeInUp 0.6s ease forwards; }
@keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }

.reveal { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease, transform 0.6s ease; }
.reveal.visible { opacity: 1; transform: translateY(0); }

/* RESPONSIVE */
@media (max-width: 1024px) {
    .user-sidebar { transform: translateX(-100%); }
    .user-sidebar.active { transform: translateX(0); }
    .user-sidebar-toggle { display: block; }
    .user-main { margin-left: 0; }
    .user-topbar { padding: 0 20px; }
    .user-content { padding: 24px 20px; }
    .dash-stats { grid-template-columns: 1fr 1fr; }
    .dash-actions { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 768px) {
    .dash-welcome { flex-direction: column; text-align: center; padding: 24px 20px; }
    .dash-welcome-left { flex-direction: column; }
    .dash-welcome-right { width: 100%; justify-content: center; }
    .dash-stats { grid-template-columns: 1fr; gap: 12px; }
    .dash-actions { grid-template-columns: 1fr 1fr; gap: 12px; }
    .user-content { padding: 16px; }
}

@media (max-width: 480px) {
    .dash-actions { grid-template-columns: 1fr; }
}
</style>

<div class="dash-welcome">
    <div class="dash-welcome-left">
        <?php $userAvatar = getCurrentUserAvatar(); ?>
        <div class="dash-welcome-avatar" style="overflow:hidden;<?= $userAvatar ? 'background:none' : '' ?>">
            <?php if ($userAvatar): ?>
                <img src="<?= BASE_URL . htmlspecialchars($userAvatar) ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover">
            <?php else: ?>
                <?= strtoupper($userName[0]) ?>
            <?php endif; ?>
        </div>
        <div class="dash-welcome-text">
            <h1>Welcome back, <?= htmlspecialchars(explode(' ', $userName)[0]) ?>!</h1>
            <p>Here's your activity overview.</p>
        </div>
    </div>
    <div class="dash-welcome-right">
        <a href="<?= BASE_URL ?>messages" class="btn btn-primary"><i data-lucide="message-square" size="15"></i> New Message</a>
        <a href="<?= BASE_URL ?>profile" class="btn"><i data-lucide="user" size="15"></i> Profile</a>
    </div>
</div>

<div class="dash-stats stagger">
    <div class="dash-stat">
        <div class="dash-stat-top">
            <span class="dash-stat-label">Messages</span>
            <div class="dash-stat-icon blue"><i data-lucide="message-square" size="20"></i></div>
        </div>
        <div class="dash-stat-value"><?= number_format($msgCount) ?></div>
        <div class="dash-stat-sub">Total conversations</div>
    </div>
    <div class="dash-stat">
        <div class="dash-stat-top">
            <span class="dash-stat-label">Notifications</span>
            <div class="dash-stat-icon orange"><i data-lucide="bell" size="20"></i></div>
        </div>
        <div class="dash-stat-value"><?= $unreadCount ?></div>
        <div class="dash-stat-sub">Unread notifications</div>
        <div class="dash-stat-change <?= $unreadCount > 0 ? 'positive' : 'neutral' ?>"><?= $unreadCount > 0 ? 'You have updates' : 'All caught up' ?></div>
    </div>
    <div class="dash-stat">
        <div class="dash-stat-top">
            <span class="dash-stat-label">Ratings</span>
            <div class="dash-stat-icon purple"><i data-lucide="star" size="20"></i></div>
        </div>
        <div class="dash-stat-value"><?= number_format($ratingCount) ?></div>
        <div class="dash-stat-sub">Projects rated</div>
    </div>
    <div class="dash-stat">
        <div class="dash-stat-top">
            <span class="dash-stat-label">Member since</span>
            <div class="dash-stat-icon green"><i data-lucide="calendar" size="20"></i></div>
        </div>
        <div class="dash-stat-value" style="font-size:22px"><?= $memberSince ? formatDate($memberSince, 'M Y') : 'N/A' ?></div>
        <div class="dash-stat-sub">Account age</div>
    </div>
</div>

<div class="reveal">
    <div class="dash-section-header">
        <h3 class="dash-section-title">Quick Actions</h3>
    </div>
    <div class="dash-actions">
        <a href="<?= BASE_URL ?>messages" class="dash-action">
            <div class="dash-action-icon"><i data-lucide="message-square" size="22"></i></div>
            <span class="dash-action-label">Send Message</span>
        </a>
        <a href="<?= BASE_URL ?>profile" class="dash-action">
            <div class="dash-action-icon"><i data-lucide="user" size="22"></i></div>
            <span class="dash-action-label">Edit Profile</span>
        </a>
        <a href="<?= BASE_URL ?>notifications" class="dash-action">
            <div class="dash-action-icon"><i data-lucide="bell" size="22"></i></div>
            <span class="dash-action-label">Notifications</span>
        </a>
        <a href="<?= BASE_URL ?>blog" class="dash-action">
            <div class="dash-action-icon"><i data-lucide="book-open" size="22"></i></div>
            <span class="dash-action-label">Browse Blog</span>
        </a>
    </div>
</div>

<?php if (!empty($activities)): ?>
<div class="reveal" style="margin-top:28px">
    <div class="dash-section-header">
        <h3 class="dash-section-title">Recent Activity</h3>
    </div>
    <div class="dash-activity">
        <?php foreach ($activities as $a):
            $sev = $a['severity'] === 'critical' ? 'critical' : ($a['severity'] === 'warning' ? 'warning' : ($a['severity'] === 'success' ? 'success' : 'info'));
        ?>
        <div class="dash-activity-item">
            <div class="dash-activity-dot <?= $sev ?>"></div>
            <div class="dash-activity-body">
                <div class="dash-activity-action"><?= htmlspecialchars(ucfirst($a['action'])) ?></div>
                <?php if ($a['description']): ?>
                <div class="dash-activity-desc"><?= htmlspecialchars($a['description']) ?></div>
                <?php endif; ?>
                <div class="dash-activity-time"><?= timeAgo($a['created_at']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/user-footer.php'; ?>
