<?php
header('Content-Type: application/json');
require_once '../database.php';

date_default_timezone_set("Asia/Manila");

// Fetch all sensor data (used by legacy table views)
$sql = "SELECT temp, moist, ph, ec, n, p, k, timestamp FROM npksensor ORDER BY timestamp DESC";
$result = $conn->query($sql);

// Determine connection status directly from DB heartbeat
$statusSql = "SELECT last_heartbeat FROM npk_status WHERE sensor_id = 1";
$statusResult = $conn->query($statusSql);

$status = "disconnected";
$lastHeartbeat = "N/A";

if ($statusResult && $statusResult->num_rows > 0) {
    $statusRow = $statusResult->fetch_assoc();
    $lastHbTs = strtotime($statusRow['last_heartbeat']);
    $age = time() - $lastHbTs;
    $status = ($age <= 60) ? "connected" : "disconnected";
    $lastHeartbeat = date("F j, Y h:i:s A", $lastHbTs);
}

$data = [
    'status' => $status,
    'lastHeartbeat' => $lastHeartbeat,
    'tableData' => []
];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data['tableData'][] = [
            'temp'      => $row["temp"],
            'moist'     => $row["moist"],
            'ph'        => $row["ph"],
            'ec'        => $row["ec"],
            'n'         => $row["n"],
            'p'         => $row["p"],
            'k'         => $row["k"],
            'timestamp' => date("F j, Y h:i A", strtotime($row["timestamp"]))
        ];
    }
}

$conn->close();
echo json_encode($data);
?>