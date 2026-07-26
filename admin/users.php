<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Users'; require __DIR__ . '/../includes/admin-header.php';

$db = Database::getInstance()->getConnection();
$msg = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrf)) { $msg = 'Invalid session token.'; }
    else {
        // Add user
        if (isset($_POST['add_user'])) {
            $name = sanitize($_POST['name'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = in_array($_POST['role'] ?? '', ['user','admin']) ? $_POST['role'] : 'user';
            $status = in_array($_POST['status'] ?? '', ['active','inactive','banned']) ? $_POST['status'] : 'active';
            if ($name && $email && $password) {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
                $stmt = $db->prepare("INSERT INTO users (name, username, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $username ?: $name, $email, $hash, $role, $status]);
                logActivity('user_add', "Admin added user: $name", [], 'info');
                $msg = 'User added successfully.';
            } else { $msg = 'Name, email, and password are required.'; }
        }
        // Edit user
        if (isset($_POST['edit_user'])) {
            $id = (int)($_POST['user_id'] ?? 0);
            $name = sanitize($_POST['name'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $username = sanitize($_POST['username'] ?? '');
            $role = in_array($_POST['role'] ?? '', ['user','admin','superadmin']) ? $_POST['role'] : 'user';
            $status = in_array($_POST['status'] ?? '', ['active','inactive','banned']) ? $_POST['status'] : 'active';
            if ($id && $name && $email) {
                $stmt = $db->prepare("UPDATE users SET name=?, username=?, email=?, role=?, status=? WHERE id=?");
                $stmt->execute([$name, $username ?: $name, $email, $role, $status, $id]);
                if (!empty($_POST['password'])) {
                    $hash = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
                    $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $id]);
                }
                logActivity('user_edit', "Admin edited user ID: $id", [], 'info');
                $msg = 'User updated successfully.';
            } else { $msg = 'Name and email are required.'; }
        }
    }
}

// POST actions (block, unblock, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_action'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid security token.';
    } else {
        $action = $_POST['user_action'];
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($action === 'block' && $userId) {
            $db->prepare("UPDATE users SET status='banned' WHERE id=?")->execute([$userId]);
            logActivity('user_block', "Admin blocked user ID: $userId", [], 'warning');
            $msg = 'User blocked.';
        }
        if ($action === 'unblock' && $userId) {
            $db->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$userId]);
            logActivity('user_unblock', "Admin unblocked user ID: $userId", [], 'info');
            $msg = 'User unblocked.';
        }
        if ($action === 'delete' && $userId) {
            $db->prepare("DELETE FROM users WHERE id=? AND role != 'superadmin'")->execute([$userId]);
            logActivity('user_delete', "Admin deleted user ID: $userId", [], 'critical');
            $msg = 'User deleted.';
        }
    }
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Users</h1>
        <p class="page-subtitle">Manage all registered users (<?= count($users) ?> total)</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary btn-sm" onclick="openModal('addUserModal')"><i data-lucide="user-plus" size="16"></i> Add User</button>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-<?= strpos($msg, 'error') !== false || strpos($msg, 'required') !== false ? 'error' : 'success' ?>"><?= $msg ?></div>
<?php endif; ?>

<div class="table-container reveal">
    <table class="table">
        <thead>
            <tr><th style="width:40px"><input type="checkbox" data-select-all="users-table"></th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
        </thead>
        <tbody id="users-table">
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><input type="checkbox"></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <?php $avatarUrl = !empty($u['avatar']) ? BASE_URL . $u['avatar'] : ''; ?>
                            <div style="width:34px;height:34px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px;overflow:hidden;flex-shrink:0;<?= $avatarUrl ? 'background:none' : '' ?>">
                                <?php if ($avatarUrl): ?>
                                    <img src="<?= $avatarUrl ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover">
                                <?php else: ?>
                                    <?= strtoupper($u['name'][0]) ?>
                                <?php endif; ?>
                            </div>
                            <span style="font-weight:600"><?= htmlspecialchars($u['name']) ?></span>
                            <span style="width:8px;height:8px;border-radius:50%;display:inline-block;flex-shrink:0;background:<?= isOnline($u['last_activity'] ?? '') ? '#22c55e' : '#9ca3af' ?>"></span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge badge-<?= $u['role'] === 'superadmin' ? 'warning' : ($u['role'] === 'admin' ? 'info' : 'primary') ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td>
                        <?php if ($u['status'] === 'active'): ?>
                            <span class="badge badge-success">Active</span>
                        <?php elseif ($u['status'] === 'banned'): ?>
                            <span class="badge badge-error">Banned</span>
                        <?php else: ?>
                            <span class="badge" style="background:var(--bg-gray);color:var(--text-muted)">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--text-muted);font-size:13px"><?= formatDate($u['created_at']) ?></td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <?php if ($u['role'] !== 'superadmin'): ?>
                                <button class="btn btn-ghost btn-icon btn-sm" title="Edit" onclick="editUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>', '<?= $u['role'] ?>', '<?= $u['status'] ?>')"><i data-lucide="edit-2" size="14"></i></button>
                                <?php if ($u['status'] === 'banned'): ?>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Unblock this user?')">
                                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                        <input type="hidden" name="user_action" value="unblock">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="Unblock" style="color:#4CAF50;border:none;cursor:pointer;background:none"><i data-lucide="unlock" size="14"></i></button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Block this user?')">
                                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                        <input type="hidden" name="user_action" value="block">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="Block" style="color:#FF9800;border:none;cursor:pointer;background:none"><i data-lucide="lock" size="14"></i></button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this user? This cannot be undone.')">
                                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                    <input type="hidden" name="user_action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="Delete" style="color:#F44336;border:none;cursor:pointer;background:none"><i data-lucide="trash-2" size="14"></i></button>
                                </form>
                            <?php else: ?>
                                <span style="color:var(--text-muted);font-size:12px">Protected</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal-content">
        <div class="modal-header"><h3 class="modal-title">Add New User</h3><button class="modal-close" onclick="closeModal('addUserModal')">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="add_user" value="1">
                <div class="form-group"><label class="form-label">Name</label><input type="text" name="name" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Username</label><input type="text" name="username" class="form-input"></div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Password</label><input type="password" name="password" class="form-input" required></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Role</label><select name="role" class="form-select"><option value="user">User</option><option value="admin">Admin</option></select></div>
                    <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option><option value="banned">Banned</option></select></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal-content">
        <div class="modal-header"><h3 class="modal-title">Edit User</h3><button class="modal-close" onclick="closeModal('editUserModal')">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="edit_user" value="1">
                <input type="hidden" name="user_id" id="edit_user_id" value="0">
                <div class="form-group"><label class="form-label">Name</label><input type="text" name="name" id="edit_name" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Username</label><input type="text" name="username" id="edit_username" class="form-input"></div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" id="edit_email" class="form-input" required></div>
                <div class="form-group"><label class="form-label">New Password <span style="color:var(--text-muted);font-size:12px">(leave blank to keep current)</span></label><input type="password" name="password" class="form-input" autocomplete="new-password"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Role</label><select name="role" id="edit_role" class="form-select"><option value="user">User</option><option value="admin">Admin</option><option value="superadmin">Superadmin</option></select></div>
                    <div class="form-group"><label class="form-label">Status</label><select name="status" id="edit_status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option><option value="banned">Banned</option></select></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}
function editUser(id, name, username, email, role, status) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_status').value = status;
    openModal('editUserModal');
}
</script>

<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body></html>
