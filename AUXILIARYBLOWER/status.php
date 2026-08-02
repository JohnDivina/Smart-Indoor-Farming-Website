<?php
/*
 * Status Endpoint — Auxiliary Blower (used by browser frontend)
 *
 * Returns blower state, ESP32 online status, mode, and schedule settings.
 */
header('Content-Type: application/json');
include '../database.php';

$response = [
    'success'       => true,
    'status'        => 'disconnected',
    'relay'         => 'off',
    'message'       => 'Checking system...',
    'esp_fan_state' => 'off',
    'esp_online'    => false,
    'mode'          => 'manual',
    'schedule_time' => null,
    'schedule_stop_time' => null
];

try {
    $result = $conn->query("SELECT * FROM fan_state WHERE id = 1");
    if ($result !== false && $row = $result->fetch_assoc()) {

        // Online if heartbeat within last 60 seconds
        $last_hb   = !empty($row['last_heartbeat']) ? strtotime($row['last_heartbeat']) : 0;
        $now       = time();
        $is_online = ($last_hb > 0) && ($now - $last_hb) <= 60;

        $response['esp_online'] = $is_online;
        $response['status']     = $is_online ? 'connected' : 'disconnected';

        // Show actual ESP32 reported state when online, else desired
        $fan_state = ($is_online && !in_array($row['esp_fan_state'], ['', null]))
            ? strtolower($row['esp_fan_state'])
            : strtolower($row['desired_fan_state']);

        $response['esp_fan_state']   = $fan_state;
        $response['relay']           = $fan_state;
        $response['mode']            = $row['mode'];
        $response['schedule_time']   = $row['schedule_time'];
        $response['schedule_stop_time'] = $row['schedule_stop_time'] ?? null;
        $response['config_version']     = (int)$row['config_version'];
        $response['ack_config_version'] = (int)$row['ack_config_version'];
        $response['message']         = 'Blower system ' . ($is_online ? 'operational' : 'unreachable');
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
