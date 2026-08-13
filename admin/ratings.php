<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Ratings'; require __DIR__ . '/../includes/admin-header.php';
$db = Database::getInstance()->getConnection();

// Handle actions via POST
$action = '';
$ratingId = 0;
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating_action'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid security token.';
    } else {
        $action = $_POST['rating_action'];
        $ratingId = (int)($_POST['rating_id'] ?? 0);
        if ($ratingId) {
            if ($action === 'approve') {
                $stmt = $db->prepare("UPDATE ratings SET is_approved = 1 WHERE id = ?");
                $stmt->execute([$ratingId]);
                logActivity('rating_approve', "Admin approved rating ID: $ratingId", [], 'info');
                $msg = 'Rating approved';
            } elseif ($action === 'unapprove') {
                $stmt = $db->prepare("UPDATE ratings SET is_approved = 0 WHERE id = ?");
                $stmt->execute([$ratingId]);
                logActivity('rating_unapprove', "Admin unapproved rating ID: $ratingId", [], 'info');
                $msg = 'Rating unapproved';
            } elseif ($action === 'delete') {
                $stmt = $db->prepare("DELETE FROM ratings WHERE id = ?");
                $stmt->execute([$ratingId]);
                logActivity('rating_delete', "Admin deleted rating ID: $ratingId", [], 'warning');
                $msg = 'Rating deleted';
            }
        }
    }
}

// Handle POST: add rating
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_rating'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid security token';
    } else {
        $userIds = $_POST['user_ids'] ?? [];
        if (!is_array($userIds)) $userIds = [$userIds];
        $userIds = array_map('intval', $userIds);
        $userIds = array_filter($userIds, function($id) { return $id > 0; });
        $itemType = sanitize($_POST['item_type'] ?? '');
        $itemId = (int)($_POST['item_id'] ?? 1);
        $rating = (int)($_POST['rating'] ?? 0);
        $review = sanitize($_POST['review'] ?? '');
        if (!empty($userIds) && $itemType && $rating >= 1 && $rating <= 5 && in_array($itemType, ['project', 'service', 'post', 'business'])) {
            $stmt = $db->prepare("INSERT INTO ratings (user_id, item_id, item_type, rating, review, is_approved) VALUES (?, ?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE rating = VALUES(rating), review = VALUES(review), is_approved = 1");
            $count = 0;
            foreach ($userIds as $uid) {
                $stmt->execute([$uid, $itemId, $itemType, $rating, $review]);
                $count++;
            }
            logActivity('rating_added', "Admin added {$itemType} rating for {$count} user(s)", ['rating' => $rating], 'info');
            $msg = "Rating added for {$count} user(s)";
        } else {
            $msg = 'Invalid rating data';
        }
    }
}

// Filters
$typeFilter = $_GET['type'] ?? '';
$where = '';
$params = [];
if ($typeFilter && in_array($typeFilter, ['project', 'service', 'post', 'business'])) {
    $where = "WHERE r.item_type = ?";
    $params[] = $typeFilter;
}

$ratings = $db->prepare("SELECT r.*, COALESCE(u.name, r.guest_name, 'Guest') as user_name FROM ratings r LEFT JOIN users u ON r.user_id = u.id $where ORDER BY r.is_approved ASC, r.created_at DESC");
$ratings->execute($params);
$ratings = $ratings->fetchAll();

$stats = $db->query("SELECT COUNT(*) as total, ROUND(AVG(rating),1) as avg, SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending FROM ratings")->fetch();
$typeCounts = $db->query("SELECT item_type, COUNT(*) as c FROM ratings GROUP BY item_type")->fetchAll();
$typeCountMap = []; foreach ($typeCounts as $t) $typeCountMap[$t['item_type']] = $t['c'];
$users = $db->query("SELECT id, name, username FROM users WHERE status = 'active' ORDER BY name ASC")->fetchAll();

$typeLabels = ['business' => 'Business', 'project' => 'Project', 'service' => 'Service', 'post' => 'Post'];

function ratingItemName($db, $type, $id) {
    $id = (int)$id;
    if ($type === 'business') return 'ASAAS STUDIO';
    if ($type === 'project') $table = 'portfolio_projects';
    elseif ($type === 'service') $table = 'services';
    elseif ($type === 'post') $table = 'posts';
    else return '#' . $id;
    $stmt = $db->prepare("SELECT title FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $title = $stmt->fetchColumn();
    return $title ?: ('#' . $id);
}
?>
<style>
.rating-user{display:flex;align-items:center;gap:8px}
.rating-user-avatar{width:28px;height:28px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:11px}
.rating-item-type{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted)}
.rating-stars{display:inline-flex;gap:2px;vertical-align:middle}
.rating-review{max-width:240px;font-size:13px;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.rating-review:hover{white-space:normal;overflow:visible}
.admin-star-btn{background:none;border:none;cursor:pointer;padding:2px;color:#d0d0d0;transition:transform .15s ease,color .15s ease;font-size:0;line-height:0}
.admin-star-btn:hover{transform:scale(1.2)}
.admin-star-btn.active,.admin-star-btn.hovered{color:#FFC107}
</style>
<?php if ($msg): ?>
<div class="alert alert-info" style="margin-bottom:16px"><i data-lucide="info" size="18"></i> <?= $msg ?></div>
<?php endif; ?>
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Ratings</h1>
        <p class="page-subtitle">Manage user ratings and reviews</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('addRatingModal')">
        <i data-lucide="plus" size="16"></i> Add Rating
    </button>
</div>

<div class="stats-grid" style="margin-bottom:24px">
    <div class="stat-card reveal">
        <div class="stat-icon" style="background:rgba(232,99,42,0.1);color:var(--primary)"><i data-lucide="star" size="22"></i></div>
        <div><div class="stat-value"><?= $stats['total'] ?></div><div class="stat-label">Total Ratings</div></div>
    </div>
    <div class="stat-card reveal">
        <div class="stat-icon" style="background:rgba(76,175,80,0.1);color:#4CAF50"><i data-lucide="award" size="22"></i></div>
        <div><div class="stat-value"><?= $stats['avg'] ?? '-' ?></div><div class="stat-label">Average Rating</div></div>
    </div>
    <div class="stat-card reveal">
        <div class="stat-icon" style="background:rgba(255,152,0,0.1);color:#FF9800"><i data-lucide="clock" size="22"></i></div>
        <div><div class="stat-value"><?= (int)$stats['pending'] ?></div><div class="stat-label">Pending Approval</div></div>
    </div>
</div>

<div class="table-container reveal">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;flex-wrap:wrap">
        <a href="?type=" class="btn btn-sm <?= !$typeFilter ? 'btn-primary' : 'btn-ghost' ?>">All (<?= array_sum($typeCountMap) ?: 0 ?>)</a>
        <?php foreach (['project' => 'Projects', 'service' => 'Services', 'post' => 'Posts', 'business' => 'Business'] as $val => $label): ?>
            <a href="?type=<?= $val ?>" class="btn btn-sm <?= $typeFilter === $val ? 'btn-primary' : 'btn-ghost' ?>"><?= $label ?> (<?= (int)($typeCountMap[$val] ?? 0) ?>)</a>
        <?php endforeach; ?>
    </div>
    <table class="table">
        <thead><tr><th>User</th><th>Type</th><th>Item</th><th>Rating</th><th>Review</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if (empty($ratings)): ?>
                <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">No ratings found</td></tr>
            <?php else: foreach ($ratings as $r): ?>
                <tr>
                    <td>
                        <div class="rating-user">
                            <div class="rating-user-avatar"><?= strtoupper($r['user_name'][0]) ?></div>
                            <span style="font-weight:500;font-size:13px"><?= htmlspecialchars($r['user_name']) ?></span>
                        </div>
                    </td>
                    <td><span class="rating-item-type"><?= $typeLabels[$r['item_type']] ?? $r['item_type'] ?></span></td>
                    <td style="font-size:13px;font-weight:500">
                        <?= htmlspecialchars(ratingItemName($db, $r['item_type'], $r['item_id'])) ?>
                        <?php if ($r['item_type'] !== 'business'): ?>
                            <span style="color:var(--text-muted);font-weight:400">#<?= (int)$r['item_id'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="rating-stars">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <i data-lucide="star" width="13" height="13" fill="<?= $i < $r['rating'] ? '#FFC107' : 'none' ?>" color="#FFC107" style="display:inline"></i>
                            <?php endfor; ?>
                        </span>
                    </td>
                    <td><div class="rating-review" title="<?= htmlspecialchars($r['review'] ?? '') ?>"><?= htmlspecialchars($r['review'] ?? '-') ?></div></td>
                    <td><span class="badge badge-<?= $r['is_approved'] ? 'success' : 'warning' ?>"><?= $r['is_approved'] ? 'Approved' : 'Pending' ?></span></td>
                    <td style="font-size:12px;color:var(--text-muted)"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <?php if ($r['is_approved']): ?>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Unapprove this rating?')">
                                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                    <input type="hidden" name="rating_action" value="unapprove">
                                    <input type="hidden" name="rating_id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="type" value="<?= htmlspecialchars($typeFilter) ?>">
                                    <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="Unapprove" style="color:#FF9800;border:none;cursor:pointer;background:none"><i data-lucide="eye-off" size="14"></i></button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Approve this rating?')">
                                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                    <input type="hidden" name="rating_action" value="approve">
                                    <input type="hidden" name="rating_id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="type" value="<?= htmlspecialchars($typeFilter) ?>">
                                    <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="Approve" style="color:#4CAF50;border:none;cursor:pointer;background:none"><i data-lucide="check-circle" size="14"></i></button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this rating? This cannot be undone.')">
                                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                <input type="hidden" name="rating_action" value="delete">
                                <input type="hidden" name="rating_id" value="<?= $r['id'] ?>">
                                <input type="hidden" name="type" value="<?= htmlspecialchars($typeFilter) ?>">
                                <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="Delete" style="color:#F44336;border:none;cursor:pointer;background:none"><i data-lucide="trash-2" size="14"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Rating Modal -->
<div class="modal-overlay" id="addRatingModal">
    <div class="modal-content">
        <div class="modal-header"><h3 class="modal-title">Add Rating</h3><button class="modal-close" onclick="closeModal('addRatingModal')">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="add_rating" value="1">
                <input type="hidden" name="rating" id="adminRatingValue" value="0">
                <div class="form-group">
                    <label class="form-label">Users <span style="color:var(--text-muted);font-size:12px">(click to select multiple)</span></label>
                    <div style="border:1px solid var(--border);border-radius:var(--radius-sm);max-height:180px;overflow-y:auto;padding:4px" id="userSelectContainer">
                        <?php foreach ($users as $u): ?>
                            <label style="display:flex;align-items:center;gap:8px;padding:6px 8px;border-radius:4px;cursor:pointer;transition:background .15s;font-size:13px" class="user-option" data-id="<?= $u['id'] ?>" onclick="toggleUser(this)">
                                <input type="checkbox" name="user_ids[]" value="<?= $u['id'] ?>" style="accent-color:var(--primary)">
                                <span><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['username']) ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:6px">
                        <button type="button" class="btn btn-ghost btn-sm" onclick="document.querySelectorAll('#userSelectContainer input[type=checkbox]').forEach(function(c){c.checked=true;c.closest('.user-option').style.background='var(--primary-alpha)'})">Select All</button>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="document.querySelectorAll('#userSelectContainer input[type=checkbox]').forEach(function(c){c.checked=false;c.closest('.user-option').style.background=''})">Clear All</button>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Item Type</label>
                        <select name="item_type" class="form-select" id="adminItemType" required>
                            <option value="business" selected>Business</option>
                            <option value="project">Project</option>
                            <option value="service">Service</option>
                            <option value="post">Post</option>
                        </select>
                    </div>
                    <div class="form-group" id="adminItemIdGroup">
                        <label class="form-label">Item ID</label>
                        <input type="number" name="item_id" class="form-input" id="adminItemId" value="1" min="1" required>
                        <span style="font-size:11px;color:var(--text-muted)">Project / service / post ID</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Rating</label>
                    <div style="display:flex;gap:4px;margin-top:4px" id="adminStarRow">
                        <button class="admin-star-btn" type="button" data-star="1"><i data-lucide="star" size="28" fill="currentColor"></i></button>
                        <button class="admin-star-btn" type="button" data-star="2"><i data-lucide="star" size="28" fill="currentColor"></i></button>
                        <button class="admin-star-btn" type="button" data-star="3"><i data-lucide="star" size="28" fill="currentColor"></i></button>
                        <button class="admin-star-btn" type="button" data-star="4"><i data-lucide="star" size="28" fill="currentColor"></i></button>
                        <button class="admin-star-btn" type="button" data-star="5"><i data-lucide="star" size="28" fill="currentColor"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Review <span style="color:var(--text-muted);font-size:12px">(optional)</span></label>
                    <textarea name="review" class="form-input" rows="3" placeholder="Write a review..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addRatingModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Rating</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>
lucide.createIcons();
function openModal(id){document.getElementById(id).classList.add('active')}
function closeModal(id){document.getElementById(id).classList.remove('active')}
function toggleUser(el){var cb=el.querySelector('input[type=checkbox]');cb.checked=!cb.checked;el.style.background=cb.checked?'var(--primary-alpha)':''}
(function(){var sel=document.getElementById('adminItemType'),grp=document.getElementById('adminItemIdGroup');if(!sel||!grp)return;
function t(){grp.style.display=sel.value==='business'?'none':''}
sel.addEventListener('change',t);t();})();
(function(){
var row=document.getElementById('adminStarRow');if(!row)return;
var btns=row.querySelectorAll('.admin-star-btn'),hInput=document.getElementById('adminRatingValue');
btns.forEach(function(s,i){s.addEventListener('mouseenter',function(){btns.forEach(function(x,j){x.classList.toggle('hovered',j<=i)})});
s.addEventListener('mouseleave',function(){btns.forEach(function(x){x.classList.remove('hovered')})});
s.addEventListener('click',function(){var v=i+1;btns.forEach(function(x,j){x.classList.toggle('active',j<v)});hInput.value=v})});
})();
</script>
</body></html>
