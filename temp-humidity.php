<?php
session_start();
if (!isset($_SESSION["id"])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Temperature & Humidity - Smart Farm</title>
    
    <style>
        .tabs-wrapper {
            display: flex; gap: 8px; margin-bottom: 24px;
            background: var(--glass-bg);
            padding: 6px; border-radius: var(--border-radius-md);
            border: 1px solid var(--glass-border);
            width: fit-content;
        }

        .tab-btn {
            background: transparent; border: none;
            padding: 10px 24px; border-radius: 8px;
            color: var(--text-secondary); font-weight: 600;
            cursor: pointer; transition: all var(--transition-normal);
        }

        .tab-btn.active {
            background: var(--accent-primary); color: #fff;
            box-shadow: 0 4px 12px rgba(25, 201, 104, 0.2);
        }

        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Temp & Humid Grid */
        .sensor-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-bottom: 32px; }
        
        .sensor-card {
            background: var(--glass-bg); backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border); padding: 40px 24px;
            border-radius: var(--border-radius-lg); text-align: center;
        }

        .sensor-icon { font-size: 48px; margin-bottom: 16px; }
        .temp-icon { color: #e74c3c; }
        .humid-icon { color: #3498db; }

        .sensor-value { font-size: 48px; font-weight: 700; color: var(--text-primary); margin: 8px 0; font-variant-numeric: tabular-nums; }
        .sensor-label { font-size: 16px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;}
        .sensor-unit { font-size: 20px; color: var(--text-muted); font-weight: 400; }

        /* Chart Controls */
        .chart-controls {
            display: flex; gap: 16px; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap;
        }
        .input-group label { display: block; font-size: 13px; color: var(--text-secondary); margin-bottom: 6px; }
        .input-group input {
            background: var(--glass-bg); border: 1px solid var(--glass-border);
            color: var(--text-primary); padding: 10px 16px; border-radius: var(--border-radius-sm);
            font-family: inherit; font-size: 14px;
        }
        .btn-action {
            background: var(--accent-primary); border: none; color: #fff;
            padding: 10px 24px; border-radius: var(--border-radius-sm); font-weight: 600;
            cursor: pointer; height: 42px; display: flex; align-items: center; gap: 8px;
        }
        .btn-action:hover { box-shadow: 0 4px 12px rgba(25, 201, 104, 0.3); }

        .chart-container { height: 450px; width: 100%; padding: 16px; background: var(--glass-bg); border-radius: var(--border-radius-md); }

    </style>
</head>
<body class="sticky-header-page">

<div class="app-container">
    
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <header class="top-header">
            <div class="page-title">
                <p><a href="index.php" style="color:var(--accent-primary); text-decoration:none;"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a></p>
                <h1 style="margin-top: 12px;">Temperature & Humidity Sensor</h1>
                <p>Status: <span id="connStatus" style="font-weight:600; color:var(--text-muted);">Checking...</span> | Last updated: <span id="lastUpdateTs">--</span></p>
            </div>
            <div class="header-actions">
                <button id="themeToggle" class="icon-button">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </header>

        <div class="tabs-wrapper">
            <button class="tab-btn active" data-target="liveData">Live Data</button>
            <button class="tab-btn" data-target="historicalInsights">Historical Insights</button>
        </div>

        <!-- Live Data Tab -->
        <div id="liveData" class="tab-content active">
            <div class="sensor-grid">
                <div class="sensor-card">
                    <i class="fa-solid fa-temperature-half sensor-icon temp-icon"></i>
                    <div class="sensor-label">Internal Temperature</div>
                    <div class="sensor-value" id="valTemp">-- <span class="sensor-unit">°C</span></div>
                </div>
                <div class="sensor-card">
                    <i class="fa-solid fa-droplet sensor-icon humid-icon"></i>
                    <div class="sensor-label">Internal Humidity</div>
                    <div class="sensor-value" id="valHumid">-- <span class="sensor-unit">%</span></div>
                </div>
            </div>

            <!-- External Weather Reference -->
            <div class="glass-panel weather-reference" style="margin-top: 24px; padding: 24px; border-radius: var(--border-radius-lg);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <h3 style="font-size: 18px; margin: 0; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-cloud-sun" style="color: var(--accent-primary);"></i> External Weather Reference
                    </h3>
                    <span id="weatherLocation" style="font-size: 14px; color: var(--text-muted); font-weight: 500;">Detecting location...</span>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                    <div class="weather-stat" style="background: var(--glass-bg); padding: 16px; border-radius: 12px; border: 1px solid var(--glass-border);">
                        <span style="display: block; font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px;">Condition</span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i id="weatherIcon" class="fa-solid fa-cloud" style="font-size: 20px; color: var(--accent-primary);"></i>
                            <span id="weatherCond" style="font-size: 16px; font-weight: 600; color: var(--text-primary);">--</span>
                        </div>
                    </div>
                    
                    <div class="weather-stat" style="background: var(--glass-bg); padding: 16px; border-radius: 12px; border: 1px solid var(--glass-border);">
                        <span style="display: block; font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px;">Outer Temp</span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-thermometer-three-quarters" style="font-size: 18px; color: #e74c3c;"></i>
                            <span id="weatherTemp" style="font-size: 18px; font-weight: 700; color: var(--text-primary);">--°C</span>
                        </div>
                    </div>
                    
                    <div class="weather-stat" style="background: var(--glass-bg); padding: 16px; border-radius: 12px; border: 1px solid var(--glass-border);">
                        <span style="display: block; font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px;">Outer Humidity</span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-droplet" style="font-size: 18px; color: #3498db;"></i>
                            <span id="weatherHumid" style="font-size: 18px; font-weight: 700; color: var(--text-primary);">--%</span>
                        </div>
                    </div>

                    <div class="weather-stat" style="background: var(--glass-bg); padding: 16px; border-radius: 12px; border: 1px solid var(--glass-border);">
                        <span style="display: block; font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px;">Wind Speed</span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-wind" style="font-size: 18px; color: var(--text-muted);"></i>
                            <span id="weatherWind" style="font-size: 18px; font-weight: 700; color: var(--text-primary);">-- m/s</span>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 16px; font-size: 11px; color: var(--text-muted); text-align: right;">
                    Provided by OpenWeatherMap API • Last sync: <span id="weatherLastSync">--</span>
                </div>
            </div>
        </div>

        <!-- Historical Insights Tab -->
        <div id="historicalInsights" class="tab-content">
            <div class="glass-panel" style="padding: 24px;">
                <div class="chart-controls">
                    <div class="input-group">
                        <label>Select Date</label>
                        <input type="date" id="chartDateVal" value="">
                    </div>
                    <button class="btn-action" id="downloadSingleBtn">
                        <i class="fa-solid fa-download"></i> Export Data & Chart
                    </button>
                </div>

                <div class="chart-container">
                    <canvas id="envChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Average Export -->
        <div class="glass-panel" style="padding: 24px; margin-top: 24px;">
            <h3 style="font-size:18px; font-weight:700; margin-bottom:8px;">Monthly Average Export</h3>
            <p style="font-size:13px; color:var(--text-secondary); margin-bottom:20px;">
                Select a month or range to preview and download average Temperature &amp; Humidity readings.
            </p>
            <div class="chart-controls">
                <div class="input-group">
                    <label for="thStartMonth">Start Month</label>
                    <input type="month" id="thStartMonth" style="min-width:160px;">
                </div>
                <div class="input-group">
                    <label for="thEndMonth">End Month</label>
                    <input type="month" id="thEndMonth" style="min-width:160px;">
                </div>
                <button class="btn-action" id="thPreviewBtn">
                    <i class="fa-solid fa-magnifying-glass"></i> Preview
                </button>
                <button class="btn-action" id="thCsvBtn" disabled
                    style="background:var(--glass-border); color:var(--text-secondary); border:1px solid var(--glass-border); cursor:default;">
                    <i class="fa-solid fa-download"></i> Download CSV
                </button>
            </div>
            <div id="thPreviewStatus" style="font-size:13px; color:var(--text-muted); margin-bottom:12px;"></div>
            <div style="overflow-x:auto;">
                <table id="thPreviewTable" style="display:none; width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="background:rgba(25,201,104,0.08);">
                            <th style="padding:10px 14px; text-align:left; border-bottom:2px solid var(--glass-border);">Month</th>
                            <th style="padding:10px 14px; text-align:right; border-bottom:2px solid var(--glass-border);">Avg Temperature (°C)</th>
                            <th style="padding:10px 14px; text-align:right; border-bottom:2px solid var(--glass-border);">Avg Humidity (%)</th>
                        </tr>
                    </thead>
                    <tbody id="thPreviewBody"></tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<script>
// Tab Logic
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.target).classList.add('active');
    });
});

// Live Data Sync
async function syncData() {
    try {
        const res = await fetch('api/environment/get_temp_humid.php');
        const data = await res.json();
        
        if (data.success) {
            const isConnected = data.status === 'connected';
            const opacity = isConnected ? '1' : '0.5';
            
            const tEl = document.getElementById('valTemp');
            const hEl = document.getElementById('valHumid');
            tEl.innerHTML = `${data.temperature.toFixed(1)} <span class="sensor-unit">°C</span>`;
            hEl.innerHTML = `${data.humidity.toFixed(1)} <span class="sensor-unit">%</span>`;
            tEl.style.opacity = opacity;
            hEl.style.opacity = opacity;

            const cStatus = document.getElementById('connStatus');
            const updateTs = document.getElementById('lastUpdateTs');
            updateTs.textContent = data.timestamp;

            if (isConnected) {
                cStatus.textContent = 'ONLINE';
                cStatus.style.color = 'var(--accent-primary)';
                updateTs.style.color = '';
            } else {
                cStatus.textContent = 'OFFLINE (Stale Data)';
                cStatus.style.color = 'var(--accent-danger)';
                updateTs.style.color = 'var(--accent-danger)';
            }
        }
    } catch (e) {}
}

async function syncWeather() {
    try {
        const res = await fetch('api/get_external_weather.php');
        const data = await res.json();
        
        if (data.error) {
            console.error('Weather error:', data.error);
            return;
        }

        document.getElementById('weatherTemp').textContent = `${data.temperature}°C`;
        document.getElementById('weatherHumid').textContent = `${data.humidity}%`;
        document.getElementById('weatherCond').textContent = data.condition;
        document.getElementById('weatherWind').textContent = `${data.wind_speed} m/s`;
        document.getElementById('weatherLocation').textContent = data.city || 'Santa Cruz, CLSU Area';
        document.getElementById('weatherLastSync').textContent = data.last_updated;

        // Map conditions to icons
        const iconMap = {
            'clear sky': 'fa-sun',
            'few clouds': 'fa-cloud-sun',
            'scattered clouds': 'fa-cloud',
            'broken clouds': 'fa-cloud',
            'shower rain': 'fa-cloud-showers-heavy',
            'rain': 'fa-cloud-rain',
            'thunderstorm': 'fa-bolt',
            'snow': 'fa-snowflake',
            'mist': 'fa-smog'
        };
        const icon = iconMap[data.condition.toLowerCase()] || 'fa-cloud';
        document.getElementById('weatherIcon').className = `fa-solid ${icon}`;
    } catch (e) {
        console.error('Weather fetch error:', e);
    }
}

syncData();
syncWeather();
setInterval(syncData, 5000);
setInterval(syncWeather, 900000); // Sync every 15 minutes

// Chart Logic
const ctx = document.getElementById('envChart').getContext('2d');
Chart.defaults.font.family = 'Inter, -apple-system, sans-serif';

let envChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [
            { label: 'Temperature (°C)', borderColor: '#e74c3c', backgroundColor: 'rgba(231, 76, 60, 0.1)', data: [], tension: 0.3, fill: true, yAxisID: 'y' },
            { label: 'Humidity (%)', borderColor: '#3498db', backgroundColor: 'rgba(52, 152, 219, 0.1)', data: [], tension: 0.3, fill: true, yAxisID: 'y1' }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' } },
            y: { 
                type: 'linear', display: true, position: 'left',
                grid: { color: 'rgba(255,255,255,0.05)' }
            },
            y1: {
                type: 'linear', display: true, position: 'right',
                grid: { drawOnChartArea: false }
            }
        }
    }
});

// Set default chart dates and load latest available data
const chartDateInput = document.getElementById('chartDateVal');

async function initChart() {
    // Default to today
    const today = new Date().toISOString().split('T')[0];
    chartDateInput.value = today;

    try {
        // Try to find the latest date with actual data
        const res = await fetch('api/get_latest_data_date.php?sensor=environment');
        const latest = await res.json();
        if (latest.latest_date) {
            chartDateInput.value = latest.latest_date;
            loadChartData(latest.latest_date);
        } else {
            loadChartData(today);
        }
    } catch (e) {
        loadChartData(today);
    }
}

initChart();

async function loadChartData(dateStr) {
    try {
        const res = await fetch(`api/environment/get_temp_humid_chart.php?date=${dateStr}`);
        const data = await res.json();
        
        if (!data || data.length === 0) {
            envChart.data.labels = [];
            envChart.data.datasets.forEach(d => d.data = []);
            envChart.update();
            return;
        }

        envChart.data.labels = data.map(d => {
            const dt = new Date(d.timestamp);
            return dt.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        });
        
        envChart.data.datasets[0].data = data.map(d => d.temp);
        envChart.data.datasets[1].data = data.map(d => d.hum);
        envChart.update();
    } catch (e) { console.error('Error loading chart:', e); }
}

document.getElementById('chartDateVal').addEventListener('change', (e) => loadChartData(e.target.value));
loadChartData(document.getElementById('chartDateVal').value);

// Export Data ExcelJS logic
document.getElementById('downloadSingleBtn').addEventListener('click', async () => {
    const dateStr = document.getElementById('chartDateVal').value;
    if (!envChart.data.labels.length) return alert('No data available to export for this date.');
    
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet('Temp & Humid Data');
    
    ws.addRow(['Smart Farm Temperature & Humidity Report']).font = { size: 14, bold: true };
    ws.addRow([`Date: ${dateStr}`]).font = { italic: true };
    ws.addRow([]);
    
    const headers = ['Time', 'Temp (°C)', 'Humidity (%)'];
    ws.addRow(headers).font = { bold: true };
    
    for (let i = 0; i < envChart.data.labels.length; i++) {
        ws.addRow([
            envChart.data.labels[i],
            envChart.data.datasets[0].data[i],
            envChart.data.datasets[1].data[i]
        ]);
    }
    
    const b64 = envChart.toBase64Image();
    const imgId = wb.addImage({ base64: b64, extension: 'png' });
    ws.addImage(imgId, { tl: { col: headers.length + 1, row: 1 }, ext: { width: 500, height: 300 } });
    
    const buf = await wb.xlsx.writeBuffer();
    saveAs(new Blob([buf]), `TempHumid_Report_${dateStr}.xlsx`);
});

// Monthly Average Export Logic
(function() {
    const startInput = document.getElementById('thStartMonth');
    const endInput   = document.getElementById('thEndMonth');
    const previewBtn = document.getElementById('thPreviewBtn');
    const csvBtn     = document.getElementById('thCsvBtn');
    const statusEl   = document.getElementById('thPreviewStatus');
    const table      = document.getElementById('thPreviewTable');
    const tbody      = document.getElementById('thPreviewBody');
    let previewData  = [];

    const activeStyle   = 'background:var(--accent-primary);color:#fff;border:none;cursor:pointer;';
    const disabledStyle = 'background:var(--glass-border);color:var(--text-secondary);border:1px solid var(--glass-border);cursor:default;';
    const btnBase       = 'padding:10px 24px;border-radius:var(--border-radius-sm);font-weight:600;height:42px;display:flex;align-items:center;gap:8px;';

    function invalidatePreview() {
        csvBtn.disabled = true;
        csvBtn.setAttribute('style', disabledStyle + btnBase);
        previewData = [];
        table.style.display = 'none';
        tbody.innerHTML = '';
        statusEl.textContent = '';
    }

    startInput.addEventListener('change', invalidatePreview);
    endInput.addEventListener('change', invalidatePreview);

    previewBtn.addEventListener('click', async function() {
        const sm = startInput.value, em = endInput.value;
        if (!sm || !em) { statusEl.textContent = 'Please select both Start Month and End Month.'; return; }
        if (sm > em)    { statusEl.textContent = 'Start Month cannot be after End Month.'; return; }
        statusEl.textContent = 'Loading\u2026';
        table.style.display = 'none';
        try {
            const res  = await fetch(`api/monthly_avg/get_th_monthly_avg.php?start_month=${sm}&end_month=${em}`);
            const data = await res.json();
            if (data.error || !data.length) {
                statusEl.textContent = data.error || 'No data for the selected range.';
                return;
            }
            previewData = data;
            tbody.innerHTML = data.map(r =>
                `<tr style="border-bottom:1px solid var(--glass-border);">
                    <td style="padding:8px 14px;font-weight:${r.month==='Overall Average'?'700':'400'};">${r.month}</td>
                    <td style="padding:8px 14px;text-align:right;">${r.avg_temperature}</td>
                    <td style="padding:8px 14px;text-align:right;">${r.avg_humidity}</td>
                </tr>`
            ).join('');
            table.style.display = 'table';
            statusEl.textContent = `Showing ${data.filter(r=>r.month!=='Overall Average').length} month(s).`;
            csvBtn.disabled = false;
            csvBtn.setAttribute('style', activeStyle + btnBase);
        } catch(e) {
            statusEl.textContent = 'Error loading data.';
        }
    });

    csvBtn.addEventListener('click', function() {
        if (!previewData.length) return;
        let csv = 'Month,Avg Temperature (\u00b0C),Avg Humidity (%)\n';
        previewData.forEach(r => { csv += `"${r.month}",${r.avg_temperature},${r.avg_humidity}\n`; });
        const blob = new Blob([csv], {type:'text/csv'});
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

</body>
</html>
