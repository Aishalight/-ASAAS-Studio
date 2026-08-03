<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAuth();
$pageTitle = 'Profile';
$db = Database::getInstance()->getConnection();
$userId = getCurrentUserId();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
$success = ''; $error = '';
$avatarDir = __DIR__ . '/../uploads/avatars/';
if (!is_dir($avatarDir)) mkdir($avatarDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session token. Please refresh the page.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $company = sanitize($_POST['company'] ?? '');
        $bio = sanitize($_POST['bio'] ?? '');
        $root = realpath(__DIR__ . '/..');

        // Avatar upload
        $avatarPath = $user['avatar'] ?? null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, GIF, WebP images are allowed.';
            } else {
                // Delete old avatar file
                if ($avatarPath) {
                    $oldFile = $root . '/' . ltrim($avatarPath, '/');
                    if (file_exists($oldFile)) unlink($oldFile);
                }
                $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $root . '/uploads/avatars/' . $filename)) {
                    $avatarPath = 'uploads/avatars/' . $filename;
                } else {
                    $error = 'Failed to upload image. Check directory permissions.';
                }
            }
        } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $error = 'Upload error code: ' . $_FILES['avatar']['error'];
        }

        if ($name && !$error) {
            $stmt = $db->prepare("UPDATE users SET name=?, phone=?, company=?, bio=?, avatar=? WHERE id=?");
            $stmt->execute([$name, $phone, $company, $bio, $avatarPath, $userId]);
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully.';
            logActivity('profile_update', 'User updated profile', [], 'info');
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        } elseif (!$name) {
            $error = 'Name is required.';
        }
    }
}
require __DIR__ . '/../includes/user-header.php';
?>
<style>
:root {
    --primary: #E8632A; --primary-dark: #d4551f; --bg-dark: #1a1a2e;
    --bg-white: #fff; --bg-light: #f8f9fb; --bg-gray: #f1f3f5;
    --border: #e8e8f0; --text-primary: #1a1a2e; --text-secondary: #4a4a6a;
    --text-muted: #8a8aaa; --radius-lg: 16px; --radius-md: 12px; --radius-sm: 8px;
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.06); --shadow-lg: 0 8px 40px rgba(0,0,0,0.12);
    --sidebar-width: 260px; --transition: 0.3s ease;
}

* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Inter', sans-serif; font-size: 16px; line-height: 1.6; color: var(--text-primary); background: var(--bg-light); }
a { color: var(--primary); text-decoration: none; }
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

/* PROFILE */
.profile-header { background: var(--bg-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 24px; }
.profile-cover { height: 160px; background: linear-gradient(135deg, var(--primary), #ff6b35); position: relative; }
.profile-info { display: flex; align-items: flex-end; gap: 24px; padding: 0 28px; margin-top: -50px; padding-bottom: 20px; position: relative; }
.profile-avatar-large { width: 100px; height: 100px; border-radius: 50%; border: 4px solid white; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 40px; box-shadow: var(--shadow-lg); min-width: 100px; }
.profile-details { flex: 1; padding-top: 50px; }
.profile-details h2 { font-size: 22px; font-weight: 800; }
.profile-details p { color: var(--text-secondary); margin-top: 2px; }
.profile-body { background: var(--bg-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 28px; }

/* FORM */
.form-group { margin-bottom: 20px; }
.form-label { display: block; font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px; }
.form-input, .form-textarea { width: 100%; padding: 12px 16px; border: 2px solid var(--border); border-radius: var(--radius-sm); font-family: 'Inter', sans-serif; font-size: 15px; color: var(--text-primary); background: var(--bg-white); transition: all var(--transition); outline: none; }
.form-input:focus, .form-textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(232,99,42,0.1); }
.form-input:disabled { background: var(--bg-light); color: var(--text-muted); cursor: not-allowed; }
.form-textarea { min-height: 120px; resize: vertical; }
.profile-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }

/* BUTTON */
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border: none; border-radius: var(--radius-md); font-family: 'Inter', sans-serif; font-size: 15px; font-weight: 600; cursor: pointer; transition: all var(--transition); text-decoration: none; line-height: 1.4; }
.btn-primary { background: var(--primary); color: white; }
.btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 8px 25px rgba(232,99,42,0.35); color: white; }

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
    .profile-info { flex-direction: column; align-items: center; text-align: center; padding: 0 20px; margin-top: -40px; }
    .profile-avatar-large { width: 80px; height: 80px; font-size: 32px; min-width: 80px; }
    .profile-details { padding-top: 0; }
    .profile-body { padding: 20px; }
    .profile-form-grid { grid-template-columns: 1fr; }
    .user-content { padding: 16px; }
}
</style>

<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Manage your personal information.</p>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><i data-lucide="check-circle" size="18"></i> <?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><i data-lucide="alert-circle" size="18"></i> <?= $error ?></div>
<?php endif; ?>

<div class="profile-header fade-in-up">
    <div class="profile-cover"></div>
    <div class="profile-info">
        <?php $avatarUrl = !empty($user['avatar']) ? BASE_URL . $user['avatar'] : ''; ?>
        <div class="profile-avatar-large" id="profile-avatar-display" style="overflow:hidden;<?= $avatarUrl ? "background:none" : '' ?>">
            <?php if ($avatarUrl): ?>
                <img src="<?= $avatarUrl ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover">
            <?php else: ?>
                <?= strtoupper(($user['name'] ?? 'U')[0]) ?>
            <?php endif; ?>
        </div>
        <div class="profile-details">
            <h2><?= htmlspecialchars($user['name'] ?? 'User') ?></h2>
            <p><?= htmlspecialchars($user['email'] ?? '') ?></p>
            <p style="font-size:13px;color:var(--text-muted);margin-top:8px">Member since <?= formatDate($user['created_at'] ?? date('Y-m-d')) ?></p>
        </div>
    </div>
</div>

<div class="profile-body reveal">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
        <div class="profile-form-grid">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+252 61 234 5678">
            </div>
            <div class="form-group">
                <label class="form-label">Company</label>
                <input type="text" name="company" class="form-input" value="<?= htmlspecialchars($user['company'] ?? '') ?>" placeholder="Your company">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Profile Photo</label>
            <div style="display:flex;align-items:center;gap:16px">
                <div style="width:64px;height:64px;border-radius:50%;overflow:hidden;background:var(--bg-gray);flex-shrink:0">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= $avatarUrl ?>" id="avatar-preview" style="width:100%;height:100%;object-fit:cover">
                    <?php else: ?>
                        <div id="avatar-preview" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-weight:700;font-size:24px"><?= strtoupper(($user['name'] ?? 'U')[0]) ?></div>
                    <?php endif; ?>
                </div>
                <input type="file" name="avatar" id="avatar-input" accept="image/jpeg,image/png,image/gif,image/webp" style="font-size:14px">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Bio</label>
            <textarea name="bio" class="form-textarea" placeholder="Tell us about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes <i data-lucide="check" size="16"></i></button>
    </form>
</div>

<script>
document.getElementById('avatar-input')?.addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(ev) {
        var preview = document.getElementById('avatar-preview');
        var display = document.getElementById('profile-avatar-display');
        if (preview.tagName === 'IMG') {
            preview.src = ev.target.result;
        } else {
            var img = document.createElement('img');
            img.src = ev.target.result;
            img.style.width = '100%'; img.style.height = '100%'; img.style.objectFit = 'cover';
            preview.parentNode.replaceChild(img, preview);
            img.id = 'avatar-preview';
        }
        if (display) {
            display.style.background = 'none';
            display.innerHTML = '<img src="'+ev.target.result+'" style="width:100%;height:100%;object-fit:cover">';
        }
    };
    reader.readAsDataURL(file);
});
</script>

<?php require __DIR__ . '/../includes/user-footer.php'; ?>
