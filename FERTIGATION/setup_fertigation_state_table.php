<?php
// Run this file once to create the fertigation_state table
include '../database.php';

$sql = "CREATE TABLE IF NOT EXISTS fertigation_state (
    id INT PRIMARY KEY DEFAULT 1,
    
    -- Scheduled Mode Settings
    scheduled_mode_enabled BOOLEAN DEFAULT FALSE,
    scheduled_time TIME DEFAULT NULL COMMENT 'HH:MM:SS format',
    scheduled_runtime INT DEFAULT 30 COMMENT 'Minutes',
    
    -- Current State
    desired_pump_state ENUM('ON', 'OFF') DEFAULT 'OFF',
    last_schedule_run DATETIME DEFAULT NULL,
    
    -- ESP32 Heartbeat Tracking
    last_esp_heartbeat DATETIME DEFAULT NULL,
    last_reported_pump_state ENUM('ON', 'OFF', 'UNKNOWN') DEFAULT 'UNKNOWN',
    last_reported_mode VARCHAR(20) DEFAULT 'UNKNOWN',
    
    -- Metadata
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Single row table for fertigation system state'";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'fertigation_state' created successfully!<br>";
    
    // Insert default row
    $insertSql = "INSERT INTO fertigation_state (id) VALUES (1) ON DUPLICATE KEY UPDATE id=1";
    if ($conn->query($insertSql) === TRUE) {
        echo "✅ Default state row initialized!<br>";
    }
    
    echo "<br>You can now delete this file (setup_fertigation_state_table.php)";
} else {
    echo "❌ Error creating table: " . $conn->error;
}

$conn->close();
?>
