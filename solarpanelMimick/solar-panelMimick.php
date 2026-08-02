<?php
// Simulated Solar Panel
date_default_timezone_set("Asia/Manila");

// Initial states
$status = 'disconnected';
$relay = 'unknown';
$panelMessage = 'Connection not established.';
$panelText = 'Unknown';
$panelClass = 'panel-status-unknown';
$buttonDisabled = 'disabled';

if (isset($_GET['relay'])) {
    $relayState = $_GET['relay'];
    if (in_array($relayState, ['on', 'off'])) {
        $status = 'connected';
        $relay = $relayState;
        $buttonDisabled = '';
        $panelText = $relay === 'on' ? 'Opened' : 'Closed';
        $panelMessage = 'System is operational.';
        $panelClass = '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Solar Panel Control</title>

  <!-- Bootstrap CSS and Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="solarCSS.css">

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

.panel {
  transition: transform 5s ease-in-out;
}

/* OPEN: rightmost panel moves first, next panels start every 2 seconds */
.panel-card.open .panel:nth-child(6) {
  transform: translateX(500%);
  transition-delay: 0s;
}
.panel-card.open .panel:nth-child(5) {
  transform: translateX(400%);
  transition-delay: 2s;
}
.panel-card.open .panel:nth-child(4) {
  transform: translateX(300%);
  transition-delay: 4s;
}
.panel-card.open .panel:nth-child(3) {
  transform: translateX(200%);
  transition-delay: 6s;
}
.panel-card.open .panel:nth-child(2) {
  transform: translateX(100%);
  transition-delay: 8s;
}
.panel-card.open .panel:nth-child(1) {
  transform: translateX(30%);
  transition-delay: 10s;
}

/* CLOSE: leftmost panel moves first, next panels start every 2 seconds */
.panel-card.close .panel:nth-child(1) {
  transform: translateX(0%);
  transition-delay: 0s;
}
.panel-card.close .panel:nth-child(2) {
  transform: translateX(0%);
  transition-delay: 2s;
}
.panel-card.close .panel:nth-child(3) {
  transform: translateX(0%);
  transition-delay: 4s;
}
.panel-card.close .panel:nth-child(4) {
  transform: translateX(0%);
  transition-delay: 6s;
}
.panel-card.close .panel:nth-child(5) {
  transform: translateX(0%);
  transition-delay: 8s;
}
.panel-card.close .panel:nth-child(6) {
  transform: translateX(0%);
  transition-delay: 10s;
}

  </style>
</head>

<body>

<div class="header">
  <i class="bi bi-arrow-left" id="backButton"></i> SOLAR PANEL
</div>

<div class="container-fluid">
  <img src="https://gospringsolarnow.com/storage/2021/02/Main-Purpose-Of-Solar-Panels-scaled-2560x1280.jpeg" alt="Background" class="background">
  <div id="panelCard" class="panel-card <?= $relay === 'on' ? 'open' : 'close'; ?>">
    <?php for ($i = 0; $i < 6; $i++): ?>
      <img src="/smartfarm/assets/solar_panel.png" alt="Solar Panel" class="panel">
    <?php endfor; ?>
  </div>
</div>

<div class="status <?= $status; ?>" id="connectionStatus">
  Status: <span id="statusText"><?= ucfirst($status); ?></span><br />
  Panels are <span id="panelStatus" class="<?= $panelClass ?>"><?= $panelText ?></span>
</div>

<div class="button-container">
  <button class="btn btn-success" id="openButton" onclick="openPanels()" <?= $buttonDisabled ?>>Open</button>
  <button class="btn btn-danger" id="closeButton" onclick="closePanels()" <?= $buttonDisabled ?>>Close</button>
</div>

<div class="footer" id="footerMessage">
  <i class="bi bi-check-circle-fill"></i>
  <span id="footerText"><?= $panelMessage ?></span>
</div>

<!-- JavaScript -->
<script>
const statusText = document.getElementById('statusText');
const panelStatus = document.getElementById('panelStatus');
const footerText = document.getElementById('footerText');
const panelCard = document.getElementById('panelCard');
const openButton = document.getElementById('openButton');
const closeButton = document.getElementById('closeButton');

// Simulate status change from disconnected to connected after 5 seconds
if (statusText.innerText.toLowerCase() === 'disconnected') {
  setTimeout(() => {
    statusText.innerText = 'Connected';
    document.getElementById('connectionStatus').classList.remove('disconnected');
    document.getElementById('connectionStatus').classList.add('connected');
    footerText.innerText = 'System is operational';
    openButton.disabled = false;
    closeButton.disabled = false;
    panelStatus.innerText = 'Close';
    panelStatus.className = 'panel-status-unknown';
  }, 1000);
}

// Open Panels Function
function openPanels() {
  panelCard.classList.add('open');
  panelCard.classList.remove('close');
  panelStatus.innerText = 'Opening Panels...';

  setTimeout(() => {
    panelStatus.innerText = 'Opened';
    window.location.href = "?relay=on";
  }, 15000); // 30 seconds
}

function closePanels() {
  panelCard.classList.remove('open');
  panelCard.classList.add('close');
  panelStatus.innerText = 'Closing Panels...';
  panelStatus.style.color = 'gray'; // Set color to gray

  setTimeout(() => {
    panelStatus.innerText = 'Closed';
    panelStatus.style.color = ''; // Reset color to default
    window.location.href = "?relay=off";
  }, 15000); // 15 seconds
}

// Back Button Navigation
document.getElementById('backButton').addEventListener('click', function () {
  window.location.href = 'index.php';
});
</script>

</body>
</html>
