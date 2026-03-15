<?php
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Analytics';
$current = 'analytics';

$db = getDB();

// Top recipes by views (real data, updates when users open recipe pages)
$topRecipes = $db->query("
  SELECT title, views_count
  FROM recipes
  ORDER BY views_count DESC
  LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Most popular moods by total recipe views (real-time, based on views_count and mood_tags_json)
$moodStats = [];
$moodChartLabels = [];
$moodChartValues = [];

// Load moods as index by slug
$moodRows = $db->query("SELECT id, name, slug, icon FROM moods ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$moodIndex = [];
foreach ($moodRows as $m) {
  $moodIndex[$m['slug']] = [
    'mood' => $m['name'],
    'icon' => $m['icon'],
    'total_views' => 0,
  ];
}

if (!empty($moodIndex)) {
  // Load recipes that have mood tags and views
  $recipeRows = $db->query("
    SELECT views_count, mood_tags_json
    FROM recipes
    WHERE mood_tags_json IS NOT NULL
      AND mood_tags_json <> ''
  ")->fetchAll(PDO::FETCH_ASSOC);

  foreach ($recipeRows as $row) {
    $views = (int)$row['views_count'];
    if ($views <= 0) {
      continue;
    }
    $tags = json_decode((string)$row['mood_tags_json'], true);
    if (!is_array($tags)) {
      continue;
    }
    foreach ($tags as $slug) {
      if (isset($moodIndex[$slug])) {
        $moodIndex[$slug]['total_views'] += $views;
      }
    }
  }

  // Build stats array and sort by total views desc
  foreach ($moodIndex as $entry) {
    $moodStats[] = $entry;
  }
  usort($moodStats, function ($a, $b) {
    return $b['total_views'] <=> $a['total_views'];
  });

  foreach ($moodStats as $row) {
    $label = $row['icon'] ? ($row['icon'] . ' ' . $row['mood']) : $row['mood'];
    $moodChartLabels[] = $label;
    $moodChartValues[] = (int)$row['total_views'];
  }
}

// Recent users (real data)
$recentUsers = $db->query("
  SELECT username, email, created_at
  FROM users
  ORDER BY created_at DESC
  LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 mb-0" style="font-weight:900;">Analytics</h1>
</div>

<div class="row g-3">
  <div class="col-12 col-xl-6">
    <div class="card card-admin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 class="card-title mb-0" style="font-weight:800;">Top recipes by views</h5>
          <span class="text-muted">Real data</span>
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-admin mb-0">
            <thead>
              <tr>
                <th>Recipe</th>
                <th class="text-end">Views</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($topRecipes as $r): ?>
              <tr>
                <td><?= e($r['title']) ?></td>
                <td class="text-end"><?= number_format((int)$r['views_count']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if (empty($topRecipes)): ?>
          <p class="text-muted mb-0">No recipe views yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-6">
    <div class="card card-admin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 class="card-title mb-0" style="font-weight:800;">Recent users</h5>
          <span class="text-muted">Last 10</span>
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-admin mb-0">
            <thead>
              <tr>
                <th>Username</th>
                <th>Email</th>
                <th class="text-end">Joined</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentUsers as $u): ?>
              <tr>
                <td><?= e($u['username']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td class="text-end"><?= e(date('Y-m-d H:i', strtotime($u['created_at']))) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if (empty($recentUsers)): ?>
          <p class="text-muted mb-0">No users yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card card-admin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 class="card-title mb-0" style="font-weight:800;">Most popular moods</h5>
          <span class="text-muted">Total views</span>
        </div>
        <?php if (!empty($moodStats)): ?>
          <div class="table-responsive mb-3">
            <table class="table table-sm table-admin mb-0">
              <thead>
                <tr>
                  <th>Mood</th>
                  <th class="text-end">Total views</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($moodStats as $row): ?>
                <tr>
                  <td><?= e($row['mood']) ?></td>
                  <td class="text-end"><?= number_format((int)$row['total_views']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-muted mb-0">No mood view data yet.</p>
        <?php endif; ?>

        <?php if (!empty($moodChartLabels)): ?>
          <canvas id="chartMoods" height="120"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($moodChartLabels)): ?>
<script>
(function() {
  const PINK = { border: 'rgba(255,20,147,0.75)', fill: 'rgba(255,182,193,0.35)', grid: 'rgba(255,182,193,0.35)', text: '#5a3d5c' };
  new Chart(document.getElementById('chartMoods'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($moodChartLabels, JSON_UNESCAPED_UNICODE) ?>,
      datasets: [{
        label: 'Mood Popularity (Views)',
        data: <?= json_encode($moodChartValues) ?>,
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
})();
</script>
<?php endif; ?>

<script>
// Auto-refresh analytics every 30 seconds so "Top Recipes by Views" stays up to date
setInterval(function() { location.reload(); }, 30000);
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
