<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Services'; require __DIR__ . '/../includes/admin-header.php';

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid security token.';
    } else {
        if (isset($_POST['create_service'])) {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = trim($_POST['price'] ?? '');
            $icon = trim($_POST['icon'] ?? 'briefcase');
            $sortOrder = (int)($_POST['sort_order'] ?? 0);
            if (empty($title)) {
                $msg = 'Title is required.';
            } else {
                $slug = slugify($title);
                $existingSlug = $db->prepare("SELECT id FROM services WHERE slug = ?");
                $existingSlug->execute([$slug]);
                if ($existingSlug->fetch()) $slug .= '-' . time();
                $stmt = $db->prepare("INSERT INTO services (title, slug, description, icon, price, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $description ?: null, $icon ?: 'briefcase', $price ?: null, $sortOrder]);
                logActivity('service_create', "Service created: $title", [], 'info');
                header('Location: ' . BASE_URL . 'admin-services');
                exit;
            }
        }

        if (isset($_POST['edit_service'])) {
            $serviceId = (int)($_POST['service_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = trim($_POST['price'] ?? '');
            $icon = trim($_POST['icon'] ?? 'briefcase');
            $sortOrder = (int)($_POST['sort_order'] ?? 0);
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            if (!$serviceId || empty($title)) {
                $msg = 'Invalid service data.';
            } else {
                $stmt = $db->prepare("UPDATE services SET title=?, slug=?, description=?, icon=?, price=?, sort_order=?, is_active=? WHERE id=?");
                $stmt->execute([$title, slugify($title), $description ?: null, $icon ?: 'briefcase', $price ?: null, $sortOrder, $isActive, $serviceId]);
                logActivity('service_update', "Service updated: $title (ID: $serviceId)", [], 'info');
                header('Location: ' . BASE_URL . 'admin-services');
                exit;
            }
        }

        if (isset($_POST['delete_service'])) {
            $serviceId = (int)($_POST['service_id'] ?? 0);
            if ($serviceId) {
                $db->prepare("DELETE FROM services WHERE id = ?")->execute([$serviceId]);
                logActivity('service_delete', "Admin deleted service ID: $serviceId", [], 'warning');
                header('Location: ' . BASE_URL . 'admin-services');
                exit;
            }
        }
    }
}

$services = $db->query("SELECT * FROM services ORDER BY sort_order ASC, created_at DESC")->fetchAll();
?>
<?php if (!empty($msg)): ?>
<div class="alert alert-error" style="margin-bottom:20px"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Services</h1>
        <p class="page-subtitle">Manage your service offerings (<?= count($services) ?> services)</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary btn-sm" onclick="openModal('newServiceModal')"><i data-lucide="plus" size="16"></i> Add Service</button>
    </div>
</div>

<div class="table-container reveal">
    <table class="table">
        <thead><tr><th style="width:40px">#</th><th>Service</th><th>Price</th><th>Features</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if (empty($services)): ?>
            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted)">No services yet. Click "Add Service" to create your first service.</td></tr>
            <?php else: foreach ($services as $s): ?>
                <tr>
                    <td><?= $s['sort_order'] ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:36px;height:36px;border-radius:var(--radius-sm);background:var(--primary-alpha);display:flex;align-items:center;justify-content:center;color:var(--primary)"><i data-lucide="<?= htmlspecialchars($s['icon'] ?: 'briefcase') ?>" size="18"></i></div>
                            <span style="font-weight:600"><?= htmlspecialchars($s['title']) ?></span>
                        </div>
                    </td>
                    <td><span style="font-weight:700;color:var(--primary)"><?= htmlspecialchars($s['price'] ?? '-') ?></span></td>
                    <td><?= $s['features'] ? count(json_decode($s['features'], true) ?: []) . ' features' : '-' ?></td>
                    <td><span class="badge badge-<?= $s['is_active'] ? 'success' : 'error' ?>"><?= $s['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-ghost btn-icon btn-sm" onclick="editService(<?= htmlspecialchars(json_encode($s)) ?>)"><i data-lucide="edit-2" size="14"></i></button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this service?')">
                                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                <button type="submit" name="delete_service" class="btn btn-ghost btn-icon btn-sm" style="color:#F44336"><i data-lucide="trash-2" size="14"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- New Service Modal -->
<div class="modal-overlay" id="newServiceModal">
    <div class="modal-content" style="max-width:600px">
        <div class="modal-header"><h3 class="modal-title">Add Service</h3><button class="modal-close" onclick="closeModal('newServiceModal')">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-textarea" rows="3"></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Price</label><input type="text" name="price" class="form-input" placeholder="$5,000+"></div>
                    <div class="form-group"><label class="form-label">Icon</label>
                        <select name="icon" class="form-select">
                            <option value="briefcase">briefcase</option>
                            <option value="palette">palette</option>
                            <option value="code">code</option>
                            <option value="award">award</option>
                            <option value="layers">layers</option>
                            <option value="trending-up">trending-up</option>
                            <option value="smartphone">smartphone</option>
                            <option value="monitor">monitor</option>
                            <option value="settings">settings</option>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-input" value="0" min="0"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('newServiceModal')">Cancel</button>
                <button type="submit" name="create_service" class="btn btn-primary">Add Service</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal-overlay" id="editServiceModal">
    <div class="modal-content" style="max-width:600px">
        <div class="modal-header"><h3 class="modal-title">Edit Service</h3><button class="modal-close" onclick="closeModal('editServiceModal')">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="service_id" id="edit_service_id">
                <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" id="edit_service_title" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Description</label><textarea name="description" id="edit_service_description" class="form-textarea" rows="3"></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Price</label><input type="text" name="price" id="edit_service_price" class="form-input" placeholder="$5,000+"></div>
                    <div class="form-group"><label class="form-label">Icon</label>
                        <select name="icon" id="edit_service_icon" class="form-select">
                            <option value="briefcase">briefcase</option>
                            <option value="palette">palette</option>
                            <option value="code">code</option>
                            <option value="award">award</option>
                            <option value="layers">layers</option>
                            <option value="trending-up">trending-up</option>
                            <option value="smartphone">smartphone</option>
                            <option value="monitor">monitor</option>
                            <option value="settings">settings</option>
                        </select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Sort Order</label><input type="number" name="sort_order" id="edit_service_sort" class="form-input" value="0" min="0"></div>
                    <div class="form-group"><label class="form-label">Status</label>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:8px;cursor:pointer">
                            <input type="checkbox" name="is_active" id="edit_service_active" value="1" style="width:18px;height:18px">
                            <span>Active</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editServiceModal')">Cancel</button>
                <button type="submit" name="edit_service" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>
lucide.createIcons();
function editService(s) {
    document.getElementById('edit_service_id').value = s.id;
    document.getElementById('edit_service_title').value = s.title || '';
    document.getElementById('edit_service_description').value = s.description || '';
    document.getElementById('edit_service_price').value = s.price || '';
    document.getElementById('edit_service_icon').value = s.icon || 'briefcase';
    document.getElementById('edit_service_sort').value = s.sort_order || 0;
    document.getElementById('edit_service_active').checked = parseInt(s.is_active) === 1;
    openModal('editServiceModal');
}
</script>
</body></html>
