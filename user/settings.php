<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAuth();
$pageTitle = 'Settings';
$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['current_password']) && isset($_POST['new_password'])) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([getCurrentUserId()]);
        $user = $stmt->fetch();
        if (password_verify($_POST['current_password'], $user['password'])) {
            if (validatePassword($_POST['new_password'])) {
                $hashed = password_hash($_POST['new_password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
                $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, getCurrentUserId()]);
                $success = 'Password updated successfully.';
                logActivity('password_change', 'User changed password', [], 'info');
            } else {
                $error = 'Password must be at least 8 characters with uppercase, lowercase, and number.';
            }
        } else {
            $error = 'Current password is incorrect.';
        }
    }
}
require __DIR__ . '/../includes/user-header.php';
?>
<style>
:root {
    --primary: #E8632A; --primary-dark: #d4551f;
    --bg-dark: #1a1a2e; --bg-white: #fff; --bg-light: #f8f9fb;
    --border: #e8e8f0; --border-dark: #d0d0e0;
    --text-primary: #1a1a2e; --text-secondary: #4a4a6a; --text-muted: #8a8aaa;
    --radius-lg: 16px; --radius-md: 12px; --radius-sm: 8px;
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.06); --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
    --sidebar-width: 260px; --transition: 0.3s ease;
}

* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Inter', sans-serif; font-size: 16px; color: var(--text-primary); background: var(--bg-light); }
.dashboard-layout { display: flex; min-height: 100vh; background: var(--bg-light); }

/* SIDEBAR */
.user-sidebar { width: var(--sidebar-width); background: var(--bg-dark); color: white; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 100; transition: transform var(--transition); }
.user-sidebar-brand { display: flex; align-items: center; gap: 10px; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.08); }
.user-sidebar-logo { width: 34px; height: 34px; min-width: 34px; background: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 16px; color: white; }
.user-sidebar-name { font-size: 17px; font-weight: 800; letter-spacing: 0.02em; }
.user-sidebar-user { display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); }
.user-sidebar-avatar { width: 38px; height: 38px; border-radius: 50%; min-width: 38px; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 15px; color: white; box-shadow: 0 2px 8px rgba(232,99,42,0.3); }
.user-sidebar-user-info { overflow: hidden; }
.user-sidebar-user-name { font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-sidebar-user-role { font-size: 12px; color: rgba(255,255,255,0.5); }
.user-sidebar-nav { flex: 1; padding: 12px; overflow-y: auto; }
.user-sidebar-section-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: rgba(255,255,255,0.3); padding: 0 10px; margin-bottom: 6px; }
.user-sidebar-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; color: rgba(255,255,255,0.65); text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.2s; margin-bottom: 2px; }
.user-sidebar-item:hover { background: rgba(255,255,255,0.06); color: white; }
.user-sidebar-item.active { background: rgba(255,255,255,0.1); color: white; position: relative; }
.user-sidebar-item.active::before { content: ''; position: absolute; left: 0; top: 6px; bottom: 6px; width: 3px; background: var(--primary); border-radius: 0 3px 3px 0; }
.user-sidebar-item-icon { width: 20px; height: 20px; min-width: 20px; display: flex; align-items: center; justify-content: center; }
.user-sidebar-item-text { overflow: hidden; white-space: nowrap; }
.user-sidebar-danger { color: rgba(244,67,54,0.7) !important; }
.user-sidebar-danger:hover { background: rgba(244,67,54,0.1) !important; color: #F44336 !important; }
.user-sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; backdrop-filter: blur(4px); }
.user-sidebar-overlay.active { display: block; }

/* MAIN */
.user-main { flex: 1; margin-left: var(--sidebar-width); min-height: 100vh; }
.user-topbar { display: flex; align-items: center; justify-content: space-between; padding: 0 32px; height: 64px; background: rgba(255,255,255,0.9); backdrop-filter: blur(16px); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 50; }
.user-topbar-left { display: flex; align-items: center; gap: 12px; }
.user-sidebar-toggle { display: none; background: none; border: none; cursor: pointer; color: var(--text-secondary); padding: 6px; border-radius: 8px; transition: all 0.2s; }
.user-sidebar-toggle:hover { background: var(--bg-light); color: var(--text-primary); }
.user-topbar-title { font-size: 17px; font-weight: 700; color: var(--text-primary); }
.user-topbar-right { display: flex; align-items: center; gap: 8px; }
.user-topbar-icon-wrap { position: relative; color: var(--text-secondary); text-decoration: none; padding: 8px; border-radius: 8px; transition: all 0.2s; }
.user-topbar-icon-wrap:hover { background: var(--bg-light); color: var(--text-primary); }
.user-topbar-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; text-decoration: none; box-shadow: 0 2px 8px rgba(232,99,42,0.3); transition: transform 0.2s; margin-left: 4px; }
.user-topbar-avatar:hover { transform: scale(1.05); }
.user-content { padding: 28px 32px; max-width: 1120px; }

/* PAGE HEADER */
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 800; }
.page-subtitle { font-size: 14px; color: var(--text-muted); margin-top: 4px; }

/* ALERTS */
.alert { display: flex; align-items: flex-start; gap: 12px; padding: 16px 20px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px; font-weight: 500; }
.alert-success { background: rgba(76,175,80,0.08); border: 1px solid rgba(76,175,80,0.2); color: #2E7D32; }
.alert-error { background: rgba(244,67,54,0.08); border: 1px solid rgba(244,67,54,0.2); color: #C62828; }

/* SETTINGS */
.settings-section { background: var(--bg-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 28px; margin-bottom: 20px; }
.settings-section-title { font-size: 17px; font-weight: 700; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
.settings-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

/* FORM */
.form-group { margin-bottom: 20px; }
.form-label { display: block; font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px; }
.form-input { width: 100%; padding: 12px 16px; border: 2px solid var(--border); border-radius: var(--radius-sm); font-family: 'Inter', sans-serif; font-size: 15px; color: var(--text-primary); background: var(--bg-white); outline: none; transition: all var(--transition); }
.form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(232,99,42,0.1); }
.form-hint { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
.form-checkbox { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: var(--text-primary); }
.form-checkbox input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--primary); }

/* BUTTON */
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border: none; border-radius: var(--radius-md); font-family: 'Inter', sans-serif; font-size: 15px; font-weight: 600; cursor: pointer; transition: all var(--transition); text-decoration: none; }
.btn-primary { background: var(--primary); color: white; }
.btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 8px 25px rgba(232,99,42,0.35); color: white; }
.btn-secondary { background: var(--bg-white); color: var(--text-primary); border: 2px solid var(--border); }
.btn-secondary:hover { border-color: var(--primary); color: var(--primary); }
.btn-outline { background: transparent; color: var(--primary); border: 2px solid var(--primary); }
.btn-outline:hover { background: var(--primary); color: white; }

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
}
@media (max-width: 768px) {
    .settings-row { grid-template-columns: 1fr; }
    .settings-section { padding: 20px; }
    .user-content { padding: 16px; }
}
</style>

<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Manage your account settings and preferences.</p>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><i data-lucide="check-circle" size="18"></i> <?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><i data-lucide="alert-circle" size="18"></i> <?= $error ?></div>
<?php endif; ?>

<div class="settings-section reveal">
    <h3 class="settings-section-title">Change Password</h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
        <div class="settings-row">
            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-input" placeholder="Enter current password" required>
            </div>
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-input" placeholder="Enter new password" required>
                <div class="form-hint">Min 8 characters with uppercase, lowercase, and number</div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Update Password <i data-lucide="check" size="16"></i></button>
    </form>
</div>

<div class="settings-section reveal">
    <h3 class="settings-section-title">Account Preferences</h3>
    <div class="form-group">
        <label class="form-checkbox">
            <input type="checkbox" checked> Email notifications
        </label>
    </div>
    <div class="form-group">
        <label class="form-checkbox">
            <input type="checkbox" checked> Marketing emails
        </label>
    </div>
    <button class="btn btn-primary">Save Preferences <i data-lucide="check" size="16"></i></button>
</div>

<div class="settings-section reveal">
    <h3 class="settings-section-title">Account</h3>
    <p style="color:var(--text-secondary);font-size:14px;margin-bottom:16px">Manage your account and data</p>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
        <button class="btn btn-secondary">Download Data <i data-lucide="download" size="16"></i></button>
        <button class="btn btn-outline" style="color:#F44336;border-color:rgba(244,67,54,0.3)">Delete Account <i data-lucide="trash-2" size="16"></i></button>
    </div>
</div>

<?php require __DIR__ . '/../includes/user-footer.php'; ?>
