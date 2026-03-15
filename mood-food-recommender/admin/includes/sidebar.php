<?php
require_once __DIR__ . '/config.php';
$current = $current ?? '';
?>
<aside class="admin-sidebar" id="adminSidebar">
  <div class="admin-sidebar-header">
    <a class="admin-brand" href="<?= admin_url('dashboard.php') ?>">
      <span class="admin-brand-dot"></span>
      <span class="admin-brand-text">Mood Food</span>
      <span class="admin-brand-sub">Admin</span>
    </a>
  </div>

  <div class="admin-sidebar-body">
    <a class="admin-nav <?= $current === 'dashboard' ? 'active' : '' ?>" href="<?= admin_url('dashboard.php') ?>">
      <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
    </a>
    <a class="admin-nav <?= $current === 'recipes' ? 'active' : '' ?>" href="<?= admin_url('recipes.php') ?>">
      <i class="bi bi-journal-richtext"></i><span>Recipes</span>
    </a>
    <a class="admin-nav <?= $current === 'add_recipe' ? 'active' : '' ?>" href="<?= admin_url('add_recipe.php') ?>">
      <i class="bi bi-plus-circle"></i><span>Add recipe</span>
    </a>
    <a class="admin-nav <?= $current === 'moods' ? 'active' : '' ?>" href="<?= admin_url('moods.php') ?>">
      <i class="bi bi-emoji-smile"></i><span>Moods</span>
    </a>
    <a class="admin-nav <?= $current === 'users' ? 'active' : '' ?>" href="<?= admin_url('users.php') ?>">
      <i class="bi bi-people"></i><span>Users</span>
    </a>
    <a class="admin-nav <?= $current === 'analytics' ? 'active' : '' ?>" href="<?= admin_url('analytics.php') ?>">
      <i class="bi bi-graph-up"></i><span>Analytics</span>
    </a>
    <div class="admin-nav-divider"></div>
    <a class="admin-nav <?= $current === 'register' ? 'active' : '' ?>" href="<?= admin_url('register.php') ?>">
      <i class="bi bi-person-plus"></i><span>Register Admin</span>
    </a>
    <a class="admin-nav <?= $current === 'settings' ? 'active' : '' ?>" href="<?= admin_url('settings.php') ?>">
      <i class="bi bi-gear"></i><span>Settings</span>
    </a>
  </div>

  <div class="admin-sidebar-footer">
    <div class="admin-muted">v<?= e(APP_VERSION ?? '1.0.0') ?></div>
  </div>
</aside>

