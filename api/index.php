<?php
// ============================================================
// ASAAS STUDIO - API Endpoint Router
// ============================================================

require_once __DIR__ . '/../config/functions.php';
startSession();

header('Content-Type: application/json');
$allowedOrigin = (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === APP_URL) ? APP_URL : BASE_URL;
header("Access-Control-Allow-Origin: $allowedOrigin");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    $db = Database::getInstance()->getConnection();

    switch ($action) {
        // ============================================================
        // NOTIFICATIONS
        // ============================================================
        case 'unread_count':
            if (!isLoggedIn()) jsonError('Unauthorized', 401);
            $count = getUnreadNotificationCount(getCurrentUserId());
            jsonSuccess(['count' => (int)$count]);

        case 'get_notifications':
            if (!isLoggedIn()) jsonError('Unauthorized', 401);
            $notifs = getNotifications(getCurrentUserId(), $_GET['limit'] ?? 10);
            jsonSuccess(['notifications' => $notifs]);

        case 'mark_read':
            if (!isLoggedIn()) jsonError('Unauthorized', 401);
            $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
            if ($id) {
                $stmt = $db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, getCurrentUserId()]);
            }
            jsonSuccess([], 'Marked as read');

        case 'mark_all_read':
            if (!isLoggedIn()) jsonError('Unauthorized', 401);
            $db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ?")->execute([getCurrentUserId()]);
            jsonSuccess([], 'All marked as read');

        case 'mark_all_messages_read':
            if (!isLoggedIn()) jsonError('Unauthorized', 401);
            $threadId = (int)($_POST['thread_id'] ?? 0);
            $userId = getCurrentUserId();
            if ($threadId) {
                $db->prepare("UPDATE messages SET is_read = 1, read_at = NOW() WHERE thread_id = ? AND receiver_id = ? AND is_read = 0")->execute([$threadId, $userId]);
            } else {
                $db->prepare("UPDATE messages SET is_read = 1, read_at = NOW() WHERE receiver_id = ? AND is_read = 0")->execute([$userId]);
            }
            jsonSuccess([], 'Messages marked as read');

        case 'create_notification':
            if (!isAdmin()) jsonError('Unauthorized', 401);
            $userId = (int)($_POST['user_id'] ?? 0);
            $type = sanitize($_POST['type'] ?? 'info');
            $title = sanitize($_POST['title'] ?? '');
            $message = sanitize($_POST['message'] ?? '');
            $link = sanitize($_POST['link'] ?? '');
            if ($userId && $title) {
                createNotification($userId, $type, $title, $message, $link);
                jsonSuccess([], 'Notification created');
            }
            jsonError('Missing required fields');

        // ============================================================
        // MESSAGES
        // ============================================================
        case 'send_message':
            if (!isLoggedIn()) jsonError('Unauthorized', 401);
            $receiverId = (int)($_POST['receiver_id'] ?? 0);
            $message = sanitize($_POST['message'] ?? '');
            $subject = sanitize($_POST['subject'] ?? '');
            $threadId = (int)($_POST['thread_id'] ?? 0);

            if (!$receiverId || !$message) jsonError('Missing required fields');

            if (!$threadId) {
                $threadCheck = $db->prepare("SELECT thread_id FROM messages WHERE sender_id = ? AND receiver_id = ? AND thread_id IS NOT NULL ORDER BY created_at DESC LIMIT 1");
                $threadCheck->execute([getCurrentUserId(), $receiverId]);
                $existing = $threadCheck->fetchColumn();
                if ($existing) {
                    $threadId = (int)$existing;
                }
            }

            $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message, thread_id, is_admin) VALUES (?, ?, ?, ?, ?, ?)");
            $isAdmin = isAdmin() ? 1 : 0;
            $stmt->execute([getCurrentUserId(), $receiverId, $subject ?: 'No Subject', $message, $threadId ?: null, $isAdmin]);
            $insertId = $db->lastInsertId();

            if (!$threadId) {
                $db->prepare("UPDATE messages SET thread_id = ? WHERE id = ?")->execute([$insertId, $insertId]);
                $threadId = (int)$insertId;
            }

            createNotification($receiverId, 'message', 'New Message', $message, BASE_URL . 'messages');

            logActivity('message_sent', 'Message sent via API', [], 'info');
            jsonSuccess(['id' => $insertId, 'thread_id' => $threadId], 'Message sent');

        case 'get_messages':
            if (!isLoggedIn()) jsonError('Unauthorized', 401);
            $threadId = (int)($_GET['thread_id'] ?? 0);
            if (!$threadId) jsonError('Missing thread_id');
            $userId = getCurrentUserId();
            $participant = $db->prepare("SELECT id FROM messages WHERE thread_id = ? AND (sender_id = ? OR receiver_id = ?) LIMIT 1");
            $participant->execute([$threadId, $userId, $userId]);
            if (!$participant->fetch()) jsonError('Access denied', 403);
            $stmt = $db->prepare("SELECT m.*, u.name as sender_name, u.avatar as sender_avatar FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.thread_id = ? ORDER BY m.created_at ASC");
            $stmt->execute([$threadId]);
            jsonSuccess(['messages' => $stmt->fetchAll()]);

        case 'delete_thread':
            if (!isLoggedIn()) jsonError('Unauthorized', 401);
            $threadId = (int)($_POST['thread_id'] ?? 0);
            if (!$threadId) jsonError('Missing thread_id');
            $userId = getCurrentUserId();
            $participant = $db->prepare("SELECT id FROM messages WHERE thread_id = ? AND (sender_id = ? OR receiver_id = ?) LIMIT 1");
            $participant->execute([$threadId, $userId, $userId]);
            if (!$participant->fetch()) jsonError('Access denied', 403);
            $del = $db->prepare("DELETE FROM messages WHERE thread_id = ?");
            $del->execute([$threadId]);
            jsonSuccess([], 'Conversation deleted');

        case 'get_threads':
            if (!isLoggedIn()) jsonError('Unauthorized', 401);
            $stmt = $db->prepare("
                SELECT m.*, u.name as sender_name,
                (SELECT COUNT(*) FROM messages WHERE thread_id = m.thread_id AND receiver_id = ? AND is_read = 0) as unread
                FROM messages m JOIN users u ON m.sender_id = u.id
                WHERE m.sender_id = ? OR m.receiver_id = ?
                GROUP BY m.thread_id ORDER BY m.created_at DESC
            ");
            $stmt->execute([getCurrentUserId(), getCurrentUserId(), getCurrentUserId()]);
            jsonSuccess(['threads' => $stmt->fetchAll()]);

        // ============================================================
        // RATINGS
        // ============================================================
        case 'submit_rating':
            $itemId = (int)($_POST['item_id'] ?? 0);
            $itemType = sanitize($_POST['item_type'] ?? '');
            $rating = (int)($_POST['rating'] ?? 0);
            $review = sanitize($_POST['review'] ?? '');

            if (!$itemId || !$itemType || $rating < 1 || $rating > 5) jsonError('Invalid rating data');

            $userId = isLoggedIn() ? getCurrentUserId() : null;
            $guestName = !$userId ? trim($_POST['guest_name'] ?? '') : null;
            $guestEmail = !$userId ? trim($_POST['guest_email'] ?? '') : null;

            if (!$userId && empty($guestName)) jsonError('Please enter your name');

            $stmt = $db->prepare("INSERT INTO ratings (user_id, guest_name, guest_email, item_id, item_type, rating, review, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, 0) ON DUPLICATE KEY UPDATE rating = VALUES(rating), review = VALUES(review), is_approved = 0");
            $stmt->execute([$userId, $guestName, $guestEmail, $itemId, $itemType, $rating, $review]);

            logActivity('rating_submitted', 'Rating submitted', ['item_id' => $itemId, 'rating' => $rating, 'guest' => !$userId], 'info');
            jsonSuccess([], 'Rating submitted for approval');

        case 'get_ratings':
            $itemId = (int)($_GET['item_id'] ?? 0);
            $itemType = sanitize($_GET['item_type'] ?? '');
            if (!$itemId || !$itemType) jsonError('Missing parameters');
            $avg = getAverageRating($itemId, $itemType);
            $stmt = $db->prepare("SELECT r.*, u.name as user_name FROM ratings r JOIN users u ON r.user_id = u.id WHERE r.item_id = ? AND r.item_type = ? AND r.is_approved = 1 ORDER BY r.created_at DESC");
            $stmt->execute([$itemId, $itemType]);
            jsonSuccess(['average' => $avg, 'ratings' => $stmt->fetchAll()]);

        // ============================================================
        // SIEM ACTIONS (Admin only)
        // ============================================================
        case 'log_action':
            if (!isAdmin()) jsonError('Unauthorized', 401);
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) jsonError('Invalid CSRF token');
            $logId = (int)($_POST['log_id'] ?? 0);
            $actionType = sanitize($_POST['action_type'] ?? '');
            $reason = sanitize($_POST['reason'] ?? '');

            if (!$logId || !$actionType) jsonError('Missing parameters');
            $log = $db->prepare("SELECT al.*, u.name as user_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id WHERE al.id = ?");
            $log->execute([$logId]);
            $l = $log->fetch();
            if (!$l) jsonError('Log not found');

            switch ($actionType) {
                case 'mark_safe':
                    updateLogStatus($logId, 'normal');
                    createActionLog($logId, null, 'mark_safe', $l['user_id'], $l['ip_address'], ['reason' => $reason]);
                    jsonSuccess(['new_status' => 'normal'], 'Marked as safe');
                case 'mark_suspicious':
                    updateLogStatus($logId, 'suspicious');
                    createActionLog($logId, null, 'mark_suspicious', $l['user_id'], $l['ip_address'], ['reason' => $reason]);
                    jsonSuccess(['new_status' => 'suspicious'], 'Marked as suspicious');
                case 'mark_malicious':
                    updateLogStatus($logId, 'malicious');
                    createActionLog($logId, null, 'mark_malicious', $l['user_id'], $l['ip_address'], ['reason' => $reason]);
                    jsonSuccess(['new_status' => 'malicious'], 'Marked as malicious');
                case 'ignore':
                    updateLogStatus($logId, 'ignored');
                    createActionLog($logId, null, 'ignore', $l['user_id'], $l['ip_address'], ['reason' => $reason]);
                    jsonSuccess(['new_status' => 'ignored'], 'Event ignored');
                case 'block_user':
                    if (!$l['user_id']) jsonError('No user associated with this log');
                    blockUser($l['user_id'], $reason);
                    createActionLog($logId, null, 'block_user', $l['user_id'], $l['ip_address'], ['reason' => $reason]);
                    jsonSuccess(['new_status' => 'blocked'], 'User blocked');
                case 'unblock_user':
                    if (!$l['user_id']) jsonError('No user associated with this log');
                    unblockUser($l['user_id']);
                    createActionLog($logId, null, 'unblock_user', $l['user_id'], $l['ip_address'], ['reason' => $reason]);
                    jsonSuccess(['new_status' => 'normal'], 'User unblocked');
                case 'block_ip':
                    if (!$l['ip_address']) jsonError('No IP address in this log');
                    blockIp($l['ip_address'], $reason);
                    createActionLog($logId, null, 'block_ip', $l['user_id'], $l['ip_address'], ['reason' => $reason]);
                    jsonSuccess(['new_status' => 'blocked'], 'IP blocked');
                case 'unblock_ip':
                    if (!$l['ip_address']) jsonError('No IP address in this log');
                    unblockIp($l['ip_address']);
                    createActionLog($logId, null, 'unblock_ip', $l['user_id'], $l['ip_address'], ['reason' => $reason]);
                    jsonSuccess(['new_status' => 'normal'], 'IP unblocked');
                case 'force_logout':
                    if (!$l['user_id']) jsonError('No user associated with this log');
                    forceLogoutUser($l['user_id']);
                    createActionLog($logId, null, 'force_logout', $l['user_id'], $l['ip_address'], ['reason' => $reason]);
                    jsonSuccess([], 'User force logged out');
                case 'lock_login':
                    if (!$l['user_id']) jsonError('No user associated with this log');
                    lockUserLogin($l['user_id'], 15);
                    createActionLog($logId, null, 'lock_login', $l['user_id'], $l['ip_address'], ['reason' => $reason]);
                    jsonSuccess([], 'Login locked for 15 minutes');
                default:
                    jsonError('Unknown action type');
            }

        case 'alert_action':
            if (!isAdmin()) jsonError('Unauthorized', 401);
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) jsonError('Invalid CSRF token');
            $alertId = (int)($_POST['alert_id'] ?? 0);
            $actionType = sanitize($_POST['action_type'] ?? '');
            $reason = sanitize($_POST['reason'] ?? '');

            if (!$alertId || !$actionType) jsonError('Missing parameters');
            $alert = $db->prepare("SELECT * FROM alerts WHERE id = ?");
            $alert->execute([$alertId]);
            $a = $alert->fetch();
            if (!$a) jsonError('Alert not found');

            switch ($actionType) {
                case 'mark_new':
                    updateAlertStatus($alertId, 'new');
                    jsonSuccess(['new_status' => 'new'], 'Marked as new');
                case 'acknowledge':
                    updateAlertStatus($alertId, 'acknowledged');
                    $db->prepare("UPDATE alerts SET is_read = 1 WHERE id = ?")->execute([$alertId]);
                    jsonSuccess(['new_status' => 'acknowledged'], 'Alert acknowledged');
                case 'resolve':
                    updateAlertStatus($alertId, 'resolved');
                    jsonSuccess(['new_status' => 'resolved'], 'Alert resolved');
                case 'reopen':
                    updateAlertStatus($alertId, 'reopened');
                    $db->prepare("UPDATE alerts SET is_read = 0 WHERE id = ?")->execute([$alertId]);
                    jsonSuccess(['new_status' => 'reopened'], 'Alert reopened');
                case 'block_ip_alert':
                    if ($a['ip_address']) {
                        blockIp($a['ip_address'], $reason);
                        createActionLog($a['log_id'], $alertId, 'block_ip', $a['user_id'], $a['ip_address'], ['reason' => $reason]);
                    }
                    jsonSuccess([], 'IP blocked from alert');
                default:
                    jsonError('Unknown action type');
            }

        case 'investigate':
            if (!isAdmin()) jsonError('Unauthorized', 401);
            $logId = (int)($_GET['log_id'] ?? 0);
            $type = sanitize($_GET['type'] ?? 'related');
            if (!$logId) jsonError('Missing log_id');

            switch ($type) {
                case 'related':
                    $rows = getRelatedLogs($logId);
                    jsonSuccess(['logs' => $rows, 'count' => count($rows)]);
                case 'action_history':
                    $rows = getActionHistory($logId, null);
                    jsonSuccess(['actions' => $rows, 'count' => count($rows)]);
                default:
                    jsonError('Unknown investigation type');
            }

        case 'user_activity':
            if (!isAdmin()) jsonError('Unauthorized', 401);
            $userId = (int)($_GET['user_id'] ?? 0);
            if (!$userId) jsonError('Missing user_id');
            $rows = getUserActivityTimeline($userId);
            jsonSuccess(['logs' => $rows, 'count' => count($rows)]);

        case 'ip_activity':
            if (!isAdmin()) jsonError('Unauthorized', 401);
            $ip = sanitize($_GET['ip'] ?? '');
            if (!$ip) jsonError('Missing IP');
            $rows = getIpActivityHistory($ip);
            jsonSuccess(['logs' => $rows, 'count' => count($rows)]);

        // ============================================================
        // ACTIVITY LOGS
        // ============================================================
        case 'get_logs':
            if (!isAdmin()) jsonError('Unauthorized', 401);
            $limit = (int)($_GET['limit'] ?? 50);
            $stmt = $db->prepare("SELECT al.*, u.name as user_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT ?");
            $stmt->execute([$limit]);
            jsonSuccess(['logs' => $stmt->fetchAll()]);

        case 'clear_logs':
            if (!isSuperAdmin()) jsonError('Unauthorized', 401);
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('POST required');
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) jsonError('Invalid CSRF token');
            $db->exec("TRUNCATE TABLE activity_logs");
            logActivity('logs_cleared', 'Activity logs cleared by admin', [], 'warning');
            jsonSuccess([], 'Logs cleared');

        // ============================================================
        // MEDIA UPLOAD
        // ============================================================
        case 'upload_media':
            if (!isLoggedIn()) jsonError('Unauthorized', 401);
            if (empty($_FILES['file'])) jsonError('No file uploaded');

            $file = $_FILES['file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = explode(',', ALLOWED_EXTENSIONS);

            if (!in_array($ext, $allowed)) jsonError('File type not allowed');
            if ($file['size'] > MAX_UPLOAD_SIZE) jsonError('File too large');

            $filename = uniqid() . '.' . $ext;
            $filepath = 'uploads/' . $filename;
            $mimeType = mime_content_type($file['tmp_name']);
            $fileType = getFileType($mimeType);
            $folderId = (int)($_POST['folder_id'] ?? 0) ?: null;

            if (move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $filepath)) {
                $stmt = $db->prepare("INSERT INTO media (user_id, folder_id, filename, original_name, filepath, type, mime_type, size) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([getCurrentUserId(), $folderId, $filename, $file['name'], $filepath, $fileType, $mimeType, $file['size']]);
                logActivity('media_upload', 'File uploaded', ['filename' => $file['name']], 'info');
                jsonSuccess(['id' => $db->lastInsertId(), 'url' => BASE_URL . $filepath], 'File uploaded');
            }
            jsonError('Upload failed');

        case 'delete_media':
            if (!isLoggedIn()) jsonError('Unauthorized', 401);
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) jsonError('Invalid CSRF token');
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                if (isAdmin()) {
                    $stmt = $db->prepare("SELECT filepath FROM media WHERE id = ?");
                } else {
                    $stmt = $db->prepare("SELECT filepath FROM media WHERE id = ? AND user_id = ?");
                }
                $stmt->execute(isAdmin() ? [$id] : [$id, getCurrentUserId()]);
                $media = $stmt->fetch();
                if ($media) {
                    if (file_exists(__DIR__ . '/../' . $media['filepath'])) {
                        unlink(__DIR__ . '/../' . $media['filepath']);
                    }
                    $db->prepare("DELETE FROM media WHERE id = ?")->execute([$id]);
                    jsonSuccess([], 'File deleted');
                }
            }
            jsonError('File not found');

        // ============================================================
        // FOLDER MANAGEMENT
        // ============================================================
        case 'create_folder':
            if (!isAdmin()) jsonError('Unauthorized', 401);
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) jsonError('Invalid CSRF token');
            $name = trim($_POST['name'] ?? '');
            $parentId = (int)($_POST['parent_id'] ?? 0) ?: null;
            if (empty($name)) jsonError('Folder name is required');
            $stmt = $db->prepare("INSERT INTO folders (name, parent_id, user_id) VALUES (?, ?, ?)");
            $stmt->execute([$name, $parentId, getCurrentUserId()]);
            logActivity('folder_create', "Folder created: $name", [], 'info');
            jsonSuccess(['id' => $db->lastInsertId()], 'Folder created');

        case 'rename_folder':
            if (!isAdmin()) jsonError('Unauthorized', 401);
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) jsonError('Invalid CSRF token');
            $folderId = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            if (!$folderId || empty($name)) jsonError('Invalid data');
            $db->prepare("UPDATE folders SET name = ? WHERE id = ?")->execute([$name, $folderId]);
            logActivity('folder_rename', "Folder renamed to: $name", [], 'info');
            jsonSuccess([], 'Folder renamed');

        case 'delete_folder':
            if (!isAdmin()) jsonError('Unauthorized', 401);
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) jsonError('Invalid CSRF token');
            $folderId = (int)($_POST['id'] ?? 0);
            if (!$folderId) jsonError('Invalid folder ID');
            $db->prepare("UPDATE media SET folder_id = NULL WHERE folder_id = ?")->execute([$folderId]);
            $db->prepare("DELETE FROM folders WHERE id = ?")->execute([$folderId]);
            logActivity('folder_delete', "Folder deleted ID: $folderId", [], 'warning');
            jsonSuccess([], 'Folder deleted');

        case 'move_file':
            if (!isAdmin()) jsonError('Unauthorized', 401);
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) jsonError('Invalid CSRF token');
            $fileId = (int)($_POST['file_id'] ?? 0);
            $targetFolder = (int)($_POST['folder_id'] ?? 0) ?: null;
            if (!$fileId) jsonError('Invalid file ID');
            $db->prepare("UPDATE media SET folder_id = ? WHERE id = ?")->execute([$targetFolder, $fileId]);
            jsonSuccess([], 'File moved');

        // ============================================================
        // USER MANAGEMENT (Admin)
        // ============================================================
        case 'get_users':
            if (!isAdmin()) jsonError('Unauthorized', 401);
            $users = $db->query("SELECT id, name, email, role, status, created_at, last_login FROM users ORDER BY created_at DESC")->fetchAll();
            jsonSuccess(['users' => $users]);

        case 'update_user_status':
            if (!isAdmin()) jsonError('Unauthorized', 401);
            $id = (int)($_POST['id'] ?? 0);
            $status = sanitize($_POST['status'] ?? '');
            if ($id && in_array($status, ['active', 'inactive', 'banned'])) {
                $db->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$status, $id]);
                jsonSuccess([], 'Status updated');
            }
            jsonError('Invalid data');

        // ============================================================
        // SETTINGS
        // ============================================================
        case 'get_settings':
            $public = $db->query("SELECT setting_key, setting_value FROM settings WHERE is_public = 1")->fetchAll();
            $settings = [];
            foreach ($public as $s) $settings[$s['setting_key']] = $s['setting_value'];
            jsonSuccess(['settings' => $settings]);

        // ============================================================
        // DASHBOARD STATS
        // ============================================================
        case 'dashboard_stats':
            if (!isLoggedIn()) jsonError('Unauthorized', 401);
            $uid = getCurrentUserId();
            $stats = [
                'messages' => (int)$db->query("SELECT COUNT(*) FROM messages WHERE receiver_id = ? OR sender_id = ?", [$uid, $uid])->fetchColumn(),
                'notifications' => getUnreadNotificationCount($uid),
                'ratings' => (int)$db->query("SELECT COUNT(*) FROM ratings WHERE user_id = ?", [$uid])->fetchColumn(),
            ];
            jsonSuccess($stats);

        // ============================================================
        // POLL: lightweight unread counts for auto-refresh
        // ============================================================
        case 'poll':
            if (!isLoggedIn()) jsonError('Unauthorized', 401);
            $uid = getCurrentUserId();
            $notifCount = getUnreadNotificationCount($uid);
            $adminUnread = 0;
            if (isAdmin()) {
                $adminUnread = (int)$db->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
            }
            $msgStmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
            $msgStmt->execute([$uid]);
            $msgUnread = (int)$msgStmt->fetchColumn();
            jsonSuccess([
                'notifications' => $notifCount,
                'messages' => $msgUnread,
                'contacts' => $adminUnread,
                'timestamp' => time()
            ]);

        default:
            jsonError('Unknown action', 404);
    }
} catch (Exception $e) {
    logActivity('api_error', 'API Error: ' . $e->getMessage(), [], 'critical');
    jsonError('Internal server error', 500);
}
