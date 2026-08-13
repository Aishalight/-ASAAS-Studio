<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Security Logs'; require __DIR__ . '/../includes/admin-header.php';
$db = Database::getInstance()->getConnection();

// Handle mark all read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        markAlertsRead();
        echo '<script>location.href="' . BASE_URL . 'admin-logs";</script>';
        exit;
    }
}

// Handle legacy POST actions (non-JS fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_action'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { setFlash('error', 'Invalid CSRF token'); redirect(BASE_URL . 'admin-logs'); }
    $logId = (int)($_POST['log_id'] ?? 0);
    $actionType = sanitize($_POST['action_type'] ?? '');
    $reason = sanitize($_POST['reason'] ?? '');
    $logQ = $db->prepare("SELECT * FROM activity_logs WHERE id = ?");
    $logQ->execute([$logId]);
    $l = $logQ->fetch();
    if ($l) {
        $actionMap = ['mark_safe'=>'normal','mark_suspicious'=>'suspicious','mark_malicious'=>'malicious','ignore'=>'ignored'];
        if (isset($actionMap[$actionType])) {
            updateLogStatus($logId, $actionMap[$actionType]);
            createActionLog($logId, null, $actionType, $l['user_id'], $l['ip_address'], ['reason' => $reason]);
            setFlash('success', 'Log status updated');
        } elseif ($actionType === 'block_user' && $l['user_id']) { blockUser($l['user_id'], $reason); createActionLog($logId, null, 'block_user', $l['user_id'], $l['ip_address'], ['reason' => $reason]); setFlash('success', 'User blocked');
        } elseif ($actionType === 'unblock_user' && $l['user_id']) { unblockUser($l['user_id']); createActionLog($logId, null, 'unblock_user', $l['user_id'], $l['ip_address']); setFlash('success', 'User unblocked');
        } elseif ($actionType === 'block_ip' && $l['ip_address']) { blockIp($l['ip_address'], $reason); createActionLog($logId, null, 'block_ip', $l['user_id'], $l['ip_address'], ['reason' => $reason]); setFlash('success', 'IP blocked');
        } elseif ($actionType === 'unblock_ip' && $l['ip_address']) { unblockIp($l['ip_address']); createActionLog($logId, null, 'unblock_ip', $l['user_id'], $l['ip_address']); setFlash('success', 'IP unblocked');
        } elseif ($actionType === 'force_logout' && $l['user_id']) { forceLogoutUser($l['user_id']); createActionLog($logId, null, 'force_logout', $l['user_id'], $l['ip_address']); setFlash('success', 'User force logged out');
        } elseif ($actionType === 'lock_login' && $l['user_id']) { lockUserLogin($l['user_id'], 15); createActionLog($logId, null, 'lock_login', $l['user_id'], $l['ip_address']); setFlash('success', 'Login locked');
        }
    }
    redirect(BASE_URL . 'admin-logs');
}

// Stats
$totalLogs    = (int)$db->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
$failedLogins = (int)$db->query("SELECT COUNT(*) FROM activity_logs WHERE action = 'LOGIN_FAILED' AND DATE(created_at) = CURDATE()")->fetchColumn();
$suspicious   = (int)$db->query("SELECT COUNT(*) FROM activity_logs WHERE status NOT IN ('normal','ignored')")->fetchColumn();
$activeAlerts = (int)$db->query("SELECT COUNT(*) FROM alerts WHERE is_read = 0")->fetchColumn();
$maliciousCount = (int)$db->query("SELECT COUNT(*) FROM activity_logs WHERE status = 'malicious'")->fetchColumn();

// Alerts list
$alerts = $db->query("SELECT a.*, u.name as user_name FROM alerts a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 10")->fetchAll();

// Pagination & filters
$pg    = max(1, (int)($_GET['p'] ?? 1));
$limit = 20;
$off   = ($pg - 1) * $limit;

$fSeverity = $_GET['severity'] ?? '';
$fAction   = $_GET['action'] ?? '';
$fStatus   = $_GET['status'] ?? '';
$fSearch   = trim($_GET['search'] ?? '');
$fDateFrom = $_GET['from'] ?? '';
$fDateTo   = $_GET['to'] ?? '';

$w = []; $p = [];
if ($fSeverity && in_array($fSeverity, ['low','medium','high','critical'])) { $w[] = 'al.severity = ?'; $p[] = $fSeverity; }
if ($fStatus && in_array($fStatus, ['normal','suspicious','blocked','malicious','ignored'])) { $w[] = 'al.status = ?'; $p[] = $fStatus; }
if ($fAction) { $w[] = 'al.action = ?'; $p[] = $fAction; }
if ($fSearch) { $s = "%$fSearch%"; $w[] = '(al.description LIKE ? OR al.action LIKE ? OR u.name LIKE ? OR al.ip_address LIKE ?)'; array_push($p, $s, $s, $s, $s); }
if ($fDateFrom) { $w[] = 'DATE(al.created_at) >= ?'; $p[] = $fDateFrom; }
if ($fDateTo)   { $w[] = 'DATE(al.created_at) <= ?'; $p[] = $fDateTo; }
$wh = $w ? 'WHERE ' . join(' AND ', $w) : '';

$cntSt = $db->prepare("SELECT COUNT(*) FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id $wh");
$cntSt->execute($p);
$totalRows = (int)$cntSt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $limit));

$logsSt = $db->prepare("SELECT al.*, u.name as user_name, u.role as user_role FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id $wh ORDER BY al.created_at DESC LIMIT $limit OFFSET $off");
$logsSt->execute($p);
$logs = $logsSt->fetchAll();

$actions = $db->query("SELECT DISTINCT action FROM activity_logs ORDER BY action")->fetchAll(\PDO::FETCH_COLUMN);
?>
<style>
.siem-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px}
.siem-stat-card{background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:20px;display:flex;align-items:center;gap:16px;transition:all var(--transition)}
.siem-stat-card:hover{box-shadow:var(--shadow-md);transform:translateY(-2px)}
.siem-stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.siem-stat-value{font-size:26px;font-weight:800;line-height:1.1}
.siem-stat-label{font-size:13px;color:var(--text-muted)}
.siem-alerts{margin-bottom:24px}
.siem-alert{border-radius:var(--radius-md);margin-bottom:8px;border:1px solid var(--border);border-left:4px solid;transition:all var(--transition);background:var(--bg-white)}
.siem-alert:hover{box-shadow:var(--shadow-sm)}
.siem-alert.critical{border-left-color:#F44336;background:rgba(244,67,54,0.04)}
.siem-alert.high{border-left-color:#FF9800;background:rgba(255,152,0,0.04)}
.siem-alert.medium{border-left-color:#2196F3;background:rgba(33,150,243,0.04)}
.siem-alert.low{border-left-color:#4CAF50;background:rgba(76,175,80,0.04)}
.siem-alert.critical .sev-badge{background:rgba(244,67,54,0.12);color:#F44336}
.siem-alert.high .sev-badge{background:rgba(255,152,0,0.12);color:#FF9800}
.siem-alert.medium .sev-badge{background:rgba(33,150,243,0.12);color:#2196F3}
.siem-alert.low .sev-badge{background:rgba(76,175,80,0.12);color:#4CAF50}
.alert-body{padding:14px 16px;display:flex;align-items:flex-start;gap:12px}
.alert-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:16px}
.alert-content{flex:1;min-width:0}
.alert-title{font-size:14px;font-weight:600;margin-bottom:2px}
.alert-desc{font-size:12px;color:var(--text-muted);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.alert-meta{font-size:11px;color:var(--text-muted);margin-top:4px;display:flex;gap:12px;flex-wrap:wrap}
.alert-time{font-size:11px;color:var(--text-muted);white-space:nowrap}
.alert-actions{padding:8px 16px 12px;display:flex;gap:6px;flex-wrap:wrap;border-top:1px solid var(--border)}
.siem-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600}
.siem-badge.critical,.sev-badge.critical{background:rgba(244,67,54,0.12);color:#F44336}
.siem-badge.high,.sev-badge.high{background:rgba(255,152,0,0.12);color:#FF9800}
.siem-badge.medium,.sev-badge.medium{background:rgba(33,150,243,0.12);color:#2196F3}
.siem-badge.low,.sev-badge.low{background:rgba(76,175,80,0.12);color:#4CAF50}
.siem-badge.suspicious{background:rgba(255,152,0,0.12);color:#FF9800}
.siem-badge.blocked{background:rgba(244,67,54,0.12);color:#F44336}
.siem-badge.normal{background:rgba(76,175,80,0.12);color:#4CAF50}
.siem-badge.malicious{background:rgba(139,0,0,0.15);color:#8B0000}
.siem-badge.ignored{background:rgba(128,128,128,0.12);color:#666}
.log-row{cursor:pointer;transition:background .15s}
.log-row:hover{background:var(--bg-light)}
.log-row.suspicious{background:rgba(255,152,0,0.04)}
.log-row.blocked{background:rgba(244,67,54,0.04)}
.log-row.malicious{background:rgba(139,0,0,0.06)}
.log-row.ignored{background:rgba(128,128,128,0.03);opacity:.7}
.log-detail{display:none;padding:16px 20px;background:var(--bg-light);border-top:1px solid var(--border);font-size:13px}
.log-detail.open{display:block}
.log-detail-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
.log-detail-item label{font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:2px}
.log-detail-item div{color:var(--text-secondary);word-break:break-all}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center}
.filter-bar .form-input,.filter-bar .form-select{font-size:13px;padding:6px 10px}
.siem-empty{text-align:center;padding:60px 20px;color:var(--text-muted)}
.siem-empty-icon{font-size:48px;margin-bottom:12px;opacity:.3}
.severity-dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:4px;vertical-align:middle}
.severity-dot.critical{background:#F44336}
.severity-dot.high{background:#FF9800}
.severity-dot.medium{background:#2196F3}
.severity-dot.low{background:#4CAF50}
.expand-btn{background:none;border:none;cursor:pointer;padding:4px;color:var(--text-muted);border-radius:4px;transition:all .2s;font-size:0}
.expand-btn:hover{background:var(--bg-light);color:var(--text-primary)}

.log-actions{padding:12px 0 0;border-top:1px solid var(--border);margin-top:12px}
.log-actions-group{display:flex;align-items:center;gap:6px;margin-bottom:6px;flex-wrap:wrap}
.log-actions-group:last-child{margin-bottom:0}
.log-actions-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);min-width:60px}
.btn-action{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;border:1px solid var(--border);font-size:11px;font-weight:600;cursor:pointer;transition:all .2s;background:var(--bg-white);color:var(--text-secondary);font-family:inherit}
.btn-action:hover{transform:translateY(-1px);box-shadow:var(--shadow-sm)}
.btn-action:active{transform:translateY(0)}
.btn-action-safe:hover{border-color:#4CAF50;background:rgba(76,175,80,0.08);color:#4CAF50}
.btn-action-suspicious:hover{border-color:#FF9800;background:rgba(255,152,0,0.08);color:#FF9800}
.btn-action-malicious:hover{border-color:#8B0000;background:rgba(139,0,0,0.08);color:#8B0000}
.btn-action-ignore:hover{border-color:#666;background:rgba(128,128,128,0.08);color:#666}
.btn-action-danger:hover{border-color:#F44336;background:rgba(244,67,54,0.08);color:#F44336}
.btn-action-warning:hover{border-color:#FF9800;background:rgba(255,152,0,0.08);color:#FF9800}
.btn-action-info:hover{border-color:#2196F3;background:rgba(33,150,243,0.08);color:#2196F3}
.btn-action-sm{padding:3px 8px;font-size:10px}

.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center}
.modal-overlay.active{display:flex}
.modal-box{background:var(--bg-white);border-radius:var(--radius-lg);padding:24px;max-width:480px;width:90%;box-shadow:var(--shadow-lg);max-height:90vh;overflow-y:auto}
.modal-title{font-size:18px;font-weight:700;margin-bottom:4px}
.modal-desc{font-size:13px;color:var(--text-muted);margin-bottom:16px}
.modal-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:16px}
.modal-reason{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;font-family:inherit;resize:vertical;min-height:60px;margin-bottom:12px;background:var(--bg-light);color:var(--text-primary)}
.modal-reason:focus{outline:none;border-color:var(--primary)}

.investigate-modal .modal-box{max-width:700px}
.investigate-table{width:100%;border-collapse:collapse;font-size:12px}
.investigate-table th{text-align:left;padding:6px 8px;border-bottom:1px solid var(--border);font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);font-weight:600}
.investigate-table td{padding:6px 8px;border-bottom:1px solid var(--border);font-size:12px}
.investigate-table tr:hover td{background:var(--bg-light)}
.investigate-table .sev-dot{width:6px;height:6px;border-radius:50%;display:inline-block;margin-right:4px}
.investigate-table .sev-dot.critical{background:#F44336}
.investigate-table .sev-dot.high{background:#FF9800}
.investigate-table .sev-dot.medium{background:#2196F3}
.investigate-table .sev-dot.low{background:#4CAF50}
.investigate-empty{text-align:center;padding:32px;color:var(--text-muted);font-size:13px}
.investigate-loading{text-align:center;padding:32px;color:var(--text-muted)}

.toast-container{position:fixed;top:16px;right:16px;z-index:2000;display:flex;flex-direction:column;gap:8px}
.toast{padding:12px 16px;border-radius:var(--radius-md);box-shadow:var(--shadow-lg);display:flex;align-items:center;gap:10px;font-size:13px;font-weight:500;animation:slideInRight .3s ease;max-width:360px;border-left:4px solid}
.toast-success{border-left-color:#4CAF50;background:var(--bg-white);color:var(--text-primary)}
.toast-error{border-left-color:#F44336;background:var(--bg-white);color:var(--text-primary)}
.toast-info{border-left-color:#2196F3;background:var(--bg-white);color:var(--text-primary)}
.toast-close{background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:16px;padding:0;margin-left:auto;line-height:1}
@keyframes slideInRight{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}

.action-badge{display:inline-block;padding:1px 6px;border-radius:4px;font-size:10px;font-weight:600;margin-left:6px}
.action-badge-modified{background:rgba(33,150,243,0.12);color:#2196F3}

[data-theme="dark"] .siem-stat-card{background:var(--bg-white)}
[data-theme="dark"] .siem-alert{background:var(--bg-white);border-color:var(--border)}
[data-theme="dark"] .log-detail{background:#12121f}
[data-theme="dark"] .log-row.suspicious{background:rgba(255,152,0,0.08)}
[data-theme="dark"] .log-row.blocked{background:rgba(244,67,54,0.08)}
[data-theme="dark"] .log-row.malicious{background:rgba(139,0,0,0.12)}
[data-theme="dark"] .log-row.ignored{background:rgba(128,128,128,0.05)}
[data-theme="dark"] .modal-box{background:var(--bg-white)}
[data-theme="dark"] .modal-reason{background:var(--bg-light);color:var(--text-primary);border-color:var(--border)}
[data-theme="dark"] .btn-action{background:var(--bg-white);border-color:var(--border);color:var(--text-secondary)}
[data-theme="dark"] .toast-success,.toast-error,.toast-info{background:var(--bg-white)}
</style>

<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Security Logs</h1>
        <p class="page-subtitle">SIEM-style monitoring - real-time detection, alerts & response actions</p>
    </div>
</div>

<!-- Stats -->
<div class="siem-stats fade-in-up">
    <div class="siem-stat-card">
        <div class="siem-stat-icon" style="background:rgba(33,150,243,0.1);color:#2196F3"><i data-lucide="activity" size="22"></i></div>
        <div><div class="siem-stat-value"><?= number_format($totalLogs) ?></div><div class="siem-stat-label">Total Events</div></div>
    </div>
    <div class="siem-stat-card">
        <div class="siem-stat-icon" style="background:rgba(244,67,54,0.1);color:#F44336"><i data-lucide="log-in" size="22"></i></div>
        <div><div class="siem-stat-value"><?= $failedLogins ?></div><div class="siem-stat-label">Failed Logins Today</div></div>
    </div>
    <div class="siem-stat-card">
        <div class="siem-stat-icon" style="background:rgba(255,152,0,0.1);color:#FF9800"><i data-lucide="alert-triangle" size="22"></i></div>
        <div><div class="siem-stat-value"><?= $suspicious ?></div><div class="siem-stat-label">Suspicious Events</div></div>
    </div>
    <div class="siem-stat-card">
        <div class="siem-stat-icon" style="background:rgba(139,0,0,0.1);color:#8B0000"><i data-lucide="shield-off" size="22"></i></div>
        <div><div class="siem-stat-value"><?= $maliciousCount ?></div><div class="siem-stat-label">Malicious Events</div></div>
    </div>
    <div class="siem-stat-card">
        <div class="siem-stat-icon" style="background:rgba(232,99,42,0.1);color:var(--primary)"><i data-lucide="bell" size="22"></i></div>
        <div><div class="siem-stat-value"><?= $activeAlerts ?></div><div class="siem-stat-label">Active Alerts</div></div>
    </div>
</div>

<!-- Alerts Panel -->
<?php if ($activeAlerts > 0 || !empty($alerts)): ?>
<div class="siem-alerts fade-in-up">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
        <h3 style="font-size:16px;font-weight:600;display:flex;align-items:center;gap:8px">
            <i data-lucide="shield" size="18" style="color:var(--primary)"></i>
            Active Alerts
            <?php if ($activeAlerts > 0): ?><span class="siem-badge critical"><?= $activeAlerts ?> new</span><?php endif; ?>
        </h3>
        <form method="POST" action="<?= BASE_URL ?>admin-logs" style="display:inline">
            <input type="hidden" name="mark_read" value="1">
            <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
            <button type="submit" class="btn btn-ghost btn-sm"><i data-lucide="check-check" size="14"></i> Mark All Read</button>
        </form>
    </div>
    <?php if (empty($alerts)): ?>
        <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:14px">No alerts yet</div>
    <?php else:
    $sevIcon = ['critical'=>'alert-triangle','high'=>'alert-circle','medium'=>'info','low'=>'check-circle'];
    $sevColor = ['critical'=>'#F44336','high'=>'#FF9800','medium'=>'#2196F3','low'=>'#4CAF50'];
    $sevBg = ['critical'=>'rgba(244,67,54,0.1)','high'=>'rgba(255,152,0,0.1)','medium'=>'rgba(33,150,243,0.1)','low'=>'rgba(76,175,80,0.1)'];
    foreach ($alerts as $a): ?>
    <div class="siem-alert <?= $a['severity'] ?>" id="alert-<?= $a['id'] ?>">
        <div class="alert-body">
            <div class="alert-icon" style="background:<?= $sevBg[$a['severity']] ?>;color:<?= $sevColor[$a['severity']] ?>"><i data-lucide="<?= $sevIcon[$a['severity']] ?>" size="16"></i></div>
            <div class="alert-content">
                <div class="alert-title"><?= htmlspecialchars($a['title']) ?> <span class="sev-badge" style="display:inline-flex;align-items:center;gap:3px;padding:1px 6px;border-radius:10px;font-size:10px;font-weight:600;margin-left:4px"><?= ucfirst($a['status'] ?? 'new') ?></span></div>
                <div class="alert-desc"><?= htmlspecialchars($a['description'] ?? '') ?></div>
                <div class="alert-meta">
                    <span><i data-lucide="tag" size="11"></i> <?= htmlspecialchars($a['type']) ?></span>
                    <?php if ($a['user_name']): ?><span><i data-lucide="user" size="11"></i> <?= htmlspecialchars($a['user_name']) ?></span><?php endif; ?>
                    <?php if ($a['ip_address']): ?><span><i data-lucide="globe" size="11"></i> <?= htmlspecialchars($a['ip_address']) ?></span><?php endif; ?>
                </div>
            </div>
            <div class="alert-time"><?= formatTimeAgo($a['created_at']) ?></div>
        </div>
        <div class="alert-actions">
            <button class="btn-action btn-action-sm btn-action-info" data-alert-action="mark_new" data-alert-id="<?= $a['id'] ?>"><i data-lucide="rotate-ccw" size="12"></i> New</button>
            <button class="btn-action btn-action-sm btn-action-info" data-alert-action="acknowledge" data-alert-id="<?= $a['id'] ?>"><i data-lucide="eye" size="12"></i> Acknowledge</button>
            <button class="btn-action btn-action-sm btn-action-safe" data-alert-action="resolve" data-alert-id="<?= $a['id'] ?>"><i data-lucide="check-circle" size="12"></i> Resolve</button>
            <button class="btn-action btn-action-sm btn-action-warning" data-alert-action="reopen" data-alert-id="<?= $a['id'] ?>"><i data-lucide="refresh-cw" size="12"></i> Reopen</button>
            <?php if ($a['ip_address']): ?>
            <button class="btn-action btn-action-sm btn-action-danger" data-alert-action="block_ip_alert" data-alert-id="<?= $a['id'] ?>" data-ip="<?= htmlspecialchars($a['ip_address']) ?>"><i data-lucide="shield-off" size="12"></i> Block IP</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="filter-bar reveal">
    <input type="search" class="form-input" id="searchInput" placeholder="Search logs..." value="<?= htmlspecialchars($fSearch) ?>" style="min-width:180px">
    <select class="form-select" id="sevFilter" onchange="applyFilter()" style="min-width:120px">
        <option value="">All Severities</option>
        <option value="critical" <?= $fSeverity==='critical'?'selected':'' ?>>Critical</option>
        <option value="high" <?= $fSeverity==='high'?'selected':'' ?>>High</option>
        <option value="medium" <?= $fSeverity==='medium'?'selected':'' ?>>Medium</option>
        <option value="low" <?= $fSeverity==='low'?'selected':'' ?>>Low</option>
    </select>
    <select class="form-select" id="statusFilter" onchange="applyFilter()" style="min-width:120px">
        <option value="">All Statuses</option>
        <option value="normal" <?= $fStatus==='normal'?'selected':'' ?>>Normal</option>
        <option value="suspicious" <?= $fStatus==='suspicious'?'selected':'' ?>>Suspicious</option>
        <option value="blocked" <?= $fStatus==='blocked'?'selected':'' ?>>Blocked</option>
        <option value="malicious" <?= $fStatus==='malicious'?'selected':'' ?>>Malicious</option>
        <option value="ignored" <?= $fStatus==='ignored'?'selected':'' ?>>Ignored</option>
    </select>
    <select class="form-select" id="actionFilter" onchange="applyFilter()" style="min-width:140px">
        <option value="">All Actions</option>
        <?php foreach ($actions as $a): ?>
            <option value="<?= htmlspecialchars($a) ?>" <?= $fAction===$a?'selected':'' ?>><?= htmlspecialchars($a) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" class="form-input" id="dateFrom" value="<?= $fDateFrom ?>" onchange="applyFilter()" style="min-width:130px" title="From date">
    <input type="date" class="form-input" id="dateTo" value="<?= $fDateTo ?>" onchange="applyFilter()" style="min-width:130px" title="To date">
    <span style="font-size:13px;color:var(--text-muted);white-space:nowrap"><?= number_format($totalRows) ?> results</span>
    <button class="btn btn-ghost btn-sm" onclick="clearFilters()" title="Clear filters"><i data-lucide="x" size="14"></i></button>
</div>

<!-- Logs Table -->
<div class="reveal table-container">
    <table class="table">
        <thead><tr><th style="width:30px"></th><th>User</th><th>Action</th><th>Description</th><th>Severity</th><th>Status</th><th>IP</th><th>Date</th></tr></thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="8"><div class="siem-empty"><div class="siem-empty-icon"><i data-lucide="search" size="48"></i></div><h4>No matching events</h4><p>Try adjusting your filters or search terms</p></div></td></tr>
            <?php else: foreach ($logs as $l):
                $sevClass = $l['severity'];
                $details = json_decode($l['details'] ?? '{}', true);
            ?>
            <tr class="log-row <?= $l['status'] !== 'normal' ? $l['status'] : '' ?>" onclick="toggleDetail(<?= $l['id'] ?>)">
                <td><button class="expand-btn" id="expand-<?= $l['id'] ?>"><i data-lucide="chevron-down" size="16"></i></button></td>
                <td><span style="font-weight:500;font-size:13px"><?= htmlspecialchars($l['user_name'] ?? 'System') ?></span></td>
                <td><code style="font-size:11px;padding:2px 6px;background:var(--bg-light);border-radius:4px"><?= htmlspecialchars($l['action']) ?></code></td>
                <td style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;color:var(--text-secondary)"><?= htmlspecialchars($l['description'] ?? '') ?></td>
                <td><span class="siem-badge <?= $sevClass ?>"><span class="severity-dot <?= $sevClass ?>"></span><?= ucfirst($sevClass) ?></span></td>
                <td><span class="siem-badge <?= $l['status'] ?: 'normal' ?>"><?= ucfirst($l['status'] ?: 'normal') ?></span></td>
                <td style="font-size:12px;font-family:monospace;color:var(--text-muted)"><?= htmlspecialchars($l['ip_address'] ?? '-') ?></td>
                <td style="font-size:12px;color:var(--text-muted);white-space:nowrap"><?= formatTimeAgo($l['created_at']) ?></td>
            </tr>
            <tr id="detail-<?= $l['id'] ?>" class="log-detail">
                <td colspan="8">
                    <div class="log-detail-grid">
                        <div class="log-detail-item"><label>Log ID</label><div>#<?= $l['id'] ?></div></div>
                        <div class="log-detail-item"><label>User</label><div><?= htmlspecialchars($l['user_name'] ?? 'System') ?> (<?= htmlspecialchars($l['user_role'] ?? 'N/A') ?>)</div></div>
                        <div class="log-detail-item"><label>Action</label><div><code><?= htmlspecialchars($l['action']) ?></code></div></div>
                        <div class="log-detail-item"><label>Description</label><div><?= htmlspecialchars($l['description'] ?? '-') ?></div></div>
                        <div class="log-detail-item"><label>IP Address</label><div><?= htmlspecialchars($l['ip_address'] ?? '-') ?></div></div>
                        <div class="log-detail-item"><label>User Agent</label><div style="font-size:11px"><?= htmlspecialchars($l['user_agent'] ?? '-') ?></div></div>
                        <div class="log-detail-item"><label>Request URL</label><div style="font-size:11px"><?= htmlspecialchars($l['request_url'] ?? '-') ?></div></div>
                        <div class="log-detail-item"><label>Request Method</label><div><?= htmlspecialchars($l['request_method'] ?? '-') ?></div></div>
                        <?php if (!empty($details) && is_array($details)): ?>
                            <div class="log-detail-item" style="grid-column:1/-1"><label>Details (JSON)</label><div><pre style="font-size:11px;background:var(--bg-white);padding:8px;border-radius:4px;max-height:120px;overflow:auto;margin:0"><?= htmlspecialchars(json_encode($details, JSON_PRETTY_PRINT)) ?></pre></div></div>
                        <?php endif; ?>
                        <div class="log-detail-item"><label>Timestamp</label><div><?= date('M j, Y g:i:s A', strtotime($l['created_at'])) ?></div></div>
                        <div class="log-detail-item"><label>Severity / Status</label><div><span class="siem-badge <?= $sevClass ?>"><?= ucfirst($sevClass) ?></span> <span class="siem-badge <?= $l['status'] ?: 'normal' ?>"><?= ucfirst($l['status'] ?: 'normal') ?></span></div></div>
                    </div>
                    <!-- Action Panel -->
                    <div class="log-actions">
                        <div class="log-actions-group">
                            <span class="log-actions-label">Classify:</span>
                            <button class="btn-action btn-action-safe" data-log-action="mark_safe" data-log-id="<?= $l['id'] ?>"><i data-lucide="check" size="12"></i> Safe</button>
                            <button class="btn-action btn-action-suspicious" data-log-action="mark_suspicious" data-log-id="<?= $l['id'] ?>"><i data-lucide="alert-triangle" size="12"></i> Suspicious</button>
                            <button class="btn-action btn-action-malicious" data-log-action="mark_malicious" data-log-id="<?= $l['id'] ?>"><i data-lucide="shield-off" size="12"></i> Malicious</button>
                            <button class="btn-action btn-action-ignore" data-log-action="ignore" data-log-id="<?= $l['id'] ?>"><i data-lucide="slash" size="12"></i> Ignore</button>
                        </div>
                        <?php if ($l['user_id']): ?>
                        <div class="log-actions-group">
                            <span class="log-actions-label">Respond:</span>
                            <button class="btn-action btn-action-danger" data-log-action="block_user" data-log-id="<?= $l['id'] ?>"><i data-lucide="ban" size="12"></i> Block User</button>
                            <button class="btn-action btn-action-safe" data-log-action="unblock_user" data-log-id="<?= $l['id'] ?>"><i data-lucide="unlock" size="12"></i> Unblock User</button>
                            <button class="btn-action btn-action-warning" data-log-action="force_logout" data-log-id="<?= $l['id'] ?>"><i data-lucide="log-out" size="12"></i> Force Logout</button>
                            <button class="btn-action btn-action-warning" data-log-action="lock_login" data-log-id="<?= $l['id'] ?>"><i data-lucide="lock" size="12"></i> Lock Login</button>
                        </div>
                        <?php endif; ?>
                        <?php if ($l['ip_address']): ?>
                        <div class="log-actions-group">
                            <span class="log-actions-label">Network:</span>
                            <button class="btn-action btn-action-danger" data-log-action="block_ip" data-log-id="<?= $l['id'] ?>"><i data-lucide="shield-off" size="12"></i> Block IP</button>
                            <button class="btn-action btn-action-safe" data-log-action="unblock_ip" data-log-id="<?= $l['id'] ?>"><i data-lucide="shield" size="12"></i> Unblock IP</button>
                        </div>
                        <?php endif; ?>
                        <div class="log-actions-group">
                            <span class="log-actions-label">Investigate:</span>
                            <button class="btn-action btn-action-info" data-investigate="related" data-log-id="<?= $l['id'] ?>"><i data-lucide="link" size="12"></i> Related Logs</button>
                            <button class="btn-action btn-action-info" data-investigate="history" data-log-id="<?= $l['id'] ?>"><i data-lucide="clock" size="12"></i> Action History</button>
                            <button class="btn-action btn-action-info" data-investigate="user" data-log-id="<?= $l['id'] ?>" data-user-id="<?= $l['user_id'] ?>"><i data-lucide="user" size="12"></i> User Timeline</button>
                            <button class="btn-action btn-action-info" data-investigate="ip" data-log-id="<?= $l['id'] ?>" data-ip="<?= htmlspecialchars($l['ip_address'] ?? '') ?>"><i data-lucide="globe" size="12"></i> IP History</button>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div class="pagination reveal" style="margin-top:20px">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?p=<?= $i ?>&severity=<?= urlencode($fSeverity) ?>&status=<?= urlencode($fStatus) ?>&action=<?= urlencode($fAction) ?>&search=<?= urlencode($fSearch) ?>&from=<?= urlencode($fDateFrom) ?>&to=<?= urlencode($fDateTo) ?>" class="<?= $i === $pg ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Confirmation Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-box">
        <div class="modal-title" id="confirmTitle">Confirm Action</div>
        <div class="modal-desc" id="confirmDesc">Are you sure?</div>
        <textarea class="modal-reason" id="confirmReason" placeholder="Reason for this action (optional)"></textarea>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
            <button class="btn btn-primary" id="confirmBtn" onclick="executeConfirmedAction()">Confirm</button>
        </div>
        <input type="hidden" id="confirmCsrf" value="<?= getCSRFToken() ?>">
    </div>
</div>

<!-- Investigation Modal -->
<div class="modal-overlay investigate-modal" id="investigateModal">
    <div class="modal-box">
        <div class="modal-title" id="investigateTitle">Investigation</div>
        <div class="modal-desc" id="investigateDesc">Loading investigation data...</div>
        <div id="investigateContent" class="investigate-loading">Loading...</div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeInvestigateModal()">Close</button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<style>
/* Hide confirm actions by default, show via JS */
</style>

<script>
var BASE_URL = '<?= BASE_URL ?>';
var CSRF_TOKEN = '<?= getCSRFToken() ?>';
var pendingAction = null;

function toggleDetail(id){
    var r=document.getElementById('detail-'+id),e=document.getElementById('expand-'+id);
    if(!r)return;
    var open=r.classList.contains('open');
    document.querySelectorAll('.log-detail.open').forEach(function(d){d.classList.remove('open')});
    document.querySelectorAll('.expand-btn i').forEach(function(i){i.setAttribute('data-lucide','chevron-down')});
    if(!open){r.classList.add('open');if(e)e.querySelector('i').setAttribute('data-lucide','chevron-up')}
    if(typeof lucide!=='undefined')lucide.createIcons();
}
function applyFilter(){
    var p=new URLSearchParams();
    var s=document.getElementById('searchInput').value;if(s)p.set('search',s);
    var sev=document.getElementById('sevFilter').value;if(sev)p.set('severity',sev);
    var st=document.getElementById('statusFilter').value;if(st)p.set('status',st);
    var act=document.getElementById('actionFilter').value;if(act)p.set('action',act);
    var df=document.getElementById('dateFrom').value;if(df)p.set('from',df);
    var dt=document.getElementById('dateTo').value;if(dt)p.set('to',dt);
    window.location='?p=1&'+p.toString();
}
function clearFilters(){
    window.location='?p=1';
}

// Toast
function showToast(message, type){
    type=type||'success';
    var c=document.getElementById('toastContainer');
    var t=document.createElement('div');
    t.className='toast toast-'+type;
    var iconMap={success:'check-circle',error:'alert-circle',info:'info'};
    var icn=document.createElement('i');
    icn.setAttribute('data-lucide',iconMap[type]||'info');
    icn.setAttribute('size','16');
    t.appendChild(icn);
    var s=document.createElement('span');
    s.style.flex='1';
    s.textContent=message;
    t.appendChild(s);
    var x=document.createElement('button');
    x.className='toast-close';
    x.innerHTML='&times;';
    x.onclick=function(){t.remove()};
    t.appendChild(x);
    c.appendChild(t);
    if(typeof lucide!=='undefined')lucide.createIcons();
    setTimeout(function(){if(t.parentNode){t.style.transition='all .3s';t.style.transform='translateX(100%)';t.style.opacity='0';setTimeout(function(){t.remove()},300)}},4000);
}

// Confirm Modal
function showConfirmModal(title, desc, actionData, isCritical){
    document.getElementById('confirmTitle').textContent=title;
    document.getElementById('confirmDesc').textContent=desc;
    document.getElementById('confirmReason').value='';
    document.getElementById('confirmModal').classList.add('active');
    var btn=document.getElementById('confirmBtn');
    if(isCritical){btn.className='btn btn-danger';btn.textContent='Yes, '+title.replace('Confirm ','')}
    else{btn.className='btn btn-primary';btn.textContent='Confirm'}
    pendingAction=actionData;
}
function closeConfirmModal(){
    document.getElementById('confirmModal').classList.remove('active');
    pendingAction=null;
}
function executeConfirmedAction(){
    if(!pendingAction){closeConfirmModal();return}
    var reason=document.getElementById('confirmReason').value.trim();
    pendingAction.reason=reason;
    pendingAction.csrf_token=CSRF_TOKEN;
    if(pendingAction.isAlert){
        sendAlertAction(pendingAction);
    } else {
        sendLogAction(pendingAction);
    }
    closeConfirmModal();
}

// Log Actions
function sendLogAction(data){
    var form=new FormData();
    form.append('log_id',data.logId);
    form.append('action_type',data.actionType);
    form.append('csrf_token',data.csrf_token||CSRF_TOKEN);
    if(data.reason)form.append('reason',data.reason);

    fetch(BASE_URL+'api?action=log_action',{method:'POST',body:form})
    .then(function(r){return r.json()})
    .then(function(j){
        if(j.success){
            showToast(j.message,'success');
            var row=document.querySelector('#detail-'+data.logId);
            if(row){
                var statusCell=row.closest('tr').previousElementSibling;
                if(statusCell){
                    var badge=statusCell.querySelector('.siem-badge');
                    if(badge){
                        var newStatus=data.actionType.replace('mark_','').replace('ignore','ignored');
                        var statusMap={safe:'normal',suspicious:'suspicious',malicious:'malicious',ignore:'ignored',block_user:'blocked',block_ip:'blocked',unblock_user:'normal',unblock_ip:'normal'};
                        var ns=statusMap[data.actionType]||newStatus;
                        badge.className='siem-badge '+ns;
                        badge.innerHTML='<span class="severity-dot '+ns+'"></span>'+ns.charAt(0).toUpperCase()+ns.slice(1);

                        var tr=badge.closest('tr');
                        tr.className='log-row'+(ns!=='normal'?' '+ns:'');
                    }
                }
            }
            // Reload stats after action
            setTimeout(function(){location.reload()},1500);
        } else {
            showToast(j.error||'Action failed','error');
        }
    })
    .catch(function(err){showToast('Network error','error')});
}

// Alert Actions
function sendAlertAction(data){
    var form=new FormData();
    form.append('alert_id',data.alertId);
    form.append('action_type',data.actionType);
    form.append('csrf_token',data.csrf_token||CSRF_TOKEN);
    if(data.reason)form.append('reason',data.reason);

    fetch(BASE_URL+'api?action=alert_action',{method:'POST',body:form})
    .then(function(r){return r.json()})
    .then(function(j){
        if(j.success){
            showToast(j.message,'success');
            // Update alert badge
            var alertEl=document.getElementById('alert-'+data.alertId);
            if(alertEl){
                var badge=alertEl.querySelector('.sev-badge');
                if(badge){
                    var labelMap={mark_new:'new',acknowledge:'acknowledged',resolve:'resolved',reopen:'reopened'};
                    var nl=labelMap[data.actionType]||'new';
                    badge.textContent=nl.charAt(0).toUpperCase()+nl.slice(1);
                }
                if(data.actionType==='resolve'){
                    alertEl.style.opacity='.6';
                } else if(data.actionType==='reopen'){
                    alertEl.style.opacity='1';
                }
            }
            // Reload to update stats
            setTimeout(function(){location.reload()},1500);
        } else {
            showToast(j.error||'Action failed','error');
        }
    })
    .catch(function(err){showToast('Network error','error')});
}

// Investigation
function showInvestigateModal(title, contentHtml){
    document.getElementById('investigateTitle').textContent=title;
    document.getElementById('investigateContent').innerHTML=contentHtml;
    document.getElementById('investigateModal').classList.add('active');
    if(typeof lucide!=='undefined')lucide.createIcons();
}
function closeInvestigateModal(){
    document.getElementById('investigateModal').classList.remove('active');
}

function fetchInvestigation(logId, type){
    var titleMap={related:'Related Logs',history:'Action History',user:'User Activity Timeline',ip:'IP Activity History'};
    document.getElementById('investigateTitle').textContent=titleMap[type]||'Investigation';
    document.getElementById('investigateContent').innerHTML='<div class="investigate-loading"><i data-lucide="loader" size="24" class="spin"></i> Loading...</div>';
    document.getElementById('investigateModal').classList.add('active');
    if(typeof lucide!=='undefined')lucide.createIcons();

    var url=BASE_URL+'api?action=investigate&log_id='+logId+'&type='+type;
    fetch(url)
    .then(function(r){return r.json()})
    .then(function(j){
        if(!j.success){document.getElementById('investigateContent').innerHTML='<div class="investigate-empty">Failed to load data</div>';return}
        var html='';
        if(type==='related'&&j.data.logs){
            var logs=j.data.logs;
            if(logs.length===0){html='<div class="investigate-empty">No related logs found</div>'}
            else{
                html='<p style="font-size:13px;color:var(--text-muted);margin-bottom:12px">Found '+j.data.count+' related log(s)</p>';
                html+='<table class="investigate-table"><thead><tr><th>Time</th><th>User</th><th>Action</th><th>Sev</th><th>Status</th><th>IP</th></tr></thead><tbody>';
                logs.forEach(function(lg){
                    var sev=lg.severity||'low';
                    html+='<tr><td style="white-space:nowrap">'+timeAgo(lg.created_at)+'</td><td>'+(lg.user_name||'System')+'</td><td><code style="font-size:10px">'+esc(lg.action)+'</code></td><td><span class="sev-dot '+sev+'"></span>'+sev+'</td><td><span class="siem-badge '+(lg.status||'normal')+'">'+(lg.status||'normal')+'</span></td><td style="font-family:monospace">'+(lg.ip_address||'-')+'</td></tr>';
                });
                html+='</tbody></table>';
            }
        } else if(type==='history'&&j.data.actions){
            var acts=j.data.actions;
            if(acts.length===0){html='<div class="investigate-empty">No actions taken on this log yet</div>'}
            else{
                html='<p style="font-size:13px;color:var(--text-muted);margin-bottom:12px">Found '+j.data.count+' action(s)</p>';
                html+='<table class="investigate-table"><thead><tr><th>Time</th><th>Admin</th><th>Action Type</th><th>Target</th></tr></thead><tbody>';
                acts.forEach(function(a){
                    html+='<tr><td style="white-space:nowrap">'+timeAgo(a.created_at)+'</td><td>'+(a.performed_by_name||'System')+'</td><td><code style="font-size:10px">'+esc(a.action_type)+'</code></td><td>'+(a.target_user_id?'User #'+a.target_user_id:'')+(a.target_ip?' IP: '+a.target_ip:'-')+'</td></tr>';
                });
                html+='</tbody></table>';
            }
        } else if(type==='user'&&j.data.logs){
            var ulogs=j.data.logs;
            if(ulogs.length===0){html='<div class="investigate-empty">No activity found for this user</div>'}
            else{
                html='<p style="font-size:13px;color:var(--text-muted);margin-bottom:12px">User activity timeline - '+j.data.count+' event(s)</p>';
                html+='<table class="investigate-table"><thead><tr><th>Time</th><th>Action</th><th>Description</th><th>Sev</th><th>IP</th></tr></thead><tbody>';
                ulogs.forEach(function(lg){
                    var sev=lg.severity||'low';
                    html+='<tr><td style="white-space:nowrap">'+timeAgo(lg.created_at)+'</td><td><code style="font-size:10px">'+esc(lg.action)+'</code></td><td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'+esc(lg.description||'')+'</td><td><span class="sev-dot '+sev+'"></span>'+sev+'</td><td style="font-family:monospace">'+(lg.ip_address||'-')+'</td></tr>';
                });
                html+='</tbody></table>';
            }
        } else if(type==='ip'&&j.data.logs){
            var ilogs=j.data.logs;
            if(ilogs.length===0){html='<div class="investigate-empty">No activity found for this IP</div>'}
            else{
                html='<p style="font-size:13px;color:var(--text-muted);margin-bottom:12px">IP activity history - '+j.data.count+' event(s)</p>';
                html+='<table class="investigate-table"><thead><tr><th>Time</th><th>User</th><th>Action</th><th>Sev</th><th>Status</th></tr></thead><tbody>';
                ilogs.forEach(function(lg){
                    var sev=lg.severity||'low';
                    html+='<tr><td style="white-space:nowrap">'+timeAgo(lg.created_at)+'</td><td>'+(lg.user_name||'System')+'</td><td><code style="font-size:10px">'+esc(lg.action)+'</code></td><td><span class="sev-dot '+sev+'"></span>'+sev+'</td><td><span class="siem-badge '+(lg.status||'normal')+'">'+(lg.status||'normal')+'</span></td></tr>';
                });
                html+='</tbody></table>';
            }
        } else {
            html='<div class="investigate-empty">No data available</div>';
        }
        document.getElementById('investigateContent').innerHTML=html;
        if(typeof lucide!=='undefined')lucide.createIcons();
    })
    .catch(function(err){document.getElementById('investigateContent').innerHTML='<div class="investigate-empty">Error loading data</div>'});
}

function timeAgo(dt){
    if(!dt)return 'N/A';
    var ts=new Date(dt.replace(' ','T')+'Z').getTime();
    if(isNaN(ts)){var p=dt.split(/[- :]/);ts=new Date(p[0],p[1]-1,p[2],p[3],p[4],p[5]).getTime()}
    var diff=Math.floor((Date.now()-ts)/1000);
    if(diff<60)return 'just now';
    if(diff<3600)return Math.floor(diff/60)+'m ago';
    if(diff<86400)return Math.floor(diff/3600)+'h ago';
    if(diff<604800)return Math.floor(diff/86400)+'d ago';
    return dt.substring(0,10);
}
function esc(s){
    if(!s)return'';
    var d=document.createElement('div');d.textContent=s;return d.innerHTML;
}

// Event Delegation
document.addEventListener('DOMContentLoaded',function(){
    document.getElementById('searchInput').addEventListener('keydown',function(e){if(e.key==='Enter')applyFilter()});

    // Log action buttons
    document.addEventListener('click',function(e){
        var btn=e.target.closest('[data-log-action]');
        if(!btn)return;
        e.preventDefault();
        e.stopPropagation();
        var actionType=btn.getAttribute('data-log-action');
        var logId=btn.getAttribute('data-log-id');
        var criticalActions={block_user:1,block_ip:1,force_logout:1,lock_login:1,mark_malicious:1};
        var confirmTitles={block_user:'Block User',block_ip:'Block IP Address',force_logout:'Force Logout User',lock_login:'Lock User Login',mark_malicious:'Mark as Malicious'};
        var confirmDescs={block_user:'This will immediately block the user account. They will not be able to login.',block_ip:'This will block all requests from this IP address.',force_logout:'This will terminate all active sessions for this user.',lock_login:'This will temporarily lock the user out for 15 minutes.',mark_malicious:'This marks the event as malicious activity.'};

        if(criticalActions[actionType]){
            showConfirmModal(
                'Confirm '+confirmTitles[actionType]||'Action',
                confirmDescs[actionType]||'Are you sure you want to perform this action?',
                {logId:logId,actionType:actionType,isAlert:false},
                true
            );
        } else {
            var form=new FormData();
            form.append('log_id',logId);
            form.append('action_type',actionType);
            form.append('csrf_token',CSRF_TOKEN);
            sendLogAction({logId:logId,actionType:actionType,isAlert:false});
        }
    });

    // Alert action buttons
    document.addEventListener('click',function(e){
        var btn=e.target.closest('[data-alert-action]');
        if(!btn)return;
        e.preventDefault();
        e.stopPropagation();
        var actionType=btn.getAttribute('data-alert-action');
        var alertId=btn.getAttribute('data-alert-id');
        var ip=btn.getAttribute('data-ip')||'';
        var criticalMap={block_ip_alert:1};
        if(criticalMap[actionType]){
            showConfirmModal(
                'Block IP Address',
                'Are you sure you want to block IP: '+ip+'?',
                {alertId:alertId,actionType:actionType,isAlert:true},
                true
            );
        } else {
            sendAlertAction({alertId:alertId,actionType:actionType,isAlert:true});
        }
    });

    // Investigate buttons
    document.addEventListener('click',function(e){
        var btn=e.target.closest('[data-investigate]');
        if(!btn)return;
        e.preventDefault();
        e.stopPropagation();
        var type=btn.getAttribute('data-investigate');
        var logId=btn.getAttribute('data-log-id');
        var userId=btn.getAttribute('data-user-id');
        var ip=btn.getAttribute('data-ip');

        if(type==='user'&&userId){
            document.getElementById('investigateTitle').textContent='User Activity Timeline';
            document.getElementById('investigateContent').innerHTML='<div class="investigate-loading"><i data-lucide="loader" size="24" class="spin"></i> Loading...</div>';
            document.getElementById('investigateModal').classList.add('active');
            if(typeof lucide!=='undefined')lucide.createIcons();
            fetch(BASE_URL+'api?action=user_activity&user_id='+userId)
            .then(function(r){return r.json()})
            .then(function(j){
                if(!j.success){document.getElementById('investigateContent').innerHTML='<div class="investigate-empty">Failed to load</div>';return}
                var html='<p style="font-size:13px;color:var(--text-muted);margin-bottom:12px">User activity timeline - '+j.data.count+' event(s)</p>';
                html+='<table class="investigate-table"><thead><tr><th>Time</th><th>Action</th><th>Description</th><th>Sev</th><th>Status</th><th>IP</th></tr></thead><tbody>';
                (j.data.logs||[]).forEach(function(lg){
                    var sev=lg.severity||'low';
                    html+='<tr><td style="white-space:nowrap">'+timeAgo(lg.created_at)+'</td><td><code style="font-size:10px">'+esc(lg.action)+'</code></td><td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'+esc(lg.description||'')+'</td><td><span class="sev-dot '+sev+'"></span>'+sev+'</td><td><span class="siem-badge '+(lg.status||'normal')+'">'+(lg.status||'normal')+'</span></td><td style="font-family:monospace">'+(lg.ip_address||'-')+'</td></tr>';
                });
                html+='</tbody></table>';
                document.getElementById('investigateContent').innerHTML=html;
                if(typeof lucide!=='undefined')lucide.createIcons();
            })
            .catch(function(){document.getElementById('investigateContent').innerHTML='<div class="investigate-empty">Error loading</div>'});
        } else if(type==='ip'&&ip){
            document.getElementById('investigateTitle').textContent='IP Activity History';
            document.getElementById('investigateContent').innerHTML='<div class="investigate-loading"><i data-lucide="loader" size="24" class="spin"></i> Loading...</div>';
            document.getElementById('investigateModal').classList.add('active');
            if(typeof lucide!=='undefined')lucide.createIcons();
            fetch(BASE_URL+'api?action=ip_activity&ip='+encodeURIComponent(ip))
            .then(function(r){return r.json()})
            .then(function(j){
                if(!j.success){document.getElementById('investigateContent').innerHTML='<div class="investigate-empty">Failed to load</div>';return}
                var html='<p style="font-size:13px;color:var(--text-muted);margin-bottom:12px">IP activity history - '+j.data.count+' event(s)</p>';
                html+='<table class="investigate-table"><thead><tr><th>Time</th><th>User</th><th>Action</th><th>Sev</th><th>Status</th></tr></thead><tbody>';
                (j.data.logs||[]).forEach(function(lg){
                    var sev=lg.severity||'low';
                    html+='<tr><td style="white-space:nowrap">'+timeAgo(lg.created_at)+'</td><td>'+(lg.user_name||'System')+'</td><td><code style="font-size:10px">'+esc(lg.action)+'</code></td><td><span class="sev-dot '+sev+'"></span>'+sev+'</td><td><span class="siem-badge '+(lg.status||'normal')+'">'+(lg.status||'normal')+'</span></td></tr>';
                });
                html+='</tbody></table>';
                document.getElementById('investigateContent').innerHTML=html;
                if(typeof lucide!=='undefined')lucide.createIcons();
            })
            .catch(function(){document.getElementById('investigateContent').innerHTML='<div class="investigate-empty">Error loading</div>'});
        } else {
            fetchInvestigation(logId, type);
        }
    });

    // Close modals on overlay click
    document.getElementById('confirmModal').addEventListener('click',function(e){
        if(e.target===this)closeConfirmModal();
    });
    document.getElementById('investigateModal').addEventListener('click',function(e){
        if(e.target===this)closeInvestigateModal();
    });
    // Escape key closes modals
    document.addEventListener('keydown',function(e){
        if(e.key==='Escape'){closeConfirmModal();closeInvestigateModal()}
    });
});
</script>
<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body></html>
