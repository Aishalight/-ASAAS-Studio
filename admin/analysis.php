<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Analysis'; require __DIR__ . '/../includes/admin-header.php';

$period = $_GET['period'] ?? 'all';
$stats = getVisitStats($period);
$byDevice = getVisitsByDevice($period === 'all' ? 'all' : 30);
$byBrowser = getVisitsByBrowser($period === 'all' ? 'all' : 30);
$byOS = getVisitsByOS($period === 'all' ? 'all' : 30);
$byPage = getVisitsByPage($period === 'all' ? 'all' : 30, 10);
$dailyVisits = getDailyVisits(30);
$growthSign = $stats['growth'] >= 0 ? 'positive' : 'negative';
$growthPrefix = $stats['growth'] >= 0 ? '+' : '';
?>

<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Analysis</h1>
        <p class="page-subtitle">Real-time website analytics and visitor insights.</p>
    </div>
    <div class="page-actions">
        <select class="form-select" style="width:auto;min-width:140px" onchange="location.href='?period='+this.value">
            <option value="all" <?= $period === 'all' ? 'selected' : '' ?>>All Time</option>
            <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>Last 30 Days</option>
            <option value="week" <?= $period === 'week' ? 'selected' : '' ?>>Last 7 Days</option>
            <option value="today" <?= $period === 'today' ? 'selected' : '' ?>>Today</option>
        </select>
        <button class="btn btn-secondary btn-sm" onclick="location.reload()"><i data-lucide="refresh-cw" size="16"></i> Refresh</button>
    </div>
</div>

<div class="stats-grid fade-in-up">
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Total Visits</span>
            <div class="stat-card-icon blue"><i data-lucide="eye" size="22"></i></div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['total']) ?></div>
        <div class="stat-card-label">All time page views</div>
        <div class="stat-card-change <?= $growthSign ?>"><?= $growthPrefix ?><?= $stats['growth'] ?>% vs yesterday</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Unique Visitors</span>
            <div class="stat-card-icon purple"><i data-lucide="users" size="22"></i></div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['unique']) ?></div>
        <div class="stat-card-label">Unique IP addresses</div>
        <div class="stat-card-change positive">Across selected period</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Today</span>
            <div class="stat-card-icon green"><i data-lucide="activity" size="22"></i></div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['today']) ?></div>
        <div class="stat-card-label">Visits today</div>
        <div class="stat-card-change <?= $growthSign ?>"><?= $growthPrefix ?><?= $stats['growth'] ?>% vs yesterday</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Yesterday</span>
            <div class="stat-card-icon orange"><i data-lucide="calendar" size="22"></i></div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['yesterday']) ?></div>
        <div class="stat-card-label">Visits yesterday</div>
        <div class="stat-card-change">Baseline comparison</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:32px">
    <div class="chart-container reveal">
        <div class="chart-header">
            <h3 class="chart-title">Daily Visits (Last 30 Days)</h3>
        </div>
        <div class="chart-body"><canvas id="visitLineChart"></canvas></div>
    </div>
    <div class="chart-container reveal">
        <div class="chart-header">
            <h3 class="chart-title">Devices</h3>
        </div>
        <div class="chart-body"><canvas id="devicePieChart"></canvas></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:24px;margin-bottom:32px">
    <div class="chart-container reveal">
        <div class="chart-header">
            <h3 class="chart-title">Browsers</h3>
        </div>
        <div class="chart-body"><canvas id="browserBarChart"></canvas></div>
    </div>
    <div class="chart-container reveal">
        <div class="chart-header">
            <h3 class="chart-title">Operating Systems</h3>
        </div>
        <div class="chart-body"><canvas id="osBarChart"></canvas></div>
    </div>
    <div class="reveal" style="background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:24px">
        <h3 style="font-size:18px;font-weight:700;margin-bottom:16px">Top Pages</h3>
        <div class="activity-feed">
            <?php if (empty($byPage)): ?>
                <p style="color:var(--text-muted);font-size:14px;text-align:center;padding:24px 0">No data yet</p>
            <?php else:
                $maxCount = (int)$byPage[0]['count'];
                foreach ($byPage as $p):
                    $pct = $maxCount > 0 ? round(($p['count'] / $maxCount) * 100) : 0; ?>
                <div class="activity-item">
                    <div class="activity-icon info"><i data-lucide="file-text" size="14"></i></div>
                    <div class="activity-content" style="width:100%">
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <div class="activity-action"><?= htmlspecialchars($p['page_url']) ?></div>
                            <span style="font-size:13px;font-weight:600"><?= number_format($p['count']) ?></span>
                        </div>
                        <div style="margin-top:6px;height:4px;background:var(--bg-light);border-radius:4px;overflow:hidden">
                            <div style="height:100%;width:<?= $pct ?>%;background:var(--primary);border-radius:4px"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<script>
const dailyData = <?= json_encode($dailyVisits) ?>;
const deviceData = <?= json_encode($byDevice) ?>;
const browserData = <?= json_encode($byBrowser) ?>;
const osData = <?= json_encode($byOS) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>
lucide.createIcons();

Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.font.size = 12;

var primaryColor = '#E8632A';
var primaryLight = 'rgba(232,99,42,0.15)';
var gridColor = '#f0f0f0';
var labelColor = '#8a8aaa';

if (dailyData.length > 0) {
    new Chart(document.getElementById('visitLineChart'), {
        type: 'line',
        data: {
            labels: dailyData.map(function(d) { return d.visit_date; }),
            datasets: [{
                label: 'Visits',
                data: dailyData.map(function(d) { return parseInt(d.count); }),
                borderColor: primaryColor,
                backgroundColor: primaryLight,
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: primaryColor,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1a2e',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(ctx) { return ctx.parsed.y + ' visits'; }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: labelColor, maxRotation: 45, maxTicksLimit: 8 }
                },
                y: {
                    grid: { color: gridColor },
                    ticks: { color: labelColor },
                    beginAtZero: true
                }
            }
        }
    });
}

if (deviceData.length > 0) {
    new Chart(document.getElementById('devicePieChart'), {
        type: 'doughnut',
        data: {
            labels: deviceData.map(function(d) { return d.device_type.charAt(0).toUpperCase() + d.device_type.slice(1); }),
            datasets: [{
                data: deviceData.map(function(d) { return parseInt(d.count); }),
                backgroundColor: ['#2196F3', '#E8632A', '#9C27B0', '#4CAF50', '#FF9800'],
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } },
                tooltip: {
                    backgroundColor: '#1a1a2e',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx) {
                            var total = ctx.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                            var pct = Math.round((ctx.parsed / total) * 100);
                            return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });
}

var browserColors = ['#2196F3', '#FF9800', '#9C27B0', '#4CAF50', '#F44336', '#00BCD4', '#795548'];
if (browserData.length > 0) {
    new Chart(document.getElementById('browserBarChart'), {
        type: 'bar',
        data: {
            labels: browserData.map(function(d) { return d.browser || 'Unknown'; }),
            datasets: [{
                label: 'Visits',
                data: browserData.map(function(d) { return parseInt(d.count); }),
                backgroundColor: browserColors.slice(0, browserData.length),
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 40
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1a2e',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(ctx) { return ctx.parsed.x + ' visits'; }
                    }
                }
            },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: labelColor }, beginAtZero: true },
                y: { grid: { display: false }, ticks: { color: labelColor, font: { weight: 600 } } }
            }
        }
    });
}

if (osData.length > 0) {
    new Chart(document.getElementById('osBarChart'), {
        type: 'bar',
        data: {
            labels: osData.map(function(d) { return d.os || 'Unknown'; }),
            datasets: [{
                label: 'Visits',
                data: osData.map(function(d) { return parseInt(d.count); }),
                backgroundColor: primaryColor,
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 40
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1a2e',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(ctx) { return ctx.parsed.x + ' visits'; }
                    }
                }
            },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: labelColor }, beginAtZero: true },
                y: { grid: { display: false }, ticks: { color: labelColor, font: { weight: 600 } } }
            }
        }
    });
}
</script>
</body></html>
