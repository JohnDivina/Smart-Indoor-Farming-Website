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

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/clsu-official-logo.png" />
  <link rel="stylesheet" href="../CSS/main.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
  <title>Dashboard</title>
  <style>
    .switch {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 34px;
    }

    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: .4s;
      border-radius: 34px;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 26px;
      width: 26px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }

    input:checked + .slider {
      background-color: #2196F3;
    }

    input:checked + .slider:before {
      transform: translateX(26px);
    }
  </style>
</head>

<body>
  <!-- Solar Panel -->
  <div class="card col" style="cursor: pointer;">
    <div class="card-body">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g fill="none">
          <path d="M24 0v24H0V0zM12.594 23.258l-.012.002l-.071.035l-.02.004l-.014-.004l-.071-.036c-.01-.003-.019 0-.024.006l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427c-.002-.01-.009-.017-.016-.018m.264-.113l-.014.002l-.184.093l-.01.01l-.003.011l.018.43l.005.012l.008.008l.201.092c.012.004.023 0 .029-.008l.004-.014l-.034-.614c-.003-.012-.01-.02-.02-.022m-.715.002a.023.023 0 0 0-.027.006l-.006.014l-.034.614c0 .012.007.02.017.024l.015-.002l.201-.093l.01-.008l.003-.011l.018-.43l-.003-.012l-.01-.01z" />
          <path fill="currentColor" d="M10.105 4h3.79l.5 5h-4.79zm-.7 7h5.19l.5 5H13v2h2a1 1 0 1 1 0 2H9a1 1 0 1 1 0-2h2v-2H8.905zm7.2 0l.5 5h3.285a1.5 1.5 0 0 0 1.471-1.794L21.22 11zm-.7-7l.5 5h4.415l-.679-3.392A2 2 0 0 0 18.181 4zm-7.81 0H5.82a2 2 0 0 0-1.961 1.608L3.18 9h4.415zm-.7 7H2.78l-.641 3.206A1.5 1.5 0 0 0 3.609 16h3.286z" />
        </g>
      </svg>
      <p>Solar Panel</p>

      <p>Relay Status: <b id="relay-status"><?php echo $status; ?></b></p>
      <label class="switch">
        <input type="checkbox" id="relay-toggle" <?php echo $status == 'on' ? 'checked' : ''; ?>>
        <span class="slider"></span>
      </label>
    </div>
  </div>

  <script>
    const savedState = localStorage.getItem('relayState');
    if (savedState) {
      document.getElementById('relay-toggle').checked = savedState === 'on';
    }

    document.getElementById('relay-toggle').addEventListener('change', function() {
      const relayState = this.checked ? 'on' : 'off';
      localStorage.setItem('relayState', relayState); 
      window.location.href = `?relay=${relayState}`;
    });

    function fetchRelayStatus() {
      fetch('status.php') 
        .then(response => response.text())
        .then(data => {
          document.getElementById('relay-status').innerHTML = data; 
          document.getElementById('relay-toggle').checked = data === 'on';
          localStorage.setItem('relayState', data); 
        })
        .catch(error => console.error('Error fetching status:', error));
    }

    setInterval(fetchRelayStatus, 60000); 
  </script>
</body>

</html>