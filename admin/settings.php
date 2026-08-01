<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Settings'; require __DIR__ . '/../includes/admin-header.php';

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $success = 'Invalid security token.';
    } else {
        foreach ($_POST as $key => $value) {
            if ($key !== 'csrf_token') {
                updateSetting($key, sanitize($value));
            }
        }
        $success = 'Settings updated successfully.';
        logActivity('settings_update', 'Admin updated site settings', [], 'info');
    }
}
?>
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Manage site configuration</p>
    </div>
</div>

<?php if ($success): ?><div class="alert alert-success"><i data-lucide="check-circle" size="18"></i> <?= $success ?></div><?php endif; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

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
        <h3 style="font-size:18px;font-weight:700;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--border)">Homepage</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div class="form-group" style="grid-column:1/-1"><label class="form-label">Trusted By Client Names</label><input type="text" name="trusted_clients" class="form-input" value="<?= htmlspecialchars(getSetting('trusted_clients', 'TechVolve,GreenLeaf,Pulse,FinFlow,Bloom,CloudBase')) ?>"><p style="font-size:12px;color:var(--text-muted);margin-top:4px">Comma-separated list of company names shown in the "Trusted by teams at" section on the homepage.</p></div>
        </div>
    </div>

    <div class="reveal" style="background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:32px;margin-bottom:24px">
        <h3 style="font-size:18px;font-weight:700;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--border)">Social Media</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div class="form-group"><label class="form-label">Twitter URL</label><input type="url" name="social_twitter" class="form-input" value="<?= htmlspecialchars(getSetting('social_twitter')) ?>"></div>
            <div class="form-group"><label class="form-label">Instagram URL</label><input type="url" name="social_instagram" class="form-input" value="<?= htmlspecialchars(getSetting('social_instagram')) ?>"></div>
            <div class="form-group"><label class="form-label">LinkedIn URL</label><input type="url" name="social_linkedin" class="form-input" value="<?= htmlspecialchars(getSetting('social_linkedin')) ?>"></div>
            <div class="form-group"><label class="form-label">GitHub URL</label><input type="url" name="social_github" class="form-input" value="<?= htmlspecialchars(getSetting('social_github')) ?>"></div>
            <div class="form-group"><label class="form-label">WhatsApp Number</label><input type="tel" name="social_whatsapp" class="form-input" value="<?= htmlspecialchars(getSetting('social_whatsapp')) ?>" placeholder="+252 61 234 5678"><p style="font-size:12px;color:var(--text-muted);margin-top:4px">Include country code. E.g. +252612345678</p></div>
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
