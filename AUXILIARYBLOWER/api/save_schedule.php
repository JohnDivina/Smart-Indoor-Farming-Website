<?php
/*
 * Save Fan Schedule — called by browser frontend
 * Updates mode, schedule_time, schedule_stop_time, schedule_duration_minutes
 */
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
if (!empty($_SESSION['is_guest'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Action restricted in Guest mode. Please log in with an account.']);
    exit();
}
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
    exit();
}
include '../../database.php';

$data = json_decode(file_get_contents('php://input'), true);

$enabled   = isset($data['enabled'])    ? (bool)$data['enabled'] : false;
$time      = $data['time']      ?? null;   // HH:MM start
$stop_time = $data['stop_time'] ?? null;   // HH:MM stop
$reset     = isset($data['reset'])      ? (bool)$data['reset']   : false;
$mode      = ($enabled && !$reset) ? 'scheduled' : 'manual';

// Validate and format times
function validateHHMM($t) {
    if (!$t) return null;
    $t = trim($t);
    if (preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $t)) return $t . ':00'; // HH:MM → HH:MM:SS
    if (preg_match('/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $t)) return $t;  // already HH:MM:SS
    return null;
}

$start_fmt = validateHHMM($time);
$stop_fmt  = validateHHMM($stop_time);

// Compute duration_minutes from start + stop if both provided
$duration = 30; // default
if ($start_fmt && $stop_fmt) {
    $s = strtotime('2000-01-01 ' . $start_fmt);
    $e = strtotime('2000-01-01 ' . $stop_fmt);
    if ($e > $s) {
        $duration = (int)(($e - $s) / 60);
    } elseif ($e < $s) {
        // overnight
        $duration = (int)((86400 - $s + $e) / 60);
    }
}

if ($reset) {
    $start_fmt = null;
    $stop_fmt  = null;
    $duration  = 0;
} elseif ($enabled && !$start_fmt) {
    echo json_encode(['success' => false, 'message' => 'Start time required when enabling schedule']);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE fan_state SET
        mode                       = ?,
        schedule_time              = ?,
        schedule_stop_time         = ?,
        schedule_duration_minutes  = ?,
        config_version             = config_version + 1,
        last_updated               = NOW()
     WHERE id = 1"
);
$stmt->bind_param('sssi', $mode, $start_fmt, $stop_fmt, $duration);

if ($stmt->execute()) {
    $msg = $reset 
        ? 'Schedule cleared and reset to manual mode'
        : ($enabled ? "Blower schedule saved: $time – $stop_time" : 'Scheduled mode disabled');
    echo json_encode([
        'success'                   => true,
        'message'                   => $msg,
        'mode'                      => $mode,
        'schedule_time'             => $start_fmt,
        'schedule_stop_time'        => $stop_fmt,
        'schedule_duration_minutes' => $duration
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
