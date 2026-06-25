<?php
require __DIR__ . '/../vendor/autoload.php';
use AirGuard\Config;
$config = Config::load(dirname(__DIR__));
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($config['app_name']) ?></title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <aside class="sidebar">
    <div class="logo">AirGuard</div>
    <nav>
      <a class="active">Dashboard</a>
      <a>Stations</a>
      <a>Alerts</a>
      <a>Reports</a>
      <a>Settings</a>
    </nav>
  </aside>

  <main class="main">
    <header class="topbar">
      <div>
        <h1>AirGuard Construction</h1>
        <p>Real-time environmental monitoring from MQTT sensors</p>
      </div>
      <div class="status"><span></span> MQTT Data</div>
    </header>

    <section class="cards" id="cards"></section>

    <section class="panel">
      <div class="panel-head">
        <h2>Trend</h2>
        <select id="stationSelect"></select>
      </div>
      <canvas id="trendChart" height="110"></canvas>
    </section>

    <section class="panel">
      <h2>Latest Readings</h2>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Station</th><th>PM2.5</th><th>PM10</th><th>Temp</th><th>Humidity</th><th>Noise</th><th>Time</th>
            </tr>
          </thead>
          <tbody id="readingTable"></tbody>
        </table>
      </div>
    </section>
  </main>

<script>
let chart;
let currentStation = '';

function valueOrDash(v, suffix = '') {
  return v === null || v === undefined ? '-' : `${v}${suffix}`;
}

function levelPM25(pm25) {
  const v = Number(pm25 || 0);
  if (v <= 25) return 'good';
  if (v <= 50) return 'moderate';
  return 'bad';
}

async function loadLatest() {
  const res = await fetch('api.php?action=latest');
  const json = await res.json();
  const rows = json.data || [];

  renderCards(rows);
  renderTable(rows);
  renderStationOptions(rows);

  if (!currentStation && rows.length) {
    currentStation = rows[0].station_id;
    document.getElementById('stationSelect').value = currentStation;
  }
  if (currentStation) loadRecent(currentStation);
}

function renderCards(rows) {
  const cards = document.getElementById('cards');
  cards.innerHTML = rows.map(r => `
    <article class="card ${levelPM25(r.pm25)}">
      <div class="card-title">${r.station_id}</div>
      <div class="big">${valueOrDash(r.pm25)}</div>
      <div class="unit">PM2.5 µg/m³</div>
      <div class="mini">
        <span>PM10 ${valueOrDash(r.pm10)}</span>
        <span>${valueOrDash(r.temperature, '°C')}</span>
        <span>${valueOrDash(r.humidity, '%')}</span>
      </div>
    </article>
  `).join('') || '<p class="empty">No MQTT data yet.</p>';
}

function renderTable(rows) {
  const tbody = document.getElementById('readingTable');
  tbody.innerHTML = rows.map(r => `
    <tr>
      <td>${r.station_id}</td>
      <td>${valueOrDash(r.pm25)}</td>
      <td>${valueOrDash(r.pm10)}</td>
      <td>${valueOrDash(r.temperature, '°C')}</td>
      <td>${valueOrDash(r.humidity, '%')}</td>
      <td>${valueOrDash(r.noise, ' dB')}</td>
      <td>${r.received_at}</td>
    </tr>
  `).join('');
}

function renderStationOptions(rows) {
  const select = document.getElementById('stationSelect');
  const options = rows.map(r => `<option value="${r.station_id}">${r.station_id}</option>`).join('');
  select.innerHTML = options;
  if (currentStation) select.value = currentStation;
}

async function loadRecent(station) {
  const res = await fetch(`api.php?action=recent&station=${encodeURIComponent(station)}`);
  const json = await res.json();
  const rows = json.data || [];
  const labels = rows.map(r => r.received_at.slice(11, 19));

  const data = {
    labels,
    datasets: [
      { label: 'PM2.5', data: rows.map(r => r.pm25) },
      { label: 'PM10', data: rows.map(r => r.pm10) },
      { label: 'Noise dB', data: rows.map(r => r.noise) }
    ]
  };

  if (!chart) {
    chart = new Chart(document.getElementById('trendChart'), { type: 'line', data, options: { responsive: true } });
  } else {
    chart.data = data;
    chart.update();
  }
}

document.getElementById('stationSelect').addEventListener('change', e => {
  currentStation = e.target.value;
  loadRecent(currentStation);
});

loadLatest();
setInterval(loadLatest, 5000);
</script>
</body>
</html>
