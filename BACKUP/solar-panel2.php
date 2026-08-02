<?php
// solar-panel.php

date_default_timezone_set("Asia/Manila");

$esp32_ip = "http://192.168.0.116";
$time = date("Y-m-d H:i:s");
$status = 'disconnected';
$relay = 'unknown';
$buttonDisabled = 'disabled';

// Handle relay toggle requests (?relay=on or ?relay=off)
if (isset($_GET['relay'])) {
    $relayState = $_GET['relay'];
    if (in_array($relayState, ['on', 'off'])) {
        $endpoint = "$esp32_ip/relay/$relayState";
        $result = @file_get_contents($endpoint);
        if ($result !== false) {
            $relay = $relayState;
        }
    }
}

// Fetch current ESP32 status
try {
    $relayRaw = @file_get_contents("$esp32_ip/status");
    if ($relayRaw !== false) {
        $status = 'connected';
        $buttonDisabled = ''; // Enable buttons when connected

        $relayRaw = trim($relayRaw);
        // NOTE: swapped meaning
        if (stripos($relayRaw, 'off') !== false) {
            $relay = 'off';  // panels OPENED
        } elseif (stripos($relayRaw, 'on') !== false) {
            $relay = 'on';   // panels CLOSED
        } else {
            $relay = 'unknown';
        }
    }
} catch (Exception $e) {
    $status = 'disconnected';
    $relay = 'unknown';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Solar Panel Control</title>

  <!-- Bootstrap CSS and Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/solar-panel.css" />

  <style>
    .status span:first-child {
      font-weight: bold;
    }

    .status.connected span:first-child {
      color: green;
    }

    .status.disconnected span:first-child {
      color: red;
    }
    .status .panel-status-unknown {
      color: gray;
    }

    .button-container button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  </style>
</head>

<body>

  <!-- Header -->
  <div class="header">
    <i class="bi bi-arrow-left" id="backButton" style="cursor:pointer;"></i> SOLAR PANEL
  </div>

  <!-- Banner and Panels -->
  <div class="container-fluid position-relative p-0">
    <img
      src="https://gospringsolarnow.com/storage/2021/02/Main-Purpose-Of-Solar-Panels-scaled-2560x1280.jpeg"
      alt="Background"
      class="background w-100"
      style="object-fit: cover; height: 300px;"
    />

    <div id="panelCard" class="panel-card <?= $relay === 'off' ? 'open' : 'close' ?>">
      <?php for ($i = 0; $i < 6; $i++): ?>
        <img src="assets/solar_panel.png" alt="Solar Panel" class="panel" />
      <?php endfor; ?>
    </div>
  </div>

  <!-- Status -->
  <div class="status <?= $status; ?> mt-3 text-center">
    Status: <span><?= ucfirst($status); ?></span><br />
    <?php
    $panelText = $relay === 'off' ? 'Opened' : ($relay === 'on' ? 'Closed' : 'Unknown');
    $panelClass = ($relay === 'unknown') ? 'panel-status-unknown' : '';
    ?>
    Panels are <span id="panelStatus" class="<?= $panelClass ?>"><?= $panelText ?></span>
  </div>

  <!-- Buttons -->
  <div class="button-container d-flex justify-content-center gap-3 mt-3">
    <button class="btn btn-success" id="openButton" onclick="openPanels()" <?= $buttonDisabled ?>>Open</button>
    <button class="btn btn-danger" id="closeButton" onclick="closePanels()" <?= $buttonDisabled ?>>Close</button>
  </div>

  <!-- Footer -->
  <div class="footer mt-4 text-center text-muted">
    <i class="bi bi-check-circle-fill"></i>
    <span id="footerText"><?= $status === 'connected' ? 'System is operational.' : 'Connection not established.' ?></span>
  </div>

  <!-- JavaScript -->
  <script>
    const panelStatus = document.getElementById('panelStatus');
    const panelCard = document.getElementById('panelCard');

    function openPanels() {
      panelCard.classList.add('open');
      panelCard.classList.remove('close');
      panelStatus.innerText = 'Opening...';

      setTimeout(() => {
        panelStatus.innerText = 'Opened';
      }, 120000); // 2 minutes

      // Send relay=off to OPEN panels
      window.location.href = "?relay=off";
    }

    function closePanels() {
      panelCard.classList.add('close');
      panelCard.classList.remove('open');
      panelStatus.innerText = 'Closing...';

      setTimeout(() => {
        panelStatus.innerText = 'Closed';
      }, 120000); // 2 minutes

      // Send relay=on to CLOSE panels
      window.location.href = "?relay=on";
    }

    document.getElementById('backButton').addEventListener('click', function () {
      window.location.href = 'index.php';
    });
  </script>

  <script>
    const connectionStatus = "<?= $status; ?>";
    const footerText = document.getElementById('footerText');

    if (connectionStatus === 'connected') {
      footerText.innerText = 'System is operational.';
    } else {
      footerText.innerText = 'Connection is not established.';
    }
  </script>

</body>
</html>
