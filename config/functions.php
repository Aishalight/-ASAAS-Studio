<?php
// ============================================================
// ASAAS STUDIO - Core Functions
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

// ============================================================
// SESSION MANAGEMENT
// ============================================================

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function setSession($key, $value) {
    $_SESSION[$key] = $value;
}

function getSession($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

function hasSession($key) {
    return isset($_SESSION[$key]);
}

function removeSession($key) {
    unset($_SESSION[$key]);
}

function destroySession() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

// ============================================================
// AUTHENTICATION
// ============================================================

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    startSession();
    return isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'superadmin');
}

function isSuperAdmin() {
    startSession();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'superadmin';
}

function requireAuth() {
    if (!isLoggedIn()) {
        setFlash('error', 'Please login to access this page.');
        redirect(BASE_URL . 'auth/login.php');
    }
}

function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        setFlash('error', 'Access denied. Admin privileges required.');
        redirect(BASE_URL . 'index.php');
    }
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? 0;
}

function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

function getCurrentUserEmail() {
    return $_SESSION['user_email'] ?? '';
}

function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? '';
}

function getCurrentUserAvatar() {
    return $_SESSION['user_avatar'] ?? '';
}

function updateLastActivity() {
    $userId = getCurrentUserId();
    if ($userId) {
        try {
            $db = Database::getInstance()->getConnection();
            $db->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?")->execute([$userId]);
        } catch (Exception $e) {}
    }
}

function isOnline($lastActivity) {
    if (empty($lastActivity)) return false;
    $diff = time() - strtotime($lastActivity);
    return $diff < 300;
}

// ============================================================
// CSRF PROTECTION
// ============================================================

function generateCSRFToken() {
    startSession();
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    return $token;
}

function getCSRFToken() {
    startSession();
    if (!isset($_SESSION['csrf_token']) || (time() - $_SESSION['csrf_token_time']) > 3600) {
        return generateCSRFToken();
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    startSession();
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    if ((time() - $_SESSION['csrf_token_time']) > 3600) {
        return false;
    }
    return true;
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . getCSRFToken() . '">';
}

// ============================================================
// FLASH MESSAGES
// ============================================================

function setFlash($type, $message) {
    startSession();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlashes() {
    startSession();
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function hasFlashes() {
    startSession();
    return !empty($_SESSION['flash']);
}

function displayFlashes() {
    $flashes = getFlashes();
    if (empty($flashes)) return '';
    $html = '<div class="flash-container">';
    foreach ($flashes as $flash) {
        $icon = match($flash['type']) {
            'success' => 'check-circle',
            'error' => 'alert-circle',
            'warning' => 'alert-triangle',
            default => 'info'
        };
        $html .= '<div class="flash-message flash-' . $flash['type'] . '">';
        $html .= '<i data-lucide="' . $icon . '" size="18"></i>';
        $html .= '<span>' . htmlspecialchars($flash['message']) . '</span>';
        $html .= '<button class="flash-close" onclick="this.parentElement.remove()">&times;</button>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

// ============================================================
// ACTIVITY LOGGING
// ============================================================

function logActivity($action, $description = '', $details = [], $severity = 'low') {
    $map = ['info' => 'low', 'warning' => 'medium'];
    if (isset($map[$severity])) $severity = $map[$severity];

    $db = Database::getInstance()->getConnection();
    $userId = getCurrentUserId() ?: null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $method = $_SERVER['REQUEST_METHOD'] ?? null;
    $url = $_SERVER['REQUEST_URI'] ?? null;

    $status = 'normal';
    $detection = detectSuspiciousActivity($action, $ip, $userId);
    if ($detection) {
        $status = $detection['status'];
        if ($detection['severity']) $severity = $detection['severity'];
    }

    $sql = "INSERT INTO activity_logs (user_id, action, description, details, ip_address, user_agent, request_method, request_url, severity, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$userId, $action, $description, json_encode($details), $ip, $ua, $method, $url, $severity, $status]);
    $logId = $db->lastInsertId();

    if ($status !== 'normal') {
        $stmt2 = $db->prepare("INSERT INTO alerts (log_id, type, title, description, severity, ip_address, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt2->execute([$logId, $detection['type'], $detection['title'], $description, $severity, $ip, $userId]);
    }

    // Auto-respond to threats
    if ($detection) {
        autoRespondToThreat($action, $userId, $ip, $detection);
    }

    return $logId;
}

function detectSuspiciousActivity($action, $ip, $userId) {
    $db = Database::getInstance()->getConnection();

    if ($action === 'LOGIN_FAILED') {
        $window = date('Y-m-d H:i:s', strtotime('-15 minutes'));
        $st = $db->prepare("SELECT COUNT(*) FROM activity_logs WHERE action = 'LOGIN_FAILED' AND ip_address = ? AND created_at >= ?");
        $st->execute([$ip, $window]);
        if ((int)$st->fetchColumn() >= 5) {
            return ['status' => 'blocked', 'severity' => 'critical', 'type' => 'brute_force', 'title' => 'Brute Force Attack Detected'];
        }
    }

    if ($ip) {
        $window = date('Y-m-d H:i:s', strtotime('-1 minute'));
        $st = $db->prepare("SELECT COUNT(*) FROM activity_logs WHERE ip_address = ? AND created_at >= ?");
        $st->execute([$ip, $window]);
        if ((int)$st->fetchColumn() >= 10) {
            return ['status' => 'suspicious', 'severity' => 'high', 'type' => 'rapid_requests', 'title' => 'Rapid Requests from Same IP'];
        }
    }

    if ($userId && in_array($action, ['DELETE_POST','DELETE_USER','DELETE_MEDIA','DELETE_RATING','DELETE_TESTIMONIAL'])) {
        $window = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $st = $db->prepare("SELECT COUNT(*) FROM activity_logs WHERE user_id = ? AND action IN ('DELETE_POST','DELETE_USER','DELETE_MEDIA','DELETE_RATING','DELETE_TESTIMONIAL') AND created_at >= ?");
        $st->execute([$userId, $window]);
        if ((int)$st->fetchColumn() >= 5) {
            return ['status' => 'suspicious', 'severity' => 'high', 'type' => 'admin_abuse', 'title' => 'Admin Abuse Detected - Excessive Deletions'];
        }
    }

    if ($action === 'UNAUTHORIZED_ACCESS') {
        return ['status' => 'blocked', 'severity' => 'critical', 'type' => 'unauthorized_access', 'title' => 'Unauthorized Access Attempt'];
    }

    if ($action === 'SUSPICIOUS_BEHAVIOR') {
        return ['status' => 'suspicious', 'severity' => 'high', 'type' => 'suspicious', 'title' => 'Suspicious Activity Detected'];
    }

    return null;
}

function getUnreadAlertCount() {
    $db = Database::getInstance()->getConnection();
    $r = $db->query("SELECT COUNT(*) FROM alerts WHERE is_read = 0")->fetchColumn();
    return (int)$r;
}

function markAlertsRead() {
    $db = Database::getInstance()->getConnection();
    $db->exec("UPDATE alerts SET is_read = 1 WHERE is_read = 0");
}

// ============================================================
// ACTION & RESPONSE SYSTEM
// ============================================================

function createActionLog($logId, $alertId, $actionType, $targetUserId = null, $targetIp = null, $details = []) {
    $db = Database::getInstance()->getConnection();
    $performedBy = getCurrentUserId() ?: null;
    $sql = "INSERT INTO action_logs (log_id, alert_id, action_type, action_details, performed_by, target_user_id, target_ip) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$logId, $alertId, $actionType, json_encode($details), $performedBy, $targetUserId, $targetIp]);
    return $db->lastInsertId();
}

function blockUser($userId, $reason = '') {
    $db = Database::getInstance()->getConnection();
    $db->prepare("UPDATE users SET status = 'banned' WHERE id = ?")->execute([$userId]);
    $user = $db->prepare("SELECT name, email FROM users WHERE id = ?");
    $user->execute([$userId]);
    $u = $user->fetch();
    logActivity('USER_BLOCKED', 'User blocked: ' . ($u['name'] ?? 'Unknown'), ['target_user_id' => $userId, 'reason' => $reason], 'medium');
    createNotification($userId, 'warning', 'Account Blocked', 'Your account has been blocked by an administrator.', BASE_URL . 'dashboard');
    $adminId = getCurrentUserId();
    if ($adminId) {
        createNotification($adminId, 'system', 'User Blocked', ($u['name'] ?? 'User') . ' has been blocked.', BASE_URL . 'admin-users');
    }
}

function unblockUser($userId) {
    $db = Database::getInstance()->getConnection();
    $db->prepare("UPDATE users SET status = 'active', locked_until = NULL, login_attempts = 0 WHERE id = ?")->execute([$userId]);
    $user = $db->prepare("SELECT name, email FROM users WHERE id = ?");
    $user->execute([$userId]);
    $u = $user->fetch();
    logActivity('USER_UNBLOCKED', 'User unblocked: ' . ($u['name'] ?? 'Unknown'), ['target_user_id' => $userId], 'low');
    createNotification($userId, 'success', 'Account Unblocked', 'Your account has been unblocked. You may now login.', BASE_URL . 'auth/login.php');
    $adminId = getCurrentUserId();
    if ($adminId) {
        createNotification($adminId, 'system', 'User Unblocked', ($u['name'] ?? 'User') . ' has been unblocked.', BASE_URL . 'admin-users');
    }
}

function blockIp($ip, $reason = '') {
    $db = Database::getInstance()->getConnection();
    $blockedBy = getCurrentUserId() ?: null;
    $st = $db->prepare("SELECT COUNT(*) FROM blocked_ips WHERE ip_address = ? AND is_active = 1");
    $st->execute([$ip]);
    if ((int)$st->fetchColumn() == 0) {
        $db->prepare("INSERT INTO blocked_ips (ip_address, reason, blocked_by) VALUES (?, ?, ?)")->execute([$ip, $reason, $blockedBy]);
    }
    logActivity('IP_BLOCKED', 'IP blocked: ' . $ip, ['target_ip' => $ip, 'reason' => $reason], 'high');
}

function unblockIp($ip) {
    $db = Database::getInstance()->getConnection();
    $db->prepare("UPDATE blocked_ips SET is_active = 0, unblocked_at = NOW() WHERE ip_address = ? AND is_active = 1")->execute([$ip]);
    logActivity('IP_UNBLOCKED', 'IP unblocked: ' . $ip, ['target_ip' => $ip], 'low');
}

function isIpBlocked($ip) {
    if (!$ip) return false;
    $db = Database::getInstance()->getConnection();
    $st = $db->prepare("SELECT COUNT(*) FROM blocked_ips WHERE ip_address = ? AND is_active = 1");
    $st->execute([$ip]);
    return (int)$st->fetchColumn() > 0;
}

function forceLogoutUser($userId) {
    $db = Database::getInstance()->getConnection();
    $db->prepare("DELETE FROM sessions WHERE user_id = ?")->execute([$userId]);
    $user = $db->prepare("SELECT name FROM users WHERE id = ?");
    $user->execute([$userId]);
    $u = $user->fetch();
    logActivity('USER_FORCE_LOGOUT', 'User force logged out: ' . ($u['name'] ?? 'Unknown'), ['target_user_id' => $userId], 'medium');
    createNotification($userId, 'warning', 'Session Terminated', 'Your session was terminated by an administrator. Please login again.', BASE_URL . 'auth/login.php');
}

function lockUserLogin($userId, $minutes = 15) {
    $db = Database::getInstance()->getConnection();
    $lockedUntil = date('Y-m-d H:i:s', time() + ($minutes * 60));
    $db->prepare("UPDATE users SET locked_until = ? WHERE id = ?")->execute([$lockedUntil, $userId]);
    $user = $db->prepare("SELECT name FROM users WHERE id = ?");
    $user->execute([$userId]);
    $u = $user->fetch();
    logActivity('LOGIN_LOCKED', 'Login locked for user: ' . ($u['name'] ?? 'Unknown') . " ($minutes min)", ['target_user_id' => $userId, 'duration' => $minutes], 'medium');
    createNotification($userId, 'warning', 'Login Temporarily Locked', 'Your login has been temporarily locked. Please try again later.', BASE_URL . 'auth/login.php');
}

function updateLogStatus($logId, $newStatus) {
    $db = Database::getInstance()->getConnection();
    $allowed = ['normal','suspicious','blocked','malicious','ignored'];
    if (!in_array($newStatus, $allowed)) return false;
    $old = $db->prepare("SELECT status, action, description, ip_address FROM activity_logs WHERE id = ?");
    $old->execute([$logId]);
    $log = $old->fetch();
    if (!$log) return false;
    $db->prepare("UPDATE activity_logs SET status = ? WHERE id = ?")->execute([$newStatus, $logId]);
    logActivity('LOG_STATUS_UPDATED', 'Log status changed: ' . $log['action'] . ' → ' . $newStatus, ['log_id' => $logId, 'old_status' => $log['status'], 'new_status' => $newStatus], 'low');
    createActionLog($logId, null, 'status_update_' . $newStatus, null, $log['ip_address'], ['old_status' => $log['status'], 'new_status' => $newStatus]);
    return true;
}

function updateAlertStatus($alertId, $newStatus) {
    $db = Database::getInstance()->getConnection();
    $allowed = ['new','acknowledged','resolved','reopened'];
    if (!in_array($newStatus, $allowed)) return false;
    $old = $db->prepare("SELECT status, title FROM alerts WHERE id = ?");
    $old->execute([$alertId]);
    $alert = $old->fetch();
    if (!$alert) return false;
    $db->prepare("UPDATE alerts SET status = ? WHERE id = ?")->execute([$newStatus, $alertId]);
    if ($newStatus === 'resolved') {
        $db->prepare("UPDATE alerts SET is_read = 1 WHERE id = ?")->execute([$alertId]);
    }
    logActivity('ALERT_STATUS_UPDATED', 'Alert status changed: ' . $alert['title'] . ' → ' . $newStatus, ['alert_id' => $alertId, 'old_status' => $alert['status'], 'new_status' => $newStatus], 'low');
    createActionLog(null, $alertId, 'alert_' . $newStatus, null, null, ['old_status' => $alert['status'], 'new_status' => $newStatus]);
    return true;
}

function getRelatedLogs($logId) {
    $db = Database::getInstance()->getConnection();
    $log = $db->prepare("SELECT user_id, ip_address FROM activity_logs WHERE id = ?");
    $log->execute([$logId]);
    $l = $log->fetch();
    if (!$l) return [];
    $results = [];
    if ($l['user_id']) {
        $st = $db->prepare("SELECT al.*, u.name as user_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id WHERE al.user_id = ? AND al.id != ? ORDER BY al.created_at DESC LIMIT 20");
        $st->execute([$l['user_id'], $logId]);
        $results = array_merge($results, $st->fetchAll());
    }
    if ($l['ip_address']) {
        $st = $db->prepare("SELECT al.*, u.name as user_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id WHERE al.ip_address = ? AND al.id != ? ORDER BY al.created_at DESC LIMIT 20");
        $st->execute([$l['ip_address'], $logId]);
        $ipResults = $st->fetchAll();
        $existingIds = array_column($results, 'id');
        foreach ($ipResults as $r) {
            if (!in_array($r['id'], $existingIds)) $results[] = $r;
        }
    }
    return $results;
}

function getUserActivityTimeline($userId, $limit = 50) {
    $db = Database::getInstance()->getConnection();
    $st = $db->prepare("SELECT al.*, u.name as user_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id WHERE al.user_id = ? ORDER BY al.created_at DESC LIMIT ?");
    $st->execute([$userId, $limit]);
    return $st->fetchAll();
}

function getIpActivityHistory($ip, $limit = 50) {
    $db = Database::getInstance()->getConnection();
    $st = $db->prepare("SELECT al.*, u.name as user_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id WHERE al.ip_address = ? ORDER BY al.created_at DESC LIMIT ?");
    $st->execute([$ip, $limit]);
    return $st->fetchAll();
}

function getActionHistory($logId = null, $alertId = null) {
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT al.*, u.name as performed_by_name FROM action_logs al LEFT JOIN users u ON al.performed_by = u.id WHERE ";
    $params = [];
    if ($logId) {
        $sql .= "al.log_id = ?";
        $params[] = $logId;
    } elseif ($alertId) {
        $sql .= "al.alert_id = ?";
        $params[] = $alertId;
    } else {
        return [];
    }
    $sql .= " ORDER BY al.created_at DESC";
    $st = $db->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function autoRespondToThreat($action, $userId, $ip, $detection) {
    if (!$detection) return;
    $db = Database::getInstance()->getConnection();

    if ($detection['type'] === 'brute_force' && $ip) {
        blockIp($ip, 'Auto-block: Brute force attack detected');
        if ($userId) {
            lockUserLogin($userId, 15);
        }
        createActionLog(null, null, 'auto_block_ip', $userId, $ip, ['detection_type' => 'brute_force', 'auto_response' => 'ip_blocked_login_locked']);
    }

    if ($detection['type'] === 'unauthorized_access' && $userId) {
        forceLogoutUser($userId);
        blockUser($userId, 'Auto-block: Unauthorized access attempt');
        createActionLog(null, null, 'auto_block_user', $userId, $ip, ['detection_type' => 'unauthorized_access', 'auto_response' => 'user_blocked_force_logout']);
    }

    if ($detection['type'] === 'rapid_requests' && $ip) {
        blockIp($ip, 'Auto-flag: Rapid requests detected');
        createActionLog(null, null, 'auto_flag_ip', $userId, $ip, ['detection_type' => 'rapid_requests', 'auto_response' => 'ip_flagged']);
    }

    if ($detection['type'] === 'admin_abuse' && $userId) {
        lockUserLogin($userId, 30);
        createActionLog(null, null, 'auto_lock_login', $userId, $ip, ['detection_type' => 'admin_abuse', 'auto_response' => 'login_locked_30min']);
    }
}

// ============================================================
// NOTIFICATIONS
// ============================================================

function createNotification($userId, $type, $title, $message = '', $link = '', $icon = '') {
    $db = Database::getInstance()->getConnection();
    if (empty($icon)) {
        $icon = match($type) {
            'success' => 'check-circle',
            'error' => 'alert-circle',
            'warning' => 'alert-triangle',
            'message' => 'message-square',
            'rating' => 'star',
            'system' => 'settings',
            default => 'bell'
        };
    }
    $sql = "INSERT INTO notifications (user_id, type, title, message, link, icon) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$userId, $type, $title, $message, $link, $icon]);
    return $db->lastInsertId();
}

function getUnreadNotificationCount($userId) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function getNotifications($userId, $limit = 10) {
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

// ============================================================
// RATE LIMITING
// ============================================================

function checkLoginAttempts($email) {
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT login_attempts, locked_until FROM users WHERE email = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) return true;

    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
        return false;
    }

    if ($user['locked_until'] && strtotime($user['locked_until']) <= time()) {
        $sql = "UPDATE users SET login_attempts = 0, locked_until = NULL WHERE email = ?";
        $db->prepare($sql)->execute([$email]);
    }

    return true;
}

function incrementLoginAttempts($email) {
    $db = Database::getInstance()->getConnection();
    $maxAttempts = MAX_LOGIN_ATTEMPTS;
    $lockoutDuration = LOCKOUT_DURATION;

    $sql = "UPDATE users SET login_attempts = login_attempts + 1 WHERE email = ?";
    $db->prepare($sql)->execute([$email]);

    $sql = "SELECT login_attempts FROM users WHERE email = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$email]);
    $attempts = $stmt->fetchColumn();

    if ($attempts >= $maxAttempts) {
        $lockedUntil = date('Y-m-d H:i:s', time() + ($lockoutDuration * 60));
        $sql = "UPDATE users SET locked_until = ? WHERE email = ?";
        $db->prepare($sql)->execute([$lockedUntil, $email]);
        return false;
    }

    return true;
}

function resetLoginAttempts($email) {
    $db = Database::getInstance()->getConnection();
    $sql = "UPDATE users SET login_attempts = 0, locked_until = NULL WHERE email = ?";
    $db->prepare($sql)->execute([$email]);
}

// ============================================================
// SANITIZATION & VALIDATION
// ============================================================

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    return strtolower($filename);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword($password) {
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password);
}

function validatePhone($phone) {
    return preg_match('/^[+]?[\d\s()-]{7,20}$/', $phone);
}

function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function truncate($text, $length = 150, $ellipsis = '...') {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $ellipsis;
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

function generateToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

// ============================================================
// DATE HELPERS
// ============================================================

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $time);
}

function formatDate($datetime, $format = 'M j, Y') {
    return date($format, strtotime($datetime));
}

function formatDateTime($datetime) {
    return date('M j, Y g:i A', strtotime($datetime));
}

function formatTimeAgo($datetime) {
    if (!$datetime) return 'N/A';
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j', $ts);
}

// ============================================================
// RESPONSE HELPERS
// ============================================================

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function jsonError($message, $statusCode = 400) {
    jsonResponse(['success' => false, 'error' => $message], $statusCode);
}

function jsonSuccess($data = [], $message = 'Success') {
    jsonResponse(['success' => true, 'message' => $message, 'data' => $data]);
}

// ============================================================
// SEO
// ============================================================

function getSEOTags($title, $description = '', $image = '', $url = '', $keywords = '') {
    $siteName = APP_NAME;
    $defaultDesc = 'ASAAS Studio is a digital studio based in Mogadishu, Somalia, designing and building websites, custom web systems, and digital experiences for businesses and organizations.';
    $description = $description ?: $defaultDesc;
    $defaultKeywords = 'ASAAS studio, web design Somalia, web development Somalia, custom web systems, websites Somalia, ASAAS studio Mogadishu';
    $keywords = $keywords ?: $defaultKeywords;

    $canonical = $url ?: APP_URL;
    if (!str_starts_with($canonical, 'http')) {
        $canonical = rtrim(APP_URL, '/') . '/' . ltrim($canonical, '/');
    }

    return '
    <title>' . htmlspecialchars($title) . ' | ' . $siteName . '</title>
    <meta name="description" content="' . htmlspecialchars($description) . '">
    <meta name="keywords" content="' . htmlspecialchars($keywords) . '">
    <link rel="canonical" href="' . htmlspecialchars($canonical) . '">
    <meta property="og:title" content="' . htmlspecialchars($title) . '">
    <meta property="og:description" content="' . htmlspecialchars($description) . '">
    <meta property="og:type" content="website">
    <meta property="og:url" content="' . htmlspecialchars($canonical) . '">
    <meta property="og:image" content="' . htmlspecialchars($image ?: APP_URL . '/assets/images/og-image.png') . '">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="' . htmlspecialchars($title) . '">
    <meta name="twitter:description" content="' . htmlspecialchars($description) . '">
    ';
}

// ============================================================
// MEDIA
// ============================================================

function getMediaIcon($type) {
    $icons = [
        'image' => 'image',
        'video' => 'video',
        'audio' => 'music',
        'document' => 'file-text',
        'archive' => 'archive',
        'pdf' => 'file'
    ];
    return $icons[$type] ?? 'file';
}

function getFileType($mimeType) {
    $types = [
        'image/jpeg' => 'image', 'image/png' => 'image', 'image/gif' => 'image',
        'image/webp' => 'image', 'image/svg+xml' => 'image',
        'video/mp4' => 'video', 'video/webm' => 'video',
        'audio/mpeg' => 'audio', 'audio/wav' => 'audio',
        'application/pdf' => 'pdf',
        'application/zip' => 'archive', 'application/x-rar-compressed' => 'archive',
    ];
    return $types[$mimeType] ?? 'document';
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < 3) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

function getFileInfoIcon($mimeType) {
    if (!$mimeType) return 'file';
    if (strpos($mimeType, 'image/') === 0) return 'image';
    if (strpos($mimeType, 'video/') === 0) return 'film';
    if (strpos($mimeType, 'audio/') === 0) return 'music';
    if (strpos($mimeType, 'pdf') !== false) return 'file-text';
    if (strpos($mimeType, 'zip') !== false || strpos($mimeType, 'archive') !== false) return 'archive';
    if (strpos($mimeType, 'word') !== false || strpos($mimeType, 'document') !== false) return 'file-text';
    if (strpos($mimeType, 'sheet') !== false || strpos($mimeType, 'excel') !== false) return 'table';
    return 'file';
}

// ============================================================
// SETTINGS
// ============================================================

function getSetting($key, $default = '') {
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT setting_value FROM settings WHERE setting_key = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return $value !== false ? $value : $default;
}

function updateSetting($key, $value) {
    $db = Database::getInstance()->getConnection();
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    $stmt = $db->prepare($sql);
    return $stmt->execute([$key, $value]);
}

// ============================================================
// RATING HELPERS
// ============================================================

function getAverageRating($itemId, $itemType) {
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT ROUND(AVG(rating), 1) as avg, COUNT(*) as count
            FROM ratings WHERE item_id = ? AND item_type = ? AND is_approved = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([$itemId, $itemType]);
    return $stmt->fetch();
}

function renderStars($rating, $size = 18, $interactive = false) {
    $full = floor($rating);
    $half = $rating - $full >= 0.5;
    $html = '<div class="stars-container' . ($interactive ? ' stars-interactive' : '') . '" data-size="' . $size . '">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $html .= '<i data-lucide="star" class="star star-full" fill="currentColor" width="' . $size . '" height="' . $size . '"></i>';
        } elseif ($i == $full + 1 && $half) {
            $html .= '<div class="star-half-container"><i data-lucide="star" class="star star-half-bg" width="' . $size . '" height="' . $size . '"></i><i data-lucide="star" class="star star-half" fill="currentColor" width="' . $size . '" height="' . $size . '"></i></div>';
        } else {
            $html .= '<i data-lucide="star" class="star star-empty" width="' . $size . '" height="' . $size . '"></i>';
        }
    }
    $html .= '</div>';
    return $html;
}

// ============================================================
// ANALYTICS / PAGE VISITS
// ============================================================

function detectDeviceType($ua) {
    if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) return 'tablet';
    if (preg_match('/mobile|iphone|ipod|android.*mobile|windows phone|blackberry/i', $ua)) return 'mobile';
    return 'desktop';
}

function detectBrowser($ua) {
    if (preg_match('/Edg\//i', $ua)) return 'Edge';
    if (preg_match('/OPR\//i', $ua)) return 'Opera';
    if (preg_match('/Firefox\//i', $ua)) return 'Firefox';
    if (preg_match('/Chrome\//i', $ua)) return 'Chrome';
    if (preg_match('/Safari\//i', $ua)) return 'Safari';
    return 'Other';
}

function detectOS($ua) {
    if (preg_match('/windows nt 10/i', $ua)) return 'Windows 10';
    if (preg_match('/windows nt 11/i', $ua)) return 'Windows 11';
    if (preg_match('/macintosh|mac os x/i', $ua)) return 'macOS';
    if (preg_match('/linux/i', $ua)) return 'Linux';
    if (preg_match('/android/i', $ua)) {
        if (preg_match('/android ([\d.]+)/i', $ua, $m)) return 'Android ' . $m[1];
        return 'Android';
    }
    if (preg_match('/(iphone|ipad).*os ([\d_]+)/i', $ua, $m)) return 'iOS ' . str_replace('_', '.', $m[2]);
    return 'Other';
}

function trackPageVisit($pageUrl) {
    try {
        $db = Database::getInstance()->getConnection();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';

        $deviceType = detectDeviceType($ua);
        $browser = detectBrowser($ua);
        $os = detectOS($ua);

        $sql = "INSERT INTO page_visits (page_url, visitor_ip, user_agent, device_type, browser, os, referrer) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$pageUrl, $ip, $ua, $deviceType, $browser, $os, $referrer]);
    } catch (Exception $e) {
        // Silently fail: tracking should never break the page
    }
}

function getVisitStats($period = 'all') {
    $db = Database::getInstance()->getConnection();
    $dateFilter = '';
    switch ($period) {
        case 'today': $dateFilter = 'WHERE visit_date = CURDATE()'; break;
        case 'week': $dateFilter = 'WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)'; break;
        case 'month': $dateFilter = 'WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)'; break;
        default: $dateFilter = '';
    }

    $total = $db->query("SELECT COUNT(*) as count FROM page_visits $dateFilter")->fetch()['count'];
    $today = $db->query("SELECT COUNT(*) as count FROM page_visits WHERE visit_date = CURDATE()")->fetch()['count'];
    $yesterday = $db->query("SELECT COUNT(*) as count FROM page_visits WHERE visit_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)")->fetch()['count'];
    $unique = $db->query("SELECT COUNT(DISTINCT visitor_ip) as count FROM page_visits $dateFilter")->fetch()['count'];

    $growth = $yesterday > 0 ? round((($today - $yesterday) / $yesterday) * 100, 1) : ($today > 0 ? 100 : 0);

    return ['total' => $total, 'today' => $today, 'yesterday' => $yesterday, 'unique' => $unique, 'growth' => $growth];
}

function getVisitsByDevice($period = 'all') {
    $db = Database::getInstance()->getConnection();
    $dateFilter = $period === 'all' ? '' : "WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL $period DAY)";
    return $db->query("SELECT device_type, COUNT(*) as count FROM page_visits $dateFilter GROUP BY device_type ORDER BY count DESC")->fetchAll();
}

function getVisitsByBrowser($period = 'all') {
    $db = Database::getInstance()->getConnection();
    $dateFilter = $period === 'all' ? '' : "WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL $period DAY)";
    return $db->query("SELECT browser, COUNT(*) as count FROM page_visits $dateFilter GROUP BY browser ORDER BY count DESC")->fetchAll();
}

function getVisitsByOS($period = 'all') {
    $db = Database::getInstance()->getConnection();
    $dateFilter = $period === 'all' ? '' : "WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL $period DAY)";
    return $db->query("SELECT os, COUNT(*) as count FROM page_visits $dateFilter GROUP BY os ORDER BY count DESC")->fetchAll();
}

function getVisitsByPage($period = 'all', $limit = 10) {
    $db = Database::getInstance()->getConnection();
    $dateFilter = $period === 'all' ? '' : "WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL $period DAY)";
    $stmt = $db->prepare("SELECT page_url, COUNT(*) as count FROM page_visits $dateFilter GROUP BY page_url ORDER BY count DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getDailyVisits($days = 30) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT visit_date, COUNT(*) as count
        FROM page_visits
        WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY visit_date
        ORDER BY visit_date ASC
    ");
    $stmt->execute([$days]);
    return $stmt->fetchAll();
}

// ============================================================
// STARS CSS (INITIALIZATION)
// ============================================================

function getAvatarUrl($avatar, $name = '') {
    if ($avatar && file_exists(__DIR__ . '/../' . $avatar)) {
        return BASE_URL . $avatar;
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=E8632A&color=fff&size=200';
}
