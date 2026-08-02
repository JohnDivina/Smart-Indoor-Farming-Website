<?php
/*
 * Fan Status Endpoint — used by auxiliary-fan.php browser frontend
 * Session-protected, reads fan_state using real schema.
 */
header('Content-Type: application/json');
require_once '../../database.php';

$response = [
    'success'            => false,
    'esp_fan_state'      => 'off',
    'mode'               => 'manual',
    'schedule_time'      => null,
    'schedule_stop_time' => null,
    'esp_online'         => false,
    'message'            => ''
];

try {
    $result = $conn->query("SELECT * FROM fan_state WHERE id = 1");

    if ($row = $result->fetch_assoc()) {
        $response['success'] = true;

        // Online if heartbeat within last 60 seconds
        $last_hb   = !empty($row['last_heartbeat']) ? strtotime($row['last_heartbeat']) : 0;
        $now       = time();
        $is_online = ($last_hb > 0) && ($now - $last_hb) <= 60;
        $response['esp_online'] = $is_online;

        // Show actual when online, desired when offline
        $response['esp_fan_state'] = ($is_online && !in_array($row['esp_fan_state'], ['', null]))
            ? strtolower($row['esp_fan_state'])
            : strtolower($row['desired_fan_state']);

        $response['mode']            = $row['mode'];
        $response['schedule_time']   = $row['schedule_time'];
        $response['schedule_stop_time'] = $row['schedule_stop_time'] ?? null;
    } else {
        $response['message'] = 'State record not found';
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
