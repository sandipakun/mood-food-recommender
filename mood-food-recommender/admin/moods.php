<?php
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Moods';
$current = 'moods';
$db = getDB();

$error = null;

// POST: Create / Update / Delete mood
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrf_verify();
    $action = $_POST['action'];

    if ($action === 'create') {
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        $icon = trim((string)($_POST['icon'] ?? ''));
        $color = trim((string)($_POST['color'] ?? '#FFB6C1'));

        if (!$name || !$slug) {
            $error = 'Name and slug are required.';
        } elseif (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
            $error = 'Slug must be lowercase letters, numbers and hyphens only.';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO moods (name, slug, icon, color) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $icon ?: null, $color ?: '#FFB6C1']);
                flash_set('success', 'Mood created successfully.');
                header('Location: ' . admin_url('moods.php'));
                exit;
            } catch (PDOException $e) {
                $error = 'Slug already exists or invalid data.';
            }
        }
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        $icon = trim((string)($_POST['icon'] ?? ''));
        $color = trim((string)($_POST['color'] ?? '#FFB6C1'));

        if ($id <= 0 || !$name || !$slug) {
            $error = 'Invalid data.';
        } elseif (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
            $error = 'Slug must be lowercase letters, numbers and hyphens only.';
        } else {
            try {
                $stmt = $db->prepare("UPDATE moods SET name = ?, slug = ?, icon = ?, color = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $icon ?: null, $color ?: '#FFB6C1', $id]);
                flash_set('success', 'Mood updated successfully.');
                header('Location: ' . admin_url('moods.php'));
                exit;
            } catch (PDOException $e) {
                $error = 'Slug already in use or invalid data.';
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $error = 'Invalid mood.';
        } else {
            $stmt = $db->prepare("DELETE FROM moods WHERE id = ?");
            $stmt->execute([$id]);
            flash_set('success', 'Mood deleted.');
            header('Location: ' . admin_url('moods.php'));
            exit;
        }
    }
}

$moods = $db->query("SELECT id, name, slug, description, icon, color, food_intent, created_at FROM moods ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$recipeCounts = [];
$rows = $db->query("SELECT mood_tags_json FROM recipes WHERE mood_tags_json IS NOT NULL AND mood_tags_json != ''")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $tags = json_decode((string)$row['mood_tags_json'], true);
    if (!is_array($tags)) continue;
    foreach ($tags as $slug) {
        $recipeCounts[$slug] = ($recipeCounts[$slug] ?? 0) + 1;
    }
}
foreach ($moods as &$m) {
    $m['recipe_count'] = $recipeCounts[$m['slug']] ?? 0;
}
unset($m);

$showCreateForm = isset($_GET['action']) && $_GET['action'] === 'create';
$editId = isset($_GET['action']) && $_GET['action'] === 'edit' ? (int)($_GET['id'] ?? 0) : 0;
$editMood = null;
if ($editId > 0) {
    $stmt = $db->prepare("SELECT id, name, slug, icon, color FROM moods WHERE id = ?");
    $stmt->execute([$editId]);
    $editMood = $stmt->fetch(PDO::FETCH_ASSOC);
}

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 mb-0" style="font-weight:900;">Moods</h1>
  <a class="btn btn-pink" href="<?= admin_url('moods.php') ?>?action=create">
    <i class="bi bi-plus-circle me-1"></i>Add Mood
  </a>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($showCreateForm): ?>
<div class="card card-admin mb-3">
  <div class="card-body">
    <h5 class="card-title" style="font-weight:800;">Create Mood</h5>
    <form method="post" action="<?= admin_url('moods.php') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="create">
      <div class="row g-2">
        <div class="col-12 col-md-3">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" required maxlength="50" placeholder="e.g. Happy" value="<?= e($_POST['name'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" class="form-control" required maxlength="50" placeholder="e.g. happy" value="<?= e($_POST['slug'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label">Icon</label>
          <input type="text" name="icon" class="form-control" maxlength="100" placeholder="e.g. 😊" value="<?= e($_POST['icon'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label">Color</label>
          <input type="text" name="color" class="form-control" maxlength="20" placeholder="#FFD700" value="<?= e($_POST['color'] ?? '#FFB6C1') ?>">
        </div>
        <div class="col-12 col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-pink me-2">Create</button>
          <a class="btn btn-outline-secondary" href="<?= admin_url('moods.php') ?>">Cancel</a>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if ($editMood): ?>
<div class="card card-admin mb-3">
  <div class="card-body">
    <h5 class="card-title" style="font-weight:800;">Edit Mood</h5>
    <form method="post" action="<?= admin_url('moods.php') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" value="<?= (int)$editMood['id'] ?>">
      <div class="row g-2">
        <div class="col-12 col-md-3">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" required maxlength="50" value="<?= e($editMood['name']) ?>">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" class="form-control" required maxlength="50" value="<?= e($editMood['slug']) ?>">
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label">Icon</label>
          <input type="text" name="icon" class="form-control" maxlength="100" value="<?= e($editMood['icon'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label">Color</label>
          <input type="text" name="color" class="form-control" maxlength="20" value="<?= e($editMood['color'] ?? '#FFB6C1') ?>">
        </div>
        <div class="col-12 col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-primary me-2">Update</button>
          <a class="btn btn-outline-secondary" href="<?= admin_url('moods.php') ?>">Cancel</a>
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
            <th>Name</th>
            <th>Slug</th>
            <th>Icon</th>
            <th>Color</th>
            <th>Recipes</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($moods as $m): ?>
          <tr>
            <td><?= e($m['id']) ?></td>
            <td><?= e($m['name']) ?></td>
            <td><code><?= e($m['slug']) ?></code></td>
            <td><?= e($m['icon'] ?? '—') ?></td>
            <td><span class="badge" style="background:<?= e($m['color'] ?? '#FFB6C1') ?>; color:#333;"><?= e($m['color'] ?? '—') ?></span></td>
            <td><?= (int)$m['recipe_count'] ?></td>
            <td><?= e(date('Y-m-d', strtotime($m['created_at']))) ?></td>
            <td>
              <a class="btn btn-sm btn-primary" href="<?= admin_url('moods.php') ?>?action=edit&id=<?= (int)$m['id'] ?>">Edit</a>
              <form method="post" action="<?= admin_url('moods.php') ?>" class="d-inline" onsubmit="return confirm('Delete this mood? Recipes using it will keep the tag but the mood will be removed.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (empty($moods)): ?>
      <div class="p-4 text-center text-muted">No moods yet. <a href="<?= admin_url('moods.php') ?>?action=create">Add one</a>.</div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
