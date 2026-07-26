<?php
require_once __DIR__ . '/../config/functions.php';
startSession();
if (!isAdmin()) { die('Admin only.'); }

$db = Database::getInstance()->getConnection();
$results = [];

try {
    $db->exec("CREATE TABLE IF NOT EXISTS folders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        parent_id INT DEFAULT NULL,
        user_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_parent (parent_id),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB");
    $results[] = "OK: folders table created/verified";
} catch (Exception $e) {
    $results[] = "ERROR folders: " . $e->getMessage();
}

try {
    $colCheck = $db->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'media' AND COLUMN_NAME = 'folder_id'")->fetchColumn();
    if (!$colCheck) {
        $db->exec("ALTER TABLE media ADD COLUMN folder_id INT DEFAULT NULL AFTER user_id");
        $results[] = "OK: folder_id column added to media";
    } else {
        $results[] = "OK: folder_id column already exists";
    }
} catch (Exception $e) {
    $results[] = "ERROR folder_id: " . $e->getMessage();
}

try {
    $fkCheck = $db->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'media' AND COLUMN_NAME = 'folder_id' AND REFERENCED_TABLE_NAME = 'folders'")->fetchColumn();
    if (!$fkCheck) {
        $db->exec("ALTER TABLE media ADD FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE SET NULL");
        $results[] = "OK: FK added";
    } else {
        $results[] = "OK: FK already exists";
    }
} catch (Exception $e) {
    $results[] = "ERROR FK: " . $e->getMessage();
}

$results[] = "Migration complete!";
?>
<!DOCTYPE html>
<html><head><title>Migration</title></head>
<body>
<h2>File Manager Migration Results</h2>
<ul><?php foreach ($results as $r): ?><li><?= htmlspecialchars($r) ?></li><?php endforeach; ?></ul>
<p><a href="<?= BASE_URL ?>admin">Back to Admin</a></p>
</body></html>
