<?php
require_once '../database.php';
require_once __DIR__ . '/../includes/esp32_auth.php';
verify_esp32_auth();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $temperature = isset($_POST['temperature']) ? floatval($_POST['temperature']) : null;
    $humidity = isset($_POST['humidity']) ? floatval($_POST['humidity']) : null;

    if ($temperature === null || $humidity === null) {
        http_response_code(400);
        echo "Missing temperature or humidity value.";
        $conn->close();
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO temphumiditysensor (temperature, humidity, timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param("dd", $temperature, $humidity);

    if ($stmt->execute()) {
        echo "Data saved successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    http_response_code(405);
    echo "Only POST requests are allowed.";
    $conn->close();
    exit();
}
?>
