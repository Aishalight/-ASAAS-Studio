<?php
// ============================================================
// ASAAS STUDIO - Application Configuration
// ============================================================

define('APP_NAME', 'ASAAS STUDIO');
define('APP_URL', 'https://asaas-studio.tech');
define('BASE_URL', '/');
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('APP_VERSION', '1.0.0');

// Timezone (change to your local timezone, e.g., 'Africa/Lagos', 'America/New_York', 'Europe/London')
date_default_timezone_set('Africa/Lagos');

// Session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// Upload
define('UPLOAD_PATH', __DIR__ . '/../uploads');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,webp,pdf,doc,docx,zip,mp4');

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Security
define('BCRYPT_COST', 12);
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 7200); // 2 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 15); // minutes

// Notification types
define('NOTIFICATION_TYPES', [
    'info' => 'info',
    'success' => 'success',
    'warning' => 'warning',
    'error' => 'error',
    'message' => 'message',
    'rating' => 'rating',
    'system' => 'system'
]);

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
