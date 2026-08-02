<?php
// Quick query script for ESP32 parity
include '../database.php';

$result = $conn->query("SELECT scheduled_mode_enabled, scheduled_time, scheduled_runtime, desired_fan_state FROM fan_state WHERE id = 1");
$row = $result->fetch_assoc();

echo "Current Blower State:\n";
echo "====================\n";
echo "Scheduled Mode: " . ($row['scheduled_mode_enabled'] ? 'YES' : 'NO') . "\n";
echo "Schedule Time: " . ($row['scheduled_time'] ?: 'NULL') . "\n";
echo "Duration: " . $row['scheduled_runtime'] . " minutes\n";
echo "Desired Fan State: " . $row['desired_fan_state'] . "\n";

$conn->close();
?>
