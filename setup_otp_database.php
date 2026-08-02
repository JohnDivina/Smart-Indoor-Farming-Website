<?php
/**
 * Database Setup Script for OTP Authentication
 * Run this file once to create the login_otps table
 */

include 'database.php';

echo "Connected to database successfully.<br><br>";

// Create login_otps table
$sql = "CREATE TABLE IF NOT EXISTS login_otps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_expires (user_id, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'login_otps' created successfully or already exists.<br>";
} else {
    echo "❌ Error creating table: " . $conn->error . "<br>";
}

// Verify users table has required columns
$checkColumns = $conn->query("DESCRIBE users");
$columns = [];
while ($row = $checkColumns->fetch_assoc()) {
    $columns[] = $row['Field'];
}

echo "<br><strong>Checking users table columns:</strong><br>";
$requiredColumns = ['username', 'email', 'phonenumber', 'password'];
foreach ($requiredColumns as $col) {
    if (in_array($col, $columns)) {
        echo "✅ Column '$col' exists<br>";
    } else {
        echo "❌ Column '$col' is MISSING! Please add it to the users table.<br>";
    }
}

echo "<br><strong>Setup complete!</strong><br>";
echo "<br><strong>Next steps:</strong><br>";
echo "1. Configure SMTP settings in send_otp_email.php<br>";
echo "2. Update the email credentials (Gmail/SendGrid/etc.)<br>";
echo "3. Test the login flow<br>";

$conn->close();
?>
