-- Create comprehensive notifications table for all system notifications
CREATE TABLE IF NOT EXISTS system_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    timestamp DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add index for better performance when querying by timestamp and type
CREATE INDEX idx_timestamp ON system_notifications(timestamp);
CREATE INDEX idx_type ON system_notifications(type);
CREATE INDEX idx_type_timestamp ON system_notifications(type, timestamp);
