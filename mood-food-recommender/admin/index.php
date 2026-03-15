<?php
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Admin Home';
$current = 'dashboard';

include __DIR__ . '/includes/header.php';
?>

<div class="row g-3 mb-3">
  <div class="col-12">
    <div class="card card-admin">
      <div class="card-body d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
        <div>
          <h1 class="h4 mb-1" style="font-weight:900;">Welcome to Mood Food Admin</h1>
          <p class="text-muted mb-0">Manage recipes, moods, users, and analytics from one clean dashboard.</p>
        </div>
        <div class="mt-3 mt-md-0">
          <a href="<?= admin_url('dashboard.php') ?>" class="btn btn-pink me-2">
            <i class="bi bi-grid-1x2-fill me-1"></i>Open Dashboard
          </a>
          <a href="<?= e(BASE_URL) ?>/" class="btn btn-outline-secondary">
            <i class="bi bi-house-door me-1"></i>View Site
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-md-6 col-xl-4">
    <a href="<?= admin_url('recipes.php') ?>" class="text-decoration-none text-reset">
      <div class="card card-admin h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="h5 mb-0" style="font-weight:800;">Recipes</div>
            <span class="badge bg-pink-soft"><i class="bi bi-journal-richtext"></i></span>
          </div>
          <p class="text-muted mb-0">Browse, edit, and manage all recipes in the system.</p>
        </div>
      </div>
    </a>
  </div>
  <div class="col-12 col-md-6 col-xl-4">
    <a href="<?= admin_url('moods.php') ?>" class="text-decoration-none text-reset">
      <div class="card card-admin h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="h5 mb-0" style="font-weight:800;">Moods</div>
            <span class="badge bg-pink-soft"><i class="bi bi-emoji-smile"></i></span>
          </div>
          <p class="text-muted mb-0">Configure mood categories and their styling.</p>
        </div>
      </div>
    </a>
  </div>
  <div class="col-12 col-md-6 col-xl-4">
    <a href="<?= admin_url('users.php') ?>" class="text-decoration-none text-reset">
      <div class="card card-admin h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="h5 mb-0" style="font-weight:800;">Users</div>
            <span class="badge bg-pink-soft"><i class="bi bi-people"></i></span>
          </div>
          <p class="text-muted mb-0">View and manage registered users of the app.</p>
        </div>
      </div>
    </a>
  </div>
  <div class="col-12 col-md-6 col-xl-4">
    <a href="<?= admin_url('analytics.php') ?>" class="text-decoration-none text-reset">
      <div class="card card-admin h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="h5 mb-0" style="font-weight:800;">Analytics</div>
            <span class="badge bg-pink-soft"><i class="bi bi-graph-up"></i></span>
          </div>
          <p class="text-muted mb-0">Monitor top recipes, popular moods, and recent users.</p>
        </div>
      </div>
    </a>
  </div>
  <div class="col-12 col-md-6 col-xl-4">
    <a href="<?= admin_url('add_recipe.php') ?>" class="text-decoration-none text-reset">
      <div class="card card-admin h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="h5 mb-0" style="font-weight:800;">Add Recipe</div>
            <span class="badge bg-pink-soft"><i class="bi bi-plus-circle"></i></span>
          </div>
          <p class="text-muted mb-0">Quickly create a new recipe for any mood.</p>
        </div>
      </div>
    </a>
  </div>
  <div class="col-12 col-md-6 col-xl-4">
    <a href="<?= admin_url('register.php') ?>" class="text-decoration-none text-reset">
      <div class="card card-admin h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="h5 mb-0" style="font-weight:800;">Register Admin</div>
            <span class="badge bg-pink-soft"><i class="bi bi-person-plus"></i></span>
          </div>
          <p class="text-muted mb-0">Create additional admin accounts (logged-in admins only).</p>
        </div>
      </div>
    </a>
  </div>
  <div class="col-12 col-md-6 col-xl-4">
    <a href="<?= admin_url('settings.php') ?>" class="text-decoration-none text-reset">
      <div class="card card-admin h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="h5 mb-0" style="font-weight:800;">Settings</div>
            <span class="badge bg-pink-soft"><i class="bi bi-gear"></i></span>
          </div>
          <p class="text-muted mb-0">View core app configuration and environment details.</p>
        </div>
      </div>
    </a>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

