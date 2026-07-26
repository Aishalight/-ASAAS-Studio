<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Notifications'; require __DIR__ . '/../includes/admin-header.php';

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid security token.';
    } else {
        if (isset($_POST['mark_all_read'])) {
            $db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ?")->execute([getCurrentUserId()]);
            header('Location: ' . BASE_URL . 'admin-notifications');
            exit;
        }
        if (isset($_POST['delete_notification'])) {
            $notifId = (int)($_POST['notif_id'] ?? 0);
            if ($notifId) {
                $db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?")->execute([$notifId, getCurrentUserId()]);
                header('Location: ' . BASE_URL . 'admin-notifications');
                exit;
            }
        }
        if (isset($_POST['send_notification'])) {
            $target = sanitize($_POST['target'] ?? 'all');
            $title = sanitize($_POST['title'] ?? '');
            $message = sanitize($_POST['message'] ?? '');
            $type = sanitize($_POST['type'] ?? 'info');
            if ($title) {
                if ($target === 'all') {
                    $users = $db->query("SELECT id FROM users WHERE status = 'active'")->fetchAll();
                    foreach ($users as $u) {
                        createNotification($u['id'], $type, $title, $message);
                    }
                } elseif ($target === 'admins') {
                    $users = $db->query("SELECT id FROM users WHERE role IN ('admin','superadmin') AND status = 'active'")->fetchAll();
                    foreach ($users as $u) {
                        createNotification($u['id'], $type, $title, $message);
                    }
                }
                logActivity('notification_sent', "Admin sent notification: $title", [], 'info');
                header('Location: ' . BASE_URL . 'admin-notifications');
                exit;
            }
        }
    }
}

$notifs = $db->prepare("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 50");
$notifs->execute();
$notifs = $notifs->fetchAll();
?>
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Notifications Center</h1>
        <p class="page-subtitle">Manage system notifications</p>
    </div>
    <div class="page-actions">
        <form method="POST" style="display:inline">
            <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
            <input type="hidden" name="mark_all_read" value="1">
            <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="check-check" size="16"></i> Mark All Read</button>
        </form>
        <button class="btn btn-primary btn-sm" data-modal="newNotifModal"><i data-lucide="plus" size="16"></i> New Notification</button>
    </div>
</div>

<div class="reveal" style="background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);overflow:hidden">
    <?php if (empty($notifs)): ?>
        <div class="empty-state"><div class="empty-state-icon"><i data-lucide="bell-off" size="32"></i></div><h3 class="empty-state-title">No notifications</h3><p class="empty-state-desc">System notifications will appear here.</p></div>
    <?php else: foreach ($notifs as $n): ?>
        <div class="activity-item">
            <div class="activity-icon <?= $n['is_read'] ? 'info' : 'warning' ?>"><i data-lucide="<?= $n['icon'] ?: 'bell' ?>" size="16"></i></div>
            <div class="activity-content">
                <div class="activity-action"><?= htmlspecialchars($n['title']) ?> <?php if (!$n['is_read']): ?><span class="badge badge-primary" style="margin-left:8px">New</span><?php endif; ?></div>
                <div class="activity-desc"><?= htmlspecialchars($n['message'] ?? '') ?></div>
                <div class="activity-meta">
                    <span class="badge badge-info"><?= ucfirst($n['type']) ?></span>
                    <span><?= timeAgo($n['created_at']) ?></span>
                </div>
            </div>
            <form method="POST" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="delete_notification" value="1">
                <input type="hidden" name="notif_id" value="<?= $n['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-icon btn-sm" style="color:#F44336" onclick="return confirm('Delete this notification?')"><i data-lucide="x" size="14"></i></button>
            </form>
        </div>
    <?php endforeach; endif; ?>
</div>

<!-- New Notification Modal -->
<div class="modal-overlay" id="newNotifModal">
    <div class="modal-content">
        <div class="modal-header"><h3 class="modal-title">Send Notification</h3><button class="modal-close" onclick="closeModal(document.getElementById('newNotifModal'))">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="send_notification" value="1">
                <div class="form-group"><label class="form-label">Send To</label><select name="target" class="form-select"><option value="all">All Users</option><option value="admins">Admins Only</option></select></div>
                <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Message</label><textarea name="message" class="form-textarea" rows="4"></textarea></div>
                <div class="form-group"><label class="form-label">Type</label><select name="type" class="form-select"><option value="info">Info</option><option value="success">Success</option><option value="warning">Warning</option><option value="error">Error</option></select></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal(document.getElementById('newNotifModal'))">Cancel</button>
                <button type="submit" class="btn btn-primary">Send Notification</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body></html>
