<?php
require_once __DIR__ . '/../config/functions.php';
startSession();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = sanitize($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!checkLoginAttempts($login)) {
        $error = 'Account locked. Too many login attempts. Please try again in ' . LOCKOUT_DURATION . ' minutes.';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM users WHERE email = ? OR username = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$login, $login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'inactive' || $user['status'] === 'banned') {
                    $error = 'Your account has been deactivated. Please contact support.';
                } else {
                    startSession();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_avatar'] = $user['avatar'];

                    resetLoginAttempts($login);

                    $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
                    $db->prepare($sql)->execute([$user['id']]);

                    logActivity('login', 'User logged in', ['email' => $login], 'info');

                    if ($user['role'] === 'admin' || $user['role'] === 'superadmin') {
                        redirect(BASE_URL . 'admin');
                    }
                    redirect(BASE_URL . 'dashboard');
                }
            } else {
                incrementLoginAttempts($login);
                $error = 'Invalid credentials.';
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
    <title>Sign In | <?= APP_NAME ?></title>
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
            background: linear-gradient(135deg, var(--bg-dark), #1a1a2e);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .auth-hero-side::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: var(--primary);
            opacity: 0.1;
            top: -100px;
            right: -100px;
        }
        .auth-hero-side::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: var(--primary);
            opacity: 0.08;
            bottom: -50px;
            left: -50px;
        }
        .auth-hero-content {
            position: relative;
            z-index: 1;
        }
        .auth-hero-side h2 {
            color: white;
            margin-bottom: 16px;
        }
        .auth-hero-side p {
            color: var(--text-light);
            font-size: 15px;
            line-height: 1.7;
        }
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 24px 0;
        }
        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        .auth-divider span {
            font-size: 13px;
            color: var(--text-muted);
        }
        @media (max-width: 768px) {
            .auth-container {
                grid-template-columns: 1fr;
            }
            .auth-hero-side {
                display: none;
            }
            .auth-form-side {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-container fade-in-up">
            <div class="auth-form-side">
                <a href="<?= BASE_URL ?>home" class="auth-brand">
                    <img src="<?= BASE_URL ?>uploads/logo2_blackbackground.png" alt="ASAAS" style="height:32px;width:auto">
                    <?= APP_NAME ?>
                </a>

                <div style="margin-bottom:32px">
                    <h2 style="margin-bottom:8px">Welcome back</h2>
                    <p style="color:var(--text-muted)">Sign in to your account to continue.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><i data-lucide="alert-circle" size="18"></i> <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" data-validate>
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <div class="form-group">
                        <label class="form-label">Email / Username</label>
                        <input type="text" name="login" class="form-input" placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
                        <div class="form-error"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                        <div class="form-error"></div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
                        <label class="form-checkbox">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        <a href="<?= BASE_URL ?>forgot-password" style="font-size:14px">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Sign In <i data-lucide="arrow-right" size="18"></i>
                    </button>
                </form>

                <div class="auth-divider"><span>OR</span></div>

                <p style="text-align:center;font-size:14px;color:var(--text-muted)">
                    Don't have an account? <a href="<?= BASE_URL ?>register" style="font-weight:600">Create one</a>
                </p>
            </div>

            <div class="auth-hero-side">
                <div class="auth-hero-content">
                    <div style="font-size:64px;margin-bottom:24px">🚀</div>
                    <h2>Build Something Amazing</h2>
                    <p>Access your dashboard, manage projects, track progress, and collaborate with our team, all in one place.</p>
                    <div style="margin-top:32px;display:flex;gap:12px;justify-content:center">
                        <div style="width:8px;height:8px;border-radius:50%;background:var(--primary)"></div>
                        <div style="width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.3)"></div>
                        <div style="width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.3)"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= BASE_URL ?>assets/js/animations.js"></script>
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
