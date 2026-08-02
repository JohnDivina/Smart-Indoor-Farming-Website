<?php

$esp32_ip = "http://192.168.0.102"; // Replace with your ESP32 IP

if (isset($_GET['relay'])) {
  $relay_state = $_GET['relay'];

  if ($relay_state == "on") {
      file_get_contents($esp32_ip . "/relay/on");
  } elseif ($relay_state == "off") {
      file_get_contents($esp32_ip . "/relay/off");
  }
}

$status = file_get_contents("$esp32_ip/status"); // Get relay status

?>