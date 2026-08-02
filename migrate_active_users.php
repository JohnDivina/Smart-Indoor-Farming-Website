<?php
/**
 * Add current_page column to active_users table
 */

include 'database.php';

echo "<h2>Active Users Table Migration</h2>";
echo "<pre>";

// Check if current_page column exists
$result = $conn->query("SHOW COLUMNS FROM active_users LIKE 'current_page'");

if ($result->num_rows == 0) {
    // Add current_page column
    $sql = "ALTER TABLE active_users ADD COLUMN current_page VARCHAR(100) DEFAULT 'Unknown' AFTER last_activity";
    
    if ($conn->query($sql)) {
        echo "✅ Added 'current_page' column to active_users table\n";
    } else {
        echo "❌ Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "ℹ️  'current_page' column already exists\n";
}

// Show final table structure
echo "\n📋 Final table structure:\n";
$result = $conn->query("DESCRIBE active_users");
while ($row = $result->fetch_assoc()) {
    echo "  - {$row['Field']} ({$row['Type']})\n";
}

echo "</pre>";
echo "<p><a href='index.php'>← Back to Dashboard</a></p>";

$conn->close();
