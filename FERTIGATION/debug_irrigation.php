<?php
// Debug script to check irrigation system
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Fertigation System Debug</h2>";

include '../database.php';

echo "<p style='color:green'>✅ Database connected successfully</p>";

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'irrigation_log'");
if ($result->num_rows > 0) {
    echo "<p style='color:green'>✅ Table 'irrigation_log' exists</p>";
    
    // Check table structure
    $result = $conn->query("DESCRIBE irrigation_log");
    echo "<h3>Table Structure:</h3><pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
    
    // Check if there are any records
    $result = $conn->query("SELECT COUNT(*) as count FROM irrigation_log");
    $row = $result->fetch_assoc();
    echo "<p>Total records: <strong>" . $row['count'] . "</strong></p>";
    
    // Show last 10 records
    $result = $conn->query("SELECT * FROM irrigation_log ORDER BY timestamp DESC LIMIT 10");
    if ($result->num_rows > 0) {
        echo "<h3>Last 10 Irrigation Events:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Action</th><th>Timestamp</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['action'] . "</td>";
            echo "<td>" . $row['timestamp'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange'>⚠️ No irrigation events logged yet</p>";
    }
} else {
    echo "<p style='color:red'>❌ Table 'irrigation_log' does NOT exist!</p>";
    echo "<p>Please run <a href='setup_irrigation_table.php'>setup_irrigation_table.php</a> first</p>";
}

// Test log_irrigation.php
echo "<h3>Testing log_irrigation.php:</h3>";
$testData = json_encode(['action' => 'START']);
$ch = curl_init('http://localhost/smartfarm2/FERTIGATION/log_irrigation.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: <strong>$httpCode</strong></p>";
echo "<p>Response: <pre>" . htmlspecialchars($response) . "</pre></p>";

// Test get_last_irrigation.php
echo "<h3>Testing get_last_irrigation.php:</h3>";
$ch = curl_init('http://localhost/smartfarm2/FERTIGATION/get_last_irrigation.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: <strong>$httpCode</strong></p>";
echo "<p>Response: <pre>" . htmlspecialchars($response) . "</pre></p>";

$conn->close();
?>
