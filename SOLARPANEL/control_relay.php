<?php
header("Content-Type: application/json");

$esp32_ip = "http://192.168.0.102"; // ESP32 IP

$response = [
    "success" => false,
    "message" => "",
];

if (isset($_GET['state'])) {
    $relay_state = $_GET['state'];

    if ($relay_state === "on" || $relay_state === "off") {
        $endpoint = "$esp32_ip/relay/" . $relay_state;

        $result = @file_get_contents($endpoint);
        if ($result !== false) {
            $response["success"] = true;
            $response["message"] = trim($result);
        } else {
            $response["message"] = "Failed to reach ESP32.";
        }
    } else {
        $response["message"] = "Invalid state value.";
    }
} else {
    $response["message"] = "Missing 'state' parameter.";
}

echo json_encode($response);
?>
