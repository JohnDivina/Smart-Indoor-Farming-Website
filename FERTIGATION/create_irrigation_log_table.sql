-- Create irrigation_log table for tracking irrigation events
CREATE TABLE IF NOT EXISTS irrigation_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(10) NOT NULL COMMENT 'START or STOP',
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp),
    INDEX idx_action_timestamp (action, timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Logs all irrigation pump start/stop events';

-- Insert sample data for testing (optional)
-- INSERT INTO irrigation_log (action, timestamp) VALUES 
-- ('START', DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
-- ('STOP', DATE_SUB(NOW(), INTERVAL 10 MINUTE)),
-- ('START', DATE_SUB(NOW(), INTERVAL 5 MINUTE));
