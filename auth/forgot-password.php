<?php
require_once __DIR__ . '/../config/functions.php';
startSession();

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        } else {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $token = generateToken(64);
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $db->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);
                $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)")
                    ->execute([$user['id'], password_hash($token, PASSWORD_BCRYPT), $expires]);

                $resetLink = APP_URL . '/reset-password?token=' . $token . '&email=' . urlencode($email);

                $emailSubject = 'Password Reset Request - ' . APP_NAME;
                $emailBody = "Hello " . htmlspecialchars($user['name']) . ",\n\n";
                $emailBody .= "We received a request to reset your password. Click the link below to set a new password:\n\n";
                $emailBody .= $resetLink . "\n\n";
                $emailBody .= "This link will expire in 1 hour.\n\n";
                $emailBody .= "If you didn't request this, please ignore this email.\n\n";
                $emailBody .= "Best regards,\n" . APP_NAME;

                $headers = 'From: ' . APP_NAME . ' <noreply@asaas-studio.tech>';
                @mail($email, $emailSubject, $emailBody, $headers);

                logActivity('password_reset_request', 'Password reset link sent', ['email' => $email], 'info');
            }

            $message = 'If an account with that email exists, we have sent a password reset link.';
            $messageType = 'info';
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
        .auth-brand-icon { width:36px;height:36px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:16px; }
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
                <h2 style="margin-bottom:8px">Reset password</h2>
                <p style="color:var(--text-muted)">Enter your email and we'll send you a reset link.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><i data-lucide="info" size="18"></i> <?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="you@example.com" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send Reset Link <i data-lucide="send" size="16"></i></button>
            </form>

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