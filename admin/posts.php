<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Blog Posts'; require __DIR__ . '/../includes/admin-header.php';

$db = Database::getInstance()->getConnection();
$blogCategories = $db->query("SELECT id, name FROM categories WHERE type = 'blog' ORDER BY name ASC")->fetchAll();
$allCategories = $db->query("SELECT id, name, type FROM categories ORDER BY type, name ASC")->fetchAll();
$catMap = []; foreach ($allCategories as $c) $catMap[$c['id']] = $c['name'];
$authorName = htmlspecialchars(getCurrentUserName());
$authorId = getCurrentUserId();

function handlePostImageUpload($fieldName) {
    if (empty($_FILES[$fieldName]['tmp_name']) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) return null;
    $file = $_FILES[$fieldName];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($ext, $allowed)) return null;
    $filename = 'post_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $uploadDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
        return 'uploads/' . $filename;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid security token.';
    } else {
        if (isset($_POST['create_post'])) {
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            $excerpt = trim($_POST['excerpt'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['draft','published','archived']) ? $_POST['status'] : 'draft';
            $featured = isset($_POST['featured']) ? 1 : 0;
            $publishedAt = null;
            if ($status === 'published') {
                $publishedAt = !empty($_POST['published_at']) ? date('Y-m-d H:i:s', strtotime($_POST['published_at'])) : date('Y-m-d H:i:s');
            }
            if (empty($title) || empty($content)) {
                $msg = 'Title and content are required.';
            } else {
                $slug = slugify($title);
                $existingSlug = $db->prepare("SELECT id FROM posts WHERE slug = ?");
                $existingSlug->execute([$slug]);
                if ($existingSlug->fetch()) $slug .= '-' . time();
                $featuredImage = handlePostImageUpload('image');
                $stmt = $db->prepare("INSERT INTO posts (title, slug, content, excerpt, tags, category_id, author_id, featured_image, status, featured, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $content, $excerpt ?: null, $tags ?: null, $categoryId ?: null, $authorId, $featuredImage, $status, $featured, $publishedAt]);
                logActivity('post_create', "Post created: $title", [], 'info');
                header('Location: ' . BASE_URL . 'admin-posts');
                exit;
            }
        }

        if (isset($_POST['edit_post'])) {
            $postId = (int)($_POST['post_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            $excerpt = trim($_POST['excerpt'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['draft','published','archived']) ? $_POST['status'] : 'draft';
            $featured = isset($_POST['featured']) ? 1 : 0;
            if (!$postId || empty($title) || empty($content)) {
                $msg = 'Invalid post data.';
            } else {
                $featuredImage = handlePostImageUpload('image');
                $publishedAt = null;
                if ($status === 'published') {
                    $check = $db->prepare("SELECT published_at FROM posts WHERE id = ?");
                    $check->execute([$postId]);
                    $row = $check->fetch();
                    $publishedAt = $row && $row['published_at'] ? $row['published_at'] : date('Y-m-d H:i:s');
                    if (!empty($_POST['published_at'])) {
                        $publishedAt = date('Y-m-d H:i:s', strtotime($_POST['published_at']));
                    }
                }
                if ($featuredImage) {
                    $stmt = $db->prepare("UPDATE posts SET title=?, slug=?, content=?, excerpt=?, tags=?, category_id=?, featured_image=?, status=?, featured=?, published_at=? WHERE id=?");
                    $stmt->execute([$title, slugify($title), $content, $excerpt ?: null, $tags ?: null, $categoryId ?: null, $featuredImage, $status, $featured, $publishedAt, $postId]);
                } else {
                    $stmt = $db->prepare("UPDATE posts SET title=?, slug=?, content=?, excerpt=?, tags=?, category_id=?, status=?, featured=?, published_at=? WHERE id=?");
                    $stmt->execute([$title, slugify($title), $content, $excerpt ?: null, $tags ?: null, $categoryId ?: null, $status, $featured, $publishedAt, $postId]);
                }
                logActivity('post_update', "Post updated: $title (ID: $postId)", [], 'info');
                header('Location: ' . BASE_URL . 'admin-posts');
                exit;
            }
        }

        if (isset($_POST['delete_post'])) {
            $postId = (int)($_POST['post_id'] ?? 0);
            if ($postId) {
                $db->prepare("DELETE FROM posts WHERE id = ?")->execute([$postId]);
                logActivity('post_delete', "Admin deleted post ID: $postId", [], 'warning');
                header('Location: ' . BASE_URL . 'admin-posts');
                exit;
            }
        }
    }
}

$posts = $db->query("SELECT p.*, c.name as category_name, u.name as author_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC")->fetchAll();
?>
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Blog Posts</h1>
        <p class="page-subtitle">Manage your blog content (<?= count($posts) ?> posts)</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary btn-sm" onclick="openNewPostModal()"><i data-lucide="plus" size="16"></i> New Post</button>
    </div>
</div>

<div class="table-container reveal">
    <table class="table">
        <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Tags</th><th>Status</th><th>Featured</th><th>Views</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if (empty($posts)): ?>
            <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted)">No posts yet. Click "New Post" to create your first blog post.</td></tr>
            <?php else: foreach ($posts as $p): ?>
                <tr>
                    <td><span style="font-weight:600"><?= htmlspecialchars($p['title']) ?></span></td>
                    <td><span class="badge badge-primary"><?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?></span></td>
                    <td><?= htmlspecialchars($p['author_name'] ?? 'Unknown') ?></td>
                    <td><?php
                        $tags = array_filter(array_map('trim', explode(',', $p['tags'] ?? '')));
                        foreach ($tags as $t): ?><span class="badge" style="background:var(--bg-gray);margin-right:4px;font-size:11px"><?= htmlspecialchars($t) ?></span><?php endforeach;
                        if (empty($tags)) echo '<span style="color:var(--text-muted)">-</span>';
                    ?></td>
                    <td><span class="badge badge-<?= $p['status'] === 'published' ? 'success' : ($p['status'] === 'archived' ? '' : 'warning') ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td><?= $p['featured'] ? '<span style="color:#FF9800" title="Featured">&#9733;</span>' : '<span style="color:var(--border)">☆</span>' ?></td>
                    <td><?= number_format($p['views'] ?? 0) ?></td>
                    <td style="color:var(--text-muted);font-size:13px">
                        <?= $p['published_at'] ? formatDate($p['published_at']) : formatDate($p['created_at']) ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-ghost btn-icon btn-sm" onclick="editPost(<?= htmlspecialchars(json_encode($p)) ?>)"><i data-lucide="edit-2" size="14"></i></button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this post? This cannot be undone.')">
                                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                <input type="hidden" name="delete_post" value="1">
                                <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-ghost btn-icon btn-sm" style="color:#F44336"><i data-lucide="trash-2" size="14"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- New Post Modal -->
<div class="modal-overlay" id="newPostModal">
    <div class="modal-content" style="max-width:800px">
        <div class="modal-header"><h3 class="modal-title">Create New Post</h3><button class="modal-close" onclick="closeModal(document.getElementById('newPostModal'))">&times;</button></div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body" style="max-height:70vh;overflow-y:auto">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="create_post" value="1">

                <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-input" required placeholder="Enter post title"></div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">- None -</option>
                            <?php foreach ($blogCategories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" id="newPostStatus">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Author</label>
                        <input type="text" class="form-input" value="<?= $authorName ?>" disabled style="background:var(--bg-gray)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Published Date</label>
                        <input type="datetime-local" name="published_at" class="form-input" id="newPostDate">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-input" placeholder="e.g. design, react, tutorial (comma-separated)">
                </div>

                <div class="form-group">
                    <label class="form-label">Featured Image</label>
                    <input type="file" name="image" class="form-input" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Excerpt</label>
                    <textarea name="excerpt" class="form-textarea" rows="2" placeholder="Brief description of the post"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Content *</label>
                    <textarea name="content" class="form-textarea" rows="12" style="min-height:250px" required placeholder="Write your post content here..."></textarea>
                </div>

                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="featured" value="1">
                        <span class="form-label" style="margin:0">Featured Post</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal(document.getElementById('newPostModal'))">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Post</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Post Modal -->
<div class="modal-overlay" id="editPostModal">
    <div class="modal-content" style="max-width:800px">
        <div class="modal-header"><h3 class="modal-title">Edit Post</h3><button class="modal-close" onclick="closeModal(document.getElementById('editPostModal'))">&times;</button></div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body" style="max-height:70vh;overflow-y:auto">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="edit_post" value="1">
                <input type="hidden" name="post_id" id="edit_post_id">

                <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" id="edit_post_title" class="form-input" required></div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="edit_post_category" class="form-select">
                            <option value="">- None -</option>
                            <?php foreach ($blogCategories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_post_status" class="form-select">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Author</label>
                        <input type="text" id="edit_post_author_display" class="form-input" disabled style="background:var(--bg-gray)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Published Date</label>
                        <input type="datetime-local" name="published_at" id="edit_post_published_at" class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" id="edit_post_tags" class="form-input" placeholder="e.g. design, react, tutorial (comma-separated)">
                </div>

                <div class="form-group">
                    <label class="form-label">Featured Image (leave empty to keep current)</label>
                    <input type="file" name="image" class="form-input" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Excerpt</label>
                    <textarea name="excerpt" id="edit_post_excerpt" class="form-textarea" rows="2"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Content *</label>
                    <textarea name="content" id="edit_post_content" class="form-textarea" rows="12" style="min-height:250px" required></textarea>
                </div>

                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="featured" id="edit_post_featured" value="1">
                        <span class="form-label" style="margin:0">Featured Post</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal(document.getElementById('editPostModal'))">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openNewPostModal() {
    document.getElementById('newPostStatus').value = 'draft';
    document.getElementById('newPostDate').value = '';
    openModal('newPostModal');
}

function editPost(p) {
    document.getElementById('edit_post_id').value = p.id;
    document.getElementById('edit_post_title').value = p.title;
    document.getElementById('edit_post_category').value = p.category_id || '';
    document.getElementById('edit_post_status').value = p.status || 'draft';
    document.getElementById('edit_post_content').value = p.content || '';
    document.getElementById('edit_post_excerpt').value = p.excerpt || '';
    document.getElementById('edit_post_tags').value = p.tags || '';
    document.getElementById('edit_post_author_display').value = p.author_name || 'Unknown';
    document.getElementById('edit_post_featured').checked = !!parseInt(p.featured);
    if (p.published_at) {
        var d = new Date(p.published_at);
        var pad = function(n){ return n < 10 ? '0'+n : n; };
        document.getElementById('edit_post_published_at').value = d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())+'T'+pad(d.getHours())+':'+pad(d.getMinutes());
    } else {
        document.getElementById('edit_post_published_at').value = '';
    }
    openModal('editPostModal');
}
</script>

<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body></html>
