<?php
/*
 * FERTIGATION SCHEDULER — updated to support schedule_stop_time
 * Included by FERTIGATION/api/esp_heartbeat.php (requires $conn)
 */
date_default_timezone_set('Asia/Manila');

if (!isset($conn)) { error_log('check_schedule.php: $conn missing'); return; }

$result = $conn->query("SELECT * FROM fertigation_control WHERE id = 1");
if (!$result) { return; }
$state = $result->fetch_assoc();
if (!$state || $state['mode'] !== 'scheduled') { return; }

$start_str = $state['schedule_time'] ?? null;
$stop_str  = !empty($state['schedule_stop_time']) ? $state['schedule_stop_time'] : null;

if (!$start_str) { return; }

$now = new DateTime();

$parts = explode(':', $start_str);
$schedule_start = clone $now;
$schedule_start->setTime((int)$parts[0], (int)$parts[1], 0);

if ($stop_str) {
    $sp = explode(':', $stop_str);
    $schedule_end = clone $now;
    $schedule_end->setTime((int)$sp[0], (int)$sp[1], 0);
    // Handle overnight
    if ($schedule_end <= $schedule_start) {
        $schedule_end->modify('+1 day');
    }
} else {
    // Fallback to duration
    $dur = (int)($state['schedule_duration_minutes'] ?? 30);
    $schedule_end = clone $schedule_start;
    $schedule_end->modify("+{$dur} minutes");
}

$is_within = ($now >= $schedule_start && $now < $schedule_end);
$new_state  = $is_within ? 'on' : 'off';

if ($state['desired_pump_state'] !== $new_state) {
    $stmt = $conn->prepare("UPDATE fertigation_control SET desired_pump_state = ?, last_updated = NOW() WHERE id = 1");
    $stmt->bind_param('s', $new_state);
    $stmt->execute();
    $stmt->close();
}
?>
