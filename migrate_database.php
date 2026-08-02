<?php
/**
 * Database Migration Script
 * Adds email_verified and last_login columns to users table
 */

include 'database.php';

echo "<h2>Database Migration: Add Email Verification</h2>";
echo "<pre>";

// Check if columns already exist
$checkColumns = $conn->query("DESCRIBE users");
$existingColumns = [];
while ($row = $checkColumns->fetch_assoc()) {
    $existingColumns[] = $row['Field'];
}

// Add email_verified column
if (!in_array('email_verified', $existingColumns)) {
    $sql = "ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER email";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Added 'email_verified' column\n";
    } else {
        echo "❌ Error adding 'email_verified': " . $conn->error . "\n";
    }
} else {
    echo "ℹ️  Column 'email_verified' already exists\n";
}

// Add last_login column
if (!in_array('last_login', $existingColumns)) {
    $sql = "ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER email_verified";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Added 'last_login' column\n";
    } else {
        echo "❌ Error adding 'last_login': " . $conn->error . "\n";
    }
} else {
    echo "ℹ️  Column 'last_login' already exists\n";
}

// Migrate existing users
echo "\n<strong>Migrating existing users...</strong>\n";

$sql = "UPDATE users SET email_verified = 1 WHERE email_verified = 0 OR email_verified IS NULL";
if ($conn->query($sql) === TRUE) {
    echo "✅ Marked " . $conn->affected_rows . " existing users as verified\n";
} else {
    echo "❌ Error updating email_verified: " . $conn->error . "\n";
}

$sql = "UPDATE users SET last_login = NOW() WHERE last_login IS NULL";
if ($conn->query($sql) === TRUE) {
    echo "✅ Set last_login for " . $conn->affected_rows . " users\n";
} else {
    echo "❌ Error updating last_login: " . $conn->error . "\n";
}

// Show summary
echo "\n<strong>Migration Summary:</strong>\n";
$result = $conn->query("SELECT COUNT(*) AS total_users, 
                               SUM(email_verified) AS verified_users,
                               COUNT(last_login) AS users_with_login_time
                        FROM users");
$row = $result->fetch_assoc();

echo "Total Users: " . $row['total_users'] . "\n";
echo "Verified Users: " . $row['verified_users'] . "\n";
echo "Users with Login Time: " . $row['users_with_login_time'] . "\n";

echo "\n✅ <strong>Migration completed successfully!</strong>\n";
echo "</pre>";

$conn->close();
?>
