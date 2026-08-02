<?php
/*
 * api/fertigation/save_schedule.php
 * Saves schedule times to DB only. Does NOT change mode.
 * Config version is bumped so the ESP32 detects the new config.
 * On reset: clears times, returns to manual, bumps config_version.
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
require_once '../../database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['reset']) && !isset($input['time'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    if (!empty($input['reset'])) {
        // Reset: clear schedule, return to manual mode
        $stmt = $conn->prepare(
            "UPDATE fertigation_control
             SET mode = 'manual',
                 schedule_time = NULL,
                 schedule_stop_time = NULL,
                 schedule_duration_minutes = 0,
                 last_schedule_run = NULL,
                 config_version = config_version + 1
             WHERE id = 1"
        );
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Schedule reset. Returned to Manual Mode.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }
        $conn->close();
        exit;
    }

    // Save times only — do NOT change mode
    $time      = $input['time']      ?? null;
    $stop_time = $input['stop_time'] ?? null;

    if (!$time || !$stop_time) {
        echo json_encode(['success' => false, 'message' => 'Missing time or stop_time']);
        exit;
    }

    if (!preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $time) ||
        !preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $stop_time)) {
        echo json_encode(['success' => false, 'message' => 'Invalid time format (HH:MM required)']);
        exit;
    }

    if ($time === $stop_time) {
        echo json_encode(['success' => false, 'message' => 'Start and Stop times cannot be the same']);
        exit;
    }

    $t1       = strtotime($time);
    $t2       = strtotime($stop_time);
    $duration = ($t2 > $t1) ? ($t2 - $t1) / 60 : (($t2 + 86400) - $t1) / 60;

    $time_full      = $time . ':00';
    $stop_time_full = $stop_time . ':00';

    $stmt = $conn->prepare(
        "UPDATE fertigation_control
         SET schedule_time = ?,
             schedule_stop_time = ?,
             schedule_duration_minutes = ?,
             config_version = config_version + 1
         WHERE id = 1"
    );
    $stmt->bind_param("ssi", $time_full, $stop_time_full, $duration);

    if ($stmt->execute()) {
        // Return the new config_version so JS can track ack
        $ver = $conn->query("SELECT config_version FROM fertigation_control WHERE id = 1")->fetch_assoc();
        echo json_encode([
            'success'        => true,
            'message'        => "Schedule saved ($time → $stop_time). Enable Scheduled Mode to activate.",
            'config_version' => (int)$ver['config_version']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
