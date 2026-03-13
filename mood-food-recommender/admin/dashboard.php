<?php
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Dashboard';
$current = 'dashboard';

$db = getDB();

// KPI cards
$totalRecipes = (int)$db->query("SELECT COUNT(*) c FROM recipes")->fetch()['c'];
$totalUsers = (int)$db->query("SELECT COUNT(*) c FROM users")->fetch()['c'];
$totalPremiumUsers = (int)$db->query("SELECT COUNT(*) c FROM users WHERE is_premium = 1")->fetch()['c'];
$totalMoods = (int)$db->query("SELECT COUNT(*) c FROM moods")->fetch()['c'];

// Recipes by cuisine
$cuisineRows = $db->query("
  SELECT c.name AS cuisine, COUNT(*) AS count
  FROM recipes r
  JOIN cuisines c ON c.id = r.cuisine_id
  GROUP BY c.id
  ORDER BY count DESC, c.name ASC
")->fetchAll();
$cuisineLabels = array_map(fn($r) => $r['cuisine'], $cuisineRows);
$cuisineCounts = array_map(fn($r) => (int)$r['count'], $cuisineRows);

// Recipes by mood (count slugs from mood_tags_json in PHP for compatibility)
$moodSlugs = $db->query("SELECT slug, name, icon FROM moods ORDER BY name ASC")->fetchAll();
$moodIndex = [];
foreach ($moodSlugs as $m) $moodIndex[$m['slug']] = $m;

$moodCounts = array_fill_keys(array_keys($moodIndex), 0);
$tagRows = $db->query("SELECT mood_tags_json FROM recipes WHERE mood_tags_json IS NOT NULL AND mood_tags_json <> ''")->fetchAll();
foreach ($tagRows as $row) {
    $tags = json_decode((string)$row['mood_tags_json'], true);
    if (!is_array($tags)) continue;
    foreach ($tags as $slug) {
        if (isset($moodCounts[$slug])) $moodCounts[$slug]++;
    }
}
$moodLabels = [];
$moodValues = [];
foreach ($moodCounts as $slug => $count) {
    $label = $moodIndex[$slug]['icon'] ? ($moodIndex[$slug]['icon'] . ' ' . $moodIndex[$slug]['name']) : $moodIndex[$slug]['name'];
    $moodLabels[] = $label;
    $moodValues[] = (int)$count;
}

// User registrations last 30 days
$regRows = $db->query("
  SELECT DATE(created_at) AS d, COUNT(*) AS c
  FROM users
  WHERE created_at >= (NOW() - INTERVAL 30 DAY)
  GROUP BY DATE(created_at)
  ORDER BY d ASC
")->fetchAll();
$regMap = [];
foreach ($regRows as $r) $regMap[$r['d']] = (int)$r['c'];

$regLabels = [];
$regCounts = [];
$start = new DateTimeImmutable('-29 days');
for ($i = 0; $i < 30; $i++) {
    $d = $start->modify("+$i days")->format('Y-m-d');
    $regLabels[] = $d;
    $regCounts[] = $regMap[$d] ?? 0;
}

include __DIR__ . '/includes/header.php';
?>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card card-admin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted">Total Recipes</div>
            <div class="h2 mb-0" style="font-weight:900;"><?= number_format($totalRecipes) ?></div>
          </div>
          <div class="chip"><i class="bi bi-journal-richtext"></i> Recipes</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card card-admin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted">Total Users</div>
            <div class="h2 mb-0" style="font-weight:900;"><?= number_format($totalUsers) ?></div>
          </div>
          <div class="chip"><i class="bi bi-people"></i> Users</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card card-admin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted">Premium Users</div>
            <div class="h2 mb-0" style="font-weight:900;"><?= number_format($totalPremiumUsers) ?></div>
          </div>
          <div class="chip"><i class="bi bi-stars"></i> Premium</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card card-admin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted">Total Moods</div>
            <div class="h2 mb-0" style="font-weight:900;"><?= number_format($totalMoods) ?></div>
          </div>
          <div class="chip"><i class="bi bi-emoji-smile"></i> Moods</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-xl-6">
    <div class="card card-admin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="h5 mb-0" style="font-weight:900;">Recipes by Cuisine</div>
          <div class="text-muted">Distribution</div>
        </div>
        <canvas id="chartCuisine" height="160"></canvas>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-6">
    <div class="card card-admin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="h5 mb-0" style="font-weight:900;">Recipes by Mood</div>
          <div class="text-muted">Tag counts</div>
        </div>
        <canvas id="chartMood" height="160"></canvas>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card card-admin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="h5 mb-0" style="font-weight:900;">User Registrations (Last 30 days)</div>
          <div class="text-muted">Daily</div>
        </div>
        <canvas id="chartUsers" height="90"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
  const PINK = {
    border: 'rgba(255,20,147,0.75)',
    fill: 'rgba(255,182,193,0.35)',
    solid: 'rgba(255,105,180,0.9)',
    grid: 'rgba(255,182,193,0.35)',
    text: '#5a3d5c',
  };

  new Chart(document.getElementById('chartCuisine'), {
    type: 'doughnut',
    data: {
      labels: <?= json_encode($cuisineLabels, JSON_UNESCAPED_UNICODE) ?>,
      datasets: [{
        data: <?= json_encode($cuisineCounts) ?>,
        backgroundColor: [
          'rgba(255,105,180,0.75)',
          'rgba(255,182,193,0.75)',
          'rgba(255,20,147,0.70)',
          'rgba(255,145,164,0.75)',
          'rgba(255,235,243,1)',
          'rgba(255,105,180,0.55)'
        ],
        borderColor: 'rgba(255,255,255,0.9)',
        borderWidth: 2
      }]
    },
    options: {
      plugins: { legend: { position: 'bottom', labels: { color: PINK.text } } }
    }
  });

  new Chart(document.getElementById('chartMood'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($moodLabels, JSON_UNESCAPED_UNICODE) ?>,
      datasets: [{
        label: 'Recipes',
        data: <?= json_encode($moodValues) ?>,
        backgroundColor: 'rgba(255,182,193,0.55)',
        borderColor: 'rgba(255,20,147,0.6)',
        borderWidth: 2,
        borderRadius: 10
      }]
    },
    options: {
      scales: {
        x: { ticks: { color: PINK.text }, grid: { color: 'transparent' } },
        y: { ticks: { color: PINK.text }, grid: { color: PINK.grid } }
      },
      plugins: { legend: { display: false } }
    }
  });

  new Chart(document.getElementById('chartUsers'), {
    type: 'line',
    data: {
      labels: <?= json_encode($regLabels) ?>,
      datasets: [{
        label: 'New users',
        data: <?= json_encode($regCounts) ?>,
        borderColor: PINK.border,
        backgroundColor: PINK.fill,
        tension: 0.35,
        fill: true,
        pointRadius: 2
      }]
    },
    options: {
      scales: {
        x: { ticks: { color: PINK.text, maxTicksLimit: 10 }, grid: { color: 'transparent' } },
        y: { ticks: { color: PINK.text }, grid: { color: PINK.grid } }
      },
      plugins: { legend: { display: false } }
    }
  });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

