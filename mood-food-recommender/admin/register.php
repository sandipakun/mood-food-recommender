<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Register Admin';
$current = 'register';

$errors = [];
$flash = flash_get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = trim((string) ($_POST['username'] ?? ''));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['confirm_password'] ?? '');

    $errors = admin_register($username, $email, $password, $confirm);

    if (empty($errors)) {
        flash_set('success', 'New admin account created. You can log in with email or username.');
        if (admin_is_logged_in()) {
            redirect('admin/index.php');
        }
        redirect('admin/login.php');
    }
}

$isLoggedIn = admin_is_logged_in();

if ($isLoggedIn) {
    include __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 mb-0" style="font-weight:900;">Register new admin</h1>
  <a class="btn btn-outline-secondary" href="<?= admin_url('index.php') ?>">
    <i class="bi bi-arrow-left me-1"></i>Back to dashboard
  </a>
</div>

<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $e): ?>
        <li><?= e($e) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="card card-admin">
  <div class="card-body">
    <p class="text-muted mb-3">Username and email must be unique; password at least 8 characters.</p>
    <form method="post" action="<?= admin_url('register.php') ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required maxlength="50" value="<?= e($_POST['username'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required maxlength="100" value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
          <div class="form-text">At least 8 characters.</div>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Confirm password</label>
          <input type="password" name="confirm_password" class="form-control" required minlength="8" autocomplete="new-password">
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-pink">
            <i class="bi bi-person-plus me-1"></i>Create admin
          </button>
          <a class="btn btn-outline-secondary ms-2" href="<?= admin_url('index.php') ?>">Cancel</a>
        </div>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php';
} else {
    // Standalone page for guests (same look as login page)
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register Admin · <?= e(ADMIN_APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= admin_url('assets/css/admin.css') ?>" rel="stylesheet">
  <style>
    .auth-wrap{min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.25rem;}
    .auth-card{max-width: 520px; width:100%;}
  </style>
</head>
<body class="admin-body">
  <div class="auth-wrap">
    <div class="card card-admin auth-card">
      <div class="card-body p-4 p-md-5">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="admin-brand-dot"></span>
          <div>
            <div class="h4 mb-0" style="font-weight:900;">Register new admin</div>
            <div class="text-muted">Create an admin account to access the dashboard</div>
          </div>
        </div>

        <?php if ($flash): ?>
          <div class="alert alert-<?= e($flash['type']) ?> admin-alert"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger admin-alert">
            <ul class="mb-0">
              <?php foreach ($errors as $e): ?>
                <li><?= e($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= admin_url('register.php') ?>" class="mt-3">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required maxlength="50" value="<?= e($_POST['username'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required maxlength="100" value="<?= e($_POST['email'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
              <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
              <button class="btn btn-outline-pink" type="button" onclick="const i=this.parentElement.querySelector('input'); i.type=i.type==='password'?'text':'password';">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="form-text">Minimum 8 characters.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm password</label>
            <div class="input-group">
              <input type="password" name="confirm_password" class="form-control" required minlength="8" autocomplete="new-password">
              <button class="btn btn-outline-pink" type="button" onclick="const i=this.parentElement.querySelector('input'); i.type=i.type==='password'?'text':'password';">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>
          <button type="submit" class="btn btn-pink w-100">
            <i class="bi bi-person-plus me-1"></i>Create admin
          </button>
        </form>

        <div class="mt-4 text-center text-muted" style="font-size:.9rem;">
          Already have an account? <a href="<?= admin_url('login.php') ?>" style="font-weight:600; color: var(--accent-pink); text-decoration:none;">Login</a>
          <span class="mx-1">·</span>
          <a href="<?= e(BASE_URL) ?>/" style="font-weight:800; color: var(--accent-pink); text-decoration:none;">Back to site</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
<?php
}
