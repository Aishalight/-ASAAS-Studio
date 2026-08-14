<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Portfolio'; require __DIR__ . '/../includes/admin-header.php';

$db = Database::getInstance()->getConnection();
$portfolioCategories = $db->query("SELECT id, name FROM categories WHERE type IN ('portfolio','project') ORDER BY name ASC")->fetchAll();

try {
    $cols = $db->query("SHOW COLUMNS FROM portfolio_projects")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('project_type', $cols, true)) {
        $db->exec("ALTER TABLE portfolio_projects ADD COLUMN project_type ENUM('client','internal','concept','student') NOT NULL DEFAULT 'client' AFTER client");
    }
} catch (Exception $e) {
    // Table may not exist yet; ignore so the page still loads.
}

function handlePortfolioImageUpload($fieldName) {
    if (empty($_FILES[$fieldName]['tmp_name']) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) return null;
    $file = $_FILES[$fieldName];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($ext, $allowed)) return null;
    $filename = 'portfolio_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $uploadDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
        return 'uploads/' . $filename;
    }
    return null;
}

function handlePortfolioGalleryUpload() {
    if (empty($_FILES['gallery_images']['tmp_name'][0])) return null;
    $uploaded = [];
    $uploadDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $count = min(count($_FILES['gallery_images']['tmp_name']), 5);
    for ($i = 0; $i < $count; $i++) {
        if ($_FILES['gallery_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $file = $_FILES['gallery_images'];
        $ext = strtolower(pathinfo($file['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) continue;
        $filename = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'][$i], $uploadDir . '/' . $filename)) {
            $uploaded[] = 'uploads/' . $filename;
        }
    }
    return !empty($uploaded) ? json_encode($uploaded) : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid security token.';
    } else { try {
        if (isset($_POST['create_project'])) {
            $title = trim($_POST['title'] ?? '');
            $slug = slugify($title);
            $description = trim($_POST['description'] ?? '');
            $content = $_POST['content'] ?? '';
            $client = trim($_POST['client'] ?? '');
            $projectType = in_array($_POST['project_type'] ?? '', ['client','internal','concept','student']) ? $_POST['project_type'] : 'client';
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['draft','published','archived']) ? $_POST['status'] : 'draft';
            $projectDate = !empty($_POST['project_date']) ? $_POST['project_date'] : null;
            $projectUrl = trim($_POST['project_url'] ?? '');
            $githubUrl = trim($_POST['github_url'] ?? '');
            $technologies = trim($_POST['technologies'] ?? '');
            $featured = isset($_POST['featured']) ? 1 : 0;
            $sortOrder = (int)($_POST['sort_order'] ?? 0);
            $testimonial = trim($_POST['testimonial'] ?? '');
            $testimonialAuthor = trim($_POST['testimonial_author'] ?? '');
            $testimonialPosition = trim($_POST['testimonial_position'] ?? '');

            if (empty($title)) {
                $msg = 'Project title is required.';
            } else {
                $existingSlug = $db->prepare("SELECT id FROM portfolio_projects WHERE slug = ?");
                $existingSlug->execute([$slug]);
                if ($existingSlug->fetch()) $slug .= '-' . time();
                $featuredImage = handlePortfolioImageUpload('image');
                $galleryJson = handlePortfolioGalleryUpload();
                $stmt = $db->prepare("INSERT INTO portfolio_projects (title, slug, description, content, client, project_type, project_date, project_url, github_url, technologies, category_id, featured_image, gallery_images, featured, sort_order, testimonial, testimonial_author, testimonial_position, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $description ?: null, $content ?: null, $client ?: null, $projectType, $projectDate, $projectUrl ?: null, $githubUrl ?: null, $technologies ?: null, $categoryId ?: null, $featuredImage, $galleryJson, $featured, $sortOrder, $testimonial ?: null, $testimonialAuthor ?: null, $testimonialPosition ?: null, $status]);
                logActivity('portfolio_create', "Portfolio project created: $title", [], 'info');
                header('Location: ' . BASE_URL . 'admin-portfolio');
                exit;
            }
        }

        if (isset($_POST['edit_project'])) {
            $projectId = (int)($_POST['project_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $slug = slugify($title);
            $description = trim($_POST['description'] ?? '');
            $content = $_POST['content'] ?? '';
            $client = trim($_POST['client'] ?? '');
            $projectType = in_array($_POST['project_type'] ?? '', ['client','internal','concept','student']) ? $_POST['project_type'] : 'client';
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['draft','published','archived']) ? $_POST['status'] : 'draft';
            $projectDate = !empty($_POST['project_date']) ? $_POST['project_date'] : null;
            $projectUrl = trim($_POST['project_url'] ?? '');
            $githubUrl = trim($_POST['github_url'] ?? '');
            $technologies = trim($_POST['technologies'] ?? '');
            $featured = isset($_POST['featured']) ? 1 : 0;
            $sortOrder = (int)($_POST['sort_order'] ?? 0);
            $testimonial = trim($_POST['testimonial'] ?? '');
            $testimonialAuthor = trim($_POST['testimonial_author'] ?? '');
            $testimonialPosition = trim($_POST['testimonial_position'] ?? '');

            if (!$projectId || empty($title)) {
                $msg = 'Invalid project data.';
            } else {
                $featuredImage = handlePortfolioImageUpload('image');
                $galleryJson = handlePortfolioGalleryUpload();
                $params = [$title, $slug, $description ?: null, $content ?: null, $client ?: null, $projectType, $projectDate, $projectUrl ?: null, $githubUrl ?: null, $technologies ?: null, $categoryId ?: null, $status, $featured, $sortOrder, $testimonial ?: null, $testimonialAuthor ?: null, $testimonialPosition ?: null];
                if ($featuredImage) {
                    $stmt = $db->prepare("UPDATE portfolio_projects SET title=?, slug=?, description=?, content=?, client=?, project_type=?, project_date=?, project_url=?, github_url=?, technologies=?, category_id=?, status=?, featured=?, sort_order=?, testimonial=?, testimonial_author=?, testimonial_position=?, featured_image=? WHERE id=?");
                    $params[] = $featuredImage;
                } else {
                    $stmt = $db->prepare("UPDATE portfolio_projects SET title=?, slug=?, description=?, content=?, client=?, project_type=?, project_date=?, project_url=?, github_url=?, technologies=?, category_id=?, status=?, featured=?, sort_order=?, testimonial=?, testimonial_author=?, testimonial_position=? WHERE id=?");
                }
                if ($galleryJson) {
                    $existing = $db->query("SELECT gallery_images FROM portfolio_projects WHERE id = " . (int)$projectId)->fetchColumn();
                    $merged = [];
                    if ($existing) {
                        $decoded = json_decode($existing, true);
                        if (is_array($decoded)) $merged = $decoded;
                    }
                    foreach (json_decode($galleryJson, true) ?: [] as $img) {
                        if (count($merged) >= 5) break;
                        if (!in_array($img, $merged, true)) $merged[] = $img;
                    }
                    $db->prepare("UPDATE portfolio_projects SET gallery_images = ? WHERE id = ?")->execute([json_encode($merged), $projectId]);
                }
                $params[] = $projectId;
                $stmt->execute($params);
                logActivity('portfolio_update', "Portfolio project updated: $title (ID: $projectId)", [], 'info');
                header('Location: ' . BASE_URL . 'admin-portfolio');
                exit;
            }
        }

        if (isset($_POST['delete_project'])) {
            $projectId = (int)($_POST['project_id'] ?? 0);
            if ($projectId) {
                $db->prepare("DELETE FROM portfolio_projects WHERE id = ?")->execute([$projectId]);
                logActivity('portfolio_delete', "Admin deleted portfolio project ID: $projectId", [], 'warning');
                header('Location: ' . BASE_URL . 'admin-portfolio');
                exit;
            }
        }
    } catch (Exception $e) {
        logActivity('portfolio_error', 'Portfolio error: ' . $e->getMessage(), [], 'error');
        $msg = 'Error: ' . $e->getMessage();
    }
    }
}

$projects = $db->query("SELECT p.*, c.name as category_name FROM portfolio_projects p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.sort_order ASC, p.created_at DESC")->fetchAll();
?>
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Portfolio Projects</h1>
        <p class="page-subtitle">Manage portfolio projects (<?= count($projects) ?> projects)</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary btn-sm" onclick="openModal('newProjectModal')"><i data-lucide="plus" size="16"></i> New Project</button>
    </div>
</div>

<?php if ($msg): ?>
<div class="alert alert-error" style="margin-bottom:16px"><i data-lucide="alert-circle" size="18"></i> <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="table-container reveal">
    <table class="table">
        <thead><tr><th>Project</th><th>Category</th><th>Client</th><th>Type</th><th>Status</th><th>Featured</th><th>Order</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if (empty($projects)): ?>
            <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted)">No projects yet. Click "New Project" to create your first portfolio project.</td></tr>
            <?php else: foreach ($projects as $p): ?>
                <tr>
                    <td>
                        <span style="font-weight:600"><?= htmlspecialchars($p['title']) ?></span>
                        <?php if ($p['technologies']): ?><br><span style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($p['technologies']) ?></span><?php endif; ?>
                    </td>
                    <td><span class="badge badge-primary"><?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?></span></td>
                    <td><?= htmlspecialchars($p['client'] ?? '-') ?></td>
                    <td><span class="badge"><?= ucfirst(htmlspecialchars($p['project_type'] ?? 'client')) ?></span></td>
                    <td><span class="badge badge-<?= $p['status'] === 'published' ? 'success' : 'warning' ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td><?= $p['featured'] ? '<span style="color:#FF9800" title="Featured">&#9733;</span>' : '<span style="color:var(--border)">☆</span>' ?></td>
                    <td><?= $p['sort_order'] ?></td>
                    <td style="color:var(--text-muted);font-size:13px"><?= $p['project_date'] ? formatDate($p['project_date']) : formatDate($p['created_at']) ?></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-ghost btn-icon btn-sm" onclick="editProject(<?= htmlspecialchars(json_encode($p)) ?>)"><i data-lucide="edit-2" size="14"></i></button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this project? This cannot be undone.')">
                                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                <input type="hidden" name="delete_project" value="1">
                                <input type="hidden" name="project_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-ghost btn-icon btn-sm" style="color:#F44336"><i data-lucide="trash-2" size="14"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- New Project Modal -->
<div class="modal-overlay" id="newProjectModal">
    <div class="modal-content" style="max-width:800px">
        <div class="modal-header"><h3 class="modal-title">Add New Project</h3><button class="modal-close" onclick="closeModal(document.getElementById('newProjectModal'))">&times;</button></div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body" style="max-height:70vh;overflow-y:auto">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="create_project" value="1">

                <div class="form-group"><label class="form-label">Project Title *</label><input type="text" name="title" class="form-input" required placeholder="Project name"></div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">- None -</option>
                            <?php foreach ($portfolioCategories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Client</label><input type="text" name="client" class="form-input" placeholder="Client name"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Project Type</label>
                    <select name="project_type" class="form-select">
                        <option value="client">Client</option>
                        <option value="internal">Internal</option>
                        <option value="concept">Concept</option>
                        <option value="student">Student</option>
                    </select>
                    <p style="font-size:12px;color:var(--text-muted);margin-top:4px">Labels this project on the portfolio. Only publish real projects.</p>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Project Date</label><input type="date" name="project_date" class="form-input"></div>
                    <div class="form-group"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-input" value="0" min="0"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="3" placeholder="Short project description"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Content</label>
                    <div class="editor-toolbar" data-editor="new_project_content">
                        <button type="button" data-cmd="bold" title="Bold"><i data-lucide="bold" size="15"></i></button>
                        <button type="button" data-cmd="italic" title="Italic"><i data-lucide="italic" size="15"></i></button>
                        <button type="button" data-cmd="underline" title="Underline"><i data-lucide="underline" size="15"></i></button>
                        <span class="editor-sep"></span>
                        <button type="button" data-cmd="formatBlock" data-val="h2" title="Heading 2"><i data-lucide="heading-2" size="15"></i></button>
                        <button type="button" data-cmd="formatBlock" data-val="h3" title="Heading 3"><i data-lucide="heading-3" size="15"></i></button>
                        <button type="button" data-cmd="formatBlock" data-val="p" title="Paragraph"><i data-lucide="pilcrow" size="15"></i></button>
                        <span class="editor-sep"></span>
                        <button type="button" data-cmd="insertUnorderedList" title="Bullet list"><i data-lucide="list" size="15"></i></button>
                        <button type="button" data-cmd="insertOrderedList" title="Numbered list"><i data-lucide="list-ordered" size="15"></i></button>
                        <button type="button" data-cmd="formatBlock" data-val="blockquote" title="Quote"><i data-lucide="quote" size="15"></i></button>
                        <button type="button" data-cmd="link" title="Link"><i data-lucide="link" size="15"></i></button>
                        <button type="button" data-cmd="removeFormat" title="Clear formatting"><i data-lucide="remove-formatting" size="15"></i></button>
                    </div>
                    <div class="editor-box" data-editor-target="new_project_content" contenteditable="true"></div>
                    <textarea name="content" id="new_project_content" class="form-textarea editor-source" style="min-height:180px"></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Project URL</label><input type="url" name="project_url" class="form-input" placeholder="https://..."></div>
                    <div class="form-group"><label class="form-label">GitHub URL</label><input type="url" name="github_url" class="form-input" placeholder="https://github.com/..."></div>
                </div>

                <div class="form-group"><label class="form-label">Technologies</label><input type="text" name="technologies" class="form-input" placeholder="e.g. React, Node.js, MongoDB (comma-separated)"></div>

                <div class="form-group"><label class="form-label">Featured Image</label><input type="file" name="image" class="form-input" accept="image/*"></div>

                <div class="form-group"><label class="form-label">Gallery Images (up to 5)</label><input type="file" name="gallery_images[]" class="form-input" accept="image/*" multiple><small style="display:block;color:var(--text-muted);margin-top:6px">Hold Ctrl (Windows) or Cmd (Mac) to select multiple images at once.</small></div>

                <hr style="border:none;border-top:1px solid var(--border);margin:16px 0">
                <h4 style="font-size:14px;font-weight:600;margin-bottom:12px;color:var(--text-muted)">Testimonial</h4>

                <div class="form-group"><label class="form-label">Testimonial</label><textarea name="testimonial" class="form-textarea" rows="3" placeholder="What the client said..."></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Testimonial Author</label><input type="text" name="testimonial_author" class="form-input" placeholder="John Doe"></div>
                    <div class="form-group"><label class="form-label">Testimonial Position</label><input type="text" name="testimonial_position" class="form-input" placeholder="CEO, Company"></div>
                </div>

                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="featured" value="1">
                        <span class="form-label" style="margin:0">Featured Project</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal(document.getElementById('newProjectModal'))">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Project</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Project Modal -->
<div class="modal-overlay" id="editProjectModal">
    <div class="modal-content" style="max-width:800px">
        <div class="modal-header"><h3 class="modal-title">Edit Project</h3><button class="modal-close" onclick="closeModal(document.getElementById('editProjectModal'))">&times;</button></div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body" style="max-height:70vh;overflow-y:auto">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="edit_project" value="1">
                <input type="hidden" name="project_id" id="edit_project_id">

                <div class="form-group"><label class="form-label">Project Title *</label><input type="text" name="title" id="edit_project_title" class="form-input" required></div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="edit_project_category" class="form-select">
                            <option value="">- None -</option>
                            <?php foreach ($portfolioCategories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Client</label><input type="text" name="client" id="edit_project_client" class="form-input"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Project Type</label>
                    <select name="project_type" id="edit_project_type" class="form-select">
                        <option value="client">Client</option>
                        <option value="internal">Internal</option>
                        <option value="concept">Concept</option>
                        <option value="student">Student</option>
                    </select>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_project_status" class="form-select">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Project Date</label><input type="date" name="project_date" id="edit_project_date" class="form-input"></div>
                    <div class="form-group"><label class="form-label">Sort Order</label><input type="number" name="sort_order" id="edit_project_sort" class="form-input" value="0" min="0"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_project_description" class="form-textarea" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Content</label>
                    <div class="editor-toolbar" data-editor="edit_project_content">
                        <button type="button" data-cmd="bold" title="Bold"><i data-lucide="bold" size="15"></i></button>
                        <button type="button" data-cmd="italic" title="Italic"><i data-lucide="italic" size="15"></i></button>
                        <button type="button" data-cmd="underline" title="Underline"><i data-lucide="underline" size="15"></i></button>
                        <span class="editor-sep"></span>
                        <button type="button" data-cmd="formatBlock" data-val="h2" title="Heading 2"><i data-lucide="heading-2" size="15"></i></button>
                        <button type="button" data-cmd="formatBlock" data-val="h3" title="Heading 3"><i data-lucide="heading-3" size="15"></i></button>
                        <button type="button" data-cmd="formatBlock" data-val="p" title="Paragraph"><i data-lucide="pilcrow" size="15"></i></button>
                        <span class="editor-sep"></span>
                        <button type="button" data-cmd="insertUnorderedList" title="Bullet list"><i data-lucide="list" size="15"></i></button>
                        <button type="button" data-cmd="insertOrderedList" title="Numbered list"><i data-lucide="list-ordered" size="15"></i></button>
                        <button type="button" data-cmd="formatBlock" data-val="blockquote" title="Quote"><i data-lucide="quote" size="15"></i></button>
                        <button type="button" data-cmd="link" title="Link"><i data-lucide="link" size="15"></i></button>
                        <button type="button" data-cmd="removeFormat" title="Clear formatting"><i data-lucide="remove-formatting" size="15"></i></button>
                    </div>
                    <div class="editor-box" data-editor-target="edit_project_content" contenteditable="true"></div>
                    <textarea name="content" id="edit_project_content" class="form-textarea editor-source" style="min-height:180px"></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Project URL</label><input type="url" name="project_url" id="edit_project_url" class="form-input" placeholder="https://..."></div>
                    <div class="form-group"><label class="form-label">GitHub URL</label><input type="url" name="github_url" id="edit_project_github" class="form-input" placeholder="https://github.com/..."></div>
                </div>

                <div class="form-group"><label class="form-label">Technologies</label><input type="text" name="technologies" id="edit_project_tech" class="form-input" placeholder="e.g. React, Node.js, MongoDB"></div>

                <div class="form-group"><label class="form-label">Featured Image (leave empty to keep current)</label><input type="file" name="image" class="form-input" accept="image/*"></div>

                <div class="form-group"><label class="form-label">Add More Gallery Images</label><input type="file" name="gallery_images[]" class="form-input" accept="image/*" multiple><small style="display:block;color:var(--text-muted);margin-top:6px">New images are added to the existing gallery (max 5 total). Hold Ctrl (Windows) or Cmd (Mac) to select multiple at once.</small></div>

                <hr style="border:none;border-top:1px solid var(--border);margin:16px 0">
                <h4 style="font-size:14px;font-weight:600;margin-bottom:12px;color:var(--text-muted)">Testimonial</h4>

                <div class="form-group"><label class="form-label">Testimonial</label><textarea name="testimonial" id="edit_project_testimonial" class="form-textarea" rows="3"></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Testimonial Author</label><input type="text" name="testimonial_author" id="edit_project_test_author" class="form-input"></div>
                    <div class="form-group"><label class="form-label">Testimonial Position</label><input type="text" name="testimonial_position" id="edit_project_test_position" class="form-input"></div>
                </div>

                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="featured" id="edit_project_featured" value="1">
                        <span class="form-label" style="margin:0">Featured Project</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal(document.getElementById('editProjectModal'))">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editProject(p) {
    document.getElementById('edit_project_id').value = p.id;
    document.getElementById('edit_project_title').value = p.title || '';
    document.getElementById('edit_project_category').value = p.category_id || '';
    document.getElementById('edit_project_client').value = p.client || '';
    document.getElementById('edit_project_type').value = p.project_type || 'client';
    document.getElementById('edit_project_status').value = p.status || 'draft';
    document.getElementById('edit_project_date').value = p.project_date || '';
    document.getElementById('edit_project_sort').value = p.sort_order || 0;
    document.getElementById('edit_project_description').value = p.description || '';
    document.getElementById('edit_project_content').value = p.content || '';
    var projectEditorBox = document.querySelector('.editor-box[data-editor-target="edit_project_content"]');
    if (projectEditorBox) projectEditorBox.innerHTML = p.content || '';
    document.getElementById('edit_project_url').value = p.project_url || '';
    document.getElementById('edit_project_github').value = p.github_url || '';
    document.getElementById('edit_project_tech').value = p.technologies || '';
    document.getElementById('edit_project_featured').checked = !!parseInt(p.featured);
    document.getElementById('edit_project_testimonial').value = p.testimonial || '';
    document.getElementById('edit_project_test_author').value = p.testimonial_author || '';
    document.getElementById('edit_project_test_position').value = p.testimonial_position || '';
    openModal('editProjectModal');
}
</script>

<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body></html>
