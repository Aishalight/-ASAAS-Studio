<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'FAQs'; require __DIR__ . '/../includes/admin-header.php';

$db = Database::getInstance()->getConnection();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid security token.';
    } else {
        $action = $_POST['faq_action'] ?? '';
        $id = (int)($_POST['id'] ?? 0);
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if ($action === 'add') {
            if ($question && $answer) {
                $stmt = $db->prepare("INSERT INTO faqs (question, answer, category, is_active, sort_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$question, $answer, $category, $isActive, $sortOrder]);
                logActivity('faq_add', "Admin added FAQ: $question", [], 'info');
                $msg = 'FAQ added.';
            } else {
                $msg = 'Question and answer are required.';
            }
        } elseif ($action === 'update' && $id) {
            if ($question && $answer) {
                $stmt = $db->prepare("UPDATE faqs SET question=?, answer=?, category=?, is_active=?, sort_order=? WHERE id=?");
                $stmt->execute([$question, $answer, $category, $isActive, $sortOrder, $id]);
                logActivity('faq_update', "Admin updated FAQ ID: $id", [], 'info');
                $msg = 'FAQ updated.';
            } else {
                $msg = 'Question and answer are required.';
            }
        } elseif ($action === 'toggle' && $id) {
            $stmt = $db->prepare("UPDATE faqs SET is_active = 1 - is_active WHERE id = ?");
            $stmt->execute([$id]);
            logActivity('faq_toggle', "Admin toggled FAQ ID: $id", [], 'info');
            $msg = 'Status updated.';
        } elseif ($action === 'delete' && $id) {
            $stmt = $db->prepare("DELETE FROM faqs WHERE id = ?");
            $stmt->execute([$id]);
            logActivity('faq_delete', "Admin deleted FAQ ID: $id", [], 'warning');
            $msg = 'FAQ deleted.';
        }
    }
}

$faqs = $db->query("SELECT * FROM faqs ORDER BY sort_order ASC, created_at DESC")->fetchAll();
?>
<?php if ($msg): ?>
<div class="alert alert-info" style="margin-bottom:16px"><i data-lucide="info" size="18"></i> <?= $msg ?></div>
<?php endif; ?>
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
    <?php if (empty($faqs)): ?>
        <div style="text-align:center;padding:40px;background:var(--bg-white);border-radius:var(--radius-md);border:1px solid var(--border);color:var(--text-muted)">No FAQs yet</div>
    <?php else: foreach ($faqs as $f): ?>
        <div style="background:var(--bg-white);border-radius:var(--radius-md);border:1px solid var(--border);padding:20px 24px;display:flex;align-items:flex-start;justify-content:space-between;gap:16px">
            <div style="flex:1">
                <h6 style="font-weight:600;margin-bottom:8px"><?= htmlspecialchars($f['question']) ?></h6>
                <p style="font-size:14px;color:var(--text-secondary)"><?= htmlspecialchars($f['answer']) ?></p>
                <div style="margin-top:8px;display:flex;gap:8px;align-items:center">
                    <span class="badge badge-info"><?= htmlspecialchars($f['category'] ?? 'General') ?></span>
                    <span class="badge" style="background:var(--bg-gray);color:var(--text-muted)">Order: <?= $f['sort_order'] ?></span>
                    <span class="badge badge-<?= $f['is_active'] ? 'success' : 'error' ?>"><?= $f['is_active'] ? 'Active' : 'Hidden' ?></span>
                </div>
            </div>
            <div style="display:flex;gap:6px;min-width:80px">
                <button class="btn btn-ghost btn-icon btn-sm edit-faq-btn" title="Edit"
                    data-id="<?= (int)$f['id'] ?>"
                    data-question="<?= htmlspecialchars($f['question'], ENT_QUOTES) ?>"
                    data-answer="<?= htmlspecialchars($f['answer'], ENT_QUOTES) ?>"
                    data-category="<?= htmlspecialchars($f['category'] ?? '', ENT_QUOTES) ?>"
                    data-active="<?= (int)$f['is_active'] ?>"
                    data-sort="<?= (int)$f['sort_order'] ?>">
                    <i data-lucide="edit-2" size="14"></i>
                </button>
                <form method="POST" style="display:inline" onsubmit="return confirm('Toggle this FAQ visibility?')">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <input type="hidden" name="faq_action" value="toggle">
                    <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                    <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="<?= $f['is_active'] ? 'Hide' : 'Show' ?>" style="color:#FF9800;border:none;cursor:pointer;background:none"><i data-lucide="<?= $f['is_active'] ? 'eye-off' : 'eye' ?>" size="14"></i></button>
                </form>
                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this FAQ? This cannot be undone.')">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <input type="hidden" name="faq_action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                    <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="Delete" style="color:#F44336;border:none;cursor:pointer;background:none"><i data-lucide="trash-2" size="14"></i></button>
                </form>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<!-- New FAQ Modal -->
<div class="modal-overlay" id="newFaqModal">
    <div class="modal-content">
        <div class="modal-header"><h3 class="modal-title">Add FAQ</h3><button class="modal-close" onclick="closeModal('newFaqModal')">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="faq_action" value="add">
                <div class="form-group"><label class="form-label">Question</label><input type="text" name="question" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Answer</label><textarea name="answer" class="form-textarea" rows="4" required></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="General">General</option>
                            <option value="Pricing">Pricing</option>
                            <option value="Process">Process</option>
                            <option value="Support">Support</option>
                            <option value="Technical">Technical</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-input" value="0"></div>
                </div>
                <label style="display:flex;align-items:center;gap:8px;margin-top:8px;cursor:pointer"><input type="checkbox" name="is_active" checked style="accent-color:var(--primary)"> <span style="font-size:14px">Active</span></label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('newFaqModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add FAQ</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit FAQ Modal -->
<div class="modal-overlay" id="editFaqModal">
    <div class="modal-content">
        <div class="modal-header"><h3 class="modal-title">Edit FAQ</h3><button class="modal-close" onclick="closeModal('editFaqModal')">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="faq_action" value="update">
                <input type="hidden" name="id" id="edit_faq_id" value="">
                <div class="form-group"><label class="form-label">Question</label><input type="text" name="question" id="edit_faq_question" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Answer</label><textarea name="answer" id="edit_faq_answer" class="form-textarea" rows="4" required></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" id="edit_faq_category" class="form-select">
                            <option value="General">General</option>
                            <option value="Pricing">Pricing</option>
                            <option value="Process">Process</option>
                            <option value="Support">Support</option>
                            <option value="Technical">Technical</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Sort Order</label><input type="number" name="sort_order" id="edit_faq_sort" class="form-input" value="0"></div>
                </div>
                <label style="display:flex;align-items:center;gap:8px;margin-top:8px;cursor:pointer"><input type="checkbox" name="is_active" id="edit_faq_is_active" style="accent-color:var(--primary)"> <span style="font-size:14px">Active</span></label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editFaqModal')">Cancel</button>
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
document.querySelectorAll('.edit-faq-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('edit_faq_id').value = this.dataset.id;
        document.getElementById('edit_faq_question').value = this.dataset.question;
        document.getElementById('edit_faq_answer').value = this.dataset.answer;
        document.getElementById('edit_faq_category').value = this.dataset.category;
        document.getElementById('edit_faq_is_active').checked = this.dataset.active === '1';
        document.getElementById('edit_faq_sort').value = this.dataset.sort;
        openModal('editFaqModal');
    });
});
</script>
</body></html>
