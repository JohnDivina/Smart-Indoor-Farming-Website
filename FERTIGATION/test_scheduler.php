<?php
/*
 * TEST SCRIPT FOR FERTIGATION SCHEDULER
 * 
 * This script tests the scheduler logic by simulating different scenarios.
 * Run this from command line: php test_scheduler.php
 */

// Set timezone
date_default_timezone_set('Asia/Manila');

// Database connection
$conn = new mysqli("localhost", "root", "", "smartfarm");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== FERTIGATION SCHEDULER TEST ===\n";
echo "Current time: " . date('Y-m-d H:i:s') . "\n\n";

// Fetch current state
$result = $conn->query("SELECT * FROM fertigation_control WHERE id = 1");
$state = $result->fetch_assoc();

echo "Current State:\n";
echo "  Mode: {$state['mode']}\n";
echo "  Schedule Time: {$state['schedule_time']}\n";
echo "  Duration: {$state['schedule_duration_minutes']} minutes\n";
echo "  Desired Pump State: {$state['desired_pump_state']}\n";
echo "  ESP Pump State: {$state['esp_pump_state']}\n";
echo "  Last Heartbeat: {$state['last_heartbeat']}\n\n";

// Run the scheduler
echo "Running scheduler...\n";
require_once __DIR__ . '/api/check_schedule.php';

// Fetch updated state
$result = $conn->query("SELECT * FROM fertigation_control WHERE id = 1");
$newState = $result->fetch_assoc();

echo "\nAfter Scheduler:\n";
echo "  Desired Pump State: {$newState['desired_pump_state']}";
if ($newState['desired_pump_state'] !== $state['desired_pump_state']) {
    echo " (CHANGED from {$state['desired_pump_state']})";
}
echo "\n";

// Calculate if we should be within window
if ($state['mode'] === 'scheduled' && $state['schedule_time']) {
    $now = new DateTime();
    $schedule_parts = explode(':', $state['schedule_time']);
    $schedule_start = clone $now;
    $schedule_start->setTime((int)$schedule_parts[0], (int)$schedule_parts[1], 0);
    
    $schedule_end = clone $schedule_start;
    $schedule_end->modify("+{$state['schedule_duration_minutes']} minutes");
    
    $is_within = ($now >= $schedule_start && $now < $schedule_end);
    
    echo "\nSchedule Window:\n";
    echo "  Start: " . $schedule_start->format('H:i:s') . "\n";
    echo "  End: " . $schedule_end->format('H:i:s') . "\n";
    echo "  Within Window: " . ($is_within ? 'YES' : 'NO') . "\n";
    echo "  Expected State: " . ($is_within ? 'on' : 'off') . "\n";
    
    if ($newState['desired_pump_state'] === ($is_within ? 'on' : 'off')) {
        echo "\n✅ SCHEDULER WORKING CORRECTLY\n";
    } else {
        echo "\n❌ SCHEDULER ERROR: State mismatch\n";
    }
} else {
    echo "\nScheduled mode not active or no schedule time set.\n";
}

$conn->close();
?>
