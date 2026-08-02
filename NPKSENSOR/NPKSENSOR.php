<?php
require_once '../database.php';
require_once __DIR__ . '/../includes/esp32_auth.php';
verify_esp32_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle regular sensor data
    if (isset($_POST['temp']) && isset($_POST['moist']) && isset($_POST['ph']) && isset($_POST['ec']) && isset($_POST['n']) && isset($_POST['p']) && isset($_POST['k'])) {
        $temp = floatval($_POST['temp']); // Ensure data is treated as float
        $moist = floatval($_POST['moist']);
        $ph = floatval($_POST['ph']);
        $ec = floatval($_POST['ec']);
        $n = floatval($_POST['n']);
        $p = floatval($_POST['p']);
        $k = floatval($_POST['k']);
        $timestamp = date("Y-m-d H:i:s"); // Get the current timestamp

        // Insert or update the latest sensor data
        $check_sql = "SELECT id FROM npksensor ORDER BY id DESC LIMIT 1";
        $result = $conn->query($check_sql);

        if ($result->num_rows > 0) {
            // Data exists, update it
            $row = $result->fetch_assoc();
            $id = $row['id'];
            $update_sql = "UPDATE npksensor SET temp = $temp, moist = $moist, ph = $ph, ec = $ec, n = $n, p = $p, k = $k, timestamp = '$timestamp' WHERE id = $id";
            if ($conn->query($update_sql) === FALSE) {
                echo "Error: " . $update_sql . "<br>" . $conn->error;
            }
        } else {
            // No data exists, insert new data
            $insert_sql = "INSERT INTO npksensor (temp, moist, ph, ec, n, p, k, timestamp) VALUES ($temp, $moist, $ph, $ec, $n, $p, $k, '$timestamp')";
            if ($conn->query($insert_sql) === FALSE) {
                echo "Error: " . $insert_sql . "<br>" . $conn->error;
            }
        }

        // Record heartbeat on data receipt to reflect live connection
        $hb_sql = "INSERT INTO npk_status (sensor_id, last_heartbeat) VALUES (1, NOW()) ON DUPLICATE KEY UPDATE last_heartbeat = NOW()";
        if ($conn->query($hb_sql) === FALSE) {
            // Optional: log error but don't break flow
        }

        // Check if the sensor was previously disconnected
        $status_sql = "SELECT last_heartbeat, reconnection_time, last_average_insert_time FROM npk_status WHERE sensor_id = 1";
        $status_result = $conn->query($status_sql);

        if ($status_result->num_rows > 0) {
            $status_row = $status_result->fetch_assoc();
            $last_heartbeat = strtotime($status_row['last_heartbeat']);
            $current_time = time();

            // If the sensor was disconnected (last heartbeat > 60 seconds ago)
            if ($current_time - $last_heartbeat > 60) {
                // Sensor was disconnected, so set the reconnection time
                $reconnection_time = $current_time;

                // Store the reconnection time in the database (for persistence across script runs)
                $update_reconnection_sql = "UPDATE npk_status SET reconnection_time = FROM_UNIXTIME($reconnection_time) WHERE sensor_id = 1";
                if ($conn->query($update_reconnection_sql) === FALSE) {
                    echo "Error: " . $update_reconnection_sql . "<br>" . $conn->error;
                }

                // Reset the last_average_insert_time to the reconnection time
                $update_last_average_sql = "UPDATE npk_status SET last_average_insert_time = FROM_UNIXTIME($reconnection_time) WHERE sensor_id = 1";
                if ($conn->query($update_last_average_sql) === FALSE) {
                    echo "Error: " . $update_last_average_sql . "<br>" . $conn->error;
                }
            } else {
                // Sensor was connected, fetch the reconnection time and last average insert time
                $reconnection_time = strtotime($status_row['reconnection_time']);
                $last_average_insert_time = strtotime($status_row['last_average_insert_time']);

                // Calculate the next insert time (6 hours after the last average insert time)
                $next_insert_time = $last_average_insert_time + 3600; // 21600 seconds = 6 hours

                // If the current time is greater than or equal to the next insert time
                if ($current_time >= $next_insert_time) {
                    // Insert the current sensor data into npksensoraverage
                    $insert_average_sql = "INSERT INTO npksensoraverage (temp, moist, ph, ec, n, p, k, timestamp) VALUES ($temp, $moist, $ph, $ec, $n, $p, $k, '$timestamp')";
                    if ($conn->query($insert_average_sql) === FALSE) {
                        echo "Error: " . $insert_average_sql . "<br>" . $conn->error;
                    } else {
                        // Update the last_average_insert_time in npk_status
                        $update_last_average_sql = "UPDATE npk_status SET last_average_insert_time = '$timestamp' WHERE sensor_id = 1";
                        if ($conn->query($update_last_average_sql) === FALSE) {
                            echo "Error: " . $update_last_average_sql . "<br>" . $conn->error;
                        }
                    }
                }
            }
        }
    }
    // Handle heartbeat
    elseif (isset($_POST['heartbeat'])) {
        $sql = "INSERT INTO npk_status (sensor_id, last_heartbeat) VALUES (1, NOW()) ON DUPLICATE KEY UPDATE last_heartbeat = NOW()";
        if ($conn->query($sql) === FALSE) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    // Handle connection status
    elseif (isset($_POST['status'])) {
        $status = $_POST['status'];
        if ($status === "disconnected") {
            // Set all sensor data to 0 in the npksensor table
            $update_sql = "UPDATE npksensor SET temp = 0, moist = 0, ph = 0, ec = 0, n = 0, p = 0, k = 0, timestamp = NOW() WHERE id = (SELECT id FROM npksensor ORDER BY id DESC LIMIT 1)";
            if ($conn->query($update_sql) === FALSE) {
                echo "Error: " . $update_sql . "<br>" . $conn->error;
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['heartbeat_status']) && $_GET['heartbeat_status'] === 'true') {
        $sql = "SELECT last_heartbeat FROM npk_status WHERE sensor_id = 1";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lastHeartbeat = strtotime($row['last_heartbeat']);
            $formattedHeartbeat = date("F j, Y h:i:s A", $lastHeartbeat);
            $currentTime = time();

            if ($currentTime - $lastHeartbeat > 60) {
                $status = "disconnected";

                // Set all sensor data to 0
                $update_sql = "UPDATE npksensor SET temp = 0, moist = 0, ph = 0, ec = 0, n = 0, p = 0, k = 0, timestamp = NOW() WHERE id = (SELECT id FROM npksensor ORDER BY id DESC LIMIT 1)";
                $conn->query($update_sql); // You may choose to handle errors here
            } else {
                $status = "connected";
            }

            echo json_encode(["status" => $status, "lastHeartbeat" => $formattedHeartbeat]);
        } else {
            echo json_encode(["status" => "disconnected", "lastHeartbeat" => "N/A"]);
        }
    }
}

$conn->close();
?>
