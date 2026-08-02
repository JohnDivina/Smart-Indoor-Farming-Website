<?php
header("Content-Type: application/json");

include '../database.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success"=>false, "msg"=>"No JSON"]);
    exit;
}

$mode      = $data["mode"] ?? "";
$moisture  = $data["moisture"] ?? null;
$ssr       = $data["ssr"] ?? null;

if (!in_array($mode, ["manual","auto","timer"])) {
    http_response_code(400);
    echo json_encode(["success"=>false, "msg"=>"Invalid mode"]);
    exit;
}

// 1. Update current mode
$stmt = $conn->prepare("UPDATE system_state SET fertigation_mode=? WHERE id=1");
$stmt->bind_param("s", $mode);
$stmt->execute();

// 2. Store latest reading + ssr state
$stmt = $conn->prepare("
    INSERT INTO fertigation_log 
    (timestamp, moisture, ssr_state) 
    VALUES (NOW(), ?, ?)
");
$stmt->bind_param("di", $moisture, $ssr);
$stmt->execute();

echo json_encode(["success"=>true]);
?>
