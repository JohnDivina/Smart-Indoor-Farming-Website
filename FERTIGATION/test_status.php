<?php
/*
 * Test Script: Check Current Status
 * 
 * Shows the current fertigation status including schedule information
 */

$conn = new mysqli("localhost", "root", "", "smartfarm");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== CURRENT FERTIGATION STATUS ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$result = $conn->query("SELECT * FROM fertigation_control WHERE id = 1");
$state = $result->fetch_assoc();

echo "Mode: {$state['mode']}\n";
echo "Desired Pump State: {$state['desired_pump_state']}\n";
echo "ESP Pump State: {$state['esp_pump_state']}\n";
echo "ESP Mode: {$state['esp_mode']}\n";
echo "\nSchedule Configuration:\n";
echo "  Time: {$state['schedule_time']}\n";
echo "  Duration: {$state['schedule_duration_minutes']} minutes\n";
echo "  Last Run: " . ($state['last_schedule_run'] ?: 'Never') . "\n";
echo "\nTimestamps:\n";
echo "  Last Heartbeat: {$state['last_heartbeat']}\n";
echo "  Last Updated: {$state['last_updated']}\n";

// Check recent irrigation logs
echo "\n=== RECENT IRRIGATION LOGS ===\n";
$logs = $conn->query("SELECT * FROM irrigation_log ORDER BY timestamp DESC LIMIT 5");
while ($log = $logs->fetch_assoc()) {
    echo "{$log['timestamp']} - {$log['action']}\n";
}

$conn->close();
?>
