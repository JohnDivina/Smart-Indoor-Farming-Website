<?php
session_start();
if (!isset($_SESSION["id"])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Light Intensity - Smart Farm</title>
    
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

        /* Light Grid */
        .sensor-card {
            background: var(--glass-bg); backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border); padding: 40px 24px;
            border-radius: var(--border-radius-lg); text-align: center;
            max-width: 500px; margin: 0 auto;
        }

        .sensor-icon { font-size: 64px; margin-bottom: 16px; color: #f1c40f; text-shadow: 0 0 20px rgba(241, 196, 15, 0.4); }
        .sensor-value { font-size: 64px; font-weight: 700; color: var(--text-primary); margin: 8px 0; font-variant-numeric: tabular-nums; }
        .sensor-label { font-size: 16px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;}
        .sensor-unit { font-size: 24px; color: var(--text-muted); font-weight: 400; }

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

        .chart-container { height: 450px; width: 100%; padding: 16px; background: rgba(0,0,0,0.01); border-radius: var(--border-radius-md); }

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
                <h1 style="margin-top: 12px;">Light Intensity Sensor</h1>
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
            <div class="sensor-card">
                <i class="fa-solid fa-sun sensor-icon"></i>
                <div class="sensor-label">Average Light Intensity</div>
                <div class="sensor-value" id="valLux">-- <span class="sensor-unit">lux</span></div>
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
                Select a month or range to preview and download average Light Intensity readings.
            </p>
            <div class="chart-controls">
                <div class="input-group">
                    <label for="lightStartMonth">Start Month</label>
                    <input type="month" id="lightStartMonth" style="min-width:160px;">
                </div>
                <div class="input-group">
                    <label for="lightEndMonth">End Month</label>
                    <input type="month" id="lightEndMonth" style="min-width:160px;">
                </div>
                <button class="btn-action" id="lightPreviewBtn">
                    <i class="fa-solid fa-magnifying-glass"></i> Preview
                </button>
                <button class="btn-action" id="lightCsvBtn" disabled
                    style="background:var(--glass-border); color:var(--text-secondary); border:1px solid var(--glass-border); cursor:default;">
                    <i class="fa-solid fa-download"></i> Download CSV
                </button>
            </div>
            <div id="lightPreviewStatus" style="font-size:13px; color:var(--text-muted); margin-bottom:12px;"></div>
            <div style="overflow-x:auto;">
                <table id="lightPreviewTable" style="display:none; width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="background:rgba(241,196,15,0.08);">
                            <th style="padding:10px 14px; text-align:left; border-bottom:2px solid var(--glass-border);">Month</th>
                            <th style="padding:10px 14px; text-align:right; border-bottom:2px solid var(--glass-border);">Avg Light Intensity (Avg of Hourly Avg, Lux)</th>
                        </tr>
                    </thead>
                    <tbody id="lightPreviewBody"></tbody>
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
        const res = await fetch('api/environment/get_light.php');
        const data = await res.json();
        
        if (data.success) {
            const isConnected = data.status === 'connected';
            const opacity = isConnected ? '1' : '0.5';
            
            const vEl = document.getElementById('valLux');
            vEl.innerHTML = `${Number(data.lux.toFixed(1)).toLocaleString()} <span class="sensor-unit">lux</span>`;
            vEl.style.opacity = opacity;

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

syncData();
setInterval(syncData, 5000);

// Chart Logic
const ctx = document.getElementById('envChart').getContext('2d');
Chart.defaults.font.family = 'Inter, -apple-system, sans-serif';

let envChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [
            { label: 'Light Intensity (Lux)', borderColor: '#f1c40f', backgroundColor: 'rgba(241, 196, 15, 0.1)', data: [], tension: 0.3, fill: true }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' } },
            y: { 
                beginAtZero: true,
                grid: { color: 'rgba(255,255,255,0.05)' }
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
        const res = await fetch('api/get_latest_data_date.php?sensor=light');
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
        const res = await fetch(`api/environment/get_light_chart.php?date=${dateStr}`);
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
        
        envChart.data.datasets[0].data = data.map(d => d.lux);
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
    const ws = wb.addWorksheet('Light Data');
    
    ws.addRow(['Smart Farm Light Intensity Report']).font = { size: 14, bold: true };
    ws.addRow([`Date: ${dateStr}`]).font = { italic: true };
    ws.addRow([]);
    
    const headers = ['Time', 'Light Intensity (Lux)'];
    ws.addRow(headers).font = { bold: true };
    
    for (let i = 0; i < envChart.data.labels.length; i++) {
        ws.addRow([
            envChart.data.labels[i],
            envChart.data.datasets[0].data[i]
        ]);
    }
    
    const b64 = envChart.toBase64Image();
    const imgId = wb.addImage({ base64: b64, extension: 'png' });
    ws.addImage(imgId, { tl: { col: headers.length + 1, row: 1 }, ext: { width: 500, height: 300 } });
    
    const buf = await wb.xlsx.writeBuffer();
    saveAs(new Blob([buf]), `LightIntensity_Report_${dateStr}.xlsx`);
});

// Monthly Average Export Logic
(function() {
    const startInput = document.getElementById('lightStartMonth');
    const endInput   = document.getElementById('lightEndMonth');
    const previewBtn = document.getElementById('lightPreviewBtn');
    const csvBtn     = document.getElementById('lightCsvBtn');
    const statusEl   = document.getElementById('lightPreviewStatus');
    const table      = document.getElementById('lightPreviewTable');
    const tbody      = document.getElementById('lightPreviewBody');
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
            const res  = await fetch(`api/monthly_avg/get_light_monthly_avg.php?start_month=${sm}&end_month=${em}`);
            const data = await res.json();
            if (data.error || !data.length) {
                statusEl.textContent = data.error || 'No data for the selected range.';
                return;
            }
            previewData = data;
            tbody.innerHTML = data.map(r =>
                `<tr style="border-bottom:1px solid var(--glass-border);">
                    <td style="padding:8px 14px;font-weight:${r.month==='Overall Average'?'700':'400'};">${r.month}</td>
                    <td style="padding:8px 14px;text-align:right;">${r.avg_lux}</td>
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
        let csv = 'Month,Avg Light Intensity (Avg of Hourly Avg\u002C Lux)\n';
        previewData.forEach(r => { csv += `"${r.month}",${r.avg_lux}\n`; });
        const blob = new Blob([csv], {type:'text/csv'});
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href = url;
        const sm = startInput.value, em = endInput.value;
        a.download = sm === em ? `Light_Monthly_Avg_${sm}.csv` : `Light_Monthly_Avg_${sm}_to_${em}.csv`;
        a.click();
        URL.revokeObjectURL(url);
    });
})();

// Theme
</script>

</body>
</html>
