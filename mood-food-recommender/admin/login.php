<?php
// Load global app config (sessions, DB, BASE_URL, utils, auth)
require_once __DIR__ . '/../config/config.php';
// Load admin helpers (admin_is_logged_in, csrf, flash, etc.)
require_once __DIR__ . '/includes/config.php';

if (admin_is_logged_in()) {
    redirect(admin_url('dashboard.php'));
}

$db = getDB();
$adminCount = (int)$db->query("SELECT COUNT(*) AS c FROM admin_users")->fetch()['c'];
$needsBootstrap = $adminCount === 0;

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $action = $_POST['action'] ?? 'login';

    if ($action === 'bootstrap' && $needsBootstrap) {
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if (!$username || !$email || !$password) {
            $error = 'All fields are required.';
        } elseif (!isValidEmail($email)) {
            $error = 'Invalid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("INSERT INTO admin_users (username, email, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hash]);
                flash_set('success', 'Admin user created. Please log in.');
                redirect(admin_url('login.php'));
            } catch (PDOException $e) {
                error_log('Admin bootstrap failed: ' . $e->getMessage());
                $error = 'Failed to create admin user.';
            }
        }
    } else {
        $login = sanitize($_POST['login'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if (!$login || !$password) {
            $error = 'Enter your username/email and password.';
        } else {
            $stmt = $db->prepare("SELECT id, username, email, password_hash, is_active FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$login, $login]);
            $admin = $stmt->fetch();

            if (!$admin || (int)$admin['is_active'] !== 1 || !password_verify($password, $admin['password_hash'])) {
                $error = 'Invalid credentials.';
            } else {
                session_regenerate_id(true);
                $_SESSION['admin_user'] = [
                    'id' => (int)$admin['id'],
                    'username' => (string)$admin['username'],
                    'email' => (string)$admin['email'],
                ];
                $upd = $db->prepare("UPDATE admin_users SET last_login_at = NOW() WHERE id = ?");
                $upd->execute([(int)$admin['id']]);
                redirect(admin_url('dashboard.php'));
            }
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
                <input class="form-control" type="password" name="password" minlength="8" required>
                <button class="btn btn-outline-pink" type="button" onclick="const i=this.parentElement.querySelector('input'); i.type=i.type==='password'?'text':'password';">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              <div class="form-text">Minimum 8 characters.</div>
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
          </form>
        <?php endif; ?>

        <div class="mt-4 text-center text-muted" style="font-size:.9rem;">
          Back to site: <a href="/mood-food-recommender/" style="font-weight:800; color: var(--accent-pink); text-decoration:none;">Mood Food</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

