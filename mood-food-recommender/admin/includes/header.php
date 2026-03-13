<?php
// Load main app config (APP_NAME, APP_VERSION, BASE_URL, DB, utils, auth)
require_once __DIR__ . '/../../config/config.php';
// Load admin-specific helpers (admin_is_logged_in, admin_user, csrf helpers, etc.)
require_once __DIR__ . '/config.php';

$pageTitle = $pageTitle ?? 'Admin';
$current = $current ?? '';
$flash = flash_get();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> · <?= e(ADMIN_APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= admin_url('assets/css/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin-body">
<div class="admin-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="admin-main">
    <nav class="navbar navbar-expand-lg admin-topbar">
      <div class="container-fluid">
        <button class="btn btn-outline-pink d-lg-none" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
          <i class="bi bi-list"></i>
        </button>
        <div class="ms-2 d-flex align-items-center gap-2">
          <span class="admin-badge"><?= e($pageTitle) ?></span>
        </div>
        <div class="ms-auto d-flex align-items-center gap-3">
          <?php if (admin_is_logged_in()): ?>
            <div class="text-end">
              <div class="admin-user-name"><?= e(admin_user()['username'] ?? 'Admin') ?></div>
              <div class="admin-user-email"><?= e(admin_user()['email'] ?? '') ?></div>
            </div>
            <a class="btn btn-pink-soft" href="<?= admin_url('logout.php') ?>">
              <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
          <?php endif; ?>
        </div>
      </div>
    </nav>

    <main class="admin-content">
      <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> admin-alert" role="alert">
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>

