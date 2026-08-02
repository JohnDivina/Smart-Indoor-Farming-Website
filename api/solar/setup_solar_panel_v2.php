<?php
// api/solar/setup_solar_panel_v2.sql
// Execute this script manually or include in your database initialization

/*
CREATE TABLE IF NOT EXISTS `solar_panel_control` (
  `id` tinyint(2) NOT NULL DEFAULT 1,
  `mode` enum('manual','scheduled') NOT NULL DEFAULT 'manual',
  `desired_state` tinyint(2) NOT NULL DEFAULT -1,
  `open_time` time DEFAULT NULL,
  `fold_time` time DEFAULT NULL,
  `config_version` int(11) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `solar_panel_status` (
  `id` tinyint(2) NOT NULL DEFAULT 1,
  `motor_running` tinyint(2) NOT NULL DEFAULT 0,
  `actual_state` tinyint(2) NOT NULL DEFAULT 0,
  `pushed` tinyint(2) NOT NULL DEFAULT 0,
  `pulled` tinyint(2) NOT NULL DEFAULT 1,
  `direction` varchar(20) NOT NULL DEFAULT 'STOP',
  `last_message` varchar(255) DEFAULT NULL,
  `wifi_status` varchar(32) NOT NULL DEFAULT 'disconnected',
  `ack_config_version` int(11) NOT NULL DEFAULT 0,
  `last_seen_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initialize tables with default rows if they don't exist
INSERT IGNORE INTO `solar_panel_control` (`id`, `mode`, `desired_state`, `config_version`) VALUES (1, 'manual', -1, 1);
INSERT IGNORE INTO `solar_panel_status` (`id`, `motor_running`, `actual_state`, `pushed`, `pulled`, `direction`, `wifi_status`, `ack_config_version`) VALUES (1, 0, 0, 0, 1, 'STOP', 'disconnected', 0);
*/

require_once __DIR__ . '/../../database.php';

try {
    // Read the SQL file from itself (the comments above) to execute it. 
    // This is just a helper for quick setup.
    // In production, you would run the SQL commands directly in your database management tool (e.g. phpMyAdmin, DBeaver)

    echo "Running setup array...<br>";
    
    $queries = [
        "CREATE TABLE IF NOT EXISTS `solar_panel_control` (
          `id` tinyint(2) NOT NULL DEFAULT 1,
          `mode` enum('manual','scheduled') NOT NULL DEFAULT 'manual',
          `desired_state` tinyint(2) NOT NULL DEFAULT -1,
          `open_time` time DEFAULT NULL,
          `fold_time` time DEFAULT NULL,
          `config_version` int(11) NOT NULL DEFAULT 1,
          `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE IF NOT EXISTS `solar_panel_status` (
          `id` tinyint(2) NOT NULL DEFAULT 1,
          `motor_running` tinyint(2) NOT NULL DEFAULT 0,
          `actual_state` tinyint(2) NOT NULL DEFAULT 0,
          `pushed` tinyint(2) NOT NULL DEFAULT 0,
          `pulled` tinyint(2) NOT NULL DEFAULT 1,
          `direction` varchar(20) NOT NULL DEFAULT 'STOP',
          `last_message` varchar(255) DEFAULT NULL,
          `wifi_status` varchar(32) NOT NULL DEFAULT 'disconnected',
          `ack_config_version` int(11) NOT NULL DEFAULT 0,
          `last_seen_at` datetime DEFAULT NULL,
          `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "INSERT IGNORE INTO `solar_panel_control` (`id`, `mode`, `desired_state`, `config_version`) VALUES (1, 'manual', -1, 1);",
        
        "INSERT IGNORE INTO `solar_panel_status` (`id`, `motor_running`, `actual_state`, `pushed`, `pulled`, `direction`, `wifi_status`, `ack_config_version`) VALUES (1, 0, 0, 0, 1, 'STOP', 'disconnected', 0);"
    ];

    foreach ($queries as $query) {
        if ($conn->query($query) === TRUE) {
            echo "Successfully executed query.<br>";
        } else {
            echo "Error executing query: " . $conn->error . "<br>";
        }
    }
    
    echo "<br><b>Database tables created and initialized successfully!</b>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
