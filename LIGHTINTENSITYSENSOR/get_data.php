<?php
require_once '../database.php';

date_default_timezone_set("Asia/Manila");

// Prevent caching so the latest row is always returned
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Fetch only the latest hourly average row
$avgSql = "SELECT hourlyAverage, timestamp FROM lightintensitysensor ORDER BY timestamp DESC LIMIT 1";
$avgResult = $conn->query($avgSql);

// Fetch the latest live reading timestamp (if available)
$liveSql = "SELECT timestamp FROM live_light_readings ORDER BY timestamp DESC LIMIT 1";
$liveResult = $conn->query($liveSql);

// Determine connection status directly from DB heartbeat (no HTTP self-call)
$hbSql = "SELECT last_heartbeat FROM light_status WHERE sensor_id = 1";
$hbResult = $conn->query($hbSql);

$status = "disconnected";
$lastHeartbeat = "N/A";

if ($hbResult && $hbResult->num_rows > 0) {
    $hbRow = $hbResult->fetch_assoc();
    $lastHbTs = strtotime($hbRow['last_heartbeat']);
    $age = time() - $lastHbTs;
    $status = ($age <= 60) ? "connected" : "disconnected";
    $lastHeartbeat = date("F j, Y h:i:s A", $lastHbTs);
}

$data = [
    'status'           => $status,
    'lastHeartbeat'    => $lastHeartbeat,
    'latestData'       => null,
    'lastLiveTimestamp'=> null
];

if ($avgResult && $avgResult->num_rows > 0) {
    $row = $avgResult->fetch_assoc();
    $data['latestData'] = [
        'hourlyAverage' => $row["hourlyAverage"],
        'timestamp'     => date("F j, Y h:i A", strtotime($row["timestamp"]))
    ];
}

if ($liveResult && $liveResult->num_rows > 0) {
    $liveRow = $liveResult->fetch_assoc();
    $data['lastLiveTimestamp'] = date("F j, Y h:i A", strtotime($liveRow["timestamp"]));
}

$conn->close();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: application/json');
echo json_encode($data);
?>