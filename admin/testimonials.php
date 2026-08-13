<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Testimonials'; require __DIR__ . '/../includes/admin-header.php';

$db = Database::getInstance()->getConnection();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid security token.';
    } else {
        $action = $_POST['testimonial_action'] ?? '';
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $rating = (int)($_POST['rating'] ?? 5);
        if ($rating < 1) $rating = 1;
        if ($rating > 5) $rating = 5;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if ($action === 'add') {
            if ($name && $content) {
                $stmt = $db->prepare("INSERT INTO testimonials (name, position, company, content, rating, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $position, $company, $content, $rating, $isActive, $sortOrder]);
                logActivity('testimonial_add', "Admin added testimonial: $name", [], 'info');
                $msg = 'Testimonial added.';
            } else {
                $msg = 'Name and content are required.';
            }
        } elseif ($action === 'update' && $id) {
            if ($name && $content) {
                $stmt = $db->prepare("UPDATE testimonials SET name=?, position=?, company=?, content=?, rating=?, is_active=?, sort_order=? WHERE id=?");
                $stmt->execute([$name, $position, $company, $content, $rating, $isActive, $sortOrder, $id]);
                logActivity('testimonial_update', "Admin updated testimonial ID: $id", [], 'info');
                $msg = 'Testimonial updated.';
            } else {
                $msg = 'Name and content are required.';
            }
        } elseif ($action === 'toggle' && $id) {
            $stmt = $db->prepare("UPDATE testimonials SET is_active = 1 - is_active WHERE id = ?");
            $stmt->execute([$id]);
            logActivity('testimonial_toggle', "Admin toggled testimonial ID: $id", [], 'info');
            $msg = 'Status updated.';
        } elseif ($action === 'delete' && $id) {
            $stmt = $db->prepare("DELETE FROM testimonials WHERE id = ?");
            $stmt->execute([$id]);
            logActivity('testimonial_delete', "Admin deleted testimonial ID: $id", [], 'warning');
            $msg = 'Testimonial deleted.';
        }
    }
}

$testimonials = $db->query("SELECT * FROM testimonials ORDER BY sort_order ASC, created_at DESC")->fetchAll();
?>
<?php if ($msg): ?>
<div class="alert alert-info" style="margin-bottom:16px"><i data-lucide="info" size="18"></i> <?= $msg ?></div>
<?php endif; ?>
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Testimonials</h1>
        <p class="page-subtitle">Client testimonials displayed on the website</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary btn-sm" data-modal="newTestimonialModal"><i data-lucide="plus" size="16"></i> Add Testimonial</button>
    </div>
</div>

<div class="table-container reveal">
    <table class="table">
        <thead><tr><th>Client</th><th>Company</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if (empty($testimonials)): ?>
                <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">No testimonials yet</td></tr>
            <?php else: foreach ($testimonials as $t): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:34px;height:34px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px"><?= strtoupper($t['name'][0]) ?></div>
                            <div>
                                <div style="font-weight:600"><?= htmlspecialchars($t['name']) ?></div>
                                <?php if ($t['position']): ?><div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($t['position']) ?></div><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($t['company'] ?? '-') ?></td>
                    <td><?php for ($i = 0; $i < ($t['rating'] ?? 5); $i++): ?><i data-lucide="star" width="14" height="14" fill="#FFC107" color="#FFC107" style="display:inline"></i><?php endfor; ?></td>
                    <td><span class="badge badge-<?= $t['is_active'] ? 'success' : 'error' ?>"><?= $t['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-ghost btn-icon btn-sm edit-testimonial-btn" title="Edit"
                                data-id="<?= (int)$t['id'] ?>"
                                data-name="<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>"
                                data-position="<?= htmlspecialchars($t['position'] ?? '', ENT_QUOTES) ?>"
                                data-company="<?= htmlspecialchars($t['company'] ?? '', ENT_QUOTES) ?>"
                                data-content="<?= htmlspecialchars($t['content'], ENT_QUOTES) ?>"
                                data-rating="<?= (int)$t['rating'] ?>"
                                data-active="<?= (int)$t['is_active'] ?>"
                                data-sort="<?= (int)$t['sort_order'] ?>">
                                <i data-lucide="edit-2" size="14"></i>
                            </button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Toggle this testimonial visibility?')">
                                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                <input type="hidden" name="testimonial_action" value="toggle">
                                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="<?= $t['is_active'] ? 'Hide' : 'Show' ?>" style="color:#FF9800;border:none;cursor:pointer;background:none"><i data-lucide="<?= $t['is_active'] ? 'eye-off' : 'eye' ?>" size="14"></i></button>
                            </form>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this testimonial? This cannot be undone.')">
                                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                <input type="hidden" name="testimonial_action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="Delete" style="color:#F44336;border:none;cursor:pointer;background:none"><i data-lucide="trash-2" size="14"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- New Testimonial Modal -->
<div class="modal-overlay" id="newTestimonialModal">
    <div class="modal-content">
        <div class="modal-header"><h3 class="modal-title">Add Testimonial</h3><button class="modal-close" onclick="closeModal('newTestimonialModal')">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="testimonial_action" value="add">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Name</label><input type="text" name="name" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Position</label><input type="text" name="position" class="form-input" placeholder="CEO"></div>
                </div>
                <div class="form-group"><label class="form-label">Company</label><input type="text" name="company" class="form-input"></div>
                <div class="form-group"><label class="form-label">Content</label><textarea name="content" class="form-textarea" rows="4" required></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Rating</label>
                        <select name="rating" class="form-select">
                            <option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-input" value="0"></div>
                </div>
                <label style="display:flex;align-items:center;gap:8px;margin-top:8px;cursor:pointer"><input type="checkbox" name="is_active" checked style="accent-color:var(--primary)"> <span style="font-size:14px">Active</span></label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('newTestimonialModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Testimonial</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Testimonial Modal -->
<div class="modal-overlay" id="editTestimonialModal">
    <div class="modal-content">
        <div class="modal-header"><h3 class="modal-title">Edit Testimonial</h3><button class="modal-close" onclick="closeModal('editTestimonialModal')">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="testimonial_action" value="update">
                <input type="hidden" name="id" id="edit_testimonial_id" value="">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Name</label><input type="text" name="name" id="edit_testimonial_name" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Position</label><input type="text" name="position" id="edit_testimonial_position" class="form-input" placeholder="CEO"></div>
                </div>
                <div class="form-group"><label class="form-label">Company</label><input type="text" name="company" id="edit_testimonial_company" class="form-input"></div>
                <div class="form-group"><label class="form-label">Content</label><textarea name="content" id="edit_testimonial_content" class="form-textarea" rows="4" required></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Rating</label>
                        <select name="rating" id="edit_testimonial_rating" class="form-select">
                            <option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Sort Order</label><input type="number" name="sort_order" id="edit_testimonial_sort" class="form-input" value="0"></div>
                </div>
                <label style="display:flex;align-items:center;gap:8px;margin-top:8px;cursor:pointer"><input type="checkbox" name="is_active" id="edit_testimonial_is_active" style="accent-color:var(--primary)"> <span style="font-size:14px">Active</span></label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editTestimonialModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>
lucide.createIcons();
document.querySelectorAll('.edit-testimonial-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('edit_testimonial_id').value = this.dataset.id;
        document.getElementById('edit_testimonial_name').value = this.dataset.name;
        document.getElementById('edit_testimonial_position').value = this.dataset.position;
        document.getElementById('edit_testimonial_company').value = this.dataset.company;
        document.getElementById('edit_testimonial_content').value = this.dataset.content;
        document.getElementById('edit_testimonial_rating').value = this.dataset.rating;
        document.getElementById('edit_testimonial_is_active').checked = this.dataset.active === '1';
        document.getElementById('edit_testimonial_sort').value = this.dataset.sort;
        openModal('editTestimonialModal');
    });
});
</script>
</body></html>
