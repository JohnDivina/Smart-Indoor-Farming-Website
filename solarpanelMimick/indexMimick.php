<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Smart Indoor Farming Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/index.css">
</head>
  <style>
  .notification-dropdown-space {
    width: 100vw;
    left: 50%;
    right: 0;
    margin-left: -50vw;
    position: relative;
    height: 0;
    transition: height 0.3s;
    z-index: 1000;
    background: transparent;
  }
  .notification-dropdown-space.open {
    height: 380px; /* or more if you want */
  }
  .notification-card {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    margin: 0;
    background-color: white;
    border-radius: 0;
    width: 100vw;
    min-width: unset;
    max-width: unset;
    min-height: 350px;
    z-index: 1001;
    border: none;
    display: none;
    box-shadow: 0 8px 22px rgba(0,0,0,0.10);
    padding: 2rem 4vw;
  }
  .notification-card.open {
    display: block;
  }
  .notification-card h6 {
    margin-bottom: 18px;
    font-size: 1.4rem;
  }
  .notification-list p {
    background: rgba(0, 128, 0, 0.7); /* 50% green */
    color: #fff;
    border-radius: 10px;
    padding: 12px 18px;
    margin-bottom: 1.2rem;
    font-size: 1.1rem;
    box-shadow: 0 6px 18px rgba(0,0,0,0.18), 0 2px 6px rgba(0,128,0,0.15);
    transition: transform 0.15s cubic-bezier(.4,2,.6,1), box-shadow 0.15s;
    transform: translateY(0);
    font-weight: 500;
    letter-spacing: 0.02em;
  }
  .notification-list p:hover {
    transform: translateY(-4px) scale(1.03);
    box-shadow: 0 12px 24px rgba(0,0,0,0.22), 0 4px 12px rgba(0,128,0,0.18);
  }

  .notification-icon .badge {
    border-radius: 50%;
    min-width: 22px;
    min-height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
  }
  
  /* Logout Modal Custom Theme */
  #logoutModal .modal-content {
    border-radius: 16px;
    border: 2px solid #1e6031;
    background: #fff;
  }
  #logoutModal .modal-header {
    background: #1e6031;
    color: #fff;
    border-bottom: none;
    border-radius: 16px 16px 0 0;
  }
  #logoutModal .modal-title {
    color: #fff;
    font-weight: 600;
  }
  #logoutModal .modal-body {
    color: #1e6031;
    font-size: 1.1rem;
    font-weight: 500;
    background: #fff;
  }
  #logoutModal .modal-footer {
    border-top: none;
    background: #fff;
    border-radius: 0 0 16px 16px;
  }
  #logoutModal .btn-secondary {
    background: #e9ecef;
    color: #1e6031;
    border: none;
  }
  #logoutModal .btn-secondary:hover {
    background: #d1d5db;
    color: #009639;
  }
  #logoutModal .btn-danger {
    background: #009639;
    color: #fff;
    border: none;
  }
  #logoutModal .btn-danger:hover {
    background: #1e6031;
    color: #fff;
  }
  #logoutModal .btn-close {
    filter: invert(1);
  }
  #logoutBtn {
    color: #fff;
    font-size: 1.5rem;
    transition: color 0.2s;
  }
  #logoutBtn:hover,
  #logoutBtn:focus {
    color: #FFD700 !important;
  }
    </style>
<body>
  <div class="header bg-green-grad">
    <div class="header-logo">
      <img src="assets/clsu-official-logo.png" alt="CLSU Official Logo" />
    </div>
    <div class="header-text">
      <h3>SMART INDOOR FARMING FOR HOT PEPPER AND TOMATO IN THE PHILLIPINES</h3>
      <p>Crops and Resources Research and Development Center (CRRDC)</p>
    </div>
  </div>

    <div class="nav-bar bg-green-grad d-flex justify-content-end align-items-center">
      <div class="nav-links d-flex align-items-center gap-4 position-relative">
        <span id="sensorBtn" class="active" style="cursor: pointer;">SENSORS</span>
        <div style="height: 30px; width: 2px; background-color: white; border-radius: 1px;"></div>
        <span id="dataBtn" style="cursor: pointer;">DATA INSIGHTS</span>

        <span class="notification-icon ms-4 position-relative" title="Notifications" style="cursor: pointer;">
          <i class="bi bi-bell-fill" style="font-size: 1.3rem;"></i>
          <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "smartfarm";

            $conn = new mysqli($servername, $username, $password, $dbname);
            $notifCount = 0;

            if (!$conn->connect_error) {
                // Get data inserted within the last 2 minutes (adjust as needed)
                $sql = "SELECT COUNT(*) as count FROM lightintensitysensor 
                        WHERE hourlyAverage != 0 
                        AND timestamp >= NOW() - INTERVAL 2 MINUTE";
                $result = $conn->query($sql);
                if ($result) {
                    $row = $result->fetch_assoc();
                    $notifCount = (int)$row['count'];
                }
            }

            if ($notifCount > 0) {
                echo '<span class="badge bg-danger position-absolute top-0 start-100 translate-middle" style="font-size:0.8rem;">' . $notifCount . '</span>';
            }
          ?>
        </span>
          <!-- Logout icon button beside notification -->
          <button class="btn btn-link ms-3 p-0" id="logoutBtn" title="Logout" style="color:#fff; font-size:1.5rem;">
            <i class="bi bi-box-arrow-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Notification dropdown space below navbar -->
    <div class="notification-dropdown-space" id="notificationDropdownSpace">
      <div id="notificationContainer" class="notification-card shadow p-3">
        <h6 class="fw-bold mb-2">Notifications</h6>
        <hr class="my-2">
        <div class="notification-list">
          <?php
            // Database connection settings
            $servername = "localhost";
            $username = "root";
            $password = ""; // your DB password
            $dbname = "smartfarm";

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                echo "<p>Database connection failed.</p>";
            } else {
                // Fetch latest non-zero hourlyAverage
                $sql = "SELECT timestamp, hourlyAverage FROM lightintensitysensor WHERE hourlyAverage != 0 ORDER BY timestamp DESC LIMIT 1";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $timestamp = $row['timestamp'];
                        $average = (float) $row['hourlyAverage'];

                        if ($average < 5000) {
                            $condition = "Low Light Alert";
                            $icon = '<i class="bi bi-exclamation-circle-fill" style="color:#ff0000; margin-right:8px;"></i>';
                            $message = "Low light detected. Your plants might not be getting enough sunlight. Please check for shading or consider supplemental lighting.";
                        } elseif ($average >= 5000 && $average < 10000) {
                            $condition = "Low Light";
                            $icon = '<i class="bi bi-cloud-moon-fill" style="color:#b2dfdb; margin-right:8px;"></i>';
                            $message = "Your light intensity sensor recorded an hourly average of <b>" . htmlspecialchars($average) . " Lux</b>, indicating low light conditions.";
                        } elseif ($average >= 10000 && $average < 30000) {
                            $condition = "Medium Light";
                            $icon = '<i class="bi bi-brightness-high-fill" style="color:#ffe082; margin-right:8px;"></i>';
                            $message = "Your light intensity sensor recorded an hourly average of <b>" . htmlspecialchars($average) . " Lux</b>, indicating medium light conditions.";
                        } elseif ($average >= 30000) {
                            $condition = "High Light";
                            $icon = '<i class="bi bi-exclamation-triangle-fill" style="color:#ff5252; margin-right:8px;"></i>';
                            $message = "Your light intensity sensor recorded an hourly average of <b>" . htmlspecialchars($average) . " Lux</b>, indicating high light conditions.";
                        } else {
                            $condition = null;
                            $icon = '';
                            $message = "No new notifications.";
                        }

                        if ($condition) {
                            // Format date and time as "Month Day Year, Hour:Minute AM/PM"
                            $formattedDateTime = date("F j, Y, g:i A", strtotime($timestamp));
                            echo "<p>$icon <b>LIGHT INTENSITY SENSOR</b><br>
                                  On $formattedDateTime, $message</p>";
                        } else {
                            echo "<p>No new notifications</p>";
                        }
                    }
                } else {
                    echo "<p>No new notifications</p>";
                }
            }
          ?>
        </div>
        </div>
      </div>
    </div>

  <div class="date-banner" id="dateBanner"></div>

  <div id="sensorSection" class="active">
    <div class="section-title">SENSORS | Solar · Fan · Light</div>
    <div class="container my-3">
      <div class="row g-4">
        <div class="col-md-4 d-flex">
            <div class="sensor-card w-100" id="solarPanelCard">
                <div class="sensor-icon"><i class="bi bi-brightness-high-fill"></i></div>
                <h3>Solar Panel</h3>
                <div class="sensor-status" id="connection-status">STATUS: CHECKING...</div>
                <div class="sensor-status" id="relay-status">Panels: CHECKING...</div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="relay-toggle" />
                    <label class="form-check-label" for="relay-toggle" id="toggle-label"></label>
                </div>
            </div>
        </div>
        <div class="col-md-4 d-flex">
          <div class="sensor-card w-100" id="fanCard">
            <div class="sensor-icon"><i class="bi bi-fan"></i></div>
            <h3>Auxiliary Fan</h3>
            <div class="sensor-status" style="color: red;">STATUS: RELAY OFF</div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" />
            </div>
          </div>
        </div>
        <div class="col-md-4 d-flex">
          <div class="sensor-card w-100" id="lightCard">
            <div class="sensor-icon"><i class="bi bi-lightbulb-fill"></i></div>
            <h3>Light Sensor</h3>
            <div class="sensor-status">STATUS: CONNECTED</div>
            <div class="sensor-value">
              <h2>20055.5 lux</h2><small>Average Per Hour<br />March 25, 2025 04:28 PM</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="section-title">SENSORS | NPK</div>
    <div class="container my-3 npk-section">
      <div class="row g-4">
        <div class="col-md-4 d-flex">
          <div class="sensor-card w-100" id="npkCard">
            <div class="sensor-icon">
              <img src="assets/npk-icon.png" alt="NPK Icon" />
            </div>
            <h3>NPK Sensor | NPK</h3>
            <div class="sensor-status">STATUS: CONNECTED</div>
            <div class="sensor-value">N: 0 ppm<br />P: 0 ppm<br />K: 0 ppm</div>
          </div>
        </div>
        <div class="col-md-4 d-flex">
          <div class="sensor-card w-100" id="npkCard">
            <div class="sensor-icon">
              <img src="assets/npk-icon.png" alt="NPK Icon" />
            </div>
            <h3>NPK Sensor | TEMP</h3>
            <div class="sensor-status">STATUS: CONNECTED</div>
            <div class="sensor-value">0°C</div>
          </div>
        </div>
        <div class="col-md-4 d-flex">
          <div class="sensor-card w-100" id="npkCard">
            <div class="sensor-icon">
              <img src="assets/npk-icon.png" alt="NPK Icon" />
            </div>
            <h3>NPK Sensor | EC</h3>
            <div class="sensor-status">STATUS: CONNECTED</div>
            <div class="sensor-value">0 µS/cm</div>
          </div>
        </div>
        <div class="col-md-4 d-flex">
          <div class="sensor-card w-100" id="npkCard">
            <div class="sensor-icon">
              <img src="assets/npk-icon.png" alt="NPK Icon" />
            </div>
            <h3>NPK Sensor | MC</h3>
            <div class="sensor-status">STATUS: CONNECTED</div>
            <div class="sensor-value">0°C</div>
          </div>
        </div>
        <div class="col-md-4 d-flex">
          <div class="sensor-card w-100" id="npkCard">
            <div class="sensor-icon">
              <img src="assets/npk-icon.png" alt="NPK Icon" />
            </div>
            <h3>NPK Sensor | PH</h3>
            <div class="sensor-status">STATUS: CONNECTED</div>
            <div class="sensor-value">0°C</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="dataSection">
    <div class="section-title">DATA INSIGHTS | Light Graph</div>
    <div class="reading-section">
        <label for="date">Readings on:</label>
        <input type="date" id="date">
    </div>
    <div class="container my-3">
      <canvas id="luxChart" style="height: 400px; width: 100%;"></canvas>
    </div>
    <div class="section-title">DATA INSIGHTS | NPK Graph</div>
    <div class="reading-section">
        <label for="npkdate">Readings on:</label>
        <input type="date" id="npkdate">
    </div>
    <div class="container my-3">
      <canvas id="sensorChart" style="height: 400px; width: 100%;"></canvas>
    </div>
    <div class="container my-3">
      <button id="downloadCSV" class="btn">Download Data </Button>
    </div>
  </div>


<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to logout?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmLogoutBtn">Logout</button>
      </div>
    </div>
  </div>
</div>



  <!-- NOTIFICATION SCRIPT -->
  <script>
  const notificationIcon = document.querySelector('.notification-icon');
  const notificationContainer = document.getElementById('notificationContainer');
  const notificationDropdownSpace = document.getElementById('notificationDropdownSpace');
  const badge = notificationIcon.querySelector('.badge');
  const notificationList = document.querySelector('.notification-list');

  // Helper: Mark notification as read in localStorage
    function markNotificationRead() {
      localStorage.setItem('notification_read', '1');
      const badge = document.querySelector('.notification-icon .badge');
      if (badge) badge.style.display = 'none';
  }

  // Toggle notification dropdown and mark as read
  notificationIcon.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = notificationDropdownSpace.classList.contains('open');
    if (!isOpen) {
      notificationDropdownSpace.classList.add('open');
      notificationContainer.classList.add('open');

      // Mark as read and hide badge
      markNotificationRead();
    } else {
      notificationDropdownSpace.classList.remove('open');
      notificationContainer.classList.remove('open');
    }
  });

  // Hide dropdown if clicked outside
  document.addEventListener('click', function(event) {
    if (
      !notificationDropdownSpace.contains(event.target) &&
      !notificationIcon.contains(event.target)
    ) {
      notificationDropdownSpace.classList.remove('open');
      notificationContainer.classList.remove('open');
    }
  });

  // Optional: If user clicks on a notification message (e.g., paragraph), mark as read too
  if (notificationList) {
    notificationList.addEventListener('click', function (e) {
      if (e.target.tagName.toLowerCase() === 'p') {
        markNotificationRead();
      }
    });
  }
  </script>

  <script>
    function updateNotificationUI(data) {
      // Check if notifications have been marked as read
      const notificationRead = localStorage.getItem('notification_read') === '1';
      const badge = document.querySelector('.notification-icon .badge');
      if (data.notifCount > 0 && !notificationRead) {
          if (badge) {
              badge.textContent = data.notifCount;
              badge.style.display = '';
          } else {
              const notifIcon = document.querySelector('.notification-icon');
              const newBadge = document.createElement('span');
              newBadge.className = "badge bg-danger position-absolute top-0 start-100 translate-middle";
              newBadge.style.fontSize = "0.8rem";
              newBadge.textContent = data.notifCount;
              notifIcon.appendChild(newBadge);
          }
      } else if (badge) {
          badge.style.display = 'none';
      }

      // Update notification dropdown content
      const notifList = document.querySelector('.notification-list');
      if (notifList) {
          notifList.innerHTML = data.notification;
      }

      // If there are no notifications, clear the read flag
      if (data.notifCount === 0) {
          localStorage.removeItem('notification_read');
      }
  }
  // Poll for new notifications every 5 seconds
  setInterval(() => {
      fetch('notification_status.php')
          .then(res => res.json())
          .then(updateNotificationUI)
          .catch(console.error);
  }, 5000);

  // Initial load
  fetch('notification_status.php')
      .then(res => res.json())
      .then(updateNotificationUI)
      .catch(console.error);
  </script>


<!-- BACKEND -->
<!-- SOLAR PANEL -->
<script>
let isUpdatingRelay = false;
let isConnected = false;

// Initialize UI
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('relay-toggle');
    const connectionStatusElement = document.getElementById('connection-status');
    const relayStatusElement = document.getElementById('relay-status');

    // Initial: Disconnected
    isConnected = false;
    connectionStatusElement.textContent = 'STATUS: DISCONNECTED';
    connectionStatusElement.style.color = 'red';

    relayStatusElement.textContent = 'Panels: UNKNOWN';
    relayStatusElement.style.color = 'gray';

    toggle.disabled = true;

    // Simulate connection after 5 seconds
    setTimeout(() => {
        isConnected = true;
        connectionStatusElement.textContent = 'STATUS: CONNECTED';
        connectionStatusElement.style.color = 'green';

        relayStatusElement.textContent = 'Panels: UNKNOWN';
        relayStatusElement.style.color = 'gray';

        // Enable toggle and restore previous state from localStorage
        toggle.disabled = false;
        const savedRelayState = localStorage.getItem('relayState') || 'off';
        toggle.checked = savedRelayState === 'on';
        updateUI(toggle.checked);
    }, 5000);

    // Toggle behavior
    toggle.addEventListener('change', function () {
        if (!isUpdatingRelay && isConnected) {
            const state = this.checked ? 'on' : 'off';
            updateRelayState(state);
        }
    });
});

function updateRelayState(state) {
    isUpdatingRelay = true;
    const toggle = document.getElementById('relay-toggle');
    toggle.disabled = true;

    // Simulate relay control success after short delay
    setTimeout(() => {
        updateUI(state === 'on');
        localStorage.setItem('relayState', state);
        toggle.disabled = false;
        isUpdatingRelay = false;
    }, 300);
}

function updateUI(isOn) {
    const relayStatusElement = document.getElementById('relay-status');
    const toggle = document.getElementById('relay-toggle');

    if (isOn) {
        relayStatusElement.textContent = 'Panels: OPEN';
        relayStatusElement.style.color = 'green';
        // Add animation logic here if needed
    } else {
        relayStatusElement.textContent = 'Panels: CLOSE';
        relayStatusElement.style.color = 'green';
        // Add animation logic here if needed
    }

    if (toggle.checked !== isOn) {
        toggle.checked = isOn;
    }
}
</script>


<!-- BACKEND -->
<!-- LIGHT INTENSITY SENSOR -->
  <script>
    function updateLightSensorData() {
        fetch('LIGHTINTENSITYSENSOR/get_data.php')
            .then(response => response.json())
            .then(data => {
                const lightSensorCard = document.getElementById('lightCard');
                const averagePerHour = data.latestData ? data.latestData.hourlyAverage : '0';
                const timestamp = data.latestData ? data.latestData.timestamp : '0';
                const status = data.status === 'connected' ? 'Connected' : 'Disconnected';

                if (lightSensorCard) {
                    const sensorValueElement = lightSensorCard.querySelector('.sensor-value');
                    if (sensorValueElement) {
                        sensorValueElement.innerHTML = `<h2>${averagePerHour} lux</h2><small>Average Per Hour<br />${timestamp}</small>`;
                    }

                    const statusElement = lightSensorCard.querySelector('.sensor-status');
                    if (statusElement) {
                        statusElement.textContent = `STATUS: ${status}`;
                        statusElement.style.color = data.status === 'connected' ? 'green' : 'red';
                    }
                }
            })
            .catch(error => console.error('Error fetching light sensor data:', error));
    }

    updateLightSensorData();
    setInterval(updateLightSensorData, 5000);
  </script>



<!-- BACKEND -->
<!-- NPK SENSOR -->
  <script>
    function updateData_npk() {
        fetch('NPKSENSOR/get_data.php')
            .then(response => response.json())
            .then(data => {
                const npkCards = document.querySelectorAll('#npkCard'); // Select all NPK sensor cards

                npkCards.forEach((card, index) => {
                    const statusElement = card.querySelector('.sensor-status');
                    const valueElement = card.querySelector('.sensor-value');
                    const status = data.status === 'connected' ? 'Connected' : 'Disconnected';

                    // Update status
                    if (statusElement) {
                        statusElement.textContent = `STATUS: ${status}`;
                        statusElement.style.color = data.status === 'connected' ? 'green' : 'red';
                    }

                    // Update sensor values based on the card type
                    if (valueElement) {
                        switch (index) {
                            case 0: // NPK Sensor | NPK
                                valueElement.innerHTML = `
                                    N: ${data.sensorData?.n || '0'} ppm<br />
                                    P: ${data.sensorData?.p || '0'} ppm<br />
                                    K: ${data.sensorData?.k || '0'} ppm
                                `;
                                break;
                            case 1: // NPK Sensor | TEMP
                                valueElement.innerHTML = `${data.sensorData?.temp || '0'}°C`;
                                break;
                            case 2: // NPK Sensor | EC
                                valueElement.innerHTML = `${data.sensorData?.ec || '0'} µS/cm`;
                                break;
                            case 3: // NPK Sensor | MC
                                valueElement.innerHTML = `${data.sensorData?.moist || '0'}%`;
                                break;
                            case 4: // NPK Sensor | PH
                                valueElement.innerHTML = `${data.sensorData?.ph || '0'}`;
                                break;
                            default:
                                valueElement.innerHTML = '0';
                        }
                    }
                });
            })
            .catch(error => console.error('Error fetching NPK sensor data:', error));
    }

    updateData_npk();
    setInterval(updateData_npk, 5000);
  </script>

<script>
document.getElementById('date').addEventListener('change', function () {
    const selectedDate = this.value;

    if (selectedDate) {
        fetch(`LIGHTINTENSITYSENSOR/get_data_by_date.php?date=${selectedDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                // Update the chart with the fetched data
                const labels = data.map(item => new Date(item.timestamp).toLocaleTimeString());
                const values = data.map(item => item.hourlyAverage);

                luxChart.data.labels = labels;
                luxChart.data.datasets[0].data = values;
                luxChart.update();
            })
            .catch(error => console.error('Error fetching data:', error));
    }
});
</script>


<!-- DATA ANALYTICS -->
  <script>
    function updateDateTime() {
      const now = new Date();
      const optionsDate = { year: 'numeric', month: 'long', day: 'numeric' };
      const formattedDate = now.toLocaleDateString('en-US', optionsDate);
      const optionsTime = { hour: 'numeric', minute: '2-digit', hour12: true };
      const formattedTime = now.toLocaleTimeString('en-US', optionsTime);
      document.getElementById('dateBanner').innerHTML =
        `Today is ${formattedDate} (${formattedTime})<br><span style="font-size: 1.5rem; color: #007f2e;">HELLO, FARMER!</span>`;
    }

    updateDateTime();
    setInterval(updateDateTime, 60000);

    document.getElementById('sensorBtn').addEventListener('click', function () {
      document.getElementById('sensorSection').classList.add('active');
      document.getElementById('dataSection').classList.remove('active');
      this.classList.add('active');
      document.getElementById('dataBtn').classList.remove('active');
    });

    document.getElementById('dataBtn').addEventListener('click', function () {
      document.getElementById('sensorSection').classList.remove('active');
      document.getElementById('dataSection').classList.add('active');
      this.classList.add('active');
      document.getElementById('sensorBtn').classList.remove('active');
    });
    
    // Light Intensity Chart Configuration (same as before)
    // Function to convert chart data to CSV
    function chartToCSV(chart) {
      const labels = chart.data.labels;
      const datasets = chart.data.datasets;
      let csv = 'Label,' + labels.join(',') + '\n';
      
      datasets.forEach(dataset => {
        csv += dataset.label + ',' + dataset.data.join(',') + '\n';
      });
      
      return csv;
    }

    // Function to trigger CSV download
    function downloadCSV(chart1, chart2) {
      const csv1 = chartToCSV(chart1);
      const csv2 = chartToCSV(chart2);
      
      // Combine both CSV files
      const combinedCSV = `Light Intensity Graph Data\n${csv1}\n\nNPK Sensor Graph Data\n${csv2}`;
      
      const blob = new Blob([combinedCSV], { type: 'text/csv' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'graphs_data.csv';
      link.click();
    }

    document.getElementById('downloadCSV').addEventListener('click', function() {
      downloadCSV(luxChart, sensorChart);
    });


      // NPK Sensor Chart Configuration

    document.getElementById('npkdate').addEventListener('change', function () {
    const selectedDate = this.value;

    if (selectedDate) {
        fetch(`NPKSENSOR/get_data_by_date.php?npkdate=${selectedDate}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                console.log(data); // Debugging: Check the fetched data

                if (data.error || data.length === 0) {
                    console.warn('No data available for the selected date.');
                    sensorChart.data.labels = [];
                    sensorChart.data.datasets.forEach(dataset => dataset.data = []);
                    sensorChart.update();
                    return;
                }

                // Map the data to chart labels and datasets
                const labels = data.map(item => new Date(item.timestamp).toLocaleTimeString());
                const tempValues = data.map(item => item.temp || 0);
                const phValues = data.map(item => item.ph || 0);
                const moistValues = data.map(item => item.moist || 0);
                const ecValues = data.map(item => item.ec || 0);
                const nValues = data.map(item => item.n || 0);
                const pValues = data.map(item => item.p || 0);
                const kValues = data.map(item => item.k || 0);

                // Update the chart
                sensorChart.data.labels = labels;
                sensorChart.data.datasets[0].data = tempValues;
                sensorChart.data.datasets[1].data = moistValues;
                sensorChart.data.datasets[2].data = ecValues;
                sensorChart.data.datasets[3].data = nValues;
                sensorChart.data.datasets[4].data = pValues;
                sensorChart.data.datasets[5].data = kValues;
                sensorChart.data.datasets[6].data = phValues;
                sensorChart.update();
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                sensorChart.data.labels = [];
                sensorChart.data.datasets.forEach(dataset => dataset.data = []);
                sensorChart.update();
            });
    }
});



window.onload = function () {

  // Light Intensity Chart Configuration
    const ctx = document.getElementById('luxChart').getContext('2d');
    luxChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [], // Empty initially
            datasets: [{
                label: 'Light Intensity',
                data: [], // Empty initially
                borderColor: '#28a745',
                borderWidth: 4,
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                legend: {
                    labels: { color: '#333' }
                }
            }
        }
    });
  // NPK Chart Configuration
      const sensorCtx = document.getElementById('sensorChart').getContext('2d');
sensorChart = new Chart(sensorCtx, {
    type: 'line',
    data: {
        labels: [], // Empty initially
        datasets: [
            { label: 'Temperature (°C)', data: [], borderColor: '#FF5733', fill: false },
            { label: 'Moisture (%)', data: [], borderColor: '#33FF57', fill: false },
            { label: 'EC (µS/cm)', data: [], borderColor: '#3357FF', fill: false },
            { label: 'Nitrogen (ppm)', data: [], borderColor: '#FF33A1', fill: false },
            { label: 'Phosphorus (ppm)', data: [], borderColor: '#8C33FF', fill: false },
            { label: 'Potassium (ppm)', data: [], borderColor: '#FF8C33', fill: false },
            { label: 'pH', data: [], borderColor: '#FFC300', fill: false }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
};
  </script>

<script>
  let luxChart, sensorChart;

  function updateDateTime() {
    const now = new Date();
    const optionsDate = { year: 'numeric', month: 'long', day: 'numeric' };
    const formattedDate = now.toLocaleDateString('en-US', optionsDate);
    const optionsTime = { hour: 'numeric', minute: '2-digit', hour12: true };
    const formattedTime = now.toLocaleTimeString('en-US', optionsTime);
    document.getElementById('dateBanner').innerHTML =
      `Today is ${formattedDate} (${formattedTime})<br><span style="font-size: 1.5rem; color: #007f2e;">HELLO, FARMER!</span>`;
  }

  updateDateTime();
  setInterval(updateDateTime, 60000);

  document.getElementById('sensorBtn').addEventListener('click', function () {
    document.getElementById('sensorSection').classList.add('active');
    document.getElementById('dataSection').classList.remove('active');
    this.classList.add('active');
    document.getElementById('dataBtn').classList.remove('active');
  });

  document.getElementById('dataBtn').addEventListener('click', function () {
    document.getElementById('sensorSection').classList.remove('active');
    document.getElementById('dataSection').classList.add('active');
    this.classList.add('active');
    document.getElementById('sensorBtn').classList.remove('active');
  });
  
  // Light Intensity Chart Configuration (same as before)
  // Function to convert chart data to CSV
  function chartToCSV(chart) {
    const labels = chart.data.labels;
    const datasets = chart.data.datasets;
    let csv = 'Label,' + labels.join(',') + '\n';
    
    datasets.forEach(dataset => {
      csv += dataset.label + ',' + dataset.data.join(',') + '\n';
    });
    
    return csv;
  }

  // Function to trigger CSV download
  function downloadCSV() {
    const csv1 = chartToCSV(luxChart);
    const csv2 = chartToCSV(sensorChart);
    
    // Combine both CSV files
    const combinedCSV = `Light Intensity Graph Data\n${csv1}\n\nNPK Sensor Graph Data\n${csv2}`;
    
    const blob = new Blob([combinedCSV], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'graphs_data.csv';
    link.click();
  }

  document.getElementById('downloadCSV').addEventListener('click', downloadCSV);
  
</script>

<script>
  // Redirect to solar-panel.html on click
  document.getElementById('solarPanelCard').addEventListener('click', function () {
    window.location.href = 'solar-panel.php';
  });
  document.getElementById('fanCard').addEventListener('click', function () {
    window.location.href = 'auxiliary-fan.html';
  });
  document.getElementById('lightCard').addEventListener('click', function () {
    window.location.href = 'light-data-insight.php';
  });
  document.getElementById('npkCard').addEventListener('click', function () {
    window.location.href = 'npk.php';
  });

  
</script>

<script>
  // Prevent toggle clicks from triggering navigation
  document.querySelectorAll('.form-check-input').forEach(toggle => {
    toggle.addEventListener('click', function (e) {
      e.stopPropagation(); // Prevent click from bubbling to parent card
    });
  });

  // Optional: You can handle actual on/off logic here if needed

    function logout() {
      const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
      logoutModal.show();
    }

    document.getElementById('confirmLogoutBtn').addEventListener('click', function () {
      window.location.href = 'user_logout.php';
    });

    document.getElementById('logoutBtn').addEventListener('click', function () {
      const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
      logoutModal.show();
    });
</script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
</body>

</html>