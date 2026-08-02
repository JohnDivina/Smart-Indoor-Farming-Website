<?php
session_start();
if (!isset($_SESSION["id"])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>NPK Sensor & Soil Analytics - Smart Farm</title>
    
    <!-- Custom NPK Styles -->
    <style>
        .tabs-wrapper {
            display: flex; gap: 8px; margin-bottom: 24px;
            background: rgba(255,255,255,0.02);
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

        /* NPK Grid */
        .npk-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
        
        .npk-card {
            background: var(--glass-bg); backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border); padding: 24px;
            border-radius: var(--border-radius-lg); text-align: center;
        }

        .npk-value { font-size: 32px; font-weight: 700; color: var(--text-primary); margin: 8px 0; font-variant-numeric: tabular-nums; }
        .npk-label { font-size: 14px; font-weight: 600; color: var(--text-secondary); }
        .npk-unit { font-size: 16px; color: var(--text-muted); font-weight: 400; }

        .nitrogen-accent { color: #0088cc !important; }
        .phosphorus-accent { color: #e67e22 !important; }
        .potassium-accent { color: #9b59b6 !important; }

        /* pH Scale */
        .ph-wrapper { padding: 32px 24px; }
        .ph-scale {
            height: 12px; border-radius: 6px;
            background: linear-gradient(to right, #e74c3c, #f1c40f, #2ecc71, #3498db, #9b59b6);
            position: relative; margin: 40px 0 20px;
        }
        .ph-indicator {
            position: absolute; top: -35px; width: 44px; height: 30px;
            background: var(--glass-bg); border: 2px solid var(--text-primary);
            border-radius: 6px; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; color: var(--text-primary);
            transform: translateX(-50%); transition: left 0.5s ease-out;
        }
        .ph-indicator::after {
            content: ''; position: absolute; bottom: -8px;
            border-width: 6px 6px 0; border-style: solid;
            border-color: var(--text-primary) transparent transparent;
        }
        .ph-labels {
            display: flex; justify-content: space-between;
            color: var(--text-secondary); font-size: 12px; font-weight: 600;
        }

        /* Chart Controls */
        .chart-controls {
            display: flex; gap: 16px; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap;
        }
        .input-group label { display: block; font-size: 13px; color: var(--text-secondary); margin-bottom: 6px; }
        .input-group input {
            background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border);
            color: var(--text-primary); padding: 10px 16px; border-radius: var(--border-radius-sm);
            font-family: inherit; font-size: 14px;
        }
        .btn-action {
            background: var(--accent-primary); border: none; color: #fff;
            padding: 10px 24px; border-radius: var(--border-radius-sm); font-weight: 600;
            cursor: pointer; height: 42px; display: flex; align-items: center; gap: 8px;
        }
        .btn-action:hover { box-shadow: 0 4px 12px rgba(25, 201, 104, 0.3); }

        .chart-container { height: 400px; width: 100%; }

        /* IG Story Export Button */
        .btn-story {
            background: linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            border: none; color: #fff;
            padding: 10px 20px; border-radius: var(--border-radius-sm); font-weight: 700;
            cursor: pointer; height: 42px; display: flex; align-items: center; gap: 8px;
            font-size: 13px; letter-spacing: 0.3px;
            box-shadow: 0 4px 15px rgba(220,39,67,0.35);
            transition: all 0.25s ease;
        }
        .btn-story:hover {
            box-shadow: 0 6px 20px rgba(220,39,67,0.5);
            transform: translateY(-1px);
        }
        .btn-story:disabled {
            background: var(--glass-border); color: var(--text-secondary);
            box-shadow: none; transform: none; cursor: default;
        }
        /* Story divider */
        .monthly-actions-row {
            display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        }

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
                <h1 style="margin-top: 12px;">Soil & Nutrient Analysis (NPK)</h1>
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
            <!-- Soil Physical Properties -->
            <h3 style="font-size: 16px; color: var(--text-secondary); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Physical Properties</h3>
            <div class="npk-grid">
                <div class="npk-card">
                    <div class="npk-label">Temperature</div>
                    <div class="npk-value" id="valTemp">-- <span class="npk-unit">°C</span></div>
                </div>
                <div class="npk-card">
                    <div class="npk-label">Moisture Content</div>
                    <div class="npk-value" id="valMoist">-- <span class="npk-unit">%</span></div>
                </div>
                <div class="npk-card">
                    <div class="npk-label">Electrical Conductivity</div>
                    <div class="npk-value" id="valEc">-- <span class="npk-unit">µS/cm</span></div>
                </div>
            </div>

            <!-- NPK Chemical Properties -->
            <h3 style="font-size: 16px; color: var(--text-secondary); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Macronutrients</h3>
            <div class="npk-grid">
                <div class="npk-card">
                    <div class="npk-label">Nitrogen (N)</div>
                    <div class="npk-value nitrogen-accent" id="valN">-- <span class="npk-unit">ppm</span></div>
                </div>
                <div class="npk-card">
                    <div class="npk-label">Phosphorus (P)</div>
                    <div class="npk-value phosphorus-accent" id="valP">-- <span class="npk-unit">ppm</span></div>
                </div>
                <div class="npk-card">
                    <div class="npk-label">Potassium (K)</div>
                    <div class="npk-value potassium-accent" id="valK">-- <span class="npk-unit">ppm</span></div>
                </div>
            </div>

            <!-- pH scale -->
            <div class="glass-panel ph-wrapper">
                <h3 style="font-size: 16px; color: var(--text-primary); text-align: center;">Soil pH Level</h3>
                <div class="ph-scale">
                    <div class="ph-indicator" id="phIndicator" style="left: 0%;">--</div>
                    <div class="ph-labels">
                        <span>0</span><span>2</span><span>4</span><span>6</span><span>7</span><span>8</span><span>10</span><span>12</span><span>14</span>
                    </div>
                </div>
            </div>

            <!-- Live Story Export -->
            <div style="display:flex; align-items:center; gap:12px; margin-top:16px; flex-wrap:wrap;">
                <span style="font-size:13px; color:var(--text-secondary); font-weight:600; letter-spacing:0.3px;">SHARE LIVE READING:</span>
                <button class="btn-story" id="liveStoryBtn">
                    <i class="fa-brands fa-instagram"></i> Export as IG Story
                </button>
            </div>
        </div>

        <!-- Historical Insights Tab -->
        <div id="historicalInsights" class="tab-content">
            <div class="glass-panel" style="padding: 24px;">
                <div class="chart-controls">
                    <div class="input-group">
                        <label>Select Date to Analyze</label>
                        <input type="date" id="chartDateVal" value="">
                    </div>
                    <button class="btn-action" id="downloadSingleBtn">
                        <i class="fa-solid fa-download"></i> Export Data & Chart
                    </button>

                    <div style="width: 1px; height: 40px; background: var(--glass-border); margin: 0 16px;"></div>

                    <div class="input-group">
                        <label>Range Start</label>
                        <input type="date" id="rangeStart">
                    </div>
                    <div class="input-group">
                        <label>Range End</label>
                        <input type="date" id="rangeEnd">
                    </div>
                    <button class="btn-action" id="downloadRangeBtn" style="background: var(--bg-hover); color: var(--text-primary); border: 1px solid var(--glass-border);">
                        <i class="fa-solid fa-file-excel"></i> Export Range
                    </button>
                </div>

                <div class="chart-container">
                    <canvas id="npkChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Average Export Panel -->
        <div class="glass-panel" style="padding: 24px; margin-top: 24px;">
            <h3 style="font-size:18px; font-weight:700; margin-bottom:8px;">Monthly Average Export</h3>
            <p style="font-size:13px; color:var(--text-secondary); margin-bottom:20px;">
                Select a month or range to preview and download average soil &amp; nutrient readings.
            </p>

            <div class="chart-controls">
                <div class="input-group">
                    <label for="npkStartMonth">Start Month</label>
                    <input type="month" id="npkStartMonth" style="min-width:160px;">
                </div>
                <div class="input-group">
                    <label for="npkEndMonth">End Month</label>
                    <input type="month" id="npkEndMonth" style="min-width:160px;">
                </div>
                <button class="btn-action" id="npkPreviewBtn">
                    <i class="fa-solid fa-magnifying-glass"></i> Preview
                </button>
                <button class="btn-action" id="npkCsvBtn" disabled
                    style="background:var(--glass-border); color:var(--text-secondary); border:1px solid var(--glass-border);">
                    <i class="fa-solid fa-download"></i> Download CSV
                </button>
                <button class="btn-story" id="npkStoryBtn" disabled>
                    <i class="fa-brands fa-instagram"></i> Monthly IG Story
                </button>
            </div>

            <div id="npkPreviewStatus" style="font-size:13px; color:var(--text-muted); margin-bottom:12px;"></div>
            <div style="overflow-x:auto;">
                <table id="npkPreviewTable" style="display:none; width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="background:rgba(25,201,104,0.08);">
                            <th style="padding:10px 12px; text-align:left; border-bottom:2px solid var(--glass-border); white-space:nowrap;">Month</th>
                            <th style="padding:10px 12px; text-align:right; border-bottom:2px solid var(--glass-border); white-space:nowrap;">Avg Temp (°C)</th>
                            <th style="padding:10px 12px; text-align:right; border-bottom:2px solid var(--glass-border); white-space:nowrap;">Avg Moisture (%)</th>
                            <th style="padding:10px 12px; text-align:right; border-bottom:2px solid var(--glass-border); white-space:nowrap;">Avg EC (µS/cm)</th>
                            <th style="padding:10px 12px; text-align:right; border-bottom:2px solid var(--glass-border); white-space:nowrap;">Avg pH</th>
                            <th style="padding:10px 12px; text-align:right; border-bottom:2px solid var(--glass-border); white-space:nowrap;">Avg N (ppm)</th>
                            <th style="padding:10px 12px; text-align:right; border-bottom:2px solid var(--glass-border); white-space:nowrap;">Avg P (ppm)</th>
                            <th style="padding:10px 12px; text-align:right; border-bottom:2px solid var(--glass-border); white-space:nowrap;">Avg K (ppm)</th>
                        </tr>
                    </thead>
                    <tbody id="npkPreviewBody"></tbody>
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
async function syncNpk() {
    try {
        const res = await fetch('api/npk/get_data.php');
        const data = await res.json();
        
        if (data.success && data.sensorData) {
            const sd = data.sensorData;
            document.getElementById('valTemp').innerHTML = `${sd.temp.toFixed(1)} <span class="npk-unit">°C</span>`;
            document.getElementById('valMoist').innerHTML = `${sd.moist.toFixed(1)} <span class="npk-unit">%</span>`;
            document.getElementById('valEc').innerHTML = `${sd.ec} <span class="npk-unit">µS/cm</span>`;
            document.getElementById('valN').innerHTML = `${sd.n} <span class="npk-unit">ppm</span>`;
            document.getElementById('valP').innerHTML = `${sd.p} <span class="npk-unit">ppm</span>`;
            document.getElementById('valK').innerHTML = `${sd.k} <span class="npk-unit">ppm</span>`;
            
            // pH logic
            const phVal = Number(sd.ph);
            const phIndicator = document.getElementById('phIndicator');
            phIndicator.textContent = phVal.toFixed(1);
            let posPercent = (phVal / 14) * 100;
            posPercent = Math.max(0, Math.min(100, posPercent));
            phIndicator.style.left = `${posPercent}%`;

            // Status logic
            const cStatus = document.getElementById('connStatus');
            const updateTs = document.getElementById('lastUpdateTs');
            updateTs.textContent = sd.timestamp;

            const isConnected = data.status === 'connected';
            const opacity = isConnected ? '1' : '0.5';

            // Visual Feedback for stale data
            const valueElements = ['valTemp', 'valMoist', 'valEc', 'valN', 'valP', 'valK', 'phIndicator'];
            valueElements.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.opacity = opacity;
            });

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

syncNpk();
setInterval(syncNpk, 5000);

// Chart Logic
const ctx = document.getElementById('npkChart').getContext('2d');
Chart.defaults.font.family = 'Inter, -apple-system, sans-serif';

let npkChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [
            { label: 'Temp (°C)', borderColor: '#e74c3c', data: [], tension: 0.3 },
            { label: 'Moisture (%)', borderColor: '#3498db', data: [], tension: 0.3 },
            { label: 'Nitrogen', borderColor: '#0088cc', data: [], tension: 0.3 },
            { label: 'Phosphorus', borderColor: '#e67e22', data: [], tension: 0.3 },
            { label: 'Potassium', borderColor: '#9b59b6', data: [], tension: 0.3 }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' } },
            y: { grid: { color: 'rgba(255,255,255,0.05)' } }
        }
    }
});

// Set default chart dates and load latest available data
const chartDateInput = document.getElementById('chartDateVal');
const rangeStartInput = document.getElementById('rangeStart');
const rangeEndInput = document.getElementById('rangeEnd');

async function initChart() {
    // Default to today
    const today = new Date().toISOString().split('T')[0];
    chartDateInput.value = today;
    rangeStartInput.value = today;
    rangeEndInput.value = today;

    try {
        // Try to find the latest date with actual data
        const res = await fetch('api/get_latest_data_date.php?sensor=npk');
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
        const res = await fetch(`api/npk/get_data_by_date.php?date=${dateStr}`);
        const data = await res.json();
        
        if (!data || data.length === 0) {
            npkChart.data.labels = [];
            npkChart.data.datasets.forEach(d => d.data = []);
            npkChart.update();
            return;
        }

        npkChart.data.labels = data.map(d => {
            const dt = new Date(d.timestamp);
            return dt.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        });
        
        npkChart.data.datasets[0].data = data.map(d => d.temp);
        npkChart.data.datasets[1].data = data.map(d => d.moist);
        npkChart.data.datasets[2].data = data.map(d => d.n);
        npkChart.data.datasets[3].data = data.map(d => d.p);
        npkChart.data.datasets[4].data = data.map(d => d.k);
        npkChart.update();
    } catch (e) { console.error('Error loading chart:', e); }
}

document.getElementById('chartDateVal').addEventListener('change', (e) => loadChartData(e.target.value));
loadChartData(document.getElementById('chartDateVal').value);

// Export Data ExcelJS logic
document.getElementById('downloadSingleBtn').addEventListener('click', async () => {
    const dateStr = document.getElementById('chartDateVal').value;
    if (!npkChart.data.labels.length) return alert('No data available to export for this date.');
    
    // Quick & robust direct client-side export using current chart data
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet('NPK Data');
    
    ws.addRow(['Smart Farm NPK Sensor Report']).font = { size: 14, bold: true };
    ws.addRow([`Date: ${dateStr}`]).font = { italic: true };
    ws.addRow([]);
    
    const headers = ['Time', 'Temp(°C)', 'Moisture(%)', 'Nitrogen(ppm)', 'Phosphorus(ppm)', 'Potassium(ppm)'];
    ws.addRow(headers).font = { bold: true };
    
    for (let i = 0; i < npkChart.data.labels.length; i++) {
        ws.addRow([
            npkChart.data.labels[i],
            npkChart.data.datasets[0].data[i],
            npkChart.data.datasets[1].data[i],
            npkChart.data.datasets[2].data[i],
            npkChart.data.datasets[3].data[i],
            npkChart.data.datasets[4].data[i]
        ]);
    }
    
    // Attach chart
    const b64 = npkChart.toBase64Image();
    const imgId = wb.addImage({ base64: b64, extension: 'png' });
    ws.addImage(imgId, { tl: { col: headers.length + 1, row: 1 }, ext: { width: 500, height: 300 } });
    
    const buf = await wb.xlsx.writeBuffer();
    saveAs(new Blob([buf]), `NPK_Report_${dateStr}.xlsx`);
});

document.getElementById('downloadRangeBtn').addEventListener('click', async () => {
    const start = document.getElementById('rangeStart').value;
    const end = document.getElementById('rangeEnd').value;
    
    if (!start || !end) return alert('Please select both start and end dates.');

    try {
        const res = await fetch(`api/npk/get_data_range.php?start=${start}&end=${end}`);
        const data = await res.json();
        
        if (!data || data.length === 0) return alert('No data found for this range.');

        const wb = new ExcelJS.Workbook();
        const ws = wb.addWorksheet('NPK Range Data');
        
        ws.addRow(['Smart Farm NPK Sensor Range Report']).font = { size: 14, bold: true };
        ws.addRow([`Range: ${start} to ${end}`]).font = { italic: true };
        ws.addRow([]);
        
        const headers = ['Timestamp', 'Temp(°C)', 'Moisture(%)', 'pH', 'EC(µS/cm)', 'Nitrogen(ppm)', 'Phosphorus(ppm)', 'Potassium(ppm)'];
        ws.addRow(headers).font = { bold: true };
        
        data.forEach(d => {
            ws.addRow([d.timestamp, d.temp, d.moist, d.ph, d.ec, d.n, d.p, d.k]);
        });
        
        const buf = await wb.xlsx.writeBuffer();
        saveAs(new Blob([buf]), `NPK_Range_Report_${start}_to_${end}.xlsx`);
    } catch (e) {
        console.error('Export error:', e);
        alert('Failed to export data range.');
    }
});

// Monthly Average Export Logic
(function() {
    const startInput = document.getElementById('npkStartMonth');
    const endInput   = document.getElementById('npkEndMonth');
    const previewBtn = document.getElementById('npkPreviewBtn');
    const csvBtn     = document.getElementById('npkCsvBtn');
    const storyBtn   = document.getElementById('npkStoryBtn');
    const statusEl   = document.getElementById('npkPreviewStatus');
    const table      = document.getElementById('npkPreviewTable');
    const tbody      = document.getElementById('npkPreviewBody');
    let previewData  = [];

    const csvBtnActiveStyle  = 'background:var(--accent-primary);color:#fff;border:none;';
    const csvBtnDisabledStyle = 'background:var(--glass-border);color:var(--text-secondary);border:1px solid var(--glass-border);';

    function invalidatePreview() {
        csvBtn.disabled = true;
        csvBtn.setAttribute('style', csvBtnDisabledStyle + ' padding:10px 24px;border-radius:var(--border-radius-sm);font-weight:600;cursor:default;height:42px;display:flex;align-items:center;gap:8px;');
        storyBtn.disabled = true;
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
            const res  = await fetch(`api/monthly_avg/get_npk_monthly_avg.php?start_month=${sm}&end_month=${em}`);
            const data = await res.json();
            if (data.error || !data.length) {
                statusEl.textContent = data.error || 'No data for the selected range.';
                csvBtn.disabled = true;
                return;
            }
            previewData = data;
            const isBold = r => r.month === 'Overall Average' ? '700' : '400';
            tbody.innerHTML = data.map(r =>
                `<tr style="border-bottom:1px solid var(--glass-border);">
                    <td style="padding:8px 12px;font-weight:${isBold(r)};white-space:nowrap;">${r.month}</td>
                    <td style="padding:8px 12px;text-align:right;">${r.avg_temperature}</td>
                    <td style="padding:8px 12px;text-align:right;">${r.avg_moisture}</td>
                    <td style="padding:8px 12px;text-align:right;">${r.avg_ec}</td>
                    <td style="padding:8px 12px;text-align:right;">${r.avg_ph}</td>
                    <td style="padding:8px 12px;text-align:right;">${r.avg_nitrogen}</td>
                    <td style="padding:8px 12px;text-align:right;">${r.avg_phosphorus}</td>
                    <td style="padding:8px 12px;text-align:right;">${r.avg_potassium}</td>
                </tr>`
            ).join('');
            table.style.display = 'table';
            statusEl.textContent = `Showing ${data.filter(r=>r.month!=='Overall Average').length} month(s).`;
            csvBtn.disabled = false;
            csvBtn.setAttribute('style', csvBtnActiveStyle + ' padding:10px 24px;border-radius:var(--border-radius-sm);font-weight:600;cursor:pointer;height:42px;display:flex;align-items:center;gap:8px;');
            storyBtn.disabled = false;
        } catch(e) {
            statusEl.textContent = 'Error loading data.';
            csvBtn.disabled = true;
        }
    });

    csvBtn.addEventListener('click', function() {
        if (!previewData.length) return;
        let csv = 'Month,Avg Temp (°C),Avg Moisture (%),Avg EC (µS/cm),Avg pH,Avg N (ppm),Avg P (ppm),Avg K (ppm)\n';
        previewData.forEach(r => {
            csv += `"${r.month}",${r.avg_temperature},${r.avg_moisture},${r.avg_ec},${r.avg_ph},${r.avg_nitrogen},${r.avg_phosphorus},${r.avg_potassium}\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href = url;
        const sm = startInput.value, em = endInput.value;
        a.download = sm === em ? `NPK_Monthly_Avg_${sm}.csv` : `NPK_Monthly_Avg_${sm}_to_${em}.csv`;
        a.click();
        URL.revokeObjectURL(url);
    });

    // ━━━━━━━━━━━ Monthly IG Story Export ━━━━━━━━━━━
    storyBtn.addEventListener('click', function() {
        if (!previewData.length) return;
        const sm = startInput.value, em = endInput.value;
        const monthLabel = sm === em ? sm : `${sm} \u2192 ${em}`;
        const rows = previewData.filter(r => r.month !== 'Overall Average');
        const overall = previewData.find(r => r.month === 'Overall Average') || rows[0] || {};
        const filename = `NPK_Monthly_Story_${sm === em ? sm : sm+'_to_'+em}.png`;
        generateStoryCanvas({
            badge: 'MONTHLY AVERAGE', badgeColor: '#16a34a',
            title: 'Soil & Nutrient', subtitle: monthLabel,
            npk: { n: overall.avg_nitrogen ?? '--', p: overall.avg_phosphorus ?? '--', k: overall.avg_potassium ?? '--' },
            secondary: [
                { label: 'Avg EC', value: overall.avg_ec ?? '--', unit: '\u00b5S/cm' },
                { label: 'Avg pH', value: overall.avg_ph ?? '--', unit: 'pH' },
                { label: 'Avg Temp', value: overall.avg_temperature ?? '--', unit: '\u00b0C' },
                { label: 'Avg Moisture', value: overall.avg_moisture ?? '--', unit: '%' },
            ],
            tableRows: rows.slice(0, 8),
            filename
        });
    });

})();

// ━━━━━━━━━━━ Live Reading IG Story Export ━━━━━━━━━━━
document.getElementById('liveStoryBtn').addEventListener('click', function() {
    // Read current displayed values safely (strip HTML tags)
    function readVal(id) {
        const el = document.getElementById(id);
        if (!el) return '--';
        // Clone, remove span children, get text
        const cl = el.cloneNode(true);
        cl.querySelectorAll('span').forEach(s => s.remove());
        return cl.textContent.trim() || '--';
    }
    const now = new Date().toLocaleString('en-US', { month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit' });
    generateStoryCanvas({
        badge: 'LIVE READING', badgeColor: '#22c55e',
        title: 'Soil & Nutrient', subtitle: `As of ${now}`,
        npk: {
            n: readVal('valN'),
            p: readVal('valP'),
            k: readVal('valK')
        },
        secondary: [
            { label: 'Electrical Cond.', value: readVal('valEc'), unit: '\u00b5S/cm' },
            { label: 'Soil pH', value: document.getElementById('phIndicator')?.textContent?.trim() || '--', unit: 'pH' },
            { label: 'Temperature', value: readVal('valTemp'), unit: '\u00b0C' },
            { label: 'Moisture', value: readVal('valMoist'), unit: '%' },
        ],
        tableRows: [],
        filename: `NPK_Live_Story_${new Date().toISOString().slice(0,10)}.png`
    });
});

// ━━━━━━━━━━━ Shared Canvas Story Generator ━━━━━━━━━━━
function generateStoryCanvas({ badge, badgeColor, title, subtitle, npk, secondary, tableRows, filename }) {
    const W = 1080, H = 1920;
    const canvas = document.createElement('canvas');
    canvas.width = W; canvas.height = H;
    const ctx = canvas.getContext('2d');

    // ── Shared helpers ──────────────────────────────────
    function rrect(x, y, w, h, r) {
        if (typeof r === 'number') r = {tl:r, tr:r, br:r, bl:r};
        ctx.beginPath();
        ctx.moveTo(x + r.tl, y);
        ctx.lineTo(x + w - r.tr, y);
        ctx.quadraticCurveTo(x+w, y, x+w, y+r.tr);
        ctx.lineTo(x+w, y+h-r.br);
        ctx.quadraticCurveTo(x+w, y+h, x+w-r.br, y+h);
        ctx.lineTo(x+r.bl, y+h);
        ctx.quadraticCurveTo(x, y+h, x, y+h-r.bl);
        ctx.lineTo(x, y+r.tl);
        ctx.quadraticCurveTo(x, y, x+r.tl, y);
        ctx.closePath();
    }
    // Draw text perfectly centered inside a rectangle
    function ctext(text, x, y, w, h, font, color) {
        ctx.save();
        ctx.font = font;
        ctx.fillStyle = color;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(String(text), x + w/2, y + h/2);
        ctx.restore();
    }
    // Draw text left-aligned, vertically centered in a row
    function ltext(text, x, y, h, font, color) {
        ctx.save();
        ctx.font = font;
        ctx.fillStyle = color;
        ctx.textAlign = 'left';
        ctx.textBaseline = 'middle';
        ctx.fillText(String(text), x, y + h/2);
        ctx.restore();
    }
    function rtext(text, x, y, w, h, font, color) {
        ctx.save();
        ctx.font = font;
        ctx.fillStyle = color;
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';
        ctx.fillText(String(text), x + w, y + h/2);
        ctx.restore();
    }

    // ── Background: light green gradient ─────────────────
    const bg = ctx.createLinearGradient(0, 0, 0, H);
    bg.addColorStop(0,    '#f0fdf4');
    bg.addColorStop(0.35, '#dcfce7');
    bg.addColorStop(0.7,  '#f0fdf4');
    bg.addColorStop(1,    '#ecfdf5');
    ctx.fillStyle = bg;
    ctx.fillRect(0, 0, W, H);

    // Subtle decorative blobs
    function blob(x, y, r, color, a) {
        const g = ctx.createRadialGradient(x, y, 0, x, y, r);
        g.addColorStop(0, color + Math.round(a*255).toString(16).padStart(2,'0'));
        g.addColorStop(1, color + '00');
        ctx.fillStyle = g;
        ctx.beginPath(); ctx.arc(x, y, r, 0, Math.PI*2); ctx.fill();
    }
    blob(1000, 100,  420, '#bbf7d0', 0.9);
    blob(80,   1800, 360, '#a7f3d0', 0.7);
    blob(540,  950,  500, '#d1fae5', 0.5);

    // ── Header bar ───────────────────────────────────────
    const headerGrad = ctx.createLinearGradient(0, 0, W, 0);
    headerGrad.addColorStop(0, '#15803d');
    headerGrad.addColorStop(1, '#16a34a');
    ctx.fillStyle = headerGrad;
    ctx.fillRect(0, 0, W, 210);

    // Farm icon badge (white circle)
    ctx.save();
    ctx.fillStyle = 'rgba(255,255,255,0.2)';
    ctx.beginPath(); ctx.arc(108, 105, 64, 0, Math.PI*2); ctx.fill();
    ctx.restore();
    ctext('🌿', 44, 37, 128, 128, '68px Arial', '#fff');

    // Title + subtitle right of icon
    ltext('Smart Farm', 200, 38, 68, 'bold 58px Arial', '#ffffff');
    ltext(title, 200, 113, 46, '40px Arial', 'rgba(255,255,255,0.82)');

    // Badge pill (e.g. "LIVE READING")
    ctx.save();
    ctx.fillStyle = 'rgba(255,255,255,0.18)';
    rrect(200, 167, 280, 38, 19); ctx.fill();
    ctx.restore();
    ctext(badge, 200, 167, 280, 38, 'bold 20px Arial', '#ffffff');

    // Subtitle (date/period) — right side of header
    rtext(subtitle, 0, 120, W-60, 50, '28px Arial', 'rgba(255,255,255,0.75)');

    // ── Section: Macronutrients (N / P / K) ──────────────
    const SEC_PAD = 60; // horizontal padding
    const SEC_W = W - SEC_PAD*2;
    let y = 250;

    // Section title
    const secLabelH = 50;
    ltext('MACRONUTRIENTS', SEC_PAD, y, secLabelH, 'bold 26px Arial', '#15803d');
    y += secLabelH + 10;

    const npkColors = ['#0369a1','#ea580c','#7c3aed'];
    const npkLabels = ['Nitrogen (N)','Phosphorus (P)','Potassium (K)'];
    const npkVals   = [npk.n, npk.p, npk.k];
    const cw = 310, ch = 250, cgap = 20;
    const ctotalW = 3*cw + 2*cgap;
    const cStartX = SEC_PAD + (SEC_W - ctotalW)/2;
    npkLabels.forEach((label, i) => {
        const cx = cStartX + i*(cw+cgap);
        const col = npkColors[i];
        // Card shadow effect
        ctx.save();
        ctx.fillStyle = 'rgba(0,0,0,0.06)';
        rrect(cx+4, y+4, cw, ch, 20); ctx.fill();
        ctx.restore();
        // Card bg: white
        ctx.save();
        ctx.fillStyle = '#ffffff';
        rrect(cx, y, cw, ch, 20); ctx.fill();
        // Left accent stripe
        ctx.fillStyle = col;
        rrect(cx, y, 10, ch, {tl:20,tr:0,br:0,bl:20}); ctx.fill();
        ctx.restore();
        // Label row
        ctext(label, cx+10, y, cw-10, 66, 'bold 26px Arial', '#374151');
        // Divider
        ctx.fillStyle = '#e5e7eb';
        ctx.fillRect(cx+10, y+66, cw-20, 1);
        // Big value — takes up most of card height
        ctext(npkVals[i], cx+10, y+67, cw-10, 130, 'bold 80px Arial', col);
        // Unit
        ctext('ppm', cx+10, y+197, cw-10, 53, '28px Arial', '#9ca3af');
    });
    y += ch + 28;

    // ── Section: Secondary stats (2×2 grid) ──────────────
    ltext('SOIL PROPERTIES', SEC_PAD, y, 50, 'bold 26px Arial', '#15803d');
    y += 60;
    const secColors  = ['#059669','#0284c7','#dc2626','#0891b2'];
    const scw = (SEC_W - 20) / 2, sch = 185, scgap = 20;
    secondary.forEach((card, i) => {
        const col  = secColors[i % secColors.length];
        const cx    = SEC_PAD + (i%2)*(scw+scgap);
        const cy    = y + Math.floor(i/2)*(sch+scgap);
        // Shadow
        ctx.save();
        ctx.fillStyle = 'rgba(0,0,0,0.05)';
        rrect(cx+3, cy+3, scw, sch, 18); ctx.fill();
        ctx.restore();
        // Card
        ctx.save();
        ctx.fillStyle = '#ffffff';
        rrect(cx, cy, scw, sch, 18); ctx.fill();
        // Top accent bar
        ctx.fillStyle = col;
        rrect(cx, cy, scw, 8, {tl:18,tr:18,br:0,bl:0}); ctx.fill();
        ctx.restore();
        // Label
        ctext(card.label, cx, cy+8, scw, 50, 'bold 24px Arial', '#6b7280');
        // Value
        ctext(card.value, cx, cy+58, scw, 80, 'bold 66px Arial', col);
        // Unit
        ctext(card.unit, cx, cy+138, scw, 47, '24px Arial', '#9ca3af');
    });
    y += 2*(sch+scgap) + 28;

    // ── Section: Monthly breakdown table ─────────────────
    if (tableRows.length > 1) {
        ltext('MONTHLY BREAKDOWN', SEC_PAD, y, 50, 'bold 26px Arial', '#15803d');
        y += 60;
        const cols   = ['Month','N','P','K','EC','pH'];
        const colRatios = [0.34, 0.12, 0.12, 0.12, 0.16, 0.12];
        const rowH   = 68;
        const tH     = (tableRows.length + 1) * rowH + 16;
        // Table bg
        ctx.save();
        ctx.fillStyle = '#ffffff';
        rrect(SEC_PAD, y, SEC_W, tH, 16); ctx.fill();
        ctx.strokeStyle = '#d1fae5';
        ctx.lineWidth = 2;
        rrect(SEC_PAD, y, SEC_W, tH, 16); ctx.stroke();
        ctx.restore();
        // Header row
        ctx.fillStyle = '#dcfce7';
        rrect(SEC_PAD, y, SEC_W, rowH, {tl:16,tr:16,br:0,bl:0}); ctx.fill();
        let hx = SEC_PAD + 16;
        cols.forEach((c, ci) => {
            const cw2 = SEC_W * colRatios[ci];
            if (ci === 0) ltext(c, hx+4, y, rowH, 'bold 24px Arial', '#15803d');
            else rtext(c, hx, y, cw2-8, rowH, 'bold 24px Arial', '#15803d');
            hx += cw2;
        });
        // Data rows
        tableRows.forEach((r, ri) => {
            const ry = y + (ri+1)*rowH;
            if (ri%2===0) {
                ctx.fillStyle = '#f9fafb';
                ctx.fillRect(SEC_PAD, ry, SEC_W, rowH);
            }
            // Bottom border
            ctx.fillStyle = '#e5e7eb';
            ctx.fillRect(SEC_PAD, ry+rowH-1, SEC_W, 1);
            const vals = [r.month, r.avg_nitrogen, r.avg_phosphorus, r.avg_potassium, r.avg_ec, r.avg_ph];
            let dx = SEC_PAD + 16;
            vals.forEach((v, ci) => {
                const cw2 = SEC_W * colRatios[ci];
                const txt = String(v ?? '--');
                if (ci === 0) ltext(txt, dx+4, ry, rowH, 'bold 24px Arial', '#111827');
                else rtext(txt, dx, ry, cw2-8, rowH, '24px Arial', '#374151');
                dx += cw2;
            });
        });
        y += tH + 28;
    }

    // ── NPK bar chart ────────────────────────────────────
    const barSectionH = 260;
    const barData  = [
        { label:'N (ppm)', value: parseFloat(npk.n)||0, color:'#0369a1' },
        { label:'P (ppm)', value: parseFloat(npk.p)||0, color:'#ea580c' },
        { label:'K (ppm)', value: parseFloat(npk.k)||0, color:'#7c3aed' },
    ];
    const maxVal   = Math.max(...barData.map(d=>d.value), 10);
    // Place the bar section at max(current y, 1580) so it always lands in lower portion
    const barBlockY = Math.min(Math.max(y, 1560), H - barSectionH - 160);
    if (barBlockY + barSectionH < H - 100) {
        ctx.save();
        ctx.fillStyle = '#ffffff';
        rrect(SEC_PAD, barBlockY, SEC_W, barSectionH, 20); ctx.fill();
        ctx.strokeStyle = '#d1fae5';
        ctx.lineWidth = 2;
        rrect(SEC_PAD, barBlockY, SEC_W, barSectionH, 20); ctx.stroke();
        ctx.restore();
        ltext('NPK CHART', SEC_PAD+20, barBlockY, 54, 'bold 26px Arial', '#15803d');
        const bw = 230, bh = 170, bgap = 30;
        const btotalW = 3*bw + 2*bgap;
        const bstartX = SEC_PAD + (SEC_W - btotalW)/2;
        barData.forEach((b, i) => {
            const bx = bstartX + i*(bw+bgap);
            const barTop = barBlockY + 60;
            const trackH = bh - 40;
            const fillH  = Math.max(4, Math.round((b.value/maxVal)*trackH));
            // Track
            ctx.fillStyle = '#f3f4f6';
            rrect(bx, barTop, bw, trackH, 10); ctx.fill();
            // Fill
            const grad = ctx.createLinearGradient(0, barTop+trackH-fillH, 0, barTop+trackH);
            grad.addColorStop(0, b.color);
            grad.addColorStop(1, b.color+'88');
            ctx.fillStyle = grad;
            rrect(bx, barTop+trackH-fillH, bw, fillH, {tl:10,tr:10,br:0,bl:0}); ctx.fill();
            // Value on top of bar
            ctext(b.value, bx, barTop+trackH-fillH-40, bw, 40, 'bold 30px Arial', b.color);
            // Label
            ctext(b.label, bx, barTop+trackH, bw, 40, '24px Arial', '#6b7280');
        });
    }

    // ── Footer ────────────────────────────────────────────
    const footerY = H - 130;
    ctx.save();
    const footerGrad = ctx.createLinearGradient(0, footerY, W, footerY);
    footerGrad.addColorStop(0, '#15803d');
    footerGrad.addColorStop(1, '#16a34a');
    ctx.fillStyle = footerGrad;
    ctx.fillRect(0, footerY, W, 130);
    ctx.restore();
    ctext('Smart Farm Dashboard', 0, footerY, W, 65, 'bold 30px Arial', 'rgba(255,255,255,0.9)');
    ctext('Generated ' + new Date().toLocaleDateString('en-US', {month:'long', day:'numeric', year:'numeric'}), 0, footerY+65, W, 65, '26px Arial', 'rgba(255,255,255,0.65)');

    // ── Download ──────────────────────────────────────────
    const dataUrl = canvas.toDataURL('image/png');
    const isIos = /ipad|iphone|ipod/i.test(navigator.userAgent);
    if (isIos) {
        const w = window.open();
        w.document.write(`<!DOCTYPE html><html><body style="margin:0;background:#f0fdf4;">`);
        w.document.write(`<img src="${dataUrl}" style="max-width:100%;display:block;margin:0 auto;">`);
        w.document.write(`<p style="text-align:center;font-family:sans-serif;color:#15803d;padding:16px;font-size:15px;">Press and hold the image \u2192 tap <strong>Save Image</strong> to save.</p></body></html>`);
    } else {
        const a = document.createElement('a');
        a.href = dataUrl;
        a.download = filename;
        a.click();
    }
}


// Theme
</script>

</body>
</html>
