<?php
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Settings';
$current = 'settings';

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 mb-0" style="font-weight:900;">Settings</h1>
</div>

<div class="card card-admin">
  <div class="card-body">
    <h5 class="card-title" style="font-weight:800;">Application</h5>
    <dl class="row mb-0">
      <dt class="col-sm-3">App name</dt>
      <dd class="col-sm-9"><?= e(APP_NAME ?? 'Mood Food Recommender') ?></dd>
      <dt class="col-sm-3">Version</dt>
      <dd class="col-sm-9"><?= e(APP_VERSION ?? '1.0.0') ?></dd>
      <dt class="col-sm-3">Base URL</dt>
      <dd class="col-sm-9"><code><?= e(BASE_URL) ?></code></dd>
    </dl>
  </div>
</div>

<div class="card card-admin mt-3">
  <div class="card-body">
    <p class="text-muted mb-0">
      To change the base URL or other app settings, edit <code>config/config.php</code> in the project root.
    </p>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
