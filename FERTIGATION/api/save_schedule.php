<?php
/*
 * Save Fertigation Schedule — updated to store schedule_stop_time
 * Called by browser frontend.
 */
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../database.php';

$data = json_decode(file_get_contents('php://input'), true);

$enabled   = isset($data['enabled']) ? (bool)$data['enabled'] : false;
$time      = $data['time']      ?? null;  // HH:MM start
$stop_time = $data['stop_time'] ?? null;  // HH:MM stop
$reset     = isset($data['reset'])      ? (bool)$data['reset'] : false;
$mode      = ($enabled && !$reset) ? 'scheduled' : 'manual';

// Validate and normalise HH:MM → HH:MM:SS
function fertFmtTime($t) {
    if (!$t) return null;
    $t = trim($t);
    if (preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $t)) return $t . ':00';
    if (preg_match('/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $t)) return $t;
    return null;
}

$start_fmt = fertFmtTime($time);
$stop_fmt  = fertFmtTime($stop_time);

// Compute duration
$duration = 30;
if ($start_fmt && $stop_fmt) {
    $s = strtotime('2000-01-01 ' . $start_fmt);
    $e = strtotime('2000-01-01 ' . $stop_fmt);
    if ($e > $s) {
        $duration = (int)(($e - $s) / 60);
    } elseif ($e < $s) {
        $duration = (int)((86400 - $s + $e) / 60);
    }
}

if ($reset) {
    $start_fmt = null;
    $stop_fmt  = null;
    $duration  = 0;
} elseif ($enabled && !$start_fmt) {
    echo json_encode(['success' => false, 'message' => 'Start time required when enabling schedule']);
    exit();
}

$stmt = $conn->prepare(
    "UPDATE fertigation_control SET
        mode                      = ?,
        schedule_time             = ?,
        schedule_stop_time        = ?,
        schedule_duration_minutes = ?,
        config_version            = config_version + 1,
        last_updated              = NOW()
     WHERE id = 1"
);
$stmt->bind_param('sssi', $mode, $start_fmt, $stop_fmt, $duration);

if ($stmt->execute()) {
    echo json_encode([
        'success'                   => true,
        'message'                   => $reset ? "Schedule reset." : ($enabled ? "Schedule saved." : 'Scheduled mode disabled'),
        'mode'                      => $mode,
        'schedule_time'             => $start_fmt,
        'schedule_stop_time'        => $stop_fmt,
        'schedule_duration_minutes' => $duration
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save schedule: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
