<?php
require_once __DIR__ . '/../config/functions.php';
startSession();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (!validatePassword($password)) {
        $error = 'Password must be at least 8 characters with uppercase, lowercase, and a number.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'An account with this email already exists.';
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
                $sql = "INSERT INTO users (name, email, password, role, status, email_verified_at) VALUES (?, ?, ?, 'user', 'active', NOW())";
                $stmt = $db->prepare($sql);
                $stmt->execute([$name, $email, $hashed]);

                $userId = $db->lastInsertId();
                logActivity('register', 'New user registered', ['email' => $email], 'info');

                // Auto login
                startSession();
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'user';

                redirect(BASE_URL . 'dashboard');
            }
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | <?= APP_NAME ?></title>
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
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-light);
            padding: 24px;
        }
        .auth-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1000px;
            width: 100%;
            background: var(--bg-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            min-height: 600px;
        }
        .auth-form-side {
            padding: 48px;
            display: flex;
            flex-direction: column;
        }
        .auth-brand {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .auth-brand-icon {
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }
        .auth-hero-side {
            background: linear-gradient(135deg, var(--primary), #ff6b35);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px;
            color: white;
            text-align: center;
        }
        .auth-hero-side h2 { color: white; margin-bottom: 16px; }
        .auth-hero-side p { color: rgba(255,255,255,0.85); font-size: 15px; line-height: 1.7; }
        @media (max-width: 768px) {
            .auth-container { grid-template-columns: 1fr; }
            .auth-hero-side { display: none; }
            .auth-form-side { padding: 32px 24px; }
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-container fade-in-up">
            <div class="auth-hero-side">
                <div style="font-size:64px;margin-bottom:24px">✨</div>
                <h2>Join ASAAS Studio</h2>
                <p>Create your account to access client portal, track projects, manage messages, and collaborate with our creative team.</p>
                <div style="margin-top:32px;display:flex;gap:12px;justify-content:center">
                    <div style="width:8px;height:8px;border-radius:50%;background:white"></div>
                    <div style="width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.3)"></div>
                    <div style="width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.3)"></div>
                </div>
            </div>
            <div class="auth-form-side">
                <a href="<?= BASE_URL ?>home" class="auth-brand">
                    <img src="<?= BASE_URL ?>assets/images/logo2_blackbackground.png" alt="ASAAS" style="height:32px;width:auto">
                    <?= APP_NAME ?>
                </a>
                <div style="margin-bottom:32px">
                    <h2 style="margin-bottom:8px">Create your account</h2>
                    <p style="color:var(--text-muted)">Start your journey with ASAAS Studio.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><i data-lucide="alert-circle" size="18"></i> <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" data-validate>
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-input" placeholder="John Doe" required>
                        <div class="form-error"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" placeholder="you@example.com" required>
                        <div class="form-error"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" placeholder="At least 8 characters" required>
                        <div class="form-hint">Must be 8+ chars with uppercase, lowercase, and number</div>
                        <div class="form-error"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-input" placeholder="Repeat your password" required>
                        <div class="form-error"></div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Create Account <i data-lucide="arrow-right" size="18"></i>
                    </button>
                </form>

                <div class="auth-divider"><span>OR</span></div>

                <p style="text-align:center;font-size:14px;color:var(--text-muted)">
                    Already have an account? <a href="<?= BASE_URL ?>login" style="font-weight:600">Sign in</a>
                </p>
            </div>
        </div>
    </div>
    <script src="<?= BASE_URL ?>assets/js/animations.js"></script>
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
