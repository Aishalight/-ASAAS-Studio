<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAuth();
$pageTitle = 'My Ratings';
$db = Database::getInstance()->getConnection();
$ratings = $db->prepare("SELECT r.*, p.title as item_title FROM ratings r LEFT JOIN portfolio_projects p ON r.item_id = p.id AND r.item_type = 'project' WHERE r.user_id = ? ORDER BY r.created_at DESC");
$ratings->execute([getCurrentUserId()]);
$ratings = $ratings->fetchAll();
require __DIR__ . '/../includes/user-header.php';
?>
<style>
:root {
    --primary: #E8632A; --primary-dark: #d4551f;
    --bg-dark: #1a1a2e; --bg-white: #fff; --bg-light: #f8f9fb;
    --border: #e8e8f0; --text-primary: #1a1a2e; --text-secondary: #4a4a6a;
    --text-muted: #8a8aaa; --radius-lg: 16px; --radius-md: 12px;
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
    --sidebar-width: 260px; --transition: 0.3s ease;
}

* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Inter', sans-serif; font-size: 16px; color: var(--text-primary); background: var(--bg-light); }
.dashboard-layout { display: flex; min-height: 100vh; background: var(--bg-light); }

/* SIDEBAR */
.user-sidebar { width: var(--sidebar-width); background: var(--bg-dark); color: white; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 100; transition: transform var(--transition); }
.user-sidebar-brand { display: flex; align-items: center; gap: 10px; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.08); }
.user-sidebar-logo { width: 34px; height: 34px; min-width: 34px; background: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 16px; color: white; }
.user-sidebar-name { font-size: 17px; font-weight: 800; letter-spacing: 0.02em; }
.user-sidebar-user { display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); }
.user-sidebar-avatar { width: 38px; height: 38px; border-radius: 50%; min-width: 38px; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 15px; color: white; box-shadow: 0 2px 8px rgba(232,99,42,0.3); }
.user-sidebar-user-info { overflow: hidden; }
.user-sidebar-user-name { font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-sidebar-user-role { font-size: 12px; color: rgba(255,255,255,0.5); }
.user-sidebar-nav { flex: 1; padding: 12px; overflow-y: auto; }
.user-sidebar-section-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: rgba(255,255,255,0.3); padding: 0 10px; margin-bottom: 6px; }
.user-sidebar-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; color: rgba(255,255,255,0.65); text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.2s; margin-bottom: 2px; }
.user-sidebar-item:hover { background: rgba(255,255,255,0.06); color: white; }
.user-sidebar-item.active { background: rgba(255,255,255,0.1); color: white; position: relative; }
.user-sidebar-item.active::before { content: ''; position: absolute; left: 0; top: 6px; bottom: 6px; width: 3px; background: var(--primary); border-radius: 0 3px 3px 0; }
.user-sidebar-item-icon { width: 20px; height: 20px; min-width: 20px; display: flex; align-items: center; justify-content: center; }
.user-sidebar-item-text { overflow: hidden; white-space: nowrap; }
.user-sidebar-danger { color: rgba(244,67,54,0.7) !important; }
.user-sidebar-danger:hover { background: rgba(244,67,54,0.1) !important; color: #F44336 !important; }
.user-sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; backdrop-filter: blur(4px); }
.user-sidebar-overlay.active { display: block; }

/* MAIN */
.user-main { flex: 1; margin-left: var(--sidebar-width); min-height: 100vh; }
.user-topbar { display: flex; align-items: center; justify-content: space-between; padding: 0 32px; height: 64px; background: rgba(255,255,255,0.9); backdrop-filter: blur(16px); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 50; }
.user-topbar-left { display: flex; align-items: center; gap: 12px; }
.user-sidebar-toggle { display: none; background: none; border: none; cursor: pointer; color: var(--text-secondary); padding: 6px; border-radius: 8px; transition: all 0.2s; }
.user-sidebar-toggle:hover { background: var(--bg-light); color: var(--text-primary); }
.user-topbar-title { font-size: 17px; font-weight: 700; color: var(--text-primary); }
.user-topbar-right { display: flex; align-items: center; gap: 8px; }
.user-topbar-icon-wrap { position: relative; color: var(--text-secondary); text-decoration: none; padding: 8px; border-radius: 8px; transition: all 0.2s; }
.user-topbar-icon-wrap:hover { background: var(--bg-light); color: var(--text-primary); }
.user-topbar-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; text-decoration: none; box-shadow: 0 2px 8px rgba(232,99,42,0.3); transition: transform 0.2s; margin-left: 4px; }
.user-topbar-avatar:hover { transform: scale(1.05); }
.user-content { padding: 28px 32px; max-width: 1120px; }

/* PAGE HEADER */
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 800; }
.page-subtitle { font-size: 14px; color: var(--text-muted); margin-top: 4px; }

/* BUTTON */
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border: none; border-radius: 12px; font-family: 'Inter', sans-serif; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; }
.btn-primary { background: var(--primary); color: white; }
.btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 8px 25px rgba(232,99,42,0.35); color: white; }

/* BADGE */
.badge { display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }
.badge-primary { background: rgba(232,99,42,0.1); color: var(--primary); }

/* RATINGS */
.ratings-card { background: var(--bg-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 24px; }
.rating-card { display: flex; align-items: flex-start; gap: 16px; padding: 16px 0; border-bottom: 1px solid var(--border); }
.rating-card:last-child { border-bottom: none; }
.rating-card-body { flex: 1; }
.rating-card-stars { display: flex; gap: 2px; margin-bottom: 6px; }
.rating-card-item { font-size: 14px; font-weight: 600; }
.rating-card-review { font-size: 13px; color: var(--text-secondary); margin-top: 4px; }
.rating-card-date { font-size: 12px; color: var(--text-muted); margin-top: 6px; }

/* EMPTY STATE */
.empty-state { text-align: center; padding: 80px 24px; }
.empty-state-icon { width: 80px; height: 80px; margin: 0 auto 24px; background: var(--bg-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-muted); }
.empty-state-title { font-size: 20px; font-weight: 700; margin-bottom: 8px; }
.empty-state-desc { color: var(--text-secondary); margin-bottom: 24px; }

/* ANIMATIONS */
.fade-in-up { opacity: 0; transform: translateY(30px); animation: fadeInUp 0.6s ease forwards; }
@keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
.reveal { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease, transform 0.6s ease; }
.reveal.visible { opacity: 1; transform: translateY(0); }

/* RESPONSIVE */
@media (max-width: 1024px) {
    .user-sidebar { transform: translateX(-100%); }
    .user-sidebar.active { transform: translateX(0); }
    .user-sidebar-toggle { display: block; }
    .user-main { margin-left: 0; }
    .user-topbar { padding: 0 20px; }
    .user-content { padding: 24px 20px; }
}
@media (max-width: 768px) { .user-content { padding: 16px; } }
</style>

<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">My Ratings</h1>
        <p class="page-subtitle">Projects and services you've rated.</p>
    </div>
</div>

<div class="ratings-card reveal">
    <?php if (empty($ratings)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"><i data-lucide="star" size="32"></i></div>
            <h3 class="empty-state-title">No ratings yet</h3>
            <p class="empty-state-desc">You haven't rated any projects or services yet. Browse our portfolio to leave a rating.</p>
            <a href="<?= BASE_URL ?>portfolio" class="btn btn-primary">Browse Portfolio <i data-lucide="arrow-right" size="16"></i></a>
        </div>
    <?php else: foreach ($ratings as $r): ?>
        <div class="rating-card">
            <div class="rating-card-body">
                <div class="rating-card-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i data-lucide="star" width="16" height="16" fill="<?= $i <= $r['rating'] ? '#FFC107' : 'none' ?>" color="#FFC107"></i>
                    <?php endfor; ?>
                </div>
                <div class="rating-card-item"><?= htmlspecialchars($r['item_title'] ?? ucfirst($r['item_type']) . ' #' . $r['item_id']) ?></div>
                <?php if ($r['review']): ?>
                    <div class="rating-card-review"><?= htmlspecialchars($r['review']) ?></div>
                <?php endif; ?>
                <div class="rating-card-date"><?= formatDate($r['created_at']) ?></div>
            </div>
            <span class="badge badge-primary"><?= ucfirst($r['item_type']) ?></span>
        </div>
    <?php endforeach; endif; ?>
</div>

<?php require __DIR__ . '/../includes/user-footer.php'; ?>
