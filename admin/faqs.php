<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'FAQs'; require __DIR__ . '/../includes/admin-header.php';

$db = Database::getInstance()->getConnection();
$faqs = $db->query("SELECT * FROM faqs ORDER BY sort_order ASC")->fetchAll();
?>
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">FAQs</h1>
        <p class="page-subtitle">Manage frequently asked questions</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary btn-sm" data-modal="newFaqModal"><i data-lucide="plus" size="16"></i> Add FAQ</button>
    </div>
</div>

<div class="reveal" style="display:flex;flex-direction:column;gap:12px">
    <?php foreach ($faqs as $f): ?>
        <div style="background:var(--bg-white);border-radius:var(--radius-md);border:1px solid var(--border);padding:20px 24px;display:flex;align-items:flex-start;justify-content:space-between;gap:16px">
            <div style="flex:1">
                <h6 style="font-weight:600;margin-bottom:8px"><?= htmlspecialchars($f['question']) ?></h6>
                <p style="font-size:14px;color:var(--text-secondary)"><?= htmlspecialchars($f['answer']) ?></p>
                <div style="margin-top:8px;display:flex;gap:8px">
                    <span class="badge badge-info"><?= htmlspecialchars($f['category'] ?? 'General') ?></span>
                    <span class="badge" style="background:var(--bg-gray);color:var(--text-muted)">Order: <?= $f['sort_order'] ?></span>
                </div>
            </div>
            <div style="display:flex;gap:6px;min-width:80px">
                <button class="btn btn-ghost btn-icon btn-sm"><i data-lucide="edit-2" size="14"></i></button>
                <button class="btn btn-ghost btn-icon btn-sm" style="color:#F44336"><i data-lucide="trash-2" size="14"></i></button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- New FAQ Modal -->
<div class="modal-overlay" id="newFaqModal">
    <div class="modal-content">
        <div class="modal-header"><h3 class="modal-title">Add FAQ</h3><button class="modal-close" onclick="closeModal(document.getElementById('newFaqModal'))">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <div class="form-group"><label class="form-label">Question</label><input type="text" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Answer</label><textarea class="form-textarea" rows="4" required></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Category</label><select class="form-select"><option>General</option><option>Pricing</option><option>Process</option><option>Support</option><option>Technical</option></select></div>
                    <div class="form-group"><label class="form-label">Sort Order</label><input type="number" class="form-input" value="1"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal(document.getElementById('newFaqModal'))">Cancel</button>
                <button type="submit" class="btn btn-primary">Add FAQ</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body></html>
