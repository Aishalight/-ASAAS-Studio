<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Dashboard'; require __DIR__ . '/../includes/admin-header.php';

$db = Database::getInstance()->getConnection();
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalMessages = $db->query("SELECT COUNT(DISTINCT thread_id) FROM messages")->fetchColumn();
$totalPosts = $db->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
$totalProjects = $db->query("SELECT COUNT(*) FROM portfolio_projects")->fetchColumn();
$avgRating = $db->query("SELECT ROUND(AVG(rating),1) FROM ratings")->fetchColumn() ?: 0;
$recentRatings = $db->query("SELECT r.*, u.name as user_name FROM ratings r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 5")->fetchAll();
$ratingCount = (int)$db->query("SELECT COUNT(*) FROM ratings")->fetchColumn() ?: 0;
$pageViews = (int)$db->query("SELECT COUNT(*) FROM page_visits")->fetchColumn() ?: 0;
$recentLogs = $db->query("SELECT al.*, u.name as user_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 6")->fetchAll();
$onlineUsers = $db->query("SELECT id, name, email, avatar, role, last_activity FROM users WHERE last_activity IS NOT NULL ORDER BY last_activity DESC")->fetchAll();

// --- Real analytics data for charts ---

// 1. Line Chart: Monthly page views (last 12 months)
$monthlyVisitsRaw = $db->query("
    SELECT DATE_FORMAT(visit_date, '%Y-%m') as ym, COUNT(*) as count
    FROM page_visits
    WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
    GROUP BY ym ORDER BY ym ASC
")->fetchAll();
$monthlyMap = [];
foreach ($monthlyVisitsRaw as $m) $monthlyMap[$m['ym']] = (int)$m['count'];
$lineLabels = [];
$lineData = [];
for ($i = 11; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i months"));
    $lineLabels[] = date('M', strtotime("-$i months"));
    $lineData[] = $monthlyMap[$ym] ?? 0;
}

// 2. Pie Chart: Traffic sources from referrers
$sourcesRaw = $db->query("
    SELECT
        CASE
            WHEN referrer IS NULL OR referrer = '' THEN 'Direct'
            WHEN referrer LIKE '%google%' OR referrer LIKE '%bing%' OR referrer LIKE '%yahoo%' THEN 'Organic'
            WHEN referrer LIKE '%facebook%' OR referrer LIKE '%twitter%' OR referrer LIKE '%linkedin%' OR referrer LIKE '%instagram%' THEN 'Social'
            ELSE 'Referral'
        END as source,
        COUNT(*) as count
    FROM page_visits GROUP BY source ORDER BY count DESC
")->fetchAll();
$pieData = [];
$pieColors = ['#E8632A', '#2196F3', '#4CAF50', '#FF9800', '#9C27B0'];
$pieTotal = array_sum(array_column($sourcesRaw, 'count')) ?: 1;
foreach ($sourcesRaw as $i => $s) {
    $pieData[] = [
        'label' => $s['source'],
        'value' => round(($s['count'] / $pieTotal) * 100),
        'color' => $pieColors[$i % count($pieColors)]
    ];
}

// 3. Bar Chart: Visits by browser
$browsersRaw = $db->query("
    SELECT browser, COUNT(*) as count
    FROM page_visits GROUP BY browser ORDER BY count DESC
")->fetchAll();
$barLabels = [];
$barData = [];
$barColors = ['#E8632A', '#2196F3', '#4CAF50', '#9C27B0', '#FF9800', '#00BCD4'];
foreach ($browsersRaw as $b) {
    $barLabels[] = $b['browser'] ?: 'Unknown';
    $barData[] = (int)$b['count'];
}
?>
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, <?= htmlspecialchars(getCurrentUserName()) ?>. Here's what's happening.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary btn-sm" onclick="location.reload()"><i data-lucide="refresh-cw" size="16"></i> Refresh</button>
        <a href="<?= BASE_URL ?>admin-posts" class="btn btn-primary btn-sm"><i data-lucide="plus" size="16"></i> New Post</a>
    </div>
</div>

<div class="stats-grid fade-in-up">
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Total Users</span>
            <div class="stat-card-icon blue"><i data-lucide="users" size="22"></i></div>
        </div>
        <div class="stat-card-value dash-stat-value"><?= number_format($totalUsers) ?></div>
        <div class="stat-card-label">Registered accounts</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Messages</span>
            <div class="stat-card-icon orange"><i data-lucide="message-square" size="22"></i></div>
        </div>
        <div class="stat-card-value dash-stat-value"><?= number_format($totalMessages) ?></div>
        <div class="stat-card-label">Total conversations</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Blog Posts</span>
            <div class="stat-card-icon green"><i data-lucide="file-text" size="22"></i></div>
        </div>
        <div class="stat-card-value dash-stat-value"><?= number_format($totalPosts) ?></div>
        <div class="stat-card-label">Published articles</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Projects</span>
            <div class="stat-card-icon purple"><i data-lucide="briefcase" size="22"></i></div>
        </div>
        <div class="stat-card-value dash-stat-value"><?= number_format($totalProjects) ?></div>
        <div class="stat-card-label">Portfolio projects</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Ratings</span>
            <div class="stat-card-icon teal"><i data-lucide="star" size="22"></i></div>
        </div>
        <div class="stat-card-value dash-stat-value"><?= number_format($avgRating, 1) ?></div>
        <div class="stat-card-label">Average rating</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Page Views</span>
            <div class="stat-card-icon red"><i data-lucide="eye" size="22"></i></div>
        </div>
        <div class="stat-card-value dash-stat-value"><?= $pageViews >= 1000 ? number_format(round($pageViews / 1000, 1)) . 'K' : number_format($pageViews) ?></div>
        <div class="stat-card-label">Total page views</div>
    </div>
</div>

<div class="reveal" style="background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:24px;margin-bottom:24px">
    <h3 style="font-size:18px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px"><span style="width:10px;height:10px;border-radius:50%;background:#22c55e;display:inline-block"></span> Currently Online</h3>
    <div style="display:flex;flex-wrap:wrap;gap:10px">
        <?php
        $onlineFound = false;
        foreach ($onlineUsers as $ou):
            if (!isOnline($ou['last_activity'])) continue;
            $onlineFound = true;
            $ouAvatar = !empty($ou['avatar']) ? BASE_URL . $ou['avatar'] : '';
        ?>
            <div style="display:flex;align-items:center;gap:8px;padding:6px 12px;background:var(--bg-light);border-radius:50px;border:1px solid var(--border)">
                <div style="width:28px;height:28px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:11px;overflow:hidden;flex-shrink:0;<?= $ouAvatar ? 'background:none' : '' ?>">
                    <?php if ($ouAvatar): ?>
                        <img src="<?= $ouAvatar ?>" alt="" style="width:100%;height:100%;object-fit:cover">
                    <?php else: ?>
                        <?= strtoupper($ou['name'][0]) ?>
                    <?php endif; ?>
                </div>
                <span style="font-size:13px;font-weight:600"><?= htmlspecialchars($ou['name']) ?></span>
                <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block"></span>
            </div>
        <?php endforeach; ?>
        <?php if (!$onlineFound): ?>
            <p style="color:var(--text-muted);font-size:14px">No users currently online</p>
        <?php endif; ?>
    </div>
</div>

<div class="reveal" style="background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:24px;margin-bottom:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <h3 style="font-size:18px;font-weight:700;display:flex;align-items:center;gap:8px"><i data-lucide="star" size="18" style="color:var(--primary)"></i> Recent Ratings</h3>
        <a href="<?= BASE_URL ?>admin-ratings" style="font-size:13px;color:var(--primary);text-decoration:none;font-weight:600">View All &rarr;</a>
    </div>
    <?php if (empty($recentRatings)): ?>
        <p style="color:var(--text-muted);font-size:14px;text-align:center;padding:24px">No ratings yet</p>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:12px">
            <?php foreach ($recentRatings as $rr): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--bg-light);border-radius:var(--radius-md)">
                <div style="width:36px;height:36px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px;flex-shrink:0">
                    <?= strtoupper(substr($rr['user_name'] ?? 'U', 0, 1)) ?>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($rr['user_name'] ?? 'Unknown') ?></div>
                    <div style="font-size:12px;color:var(--text-muted)">
                        <?= htmlspecialchars(ucfirst($rr['item_type'] ?? '')) ?> &middot; <?= timeAgo($rr['created_at']) ?>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:2px;flex-shrink:0">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i data-lucide="star" size="14" style="color:<?= $i <= $rr['rating'] ? '#f59e0b' : '#d1d5db' ?>;fill:<?= $i <= $rr['rating'] ? '#f59e0b' : 'none' ?>"></i>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
window.chartData = {
    lineLabels: <?= json_encode($lineLabels) ?>,
    lineData: <?= json_encode($lineData) ?>,
    pieData: <?= json_encode($pieData) ?>,
    barLabels: <?= json_encode($barLabels) ?>,
    barData: <?= json_encode($barData) ?>
};
</script>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:32px">
    <div class="chart-container reveal">
        <div class="chart-header">
            <h3 class="chart-title">Monthly Page Views</h3>
            <select class="form-select" style="width:auto;min-width:120px">
                <option>Last 12 months</option>
                <option>Last 6 months</option>
                <option>Last 30 days</option>
            </select>
        </div>
        <div class="chart-body"><canvas id="lineChart"></canvas></div>
    </div>
    <div class="chart-container reveal">
        <div class="chart-header">
            <h3 class="chart-title">Traffic Sources</h3>
        </div>
        <div class="chart-body"><canvas id="pieChart"></canvas></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    <div class="chart-container reveal">
        <div class="chart-header">
            <h3 class="chart-title">Visits by Browser</h3>
        </div>
        <div class="chart-body"><canvas id="barChart"></canvas></div>
    </div>
    <div class="reveal" style="background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:24px">
        <h3 style="font-size:18px;font-weight:700;margin-bottom:16px">Recent Activity</h3>
        <div class="activity-feed">
            <?php if (empty($recentLogs)): ?>
                <p style="color:var(--text-muted);font-size:14px;text-align:center;padding:24px">No activity yet</p>
            <?php else: foreach ($recentLogs as $l): ?>
                <div class="activity-item">
                    <div class="activity-icon <?= $l['severity'] ?>"><i data-lucide="<?= $l['severity'] === 'success' ? 'check' : ($l['severity'] === 'critical' ? 'alert-triangle' : ($l['severity'] === 'warning' ? 'alert-circle' : 'circle')) ?>" size="14"></i></div>
                    <div class="activity-content">
                        <div class="activity-action"><?= htmlspecialchars($l['action']) ?></div>
                        <div class="activity-desc"><?= htmlspecialchars($l['description'] ?? '') ?></div>
                        <div class="activity-meta"><span><?= htmlspecialchars($l['user_name'] ?? 'System') ?> &middot; <?= timeAgo($l['created_at']) ?></span></div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>
lucide.createIcons();
var cd = window.chartData || {};
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.font.size = 12;
var primaryColor = '#E8632A';
var gridColor = '#f0f0f0';
var labelColor = '#8a8aaa';
var pieColors = ['#E8632A', '#2196F3', '#4CAF50', '#FF9800', '#9C27B0'];

if (document.getElementById('lineChart') && cd.lineData && cd.lineData.length) {
    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: cd.lineLabels,
            datasets: [{
                label: 'Page Views',
                data: cd.lineData,
                borderColor: primaryColor,
                backgroundColor: 'rgba(232,99,42,0.12)',
                fill: true, tension: 0.4, pointRadius: 4, pointHoverRadius: 7,
                pointBackgroundColor: primaryColor, pointBorderColor: '#fff', pointBorderWidth: 2, borderWidth: 3
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1a1a2e', padding: 12, cornerRadius: 8, displayColors: false, callbacks: { label: function(c) { return c.parsed.y + ' views'; } } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: labelColor } },
                y: { grid: { color: gridColor }, ticks: { color: labelColor }, beginAtZero: true }
            }
        }
    });
}

if (document.getElementById('pieChart') && cd.pieData && cd.pieData.length) {
    new Chart(document.getElementById('pieChart'), {
        type: 'doughnut',
        data: {
            labels: cd.pieData.map(function(d) { return d.label; }),
            datasets: [{
                data: cd.pieData.map(function(d) { return d.value; }),
                backgroundColor: cd.pieData.map(function(d) { return d.color; }),
                borderWidth: 3, borderColor: '#fff', hoverOffset: 8
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '60%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } },
                tooltip: { backgroundColor: '#1a1a2e', padding: 12, cornerRadius: 8, callbacks: { label: function(c) { return c.label + ': ' + c.parsed + '%'; } } }
            }
        }
    });
}

if (document.getElementById('barChart') && cd.barData && cd.barData.length) {
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: cd.barLabels,
            datasets: [{
                label: 'Visits',
                data: cd.barData,
                backgroundColor: ['#E8632A', '#2196F3', '#4CAF50', '#9C27B0', '#FF9800', '#00BCD4', '#795548'],
                borderRadius: 6, borderSkipped: false, maxBarThickness: 40
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1a1a2e', padding: 12, cornerRadius: 8, displayColors: false, callbacks: { label: function(c) { return c.parsed.y + ' visits'; } } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: labelColor } },
                y: { grid: { color: gridColor }, ticks: { color: labelColor }, beginAtZero: true }
            }
        }
    });
}
</script>
</body></html>
