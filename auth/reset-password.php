<?php
require_once __DIR__ . '/../config/functions.php';
startSession();

$message = '';
$messageType = 'info';
$token = $_GET['token'] ?? $_POST['token'] ?? '';
$email = $_GET['email'] ?? $_POST['email'] ?? '';
$showForm = false;

if (!$token || !$email) {
    $message = 'Invalid reset link.';
    $messageType = 'error';
} else {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT pr.id, pr.expires_at, u.id as user_id FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.user_id = (SELECT id FROM users WHERE email = ?) AND pr.expires_at > NOW() ORDER BY pr.created_at DESC LIMIT 1");
    $stmt->execute([$email]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $message = 'This reset link is invalid or has expired.';
        $messageType = 'error';
    } else {
        $showForm = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $showForm) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token.';
        $messageType = 'error';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!$password || $password !== $confirmPassword) {
            $message = 'Passwords do not match.';
            $messageType = 'error';
        } elseif (!validatePassword($password)) {
            $message = 'Password must be at least 8 characters with uppercase, lowercase, and a number.';
            $messageType = 'error';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $reset['user_id']]);
            $db->prepare("DELETE FROM password_resets WHERE id = ?")->execute([$reset['id']]);
            logActivity('password_reset_complete', 'Password reset completed', ['user_id' => $reset['user_id']], 'info');
            $message = 'Your password has been reset successfully! You can now sign in.';
            $messageType = 'success';
            $showForm = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | <?= APP_NAME ?></title>
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
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/animations.css">
    <script src="https://unpkg.com/lucide@0.460.0" integrity="sha256-GyLGwEocabdaQcZMfqmSZX6PYo2r1jJJhP/GHDdhpWo=" crossorigin="anonymous"></script>
    <style>
        .auth-page { min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--bg-light);padding:24px; }
        .auth-card { max-width:440px;width:100%;background:var(--bg-white);border-radius:var(--radius-xl);padding:48px;box-shadow:var(--shadow-lg); }
        .auth-brand { font-size:24px;font-weight:800;margin-bottom:40px;display:flex;align-items:center;gap:10px; }
        @media (max-width:480px) { .auth-card { padding:32px 24px; } }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card fade-in-up">
            <a href="<?= BASE_URL ?>home" class="auth-brand">
                <img src="<?= BASE_URL ?>uploads/logo2_blackbackground.png" alt="ASAAS" style="height:32px;width:auto">
                <?= APP_NAME ?>
            </a>
            <div style="margin-bottom:32px">
                <h2 style="margin-bottom:8px">Set new password</h2>
                <p style="color:var(--text-muted)">Choose a strong password for your account.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><i data-lucide="<?= $messageType === 'success' ? 'check-circle' : 'info' ?>" size="18"></i> <?= $message ?></div>
            <?php endif; ?>

            <?php if ($showForm): ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Min 8 chars, uppercase, lowercase, number" required minlength="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-input" placeholder="Re-enter password" required minlength="8">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Reset Password <i data-lucide="check" size="16"></i></button>
            </form>
            <?php endif; ?>

            <div style="margin-top:24px;text-align:center">
                <a href="<?= BASE_URL ?>login" style="font-size:14px;font-weight:600">Back to sign in</a>
            </div>
        </div>
    </div>
    <script src="<?= BASE_URL ?>assets/js/animations.js"></script>
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>