<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Light Intensity Sensor</title>
  <link rel="icon" type="image/png" href="assets/clsu-official-logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="css/light-data-insight.css">
  <link rel="stylesheet" href="css/styles.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="js/theme.js"></script>
</head>
<body>

<div class="container">
  <!-- Header -->
  <header class="header">
    <div class="header-content">
      <button class="back-btn" id="backButton" aria-label="Go back">
        <i class="bi bi-arrow-left"></i>
      </button>
      <h1 class="page-title">Light Intensity</h1>
      <button id="themeToggle" style="background:none;border:none;font-size:1.3rem;cursor:pointer;padding:6px;margin-left:auto;color:inherit;" aria-label="Toggle theme"><i class="fa-solid fa-moon"></i></button>
    </div>
  </header>

  <!-- Tabs -->
  <div class="tab-container">
    <div class="tab active" id="lightIntensityTab">Light Intensity</div>
    <div class="tab" id="dataInsightsTab">Data Insights</div>
  </div>

  <!-- Light Intensity Content -->
  <div id="lightIntensityContent" class="content-section active">
    <main>
      <div class="panel-card">
        <h2 class="card-title">Light Sensor</h2>

        <i class="bi bi-lightbulb-fill light-icon" id="lightIcon" aria-hidden="true"></i>

        <div class="lux-display">
          <div class="lux-value" id="luxValue">0</div>
          <div class="lux-unit">lux</div>
          <div class="lux-label">Average per Hour</div>
        </div>

        <div class="sensor-info">
          <div class="info-item">
            <div class="info-label">Status</div>
            <div class="info-value" id="connectionStatus">CONNECTED</div>
          </div>
          <div class="info-item">
            <div class="info-label">Last Updated</div>
            <div class="info-value" id="lastUpdated">—</div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Data Insights Content -->
  <div id="dataInsightsContent" class="content-section">
    <div class="reading-section">
      <label for="date">Readings on:</label>
      <input type="date" id="date">
    </div>
    <button class="download-btn" onclick="downloadChartData()">Download Data</button>
    <!-- Range Download Button -->
    <button class="download-btn" data-bs-toggle="modal" data-bs-target="#dateRangeModal">
      Download Range Data
    </button>

    <div class="chart">
      <canvas id="luxChart"></canvas>
    </div>
  </div>



<!-- Date Range Modal -->
<div class="modal fade" id="dateRangeModal" tabindex="-1" aria-labelledby="dateRangeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dateRangeModalLabel">Select Date Range</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="startDate" class="form-label">Start Date:</label>
          <input type="date" class="form-control" id="startDate">
        </div>
        <div class="mb-3">
          <label for="endDate" class="form-label">End Date:</label>
          <input type="date" class="form-control" id="endDate">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="downloadRangeData()">Download</button>
      </div>
    </div>
  </div>
</div>


  

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Chart.js & JS Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Javascript -->


  <script>
    function formatDate(dateString) {
      const date = new Date(dateString);
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return date.toLocaleDateString(undefined, options);
    }

    function updateLightSensorData() {
      fetch('LIGHTINTENSITYSENSOR/get_data.php')
        .then(response => response.json())
        .then(data => {
          const averagePerHour = data.latestData ? data.latestData.hourlyAverage : '0';
          const timestamp = data.latestData ? data.latestData.timestamp : null;
          const isConnected = data.status === 'connected';

          // Update Lux Value
          const luxValueElement = document.getElementById('luxValue');
          if (luxValueElement) {
            luxValueElement.textContent = averagePerHour;
          }

          // Update Connection Status
          const statusElement = document.getElementById('connectionStatus');
          if (statusElement) {
            statusElement.textContent = isConnected ? 'CONNECTED' : 'DISCONNECTED';
            statusElement.style.color = isConnected ? '#28a745' : '#dc3545';
          }

          // Update Light Icon
          const lightIcon = document.getElementById('lightIcon');
          if (lightIcon && isConnected) {
            lightIcon.style.color = '#E0A70D';
          }

          // Update Last Updated Timestamp
          const lastUpdatedElement = document.getElementById('lastUpdated');
          if (lastUpdatedElement) {
            if (timestamp) {
              const date = new Date(timestamp);
              lastUpdatedElement.textContent = date.toLocaleTimeString();
            } else {
              lastUpdatedElement.textContent = '—';
            }
          }
        })
        .catch(error => console.error('Error fetching light sensor data:', error));
    }

    updateLightSensorData();
    setInterval(updateLightSensorData, 5000); // Update every 5 seconds
  </script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/exceljs"></script>
  <script src="https://cdn.jsdelivr.net/npm/jszip"></script>
  <script src="https://cdn.jsdelivr.net/npm/file-saver"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


  <script>
    const lightTab = document.getElementById('lightIntensityTab');
    const dataTab = document.getElementById('dataInsightsTab');
    const lightContent = document.getElementById('lightIntensityContent');
    const dataContent = document.getElementById('dataInsightsContent');

    lightTab.addEventListener('click', function() {
      lightTab.classList.add('active');
      dataTab.classList.remove('active');
      lightContent.classList.add('active');
      dataContent.classList.remove('active');
    });

    dataTab.addEventListener('click', function() {
      dataTab.classList.add('active');
      lightTab.classList.remove('active');
      dataContent.classList.add('active');
      lightContent.classList.remove('active');
    });

    // Chart.js Configuration
    const ctx = document.getElementById('luxChart').getContext('2d');
    const luxChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [],
        datasets: [{
          label: 'Light Intensity',
          data: [],
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
            labels: { }
          }
        }
      }
    });

    // Fetch chart data on date change
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

      async function downloadChartData() {
          const labels = luxChart.data.labels;
          const data = luxChart.data.datasets[0].data;
          const selectedDate = document.getElementById('date').value;
          const today = new Date();
          const formattedToday = today.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
          const formattedDate = new Date(selectedDate).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

          // Create a new Excel workbook and worksheet
          const workbook = new ExcelJS.Workbook();
          const worksheet = workbook.addWorksheet('Light Intensity');

          // Set column widths (fixed sizes)
          worksheet.columns = [
            { header: 'Time', key: 'time', width: 15 },
            { header: '', key: 'col2', width: 15 },
            { header: '', key: 'col3', width: 15 },
            { header: '', key: 'col4', width: 15 },
            { header: '', key: 'col5', width: 15 },
            { header: '', key: 'col6', width: 15 },
            { header: '', key: 'col7', width: 15 },
          ];

          // Title: Light Intensity Graph Data (Calibri 14 Bold)
          const titleRow = worksheet.addRow(['Light Intensity Graph Data']);
          titleRow.font = { name: 'Calibri', size: 14, bold: true };
          worksheet.mergeCells('A1:G1');

          // Date Today: Tuesday, May 27, 2025 (Calibri 11 Italic)
          const todayRow = worksheet.addRow([`Date Today: ${formattedToday}`]);
          todayRow.font = { name: 'Calibri', size: 11, italic: true };
          worksheet.mergeCells('A2:G2');

          // Date of the average data: Wednesday, May 21, 2025 (Calibri 11 Italic)
          const avgDateRow = worksheet.addRow([`Date of the average data: ${formattedDate}`]);
          avgDateRow.font = { name: 'Calibri', size: 11, italic: true };
          worksheet.mergeCells('A3:G3');

          worksheet.addRow([]); // Blank row

          // Add Time header row with time labels
          const timeRowValues = ['Time', ...labels];
          const timeRow = worksheet.addRow(timeRowValues);
          timeRow.font = { name: 'Calibri', size: 11 };
          
          // Add Light Intensity row with values + "LUX"
          const intensityRowValues = ['Light Intensity', ...data.map(d => `${d} LUX`)];
          const intensityRow = worksheet.addRow(intensityRowValues);
          intensityRow.font = { name: 'Calibri', size: 11 };

          // Fix column widths for all columns (already set above)

          // Embed chart image
          const imageBase64 = luxChart.toBase64Image();
          const imageId = workbook.addImage({
            base64: imageBase64,
            extension: 'png',
          });

          worksheet.addImage(imageId, {
            tl: { col: 3, row: 1 }, // Place image around top right area
            ext: { width: 500, height: 300 },
          });

          // Write workbook to buffer
          const buffer = await workbook.xlsx.writeBuffer();

          // Directly trigger XLSX download (no zip)
          const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
          const link = document.createElement('a');
          link.href = URL.createObjectURL(blob);
          link.download = 'light_intensity_data.xlsx';
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        }


        document.getElementById('backButton').addEventListener('click', function() {
          window.location.href = 'index.php';
        });
        
  </script>

  <script>
      function getDatesInRange(startDate, endDate) {
        const dates = [];
        const current = new Date(startDate);
        while (current <= endDate) {
          dates.push(new Date(current));
          current.setDate(current.getDate() + 1);
        }
        return dates;
      }

      async function downloadRangeData() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (!startDate || !endDate) {
          alert("Please select both start and end dates.");
          return;
        }

        const workbook = new ExcelJS.Workbook();
        const dates = getDatesInRange(new Date(startDate), new Date(endDate));

        for (let date of dates) {
          const dateString = date.toISOString().split('T')[0];

          const response = await fetch(`LIGHTINTENSITYSENSOR/get_data_by_date.php?date=${dateString}`);
          const data = await response.json();

          if (!data || data.length === 0) continue;

          const labels = data.map(item => new Date(item.timestamp).toLocaleTimeString());
          const values = data.map(item => item.hourlyAverage);

          // Create temp canvas for chart (same as before)
          const tempCanvas = document.createElement('canvas');
          tempCanvas.width = 600;
          tempCanvas.height = 300;
          const tempCtx = tempCanvas.getContext('2d');

          const chart = new Chart(tempCtx, {
            type: 'line',
            data: {
              labels: labels,
              datasets: [{
                label: 'Light Intensity',
                data: values,
                borderColor: '#28a745',
                borderWidth: 2,
                fill: false
              }]
            },
            options: {
              animation: false,
              responsive: false
            }
          });

          await new Promise(resolve => setTimeout(resolve, 500)); // Wait for chart to render

          const imageBase64 = chart.toBase64Image();
          const base64Data = imageBase64.replace(/^data:image\/png;base64,/, '');

          // Add new worksheet for this date
          const worksheet = workbook.addWorksheet(`Data ${dateString}`);

          const today = new Date();

          // Add rows with formatting

          // 1. Title: "Light Intensity Graph Data" (Calibri 14 Bold)
          const titleRow = worksheet.addRow(['Light Intensity Graph Data']);
          titleRow.font = { name: 'Calibri', size: 14, bold: true };

          // 2. Date Today: Calibri 11 Italic
          const dateTodayRow = worksheet.addRow([`Date Today: ${today.toDateString()}`]);
          dateTodayRow.font = { name: 'Calibri', size: 11, italic: true };

          // 3. Date of the average data: Calibri 11 Italic
          const avgDateRow = worksheet.addRow([`Date of the average data: ${date.toDateString()}`]);
          avgDateRow.font = { name: 'Calibri', size: 11, italic: true };

          // Blank row
          worksheet.addRow([]);

          // 4. Header row: Time + labels (Calibri 11, fixed column size)
          const headerRow = worksheet.addRow(['Time', ...labels]);
          headerRow.font = { name: 'Calibri', size: 11 };
          // Set column widths for time and each label
          worksheet.getColumn(1).width = 15; // Time column
          for (let i = 2; i <= labels.length + 1; i++) {
            worksheet.getColumn(i).width = 14; // fixed width for data columns
          }

          // 5. Data row: Light Intensity + values (Calibri 11, fixed column size)
          const dataRow = worksheet.addRow(['Light Intensity', ...values.map(v => `${v} LUX`)]);
          dataRow.font = { name: 'Calibri', size: 11 };

          // Embed chart image
          const imageId = workbook.addImage({
            base64: base64Data,
            extension: 'png',
          });

          worksheet.addImage(imageId, {
            tl: { col: 8, row: 0 },
            ext: { width: 500, height: 300 }
          });

          chart.destroy();
        }

        // Write and download the Excel file directly (no zip)
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
        const url = window.URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = `light_intensity_data_${startDate}_to_${endDate}.xlsx`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
      }
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

</div> <!-- end dataInsightsContent -->

<!-- Monthly Average Export -->
<div style="margin: 24px 0; background: #fff; border: 1px solid #dee2e6; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
  <h2 style="font-size:18px; font-weight:700; color:#1a232c; margin-bottom:8px;">Monthly Average Export</h2>
  <p style="font-size:13px; color:#666; margin-bottom:16px;">
    Select a month or month range to preview and download average Light Intensity readings.
  </p>
  <div style="display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end; margin-bottom:16px;">
    <div>
      <label for="lightStartMonth" style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">Start Month</label>
      <input type="month" id="lightStartMonth" class="form-control" style="min-width:160px;">
    </div>
    <div>
      <label for="lightEndMonth" style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">End Month</label>
      <input type="month" id="lightEndMonth" class="form-control" style="min-width:160px;">
    </div>
    <button id="lightPreviewBtn" class="btn btn-success" style="height:38px;">Preview</button>
    <button id="lightCsvBtn" class="btn btn-outline-secondary" style="height:38px;" disabled>Download CSV</button>
  </div>
  <div id="lightPreviewStatus" style="font-size:13px; color:#888; margin-bottom:8px;"></div>
  <div style="overflow-x:auto;">
    <table id="lightPreviewTable" style="display:none; width:100%; border-collapse:collapse; font-size:13px;">
      <thead>
        <tr style="background:rgba(0,102,0,0.08);">
          <th style="padding:10px 14px; text-align:left; border-bottom:2px solid #dee2e6;">Month</th>
          <th style="padding:10px 14px; text-align:right; border-bottom:2px solid #dee2e6;">Avg Light Intensity (Avg of Hourly Avg, Lux)</th>
        </tr>
      </thead>
      <tbody id="lightPreviewBody"></tbody>
    </table>
  </div>
</div>

<script>
(function() {
  const startInput = document.getElementById('lightStartMonth');
  const endInput   = document.getElementById('lightEndMonth');
  const previewBtn = document.getElementById('lightPreviewBtn');
  const csvBtn     = document.getElementById('lightCsvBtn');
  const statusEl   = document.getElementById('lightPreviewStatus');
  const table      = document.getElementById('lightPreviewTable');
  const tbody      = document.getElementById('lightPreviewBody');
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
      const res  = await fetch(`api/monthly_avg/get_light_monthly_avg.php?start_month=${sm}&end_month=${em}`);
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
          <td style="padding:8px 14px;text-align:right;">${r.avg_lux}</td>
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
    let csv = 'Month,Avg Light Intensity (Avg of Hourly Avg\u002C Lux)\n';
    previewData.forEach(r => { csv += `"${r.month}",${r.avg_lux}\n`; });
    const blob = new Blob([csv], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url;
    const sm = startInput.value, em = endInput.value;
    a.download = sm === em ? `Light_Monthly_Avg_${sm}.csv` : `Light_Monthly_Avg_${sm}_to_${em}.csv`;
    a.click();
    URL.revokeObjectURL(url);
  });
})();
</script>

</div> <!-- end container -->
</body>
</html>
