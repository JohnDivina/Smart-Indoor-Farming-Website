<?php
require_once '../database.php';
require_once __DIR__ . '/../includes/esp32_auth.php';
verify_esp32_auth();

date_default_timezone_set("Asia/Manila");

// Handle POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Handle live sensor data update
    if (isset($_POST['live'])) {
        $sensor1 = isset($_POST['sensor1']) ? intval($_POST['sensor1']) : null;
        $sensor2 = isset($_POST['sensor2']) ? intval($_POST['sensor2']) : null;
        $sensor3 = isset($_POST['sensor3']) ? intval($_POST['sensor3']) : null;
        $sensor4 = isset($_POST['sensor4']) ? intval($_POST['sensor4']) : null;
        $timestamp = date("Y-m-d H:i:s");

        if ($sensor1 === null || $sensor2 === null || $sensor3 === null || $sensor4 === null) {
            http_response_code(400);
            echo "Missing sensor values.";
            $conn->close();
            exit();
        }

        // Check if row exists
        $result = $conn->query("SELECT id FROM live_light_readings LIMIT 1");

        if ($result && $result->num_rows > 0) {
            // Update existing row
            $stmt = $conn->prepare(
                "UPDATE live_light_readings SET sensor1 = ?, sensor2 = ?, sensor3 = ?, sensor4 = ?, timestamp = ? WHERE id = (SELECT id FROM (SELECT id FROM live_light_readings LIMIT 1) AS temp)"
            );
        } else {
            // Insert new row
            $stmt = $conn->prepare(
                "INSERT INTO live_light_readings (sensor1, sensor2, sensor3, sensor4, timestamp) VALUES (?, ?, ?, ?, ?)"
            );
        }

        if (!$stmt) {
            http_response_code(500);
            echo "Prepare failed: " . $conn->error;
            $conn->close();
            exit();
        }

        $stmt->bind_param("dddds", $sensor1, $sensor2, $sensor3, $sensor4, $timestamp);

        if ($stmt->execute()) {
            echo "Live data processed.";
        } else {
            http_response_code(500);
            echo "Failed to process live data: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
        exit();
    }

    // Handle hourly average data
    if (isset($_POST['average'])) {
        $average = floatval($_POST['average']);
        $stmt = $conn->prepare("INSERT INTO lightintensitysensor (hourlyAverage, timestamp) VALUES (?, NOW())");
        $stmt->bind_param("d", $average);

        if ($stmt->execute()) {
            echo "Hourly average recorded";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
        exit();
    }

    // Handle heartbeat ping
    if (isset($_POST['heartbeat'])) {
        $sql = "INSERT INTO light_status (sensor_id, last_heartbeat) VALUES (1, NOW()) ON DUPLICATE KEY UPDATE last_heartbeat = NOW()";
        if ($conn->query($sql) === TRUE) {
            echo "Heartbeat received";
        } else {
            echo "Error: " . $conn->error;
        }

        $conn->close();
        exit();
    }
}

// Handle GET Requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');

    // Get live sensor data
    if (isset($_GET['live']) && $_GET['live'] === 'true') {
        $stmt = $conn->prepare("SELECT sensor1, sensor2, sensor3, sensor4, timestamp FROM live_light_readings ORDER BY timestamp DESC LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode([
                "sensor1" => $row['sensor1'] !== null ? floatval($row['sensor1']) : null,
                "sensor2" => $row['sensor2'] !== null ? floatval($row['sensor2']) : null,
                "sensor3" => $row['sensor3'] !== null ? floatval($row['sensor3']) : null,
                "sensor4" => $row['sensor4'] !== null ? floatval($row['sensor4']) : null,
                "timestamp" => date("F-d-Y h:i A", strtotime($row['timestamp'])),
                "status" => "connected"
            ]);
        } else {
            echo json_encode([
                "sensor1" => null,
                "sensor2" => null,
                "sensor3" => null,
                "sensor4" => null,
                "timestamp" => null,
                "status" => "no data"
            ]);
        }

        $stmt->close();
        $conn->close();
        exit();
    }

    // Get heartbeat status
    if (isset($_GET['heartbeat_status']) && $_GET['heartbeat_status'] === 'true') {
        $sql = "SELECT last_heartbeat FROM light_status WHERE sensor_id = 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lastHeartbeat = strtotime($row['last_heartbeat']);
            $formattedHeartbeat = date("F j, Y h:i:s A", $lastHeartbeat);
            $currentTime = time();

            $status = ($currentTime - $lastHeartbeat > 60) ? "disconnected" : "connected";

            echo json_encode([
                "status" => $status,
                "lastHeartbeat" => $formattedHeartbeat
            ]);
        } else {
            echo json_encode([
                "status" => "disconnected",
                "lastHeartbeat" => "N/A"
            ]);
        }

        $conn->close();
        exit();
    }

    // If no recognized GET param
    echo json_encode(["error" => "Invalid or missing GET parameter."]);
    $conn->close();
    exit();
}
?>
