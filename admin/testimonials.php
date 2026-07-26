<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Testimonials'; require __DIR__ . '/../includes/admin-header.php';

$db = Database::getInstance()->getConnection();
$testimonials = $db->query("SELECT * FROM testimonials ORDER BY sort_order ASC")->fetchAll();
?>
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
            <?php foreach ($testimonials as $t): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:34px;height:34px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px"><?= strtoupper($t['name'][0]) ?></div>
                            <span style="font-weight:600"><?= htmlspecialchars($t['name']) ?></span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($t['company'] ?? '-') ?></td>
                    <td><?php for ($i = 0; $i < ($t['rating'] ?? 5); $i++): ?><i data-lucide="star" width="14" height="14" fill="#FFC107" color="#FFC107" style="display:inline"></i><?php endfor; ?></td>
                    <td><span class="badge badge-<?= $t['is_active'] ? 'success' : 'error' ?>"><?= $t['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-ghost btn-icon btn-sm"><i data-lucide="edit-2" size="14"></i></button>
                            <button class="btn btn-ghost btn-icon btn-sm" style="color:#F44336"><i data-lucide="trash-2" size="14"></i></button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- New Testimonial Modal -->
<div class="modal-overlay" id="newTestimonialModal">
    <div class="modal-content">
        <div class="modal-header"><h3 class="modal-title">Add Testimonial</h3><button class="modal-close" onclick="closeModal(document.getElementById('newTestimonialModal'))">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Name</label><input type="text" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Company</label><input type="text" class="form-input"></div>
                </div>
                <div class="form-group"><label class="form-label">Content</label><textarea class="form-textarea" rows="4" required></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Rating</label><select class="form-select"><option>5</option><option>4</option><option>3</option><option>2</option><option>1</option></select></div>
                    <div class="form-group"><label class="form-label">Position</label><input type="text" class="form-input" placeholder="CEO"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal(document.getElementById('newTestimonialModal'))">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Testimonial</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body></html>
