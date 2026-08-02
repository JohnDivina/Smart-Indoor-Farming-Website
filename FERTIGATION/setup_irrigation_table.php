<?php
// Run this file once to create the irrigation_log table
include '../database.php';

$sql = "CREATE TABLE IF NOT EXISTS irrigation_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(10) NOT NULL COMMENT 'START or STOP',
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp),
    INDEX idx_action_timestamp (action, timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Logs all irrigation pump start/stop events'";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'irrigation_log' created successfully!<br>";
    echo "You can now delete this file (setup_irrigation_table.php)";
} else {
    echo "❌ Error creating table: " . $conn->error;
}

$conn->close();
?>
