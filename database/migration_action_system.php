<?php
// ============================================================
// ASAAS STUDIO - Action & Response System Migration
// Run once: php database/migration_action_system.php
// ============================================================
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Update activity_logs.status ENUM to include malicious, ignored
    try {
        $db->exec("ALTER TABLE activity_logs MODIFY COLUMN status ENUM('normal','suspicious','blocked','malicious','ignored') DEFAULT 'normal'");
        echo "[OK] activity_logs.status ENUM updated\n";
    } catch (Exception $e) {
        echo "[SKIP] " . $e->getMessage() . "\n";
    }

    // 2. Update activity_logs.severity ENUM
    try {
        $db->exec("ALTER TABLE activity_logs MODIFY COLUMN severity ENUM('low','medium','high','critical') DEFAULT 'low'");
        echo "[OK] activity_logs.severity ENUM updated\n";
    } catch (Exception $e) {
        echo "[SKIP] " . $e->getMessage() . "\n";
    }
    
    // 3. Add status column to alerts if not exists
    try {
        $db->exec("ALTER TABLE alerts ADD COLUMN status ENUM('new','acknowledged','resolved','reopened') DEFAULT 'new' AFTER is_read");
        echo "[OK] alerts.status column added\n";
    } catch (Exception $e) {
        // Column may already exist
        try {
            $db->exec("ALTER TABLE alerts MODIFY COLUMN status ENUM('new','acknowledged','resolved','reopened') DEFAULT 'new'");
            echo "[OK] alerts.status ENUM modified\n";
        } catch (Exception $e2) {
            echo "[SKIP] " . $e2->getMessage() . "\n";
        }
    }
    
    // 4. Add is_read to alerts if not exists
    try {
        $db->exec("ALTER TABLE alerts ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER user_id");
        echo "[OK] alerts.is_read column added\n";
    } catch (Exception $e) {
        echo "[SKIP] alerts.is_read already exists\n";
    }
    
    // 5. Create action_logs table
    $db->exec("CREATE TABLE IF NOT EXISTS action_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        log_id INT DEFAULT NULL,
        alert_id INT DEFAULT NULL,
        action_type VARCHAR(50) NOT NULL,
        action_details JSON DEFAULT NULL,
        performed_by INT NOT NULL,
        target_user_id INT DEFAULT NULL,
        target_ip VARCHAR(45) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (log_id) REFERENCES activity_logs(id) ON DELETE SET NULL,
        FOREIGN KEY (alert_id) REFERENCES alerts(id) ON DELETE SET NULL,
        INDEX idx_log (log_id),
        INDEX idx_alert (alert_id),
        INDEX idx_action_type (action_type),
        INDEX idx_performed_by (performed_by),
        INDEX idx_target_user (target_user_id),
        INDEX idx_target_ip (target_ip),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB");
    echo "[OK] action_logs table created\n";
    
    // 6. Create blocked_ips table
    $db->exec("CREATE TABLE IF NOT EXISTS blocked_ips (
        id INT PRIMARY KEY AUTO_INCREMENT,
        ip_address VARCHAR(45) NOT NULL,
        reason VARCHAR(500) DEFAULT NULL,
        blocked_by INT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        unblocked_at DATETIME DEFAULT NULL,
        FOREIGN KEY (blocked_by) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_ip (ip_address),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB");
    echo "[OK] blocked_ips table created\n";
    
    echo "\n=== Migration Complete ===\n";
} catch (Exception $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}
