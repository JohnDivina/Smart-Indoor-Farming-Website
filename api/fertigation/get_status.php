<?php
/*
 * api/fertigation/get_status.php  — UI polling endpoint
 *
 * Architecture: ESP32 is now the ACTIVE node (it polls the server).
 * This endpoint only reads from the DB. No curl to ESP32.
 *
 * Connection health is determined by last_heartbeat staleness (>15s = offline).
 * actual_pump_state is what the ESP32 last reported; desired_pump_state is what the
 * website requested. The UI shows actual_pump_state as ground truth.
 */
header('Content-Type: application/json');
require_once '../../database.php';

$response = [
    'success'             => false,
    'mode'                => 'manual',
    'desired_pump_state'  => 'off',
    'actual_pump_state'   => 'off',
    'esp_pump_state'      => 'off',   // alias for legacy JS compat
    'esp_online'          => false,
    'schedule_time'       => null,
    'schedule_stop_time'  => null,
    'config_version'      => 0,
    'ack_config_version'  => 0,
    'error_message'       => ''
];

try {
    $res = $conn->query("SELECT * FROM fertigation_control WHERE id = 1");
    $row = $res ? $res->fetch_assoc() : null;

    if (!$row) {
        $response['error_message'] = 'DB row not found';
        echo json_encode($response);
        $conn->close();
        exit;
    }

    $response['success']            = true;
    $response['mode']               = $row['mode'];
    $response['desired_pump_state'] = $row['desired_pump_state'];
    $response['actual_pump_state']  = $row['actual_pump_state'];
    $response['esp_pump_state']     = $row['actual_pump_state'];  // legacy alias
    $response['schedule_time']      = $row['schedule_time'];
    $response['schedule_stop_time'] = $row['schedule_stop_time'];
    $response['config_version']     = (int)$row['config_version'];
    $response['ack_config_version'] = (int)$row['ack_config_version'];

    // Determine online status from heartbeat staleness (same logic as Solar)
    if ($row['last_heartbeat']) {
        $last_seen  = strtotime($row['last_heartbeat']);
        $age_secs   = time() - $last_seen;
        $response['esp_online'] = ($age_secs <= 15);
    }

} catch (Exception $e) {
    $response['error_message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
