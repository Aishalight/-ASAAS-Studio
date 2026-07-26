-- ============================================================
-- MIGRATION: Add folders table and folder_id to media
-- Run this SQL on your database to add file manager support
-- ============================================================

-- Create folders table
CREATE TABLE IF NOT EXISTS folders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    parent_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_parent (parent_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- Add folder_id to media table (only if not already present)
SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'media' AND COLUMN_NAME = 'folder_id'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE media ADD COLUMN folder_id INT DEFAULT NULL AFTER user_id',
    'SELECT "folder_id column already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'media' AND COLUMN_NAME = 'folder_id'
);
SET @sql2 = IF(@fk_exists = 0,
    'ALTER TABLE media ADD FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE SET NULL',
    'SELECT "FK already exists"'
);
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
