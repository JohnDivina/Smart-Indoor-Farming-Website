<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensor Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .status {
            font-weight: bold;
            margin-bottom: 20px;
        }
        .status.connected {
            color: green;
        }
        .status.disconnected {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Light Sensor Dashboard</h1>
    <div id="lightStatusDiv" class="status"></div>
    <div id="lightHeartbeatDiv"></div>
    <table id="lightDataTable">
        <thead>
            <tr>
                <th>Hourly Average (lux)</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <h1>NPK Sensor Dashboard</h1>
    <div id="npkStatusDiv" class="status"></div>
    <div id="npkHeartbeatDiv"></div>
    <table id="npkDataTable">
        <thead>
            <tr>
                <th>Temp</th>
                <th>Moist</th>
                <th>pH</th>
                <th>EC</th>
                <th>N</th>
                <th>P</th>
                <th>K</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <script>
        // Function to update light sensor data
        function updateData_light() {
            fetch('LIGHTINTENSITYSENSOR/get_data.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('lightStatusDiv').className = 'status ' + data.status;
                    document.getElementById('lightStatusDiv').textContent = 'Current Status: ' + data.status.charAt(0).toUpperCase() + data.status.slice(1);
                    document.getElementById('lightHeartbeatDiv').textContent = 'Last Heartbeat: ' + data.lastHeartbeat;

                    let tableBody = document.querySelector('#lightDataTable tbody');
                    tableBody.innerHTML = ''; // Clear existing data

                    if (data.tableData.length === 0) {
                        let tr = document.createElement('tr');
                        let td = document.createElement('td');
                        td.textContent = 'No Output';
                        td.colSpan = 2; // Span across both columns
                        td.style.textAlign = 'center';
                        tr.appendChild(td);
                        tableBody.appendChild(tr);
                    } else {
                        data.tableData.forEach(row => {
                            let tr = document.createElement('tr');
                            tr.innerHTML = `<td>${row.hourlyAverage}</td><td>${row.timestamp}</td>`;
                            tableBody.appendChild(tr);
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Function to update NPK sensor data
        function updateData_npk() {
            fetch('NPKSENSOR/get_data.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('npkStatusDiv').className = 'status ' + data.status;
                    document.getElementById('npkStatusDiv').textContent = 'Current Status: ' + data.status.charAt(0).toUpperCase() + data.status.slice(1);
                    document.getElementById('npkHeartbeatDiv').textContent = 'Last Heartbeat: ' + data.lastHeartbeat;

                    let tableBody = document.querySelector('#npkDataTable tbody');
                    tableBody.innerHTML = ''; // Clear existing data

                    if (data.tableData.length === 0) {
                        let tr = document.createElement('tr');
                        let td = document.createElement('td');
                        td.textContent = 'No Output';
                        td.colSpan = 8; // Span across all columns
                        td.style.textAlign = 'center';
                        tr.appendChild(td);
                        tableBody.appendChild(tr);
                    } else {
                        data.tableData.forEach(row => {
                            let tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${row.temp}</td>
                                <td>${row.moist}</td>
                                <td>${row.ph}</td>
                                <td>${row.ec}</td>
                                <td>${row.n}</td>
                                <td>${row.p}</td>
                                <td>${row.k}</td>
                                <td>${row.timestamp}</td>
                            `;
                            tableBody.appendChild(tr);
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Initial fetch and set interval for updates
        updateData_light();
        updateData_npk();
        setInterval(updateData_light, 5000);
        setInterval(updateData_npk, 5000);
    </script>
</body>
</html>