<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

if (admin_is_logged_in()) {
    redirect('admin/index.php');
}

$db = getDB();
$adminCount = (int)$db->query("SELECT COUNT(*) AS c FROM admin_users")->fetch()['c'];
$needsBootstrap = $adminCount === 0;

$error = null;
$flash = flash_get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? 'login';

    if ($action === 'bootstrap' && $needsBootstrap) {
        $username = trim((string)($_POST['username'] ?? ''));
        $email    = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm  = (string)($_POST['confirm_password'] ?? '');

        if (!$username || !$email || !$password || !$confirm) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            $regErrors = admin_register($username, $email, $password, $confirm);
            if (!empty($regErrors)) {
                $error = implode(' ', $regErrors);
            } else {
                flash_set('success', 'Admin user created. Please log in.');
                redirect('admin/login.php');
            }
        }
    } else {
        $login    = trim((string)($_POST['login'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        if (!$login || !$password) {
            $error = 'Enter your email (or username) and password.';
        } elseif (!admin_login($login, $password)) {
            $error = 'Invalid email/username or password.';
        } else {
            redirect('admin/index.php');
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login · <?= e(ADMIN_APP_NAME) ?></title>
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
            <div class="h4 mb-0" style="font-weight:900;">Mood Food Admin</div>
            <div class="text-muted">Secure dashboard access</div>
          </div>
        </div>

        <?php if ($flash): ?>
          <div class="alert alert-<?= e($flash['type']) ?> admin-alert"><?= e($flash['message']) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger admin-alert"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($needsBootstrap): ?>
          <div class="alert alert-warning admin-alert">
            No admin users found. Create the first admin account.
          </div>
          <form method="post" class="mt-3">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="bootstrap">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input class="form-control" name="username" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input class="form-control" type="email" name="email" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <div class="input-group">
                <input class="form-control" type="password" name="password" minlength="8" required autocomplete="new-password">
                <button class="btn btn-outline-pink" type="button" onclick="const i=this.parentElement.querySelector('input'); i.type=i.type==='password'?'text':'password';">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              <div class="form-text">Minimum 8 characters.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm Password</label>
              <div class="input-group">
                <input class="form-control" type="password" name="confirm_password" minlength="8" required autocomplete="new-password" placeholder="Re-enter password">
                <button class="btn btn-outline-pink" type="button" onclick="const i=this.parentElement.querySelector('input'); i.type=i.type==='password'?'text':'password';">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>
            <button class="btn btn-pink w-100" type="submit">
              <i class="bi bi-shield-lock me-1"></i>Create admin
            </button>
          </form>
        <?php else: ?>
          <form method="post" class="mt-3">
            <?= csrf_field() ?>
            <div class="mb-3">
              <label class="form-label">Username or Email</label>
              <input class="form-control" name="login" autocomplete="username" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <div class="input-group">
                <input class="form-control" type="password" name="password" autocomplete="current-password" required>
                <button class="btn btn-outline-pink" type="button" onclick="const i=this.parentElement.querySelector('input'); i.type=i.type==='password'?'text':'password';">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>
            <button class="btn btn-pink w-100" type="submit">
              <i class="bi bi-box-arrow-in-right me-1"></i>Login
            </button>
            <div class="mt-3 text-center">
              <a href="<?= admin_url('register.php') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-person-plus me-1"></i>Register new admin
              </a>
            </div>
          </form>
        <?php endif; ?>

        <div class="mt-4 text-center text-muted" style="font-size:.9rem;">
          Back to site: <a href="<?= e(BASE_URL) ?>/" style="font-weight:800; color: var(--accent-pink); text-decoration:none;">Mood Food</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

