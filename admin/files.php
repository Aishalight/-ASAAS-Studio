<?php require_once __DIR__ . '/../config/functions.php'; startSession(); requireAdmin(); $pageTitle = 'Files'; require __DIR__ . '/../includes/admin-header.php';

$db = Database::getInstance()->getConnection();
$currentFolderId = (int)($_GET['folder'] ?? 0);
$csrf = getCSRFToken();

$breadcrumb = [];
if ($currentFolderId > 0) {
    $fid = $currentFolderId;
    while ($fid) {
        $fRow = $db->prepare("SELECT id, name, parent_id FROM folders WHERE id = ?");
        $fRow->execute([$fid]);
        $fRow = $fRow->fetch();
        if (!$fRow) break;
        array_unshift($breadcrumb, $fRow);
        $fid = $fRow['parent_id'];
    }
}

$folders = $db->prepare("SELECT f.*, (SELECT COUNT(*) FROM media WHERE folder_id = f.id) as file_count FROM folders f WHERE " . ($currentFolderId ? "f.parent_id = ?" : "f.parent_id IS NULL") . " ORDER BY f.name ASC");
if ($currentFolderId) $folders->execute([$currentFolderId]);
else $folders->execute();
$folders = $folders->fetchAll();

$files = $db->prepare("SELECT * FROM media WHERE " . ($currentFolderId ? "folder_id = ?" : "folder_id IS NULL") . " ORDER BY created_at DESC");
if ($currentFolderId) $files->execute([$currentFolderId]);
else $files->execute();
$files = $files->fetchAll();

$totalFiles = count($files);
$totalFolders = count($folders);
?>
<style>
.files-layout{display:grid;grid-template-columns:260px 1fr;gap:24px;min-height:calc(100vh - 200px)}
.files-sidebar{background:var(--bg-white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;height:fit-content;position:sticky;top:100px}
.files-sidebar h3{font-size:14px;font-weight:700;margin-bottom:12px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted)}
.folder-list{list-style:none;padding:0;margin:0}
.folder-item{display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:var(--radius-md);cursor:pointer;transition:all .2s;font-size:14px;color:var(--text-secondary)}
.folder-item:hover{background:var(--bg-light);color:var(--text-primary)}
.folder-item.active{background:var(--primary-alpha);color:var(--primary);font-weight:600}
.folder-item-icon{width:18px;height:18px;flex-shrink:0}
.folder-item-count{margin-left:auto;font-size:11px;background:var(--bg-gray);padding:1px 6px;border-radius:10px;color:var(--text-muted)}
.folder-item-actions{display:none;gap:2px;margin-left:auto}
.folder-item:hover .folder-item-actions{display:flex}
.folder-item:hover .folder-item-count{display:none}
.folder-action-btn{background:none;border:none;cursor:pointer;padding:2px;border-radius:4px;color:var(--text-muted);display:flex;align-items:center}
.folder-action-btn:hover{background:var(--bg-gray);color:var(--text-primary)}
.files-main{min-width:0}
.files-toolbar{display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap}
.files-toolbar-left{display:flex;align-items:center;gap:8px;flex:1}
.files-toolbar-right{display:flex;align-items:center;gap:8px}
.breadcrumb{display:flex;align-items:center;gap:4px;font-size:13px;color:var(--text-muted)}
.breadcrumb a{color:var(--text-secondary);text-decoration:none;font-weight:500}
.breadcrumb a:hover{color:var(--primary)}
.breadcrumb-sep{color:var(--border-dark)}
.file-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:16px}
.file-item{background:var(--bg-white);border:1px solid var(--border);border-radius:var(--radius-md);overflow:hidden;cursor:pointer;transition:all .2s}
.file-item:hover{border-color:var(--primary);box-shadow:var(--shadow-sm)}
.file-item.selected{border-color:var(--primary);box-shadow:0 0 0 2px var(--primary-alpha)}
.file-item-overlay{position:absolute;inset:0;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;gap:8px;opacity:0;transition:opacity .2s}
.file-item:hover .file-item-overlay{opacity:1}
.file-item-overlay .overlay-btn{background:white;border:none;border-radius:8px;width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.2);transition:transform .15s}
.file-item-overlay .overlay-btn:hover{transform:scale(1.1)}
.file-item-thumb{position:relative}
.file-item-thumb{height:120px;background:var(--bg-gray);display:flex;align-items:center;justify-content:center;overflow:hidden}
.file-item-thumb img{width:100%;height:100%;object-fit:cover}
.file-item-thumb .file-icon{color:var(--text-muted)}
.file-item-info{padding:10px 12px}
.file-item-name{font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.file-item-meta{font-size:11px;color:var(--text-muted);margin-top:2px}
.folder-grid-item{display:flex;align-items:center;gap:12px;background:var(--bg-white);border:1px solid var(--border);border-radius:var(--radius-md);padding:14px 16px;cursor:pointer;transition:all .2s}
.folder-grid-item:hover{border-color:var(--primary);box-shadow:var(--shadow-sm)}
.folder-grid-icon{width:40px;height:40px;border-radius:var(--radius-md);background:var(--primary-alpha);display:flex;align-items:center;justify-content:center;color:var(--primary);flex-shrink:0}
.folder-grid-name{font-weight:600;font-size:14px}
.folder-grid-count{font-size:12px;color:var(--text-muted)}
.empty-state{text-align:center;padding:60px 20px;color:var(--text-muted)}
.empty-state i{margin-bottom:16px;color:var(--border-dark)}
.empty-state p{font-size:14px;margin-top:8px}
.upload-zone-inline{border:2px dashed var(--border);border-radius:var(--radius-lg);padding:48px 32px;text-align:center;cursor:pointer;transition:all .3s;margin-bottom:24px;background:var(--bg-white)}
.upload-zone-inline:hover,.upload-zone-inline.dragover{border-color:var(--primary);background:var(--primary-alpha);transform:scale(1.01)}
.upload-zone-inline.dragover{border-style:solid;box-shadow:0 0 0 4px var(--primary-alpha)}
.upload-zone-inline i{color:var(--text-muted);margin-bottom:12px}
.upload-zone-inline p{color:var(--text-muted);font-size:14px;margin-bottom:4px}
.upload-zone-inline .upload-hint{font-size:12px;color:var(--border-dark)}
.file-info-panel{background:var(--bg-white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;margin-top:20px}
.file-info-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px}
.file-info-row:last-child{border:none}
.file-info-label{color:var(--text-muted);font-weight:500}
.file-info-value{font-weight:600;text-align:right;max-width:60%;word-break:break-all}
@media(max-width:768px){.files-layout{grid-template-columns:1fr}.files-sidebar{position:static}}
</style>

<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Files</h1>
        <p class="page-subtitle">Manage your files and folders</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary btn-sm" onclick="showNewFolderModal()"><i data-lucide="folder-plus" size="16"></i> New Folder</button>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('fileUploadInput').click()"><i data-lucide="upload" size="16"></i> Upload Files</button>
    </div>
</div>

<div class="files-layout">
    <aside class="files-sidebar">
        <h3>Folders</h3>
        <ul class="folder-list" id="folderList">
            <li class="folder-item <?= !$currentFolderId ? 'active' : '' ?>" onclick="location.href='<?= BASE_URL ?>admin-files'">
                <i data-lucide="hard-drive" size="18" class="folder-item-icon"></i>
                <span>All Files</span>
            </li>
            <?php foreach ($folders as $f): ?>
            <li class="folder-item <?= $f['id'] == $currentFolderId ? 'active' : '' ?>" onclick="location.href='<?= BASE_URL ?>admin-files?folder=<?= $f['id'] ?>'">
                <i data-lucide="folder" size="18" class="folder-item-icon"></i>
                <span><?= htmlspecialchars($f['name']) ?></span>
                <span class="folder-item-count"><?= $f['file_count'] ?></span>
                <div class="folder-item-actions">
                    <button class="folder-action-btn" onclick="event.stopPropagation();renameFolder(<?= $f['id'] ?>,'<?= htmlspecialchars(addslashes($f['name'])) ?>')" title="Rename"><i data-lucide="pencil" size="14"></i></button>
                    <button class="folder-action-btn" onclick="event.stopPropagation();deleteFolder(<?= $f['id'] ?>,'<?= htmlspecialchars(addslashes($f['name'])) ?>')" title="Delete"><i data-lucide="trash-2" size="14"></i></button>
                </div>
            </li>
            <?php endforeach; ?>
            <?php if (empty($folders)): ?>
            <li style="padding:12px;font-size:12px;color:var(--text-muted);text-align:center">No folders yet</li>
            <?php endif; ?>
        </ul>
    </aside>

    <div class="files-main">
        <div class="files-toolbar">
            <div class="files-toolbar-left">
                <div class="breadcrumb">
                    <a href="<?= BASE_URL ?>admin-files"><i data-lucide="hard-drive" size="14"></i> All Files</a>
                    <?php foreach ($breadcrumb as $b): ?>
                    <span class="breadcrumb-sep">/</span>
                    <a href="<?= BASE_URL ?>admin-files?folder=<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="files-toolbar-right">
                <span style="font-size:13px;color:var(--text-muted)"><?= $totalFolders ?> folders, <?= $totalFiles ?> files</span>
            </div>
        </div>

        <div class="upload-zone-inline" id="uploadZone">
            <input type="file" id="fileUploadInput" multiple style="display:none">
            <i data-lucide="upload-cloud" size="40"></i>
            <p style="font-size:16px;font-weight:600;margin-bottom:4px">Drop files here or click to upload</p>
            <p class="upload-hint">Supports images, documents, videos, and more</p>
        </div>

        <?php if (!empty($folders)): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:20px">
            <?php foreach ($folders as $f): ?>
            <div class="folder-grid-item" onclick="location.href='<?= BASE_URL ?>admin-files?folder=<?= $f['id'] ?>'">
                <div class="folder-grid-icon"><i data-lucide="folder" size="20"></i></div>
                <div>
                    <div class="folder-grid-name"><?= htmlspecialchars($f['name']) ?></div>
                    <div class="folder-grid-count"><?= $f['file_count'] ?> files</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="file-grid" id="fileGrid">
            <?php if (empty($files) && empty($folders)): ?>
            <div class="empty-state" style="grid-column:1/-1">
                <i data-lucide="folder-open" size="48"></i>
                <h3>No files yet</h3>
                <p>Upload files or create a folder to get started</p>
            </div>
            <?php else: foreach ($files as $m): ?>
            <div class="file-item" data-id="<?= $m['id'] ?>" data-url="<?= BASE_URL . $m['filepath'] ?>" data-mime="<?= htmlspecialchars($m['mime_type'] ?? '') ?>">
                <div class="file-item-thumb">
                    <?php if (str_starts_with($m['mime_type'] ?? '', 'image/')): ?>
                    <img src="<?= BASE_URL . $m['filepath'] ?>" alt="<?= htmlspecialchars($m['original_name']) ?>" loading="lazy">
                    <?php else: ?>
                    <i data-lucide="<?= getFileInfoIcon($m['mime_type']) ?>" size="32" class="file-icon"></i>
                    <?php endif; ?>
                    <div class="file-item-overlay">
                        <button class="overlay-btn" onclick="event.stopPropagation();openFileViewer('<?= BASE_URL . $m['filepath'] ?>')" title="Open file"><i data-lucide="external-link" size="16"></i></button>
                        <button class="overlay-btn" onclick="event.stopPropagation();showFileInfo(<?= htmlspecialchars(json_encode($m)) ?>)" title="File details"><i data-lucide="info" size="16"></i></button>
                        <button class="overlay-btn" onclick="event.stopPropagation();quickDeleteFile(<?= $m['id'] ?>,'<?= htmlspecialchars(addslashes($m['original_name'])) ?>')" title="Delete file" style="color:#F44336"><i data-lucide="trash-2" size="16"></i></button>
                    </div>
                </div>
                <div class="file-item-info">
                    <div class="file-item-name" title="<?= htmlspecialchars($m['original_name']) ?>"><?= htmlspecialchars($m['original_name']) ?></div>
                    <div class="file-item-meta"><?= formatFileSize($m['size']) ?> &middot; <?= formatDate($m['created_at']) ?></div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <div id="fileInfoPanel" style="display:none"></div>
    </div>
</div>

<!-- New Folder Modal -->
<div class="modal-overlay" id="newFolderModal">
    <div class="modal-content" style="max-width:400px">
        <div class="modal-header"><h3 class="modal-title">New Folder</h3><button class="modal-close" onclick="closeModal(document.getElementById('newFolderModal'))">&times;</button></div>
        <div class="modal-body">
            <div class="form-group"><label class="form-label">Folder Name</label><input type="text" id="newFolderName" class="form-input" placeholder="My Folder" autofocus></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal(document.getElementById('newFolderModal'))">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="createFolder()">Create Folder</button>
        </div>
    </div>
</div>

<!-- Rename Folder Modal -->
<div class="modal-overlay" id="renameFolderModal">
    <div class="modal-content" style="max-width:400px">
        <div class="modal-header"><h3 class="modal-title">Rename Folder</h3><button class="modal-close" onclick="closeModal(document.getElementById('renameFolderModal'))">&times;</button></div>
        <div class="modal-body">
            <input type="hidden" id="renameFolderId">
            <div class="form-group"><label class="form-label">Folder Name</label><input type="text" id="renameFolderName" class="form-input"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal(document.getElementById('renameFolderModal'))">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="renameFolderSave()">Rename</button>
        </div>
    </div>
</div>

<!-- File Info Modal -->
<div class="modal-overlay" id="fileInfoModal">
    <div class="modal-content" style="max-width:500px">
        <div class="modal-header"><h3 class="modal-title">File Details</h3><button class="modal-close" onclick="closeModal(document.getElementById('fileInfoModal'))">&times;</button></div>
        <div class="modal-body" id="fileInfoModalBody"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" id="fileOpenBtn" onclick="if(selectedFile)openFileViewer('<?= BASE_URL ?>' + selectedFile.filepath)"><i data-lucide="external-link" size="14"></i> Open</button>
            <button type="button" class="btn btn-danger btn-sm" id="fileDeleteBtn" onclick="deleteSelectedFile()"><i data-lucide="trash-2" size="14"></i> Delete</button>
            <button type="button" class="btn btn-secondary" onclick="closeModal(document.getElementById('fileInfoModal'))">Close</button>
        </div>
    </div>
</div>

<script>
var CSRF = '<?= $csrf ?>';
var CURRENT_FOLDER = <?= $currentFolderId ?>;
var selectedFile = null;
var API = '<?= BASE_URL ?>api/index.php';

function getFileInfoIcon(mime) {
    if (!mime) return 'file';
    if (mime.startsWith('image/')) return 'image';
    if (mime.startsWith('video/')) return 'film';
    if (mime.startsWith('audio/')) return 'music';
    if (mime.includes('pdf')) return 'file-text';
    if (mime.includes('zip') || mime.includes('archive')) return 'archive';
    if (mime.includes('word') || mime.includes('document')) return 'file-text';
    if (mime.includes('sheet') || mime.includes('excel')) return 'table';
    return 'file';
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/1048576).toFixed(1) + ' MB';
}

function showNewFolderModal() {
    document.getElementById('newFolderName').value = '';
    openModal('newFolderModal');
    setTimeout(function(){ document.getElementById('newFolderName').focus(); }, 200);
}

function createFolder() {
    var name = document.getElementById('newFolderName').value.trim();
    if (!name) return alert('Enter a folder name');
    var fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('name', name);
    fd.append('parent_id', CURRENT_FOLDER || '');
    fetch(API + '?action=create_folder', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(d){ if (d.success) location.reload(); else alert(d.error || 'Failed'); });
}

function renameFolder(id, currentName) {
    document.getElementById('renameFolderId').value = id;
    document.getElementById('renameFolderName').value = currentName;
    openModal('renameFolderModal');
    setTimeout(function(){ document.getElementById('renameFolderName').focus(); }, 200);
}

function renameFolderSave() {
    var id = document.getElementById('renameFolderId').value;
    var name = document.getElementById('renameFolderName').value.trim();
    if (!name) return alert('Enter a folder name');
    var fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('id', id);
    fd.append('name', name);
    fetch(API + '?action=rename_folder', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(d){ if (d.success) location.reload(); else alert(d.error || 'Failed'); });
}

function deleteFolder(id, name) {
    if (!confirm('Delete folder "' + name + '"? Files inside will be moved to root.')) return;
    var fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('id', id);
    fetch(API + '?action=delete_folder', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(d){ if (d.success) location.reload(); else alert(d.error || 'Failed'); });
}

function openFileViewer(url) {
    window.open(url, '_blank');
}

function quickDeleteFile(id, name) {
    if (!confirm('Delete "' + name + '"? This cannot be undone.')) return;
    var fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('id', id);
    fetch(API + '?action=delete_media', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(d){ if (d.success) location.reload(); else alert(d.error || 'Failed'); });
}

function showFileInfo(m) {
    selectedFile = m;
    var isImage = m.mime_type && m.mime_type.startsWith('image/');
    var isVideo = m.mime_type && m.mime_type.startsWith('video/');
    var isAudio = m.mime_type && m.mime_type.startsWith('audio/');
    var isPdf = m.mime_type && m.mime_type.includes('pdf');
    var html = '';
    if (isImage) {
        html += '<div style="text-align:center;margin-bottom:16px"><img src="<?= BASE_URL ?>' + m.filepath + '" style="max-width:100%;max-height:200px;border-radius:8px;object-fit:contain"></div>';
    } else if (isVideo) {
        html += '<div style="text-align:center;margin-bottom:16px"><video src="<?= BASE_URL ?>' + m.filepath + '" controls style="max-width:100%;max-height:200px;border-radius:8px"></video></div>';
    } else if (isAudio) {
        html += '<div style="text-align:center;margin-bottom:16px"><audio src="<?= BASE_URL ?>' + m.filepath + '" controls style="width:100%"></audio></div>';
    } else if (isPdf) {
        html += '<div style="text-align:center;margin-bottom:16px"><iframe src="<?= BASE_URL ?>' + m.filepath + '" style="width:100%;height:200px;border:none;border-radius:8px"></iframe></div>';
    }
    html += '<div class="file-info-row"><span class="file-info-label">Name</span><span class="file-info-value">' + (m.original_name || '') + '</span></div>';
    html += '<div class="file-info-row"><span class="file-info-label">Type</span><span class="file-info-value">' + (m.mime_type || 'Unknown') + '</span></div>';
    html += '<div class="file-info-row"><span class="file-info-label">Size</span><span class="file-info-value">' + formatSize(m.size || 0) + '</span></div>';
    html += '<div class="file-info-row"><span class="file-info-label">Uploaded</span><span class="file-info-value">' + (m.created_at || '') + '</span></div>';
    html += '<div class="file-info-row"><span class="file-info-label">URL</span><span class="file-info-value" style="font-size:11px;word-break:break-all;cursor:pointer" onclick="navigator.clipboard.writeText(\'' + '<?= BASE_URL ?>' + m.filepath + '\');this.textContent=\'Copied!\'"><?= BASE_URL ?>' + m.filepath + '</span></div>';
    document.getElementById('fileInfoModalBody').innerHTML = html;
    openModal('fileInfoModal');
    lucide.createIcons();
}

function deleteSelectedFile() {
    if (!selectedFile) return;
    if (!confirm('Delete "' + selectedFile.original_name + '"? This cannot be undone.')) return;
    var fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('id', selectedFile.id);
    fetch(API + '?action=delete_media', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(d){ if (d.success) { closeModal(document.getElementById('fileInfoModal')); location.reload(); } else { alert(d.error || 'Failed'); } });
}

// Upload handling
var uploadZone = document.getElementById('uploadZone');
var uploadInput = document.getElementById('fileUploadInput');
var filesMain = document.querySelector('.files-main');

uploadZone.addEventListener('click', function(){ uploadInput.click(); });
uploadZone.addEventListener('dragover', function(e){ e.preventDefault(); e.stopPropagation(); this.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', function(){ this.classList.remove('dragover'); });
uploadZone.addEventListener('drop', function(e){
    e.preventDefault(); e.stopPropagation(); this.classList.remove('dragover');
    if (e.dataTransfer.files.length) uploadFiles(e.dataTransfer.files);
});
uploadInput.addEventListener('change', function(){ if (this.files.length) uploadFiles(this.files); });

// Global drag-and-drop anywhere on the page
document.addEventListener('dragover', function(e){ e.preventDefault(); });
document.addEventListener('drop', function(e){ e.preventDefault(); if (e.dataTransfer.files.length) uploadFiles(e.dataTransfer.files); });

function uploadFiles(files) {
    uploadZone.innerHTML = '<div style="padding:20px"><i data-lucide="loader" size="24" style="animation:spin 1s linear infinite"></i><p style="margin-top:8px">Uploading ' + files.length + ' file(s)...</p></div>';
    lucide.createIcons();
    var done = 0, total = files.length;
    for (var i = 0; i < files.length; i++) {
        (function(file){
            var fd = new FormData();
            fd.append('file', file);
            fd.append('folder_id', CURRENT_FOLDER || '');
            fetch(API + '?action=upload_media', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(d){ done++; if (done >= total) location.reload(); })
            .catch(function(){ done++; if (done >= total) location.reload(); });
        })(files[i]);
    }
}
</script>

<script src="<?= BASE_URL ?>assets/js/admin.js"></script>
<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body></html>
