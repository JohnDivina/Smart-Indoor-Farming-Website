<?php session_start(); if (!isset($_SESSION["id"])) { header("Location: login.php"); exit(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Temperature & Humidity</title>
  <link rel="icon" type="image/png" href="assets/clsu-official-logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/temp-humidity.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Global Theme Support -->
  <link rel="stylesheet" href="css/styles.css">
  <script src="js/theme.js"></script>
</head>
<body>

<div class="container">
  <!-- Header -->
  <header class="header">
    <div class="header-content">
      <button class="back-btn" onclick="window.location.href='index.php'" aria-label="Go back">
        <i class="bi bi-arrow-left"></i>
      </button>
      <h1 class="page-title">Temperature & Humidity</h1>
    </div>
  </header>

  <!-- Main Content -->
  <main>
    <!-- Current Readings Cards Grid -->
    <div class="cards-grid">
      <!-- Current Greenhouse Readings Card -->
      <div class="info-card">
        <h2 class="card-title">Current Greenhouse Readings</h2>
        <div class="metrics-container">
          <div class="metric-row">
            <i class="bi bi-thermometer-half metric-icon"></i>
            <div class="metric-content">
              <div class="metric-value" id="currentTemp">--</div>
              <div class="metric-label">Temperature (°C)</div>
            </div>
          </div>
          <div class="metric-row">
            <i class="bi bi-droplet-half metric-icon"></i>
            <div class="metric-content">
              <div class="metric-value" id="currentHum">--</div>
              <div class="metric-label">Humidity (%)</div>
            </div>
          </div>
          <div class="timestamp-row">
            <i class="bi bi-clock"></i>
            <span id="lastUpdated">Loading...</span>
          </div>
        </div>
      </div>

      <!-- External Weather Reference Card -->
      <div class="info-card">
        <h2 class="card-title">External Weather (Reference)</h2>
        <div class="location-label">Science City of Muñoz, Nueva Ecija</div>
        <div class="metrics-container">
          <div class="weather-icon-container">
            <i class="bi bi-cloud-sun weather-icon" id="weatherIcon"></i>
            <div class="weather-desc" id="weatherDesc">Loading...</div>
          </div>
          <div class="metric-row">
            <i class="bi bi-thermometer metric-icon"></i>
            <div class="metric-content">
              <div class="metric-value" id="externalTemp">--</div>
              <div class="metric-label">Outside Temperature (°C)</div>
            </div>
          </div>
          <div class="metric-row">
            <i class="bi bi-droplet metric-icon"></i>
            <div class="metric-content">
              <div class="metric-value" id="externalHum">--</div>
              <div class="metric-label">Outside Humidity (%)</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Historical Data Card -->
    <div class="panel-card">
      <h2 class="card-title">Historical Data</h2>
      
      <div class="reading-section">
        <label for="date">Readings on:</label>
        <input type="date" id="date" class="form-control">
      </div>
      
      <button class="download-btn" id="downloadDataBtn">Download Data</button>
      <button class="download-btn" id="downloadRangeBtn" data-bs-toggle="modal" data-bs-target="#rangeModal">
        Download Range Data
      </button>
      
      <div class="chart">
        <canvas id="thChart"></canvas>
      </div>
    </div>

    <!-- Monthly Average Export -->
    <div style="margin-top: 24px; background: #fff; border: 1px solid #dee2e6; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
      <h2 style="font-size: 18px; font-weight: 700; color: #1a232c; margin-bottom: 8px;">Monthly Average Export</h2>
      <p style="font-size:13px; color:#666; margin-bottom:16px;">
        Select a month or month range to preview and download average Temperature &amp; Humidity readings.
      </p>

      <div style="display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end; margin-bottom:16px;">
        <div>
          <label for="thStartMonth" style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">Start Month</label>
          <input type="month" id="thStartMonth" class="form-control" style="min-width:160px;">
        </div>
        <div>
          <label for="thEndMonth" style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">End Month</label>
          <input type="month" id="thEndMonth" class="form-control" style="min-width:160px;">
        </div>
        <button id="thPreviewBtn" class="btn btn-success" style="height:38px;">Preview</button>
        <button id="thCsvBtn" class="btn btn-outline-secondary" style="height:38px;" disabled>Download CSV</button>
      </div>

      <div id="thPreviewStatus" style="font-size:13px; color:#888; margin-bottom:8px;"></div>
      <div style="overflow-x:auto;">
        <table id="thPreviewTable" style="display:none; width:100%; border-collapse:collapse; font-size:13px;">
          <thead>
            <tr style="background:rgba(0,102,0,0.08);">
              <th style="padding:10px 14px; text-align:left; border-bottom:2px solid #dee2e6;">Month</th>
              <th style="padding:10px 14px; text-align:right; border-bottom:2px solid #dee2e6;">Avg Temperature (°C)</th>
              <th style="padding:10px 14px; text-align:right; border-bottom:2px solid #dee2e6;">Avg Humidity (%)</th>
            </tr>
          </thead>
          <tbody id="thPreviewBody"></tbody>
        </table>
      </div>
    </div>
  </main>
</div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Monthly Average Export Logic -->
  <script>
  (function() {
    const startInput = document.getElementById('thStartMonth');
    const endInput   = document.getElementById('thEndMonth');
    const previewBtn = document.getElementById('thPreviewBtn');
    const csvBtn     = document.getElementById('thCsvBtn');
    const statusEl   = document.getElementById('thPreviewStatus');
    const table      = document.getElementById('thPreviewTable');
    const tbody      = document.getElementById('thPreviewBody');
    let previewData  = [];

    function invalidatePreview() {
      csvBtn.disabled = true;
      previewData = [];
      table.style.display = 'none';
      tbody.innerHTML = '';
      statusEl.textContent = '';
    }

    startInput.addEventListener('change', invalidatePreview);
    endInput.addEventListener('change', invalidatePreview);

    previewBtn.addEventListener('click', async function() {
      const sm = startInput.value;
      const em = endInput.value;
      if (!sm || !em) { statusEl.textContent = 'Please select both Start Month and End Month.'; return; }
      if (sm > em)    { statusEl.textContent = 'Start Month cannot be after End Month.'; return; }
      statusEl.textContent = 'Loading\u2026';
      table.style.display = 'none';
      try {
        const res  = await fetch(`api/monthly_avg/get_th_monthly_avg.php?start_month=${sm}&end_month=${em}`);
        const data = await res.json();
        if (data.error || !data.length) {
          statusEl.textContent = data.error || 'No data for the selected range.';
          csvBtn.disabled = true;
          return;
        }
        previewData = data;
        tbody.innerHTML = data.map(r =>
          `<tr style="border-bottom:1px solid #dee2e6;">
            <td style="padding:8px 14px;font-weight:${r.month==='Overall Average'?'700':'400'};">${r.month}</td>
            <td style="padding:8px 14px;text-align:right;">${r.avg_temperature}</td>
            <td style="padding:8px 14px;text-align:right;">${r.avg_humidity}</td>
          </tr>`
        ).join('');
        table.style.display = 'table';
        statusEl.textContent = `Showing ${data.filter(r=>r.month!=='Overall Average').length} month(s).`;
        csvBtn.disabled = false;
      } catch(e) {
        statusEl.textContent = 'Error loading data.';
        csvBtn.disabled = true;
      }
    });

    csvBtn.addEventListener('click', function() {
      if (!previewData.length) return;
      let csv = 'Month,Avg Temperature (\u00b0C),Avg Humidity (%)\n';
      previewData.forEach(r => { csv += `"${r.month}",${r.avg_temperature},${r.avg_humidity}\n`; });
      const blob = new Blob([csv], { type: 'text/csv' });
      const url  = URL.createObjectURL(blob);
      const a    = document.createElement('a');
      a.href = url;
      const sm = startInput.value, em = endInput.value;
      a.download = sm === em ? `TH_Monthly_Avg_${sm}.csv` : `TH_Monthly_Avg_${sm}_to_${em}.csv`;
      a.click();
      URL.revokeObjectURL(url);
    });
  })();
  </script>

  <script>
    // Fetch Current Greenhouse Readings
    function fetchCurrentReadings() {
      fetch('TEMPHUMIDITYSENSOR/get_latest.php')
        .then(response => response.json())
        .then(data => {
          // Check if data exists and is recent (within last 5 minutes)
          const isConnected = data.timestamp && 
            (new Date() - new Date(data.timestamp)) < 5 * 60 * 1000;
          
          if (isConnected && data.temperature !== null && data.humidity !== null) {
            // Sensor is connected - show values
            document.getElementById('currentTemp').textContent = parseFloat(data.temperature).toFixed(1);
            document.getElementById('currentHum').textContent = parseFloat(data.humidity).toFixed(1);
            
            const timestamp = new Date(data.timestamp);
            document.getElementById('lastUpdated').textContent = 
              `Last updated: ${timestamp.toLocaleTimeString()}`;
          } else {
            // Sensor is disconnected or no recent data
            document.getElementById('currentTemp').textContent = '—';
            document.getElementById('currentHum').textContent = '—';
            document.getElementById('lastUpdated').textContent = 'No recent data';
          }
        })
        .catch(error => {
          console.error('Error fetching current readings:', error);
          document.getElementById('currentTemp').textContent = '—';
          document.getElementById('currentHum').textContent = '—';
          document.getElementById('lastUpdated').textContent = 'Connection error';
        });
    }

    // Fetch External Weather (Science City of Muñoz, Nueva Ecija)
    function fetchExternalWeather() {
      fetch('api/get_external_weather.php')
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            // API error or not configured
            document.getElementById('weatherDesc').textContent = data.error;
            document.getElementById('externalTemp').textContent = '—';
            document.getElementById('externalHum').textContent = '—';
            document.getElementById('weatherIcon').className = 'bi bi-cloud-slash weather-icon';
            return;
          }
          
          // Display weather data
          document.getElementById('externalTemp').textContent = data.temperature.toFixed(1);
          document.getElementById('externalHum').textContent = data.humidity;
          document.getElementById('weatherDesc').textContent = 
            data.condition.charAt(0).toUpperCase() + data.condition.slice(1);
          
          // Update weather icon based on condition
          let iconClass = 'bi bi-cloud-sun weather-icon';
          const condition = data.condition.toLowerCase();
          
          if (condition.includes('clear')) iconClass = 'bi bi-sun weather-icon';
          else if (condition.includes('few clouds')) iconClass = 'bi bi-cloud-sun weather-icon';
          else if (condition.includes('cloud')) iconClass = 'bi bi-cloud weather-icon';
          else if (condition.includes('rain') || condition.includes('drizzle')) iconClass = 'bi bi-cloud-rain weather-icon';
          else if (condition.includes('thunder') || condition.includes('storm')) iconClass = 'bi bi-cloud-lightning weather-icon';
          else if (condition.includes('snow')) iconClass = 'bi bi-cloud-snow weather-icon';
          else if (condition.includes('mist') || condition.includes('fog') || condition.includes('haze')) iconClass = 'bi bi-cloud-fog weather-icon';
          
          document.getElementById('weatherIcon').className = iconClass;
        })
        .catch(error => {
          console.error('Error fetching weather:', error);
          document.getElementById('weatherDesc').textContent = 'Weather data unavailable';
          document.getElementById('externalTemp').textContent = '—';
          document.getElementById('externalHum').textContent = '—';
          document.getElementById('weatherIcon').className = 'bi bi-cloud-slash weather-icon';
        });
    }

    // Initialize current readings and weather
    fetchCurrentReadings();
    fetchExternalWeather();
    
    // Auto-refresh current readings every 5 seconds
    setInterval(fetchCurrentReadings, 5000);

    // Chart logic (existing)
    let thChart;
    let lastLabels = [], lastTemp = [], lastHum = [];
    function renderTHChart(labels, tempData, humData) {
      if (thChart) thChart.destroy();
      const ctx = document.getElementById('thChart').getContext('2d');
      thChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Temperature (°C)',
              data: tempData,
              borderColor: '#FF5733',
              backgroundColor: 'rgba(255,87,51,0.08)',
              fill: false,
              tension: 0.4
            },
            {
              label: 'Humidity (%)',
              data: humData,
              borderColor: '#33C1FF',
              backgroundColor: 'rgba(51,193,255,0.08)',
              fill: false,
              tension: 0.4
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { labels: { color: undefined } } },
          scales: { y: { beginAtZero: true } }
        }
      });
    }
    // Fetch and render for selected date
    document.getElementById('date').addEventListener('change', function () {
      const selectedDate = this.value;
      if (selectedDate) {
        fetch(`TEMPHUMIDITYSENSOR/get_data_by_date.php?date=${selectedDate}`)
          .then(response => response.json())
          .then(data => {
            if (data.error || data.length === 0) {
              renderTHChart([], [], []);
              lastLabels = []; lastTemp = []; lastHum = [];
              return;
            }
            const labels = data.map(item => new Date(item.timestamp).toLocaleTimeString());
            const tempData = data.map(item => item.temperature);
            const humData = data.map(item => item.humidity);
            renderTHChart(labels, tempData, humData);
            lastLabels = labels; lastTemp = tempData; lastHum = humData;
          })
          .catch(error => {
            console.error('Error fetching data:', error);
            renderTHChart([], [], []);
            lastLabels = []; lastTemp = []; lastHum = [];
          });
      }
    });
    // Download Data (current date)
    document.getElementById('downloadDataBtn').addEventListener('click', function() {
      if (!lastLabels.length) { alert('No data to download!'); return; }
      let csv = 'Time,Temperature (°C),Humidity (%)\n';
      for (let i = 0; i < lastLabels.length; i++) {
        csv += `${lastLabels[i]},${lastTemp[i]},${lastHum[i]}\n`;
      }
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'TempHumidityData.csv';
      a.click();
      URL.revokeObjectURL(url);
    });
    // Download Range Data
    document.getElementById('confirmRangeDownload').addEventListener('click', function() {
      const start = document.getElementById('startDate').value;
      const end = document.getElementById('endDate').value;
      if (!start || !end) { alert('Please select both start and end dates.'); return; }
      fetch(`TEMPHUMIDITYSENSOR/get_data_by_date.php?start=${start}&end=${end}`)
        .then(response => response.json())
        .then(data => {
          if (data.error || data.length === 0) {
            alert('No data found for selected range.');
            return;
          }
          let csv = 'Time,Temperature (°C),Humidity (%)\n';
          for (let i = 0; i < data.length; i++) {
            csv += `${new Date(data[i].timestamp).toLocaleString()},${data[i].temperature},${data[i].humidity}\n`;
          }
          const blob = new Blob([csv], { type: 'text/csv' });
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = 'TempHumidityData_Range.csv';
          a.click();
          URL.revokeObjectURL(url);
          var modal = bootstrap.Modal.getInstance(document.getElementById('rangeModal'));
          modal.hide();
        })
        .catch(error => {
          alert('Error downloading range data.');
        });
    });
    // Initial empty chart
    renderTHChart([], [], []);
  </script>
  <script>
    // Auto-logout after 5 minutes (300,000 ms) of inactivity
    (function() {
      let logoutTimer;
      const logoutAfter = 5 * 60 * 1000; // 5 minutes in milliseconds

      function resetLogoutTimer() {
        clearTimeout(logoutTimer);
        logoutTimer = setTimeout(() => {
          window.location.href = 'user_logout.php?inactive=1';
        }, logoutAfter);
      }

      // Reset timer on user activity
      ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt => {
        document.addEventListener(evt, resetLogoutTimer, true);
      });

      resetLogoutTimer(); // Start timer on page load
    })();
  </script>
</body>
</html> 