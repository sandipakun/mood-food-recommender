<?php
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Users';
$current = 'users';
$db = getDB();

$error = null;
$success = null;

// POST: Create user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrf_verify();
    $action = $_POST['action'];

    if ($action === 'create') {
        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (!$username || !$email || !$password) {
            $error = 'Username, email and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$username, $email, $hash]);
                flash_set('success', 'User created successfully.');
                header('Location: ' . admin_url('users.php'));
                exit;
            } catch (PDOException $e) {
                $error = 'Username or email already exists.';
            }
        }
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));

        if ($id <= 0 || !$username || !$email) {
            $error = 'Invalid data.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } else {
            try {
                $stmt = $db->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->execute([$username, $email, $id]);
                flash_set('success', 'User updated successfully.');
                header('Location: ' . admin_url('users.php'));
                exit;
            } catch (PDOException $e) {
                $error = 'Username or email already in use.';
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $error = 'Invalid user.';
        } else {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            flash_set('success', 'User deleted.');
            header('Location: ' . admin_url('users.php'));
            exit;
        }
    }
}

$users = $db->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$showCreateForm = isset($_GET['action']) && $_GET['action'] === 'create';
$editId = isset($_GET['action']) && $_GET['action'] === 'edit' ? (int)($_GET['id'] ?? 0) : 0;
$editUser = null;
if ($editId > 0) {
    $stmt = $db->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 mb-0" style="font-weight:900;">Users</h1>
  <a class="btn btn-pink" href="<?= admin_url('users.php') ?>?action=create">
    <i class="bi bi-plus-circle me-1"></i>Create User
  </a>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($showCreateForm): ?>
<div class="card card-admin mb-3">
  <div class="card-body">
    <h5 class="card-title" style="font-weight:800;">Create User</h5>
    <form method="post" action="<?= admin_url('users.php') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="create">
      <div class="row g-2">
        <div class="col-12 col-md-4">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required maxlength="50" value="<?= e($_POST['username'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required maxlength="100" value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required minlength="6">
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-pink">Create User</button>
          <a class="btn btn-outline-secondary ms-2" href="<?= admin_url('users.php') ?>">Cancel</a>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if ($editUser): ?>
<div class="card card-admin mb-3">
  <div class="card-body">
    <h5 class="card-title" style="font-weight:800;">Edit User</h5>
    <form method="post" action="<?= admin_url('users.php') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" value="<?= (int)$editUser['id'] ?>">
      <div class="row g-2">
        <div class="col-12 col-md-4">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required maxlength="50" value="<?= e($editUser['username']) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required maxlength="100" value="<?= e($editUser['email']) ?>">
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Update User</button>
          <a class="btn btn-outline-secondary ms-2" href="<?= admin_url('users.php') ?>">Cancel</a>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="card card-admin">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-admin table-hover mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><?= e($u['id']) ?></td>
            <td><?= e($u['username']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e(date('Y-m-d H:i', strtotime($u['created_at']))) ?></td>
            <td>
              <a class="btn btn-sm btn-primary" href="<?= admin_url('users.php') ?>?action=edit&id=<?= (int)$u['id'] ?>">Edit</a>
              <form method="post" action="<?= admin_url('users.php') ?>" class="d-inline" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (empty($users)): ?>
      <div class="p-4 text-center text-muted">No users yet. <a href="<?= admin_url('users.php') ?>?action=create">Create one</a>.</div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
