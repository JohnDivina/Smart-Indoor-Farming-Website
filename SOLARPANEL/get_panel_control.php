<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/esp32_auth.php';
verify_esp32_auth();
require_once __DIR__ . '/../database.php';

$response = [
    'success' => false,
    'message' => 'Unknown error'
];

try {
    // Read control
    $res = $conn->query("SELECT * FROM solar_panel_control WHERE id = 1");
    $control = $res ? $res->fetch_assoc() : null;
    
    // Read status
    $res2 = $conn->query("SELECT * FROM solar_panel_status WHERE id = 1");
    $status = $res2 ? $res2->fetch_assoc() : null;
    
    if ($control && $status) {
        $response = [
            'success' => true,
            'mode' => $control['mode'],
            'desired_state' => (int)$control['desired_state'],
            'actual_state' => (int)$status['actual_state'],
            'pushed' => (int)$status['pushed'],
            'pulled' => (int)$status['pulled'],
            'open_time' => $control['open_time'] ? date('H:i', strtotime($control['open_time'])) : "",
            'fold_time' => $control['fold_time'] ? date('H:i', strtotime($control['fold_time'])) : "",
            'config_version' => (int)$control['config_version'],
            'server_time' => date('Y-m-d H:i:s')
        ];
    } else {
        $response['message'] = 'Database error: Data not found';
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
