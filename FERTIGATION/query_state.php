<?php
// Quick query script
include '../database.php';

$result = $conn->query("SELECT mode, schedule_time, schedule_duration_minutes, desired_pump_state FROM fertigation_control WHERE id = 1");
$row = $result->fetch_assoc();

echo "Current Fertigation State:\n";
echo "========================\n";
echo "Mode: " . $row['mode'] . "\n";
echo "Schedule Time: " . ($row['schedule_time'] ?: 'NULL') . "\n";
echo "Duration: " . $row['schedule_duration_minutes'] . " minutes\n";
echo "Desired Pump State: " . $row['desired_pump_state'] . "\n";

$conn->close();
?>
