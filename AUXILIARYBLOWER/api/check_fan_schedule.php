<?php
/*
 * Fan Schedule Checker — included by esp_heartbeat.php and update_state.php
 *
 * Uses schedule_time (start) and schedule_stop_time (stop) from fan_state.
 * Falls back to schedule_time + schedule_duration_minutes if stop time absent.
 *
 * Only runs when mode = 'scheduled'. Flips desired_fan_state accordingly.
 * Uses $conn from parent file.
 */

$res = $conn->query("SELECT * FROM fan_state WHERE id = 1");
if (!$res) return;
$row = $res->fetch_assoc();

// Only act in scheduled mode
if ($row['mode'] !== 'scheduled') return;

$start_str = $row['schedule_time']      ? substr($row['schedule_time'], 0, 5) : null;
$stop_str  = !empty($row['schedule_stop_time'])
                ? substr($row['schedule_stop_time'], 0, 5)
                : null;

if (!$start_str) return;

$today = date('Y-m-d');
$now_ts = time();

$start_ts = strtotime("$today $start_str");

// Determine stop timestamp
if ($stop_str) {
    $stop_ts = strtotime("$today $stop_str");
    // Handle overnight schedules (stop < start → add 1 day)
    if ($stop_ts <= $start_ts) {
        $stop_ts = strtotime("+1 day", $stop_ts);
    }
} else {
    // Fallback: use duration
    $duration_min = (int)($row['schedule_duration_minutes'] ?? 30);
    $stop_ts = $start_ts + ($duration_min * 60);
}

// Determine desired state
if ($now_ts >= $start_ts && $now_ts < $stop_ts) {
    $new_state = 'on';
} else {
    $new_state = 'off';
}

// Only write if state has actually changed (avoid excessive writes)
if ($new_state !== strtolower($row['desired_fan_state'])) {
    $stmt = $conn->prepare(
        "UPDATE fan_state SET desired_fan_state = ?, last_schedule_run = NOW() WHERE id = 1"
    );
    $stmt->bind_param('s', $new_state);
    $stmt->execute();
    $stmt->close();
}
?>
