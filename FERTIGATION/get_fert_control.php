<?php
/*
 * FERTIGATION/get_fert_control.php
 * Called by the ESP32 Fertigation controller (HTTP GET, every 3-5 seconds).
 * Returns current mode, schedule times, desired_pump_state, server_time,
 * and config_version so the firmware can check for config changes.
 *
 * Modes:
 *   manual      - ESP32 matches pump to desired_pump_state
 *   scheduled   - ESP32 uses schedule_time/schedule_stop_time + server_time
 *   sensor_auto - ESP32 uses local moisture sensor threshold logic (built-in)
 */
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../includes/esp32_auth.php';
verify_esp32_auth();
require_once __DIR__ . '/../database.php';

$response = ['success' => false, 'message' => 'Unknown error'];

try {
    $res  = $conn->query("SELECT * FROM fertigation_control WHERE id = 1");
    $row  = $res ? $res->fetch_assoc() : null;

    if ($row) {
        $response = [
            'success'             => true,
            'mode'                => $row['mode'],
            'desired_pump_state'  => $row['desired_pump_state'],
            'schedule_time'       => $row['schedule_time']      ? substr($row['schedule_time'], 0, 5)      : '',
            'schedule_stop_time'  => $row['schedule_stop_time'] ? substr($row['schedule_stop_time'], 0, 5) : '',
            'config_version'      => (int)$row['config_version'],
            'server_time'         => date('Y-m-d H:i:s'),   // ESP32 uses this for schedule comparisons
        ];
    } else {
        $response['message'] = 'Database row not found';
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
