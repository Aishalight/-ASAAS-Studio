<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Settings'; require __DIR__ . '/../includes/admin-header.php';

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $success = 'Invalid security token.';
    } else {
        $team = [];
        for ($i = 1; $i <= 2; $i++) {
            $name = trim($_POST["team_member_name_$i"] ?? '');
            $role = trim($_POST["team_member_role_$i"] ?? '');
            $desc = trim($_POST["team_member_desc_$i"] ?? '');
            $img = trim($_POST["team_member_img_$i"] ?? '');
            if (!empty($_FILES["team_member_photo_$i"]['tmp_name']) && $_FILES["team_member_photo_$i"]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES["team_member_photo_$i"];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (in_array($ext, $allowed)) {
                    $filename = 'team_' . time() . '_' . $i . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $uploadDir = __DIR__ . '/../uploads';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
                        $img = 'uploads/' . $filename;
                    }
                }
            }
            if ($name || $role || $desc || $img) {
                $team[] = ['name' => $name, 'role' => $role, 'desc' => $desc, 'img' => $img];
            }
            unset($_POST["team_member_name_$i"], $_POST["team_member_role_$i"], $_POST["team_member_desc_$i"], $_POST["team_member_img_$i"]);
        }
        updateSetting('team_members', json_encode($team));
        foreach ($_POST as $key => $value) {
            if ($key !== 'csrf_token') {
                updateSetting($key, sanitize($value));
            }
        }
        $success = 'Settings updated successfully.';
        logActivity('settings_update', 'Admin updated site settings', [], 'info');
    }
}

$teamMembers = [];
$teamJson = getSetting('team_members', '');
if ($teamJson) {
    $decoded = json_decode($teamJson, true);
    if (is_array($decoded)) $teamMembers = $decoded;
}
?>
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Manage site configuration</p>
    </div>
</div>

<?php if ($success): ?><div class="alert alert-success"><i data-lucide="check-circle" size="18"></i> <?= $success ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

    <div class="reveal" style="background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:32px;margin-bottom:24px">
        <h3 style="font-size:18px;font-weight:700;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--border)">Team Members</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:24px">Shown in the "The People Behind ASAAS" section on the About page. Leave a name empty to hide that card.</p>
        <?php for ($i = 0; $i < 2; $i++): ?>
            <?php $m = $teamMembers[$i] ?? ['name' => '', 'role' => '', 'desc' => '', 'img' => '']; $n = $i + 1; ?>
            <div style="border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;margin-bottom:16px">
                <h4 style="font-size:14px;font-weight:700;margin-bottom:16px;color:var(--text-muted)">Member <?= $n ?></h4>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Name</label><input type="text" name="team_member_name_<?= $n ?>" class="form-input" value="<?= htmlspecialchars($m['name']) ?>"></div>
                    <div class="form-group"><label class="form-label">Role</label><input type="text" name="team_member_role_<?= $n ?>" class="form-input" value="<?= htmlspecialchars($m['role']) ?>" placeholder="e.g. Co-Founder"></div>
                </div>
                <div class="form-group"><label class="form-label">Description</label><input type="text" name="team_member_desc_<?= $n ?>" class="form-input" value="<?= htmlspecialchars($m['desc'] ?? '') ?>" placeholder="e.g. Product, Design & Development"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:center">
                    <div class="form-group"><label class="form-label">Photo</label><input type="file" name="team_member_photo_<?= $n ?>" class="form-input" accept="image/*"></div>
                    <?php if (!empty($m['img'])): ?>
                        <div style="display:flex;align-items:center;gap:12px">
                            <img src="<?= strpos($m['img'], 'uploads/') === 0 ? BASE_URL . $m['img'] : $m['img'] ?>" alt="Member <?= $n ?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:1px solid var(--border)">
                            <input type="hidden" name="team_member_img_<?= $n ?>" value="<?= htmlspecialchars($m['img']) ?>">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endfor; ?>
    </div>

    <div class="reveal" style="background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:32px;margin-bottom:24px">
        <h3 style="font-size:18px;font-weight:700;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--border)">General Settings</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div class="form-group"><label class="form-label">Site Name</label><input type="text" name="site_name" class="form-input" value="<?= htmlspecialchars(getSetting('site_name', APP_NAME)) ?>"></div>
            <div class="form-group"><label class="form-label">Site Email</label><input type="email" name="site_email" class="form-input" value="<?= htmlspecialchars(getSetting('site_email')) ?>"></div>
            <div class="form-group" style="grid-column:1/-1"><label class="form-label">Site Description</label><textarea name="site_description" class="form-textarea" rows="3"><?= htmlspecialchars(getSetting('site_description')) ?></textarea></div>
            <div class="form-group"><label class="form-label">Phone</label><input type="text" name="site_phone" class="form-input" value="<?= htmlspecialchars(getSetting('site_phone')) ?>"></div>
            <div class="form-group"><label class="form-label">Address</label><input type="text" name="site_address" class="form-input" value="<?= htmlspecialchars(getSetting('site_address')) ?>"></div>
        </div>
    </div>

    <div class="reveal" style="background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:32px;margin-bottom:24px">
        <h3 style="font-size:18px;font-weight:700;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--border)">Social Media</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div class="form-group"><label class="form-label">Twitter URL</label><input type="url" name="social_twitter" class="form-input" value="<?= htmlspecialchars(getSetting('social_twitter')) ?>"></div>
            <div class="form-group"><label class="form-label">Instagram URL</label><input type="url" name="social_instagram" class="form-input" value="<?= htmlspecialchars(getSetting('social_instagram')) ?>"></div>
            <div class="form-group"><label class="form-label">LinkedIn URL</label><input type="url" name="social_linkedin" class="form-input" value="<?= htmlspecialchars(getSetting('social_linkedin')) ?>"></div>
            <div class="form-group"><label class="form-label">GitHub URL</label><input type="url" name="social_github" class="form-input" value="<?= htmlspecialchars(getSetting('social_github')) ?>"></div>
            <div class="form-group"><label class="form-label">WhatsApp Number</label><input type="tel" name="social_whatsapp" class="form-input" value="<?= htmlspecialchars(getSetting('social_whatsapp')) ?>" placeholder="+252XXXXXXXXX"><p style="font-size:12px;color:var(--text-muted);margin-top:4px">Include country code. E.g. +252612345678</p></div>
        </div>
    </div>

    <div class="reveal" style="background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:32px;margin-bottom:24px">
        <h3 style="font-size:18px;font-weight:700;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--border)">Security</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div class="form-group"><label class="form-label">Max Login Attempts</label><input type="number" name="max_login_attempts" class="form-input" value="<?= getSetting('max_login_attempts', '5') ?>"></div>
            <div class="form-group"><label class="form-label">Lockout Duration (minutes)</label><input type="number" name="lockout_duration" class="form-input" value="<?= getSetting('lockout_duration', '15') ?>"></div>
        </div>
    </div>

    <div class="reveal" style="background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:32px;margin-bottom:24px">
        <h3 style="font-size:18px;font-weight:700;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--border)">Appearance</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div class="form-group"><label class="form-label">Primary Color</label><input type="color" name="theme_color" class="form-input" style="height:48px;padding:4px" value="<?= getSetting('theme_color', '#E8632A') ?>"></div>
            <div class="form-group"><label class="form-label">Maintenance Mode</label><select class="form-select"><option value="0" <?= getSetting('maintenance_mode') === '0' ? 'selected' : '' ?>>Disabled</option><option value="1" <?= getSetting('maintenance_mode') === '1' ? 'selected' : '' ?>>Enabled</option></select></div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">Save All Settings <i data-lucide="check" size="18"></i></button>
</form>

<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body></html>
