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
    <div class="nav-links d-flex align-items-center gap-5">
      <span id="sensorBtn" class="active">SENSORS</span>
      <div style="height: 30px; width: 2px; background-color: white; border-radius: 1px;"></div>
      <span id="dataBtn">DATA INSIGHTS</span>
    </div>
    <div class="nav-icons d-flex align-items-center gap-4 ms-4">
      <span class="notification-icon" title="Notifications" style="cursor: pointer;">
        <i class="bi bi-bell-fill" style="font-size: 1.3rem;"></i>
      </span>
      
      <span class="logout-icon" title="Logout" onclick="logout()" style="cursor: pointer;">
        <i class="bi bi-box-arrow-right" style="font-size: 1.3rem;"></i>
      </span>
    
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
            <div class="sensor-status">STATUS: RELAY ON</div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" checked />
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

<!-- SOLAR PANEL -->
<script>
// Global variables
let isUpdatingRelay = false;
let isConnected = false;

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('relay-toggle');
    
    // Initial setup
    toggle.disabled = true;
    fetchRelayStatus();
    
    // Set up the toggle event handler
    toggle.addEventListener('change', function() {
        if (!isUpdatingRelay && isConnected) {
            // INVERTED logic:
            // checked = true means user wants panels OPEN => send 'off'
            // checked = false means user wants panels CLOSED => send 'on'
            const relayState = this.checked ? 'off' : 'on';
            updateRelayState(relayState);
        }
    });
});

function updateRelayState(state) {
    isUpdatingRelay = true;
    const toggle = document.getElementById('relay-toggle');
    toggle.disabled = true;
    
    fetch(`SOLARPANEL/control_relay.php?state=${state}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // state 'off' means panels open (true)
                // state 'on' means panels closed (false)
                updateUI(state === 'off');
                localStorage.setItem('relayState', state);
            } else {
                // Silently revert the toggle if update failed
                toggle.checked = !toggle.checked;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toggle.checked = !toggle.checked;
        })
        .finally(() => {
            // Only re-enable if still connected
            toggle.disabled = !isConnected;
            isUpdatingRelay = false;
            // Refresh status after update
            setTimeout(fetchRelayStatus, 500);
        });
}

function updateUI(isOpen) {
    const relayStatusElement = document.getElementById('relay-status');
    const toggle = document.getElementById('relay-toggle');
    
    if (isOpen) {
        relayStatusElement.textContent = 'Panels: OPEN';
        relayStatusElement.style.color = 'green';
    } else {
        relayStatusElement.textContent = 'Panels: CLOSE';
        relayStatusElement.style.color = 'red';
    }
    
    // Update toggle checked state only if different
    if (toggle.checked !== isOpen) {
        toggle.checked = isOpen;
    }
}

function fetchRelayStatus() {
    fetch('SOLARPANEL/send_time.php')
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            const connectionStatusElement = document.getElementById('connection-status');
            const relayStatusElement = document.getElementById('relay-status');
            const toggle = document.getElementById('relay-toggle');
            
            isConnected = data.status === 'connected';
            connectionStatusElement.textContent = `STATUS: ${data.status.toUpperCase()}`;
            connectionStatusElement.style.color = isConnected ? 'green' : 'red';
            
            toggle.disabled = !isConnected;
            
            // INVERT relay state for UI:
            // relay 'on' means panels CLOSED -> isOpen = false
            // relay 'off' means panels OPEN -> isOpen = true
            if (data.relay === 'on') {
                updateUI(false);
            } else if (data.relay === 'off') {
                updateUI(true);
            } else {
                relayStatusElement.textContent = 'Panels: UNKNOWN';
                relayStatusElement.style.color = 'gray';
            }
        })
        .catch(error => {
            console.error('Error fetching status:', error);
            const connectionStatusElement = document.getElementById('connection-status');
            const relayStatusElement = document.getElementById('relay-status');
            const toggle = document.getElementById('relay-toggle');
            
            isConnected = false;
            connectionStatusElement.textContent = 'STATUS: DISCONNECTED';
            connectionStatusElement.style.color = 'red';

            relayStatusElement.textContent = 'Panels: UNKNOWN';
            relayStatusElement.style.color = 'gray';
            
            toggle.disabled = true;
        });
}

// Check status every 2 seconds
setInterval(fetchRelayStatus, 2000);
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
      window.location.href = 'user_logout.php'; 
  }
</script>



  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>

</html>