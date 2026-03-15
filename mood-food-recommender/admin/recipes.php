<?php
require_once __DIR__ . '/includes/auth_check.php';

if (!empty($_GET['created'])) {
    flash_set('success', 'Recipe created successfully.');
    header('Location: ' . admin_url('recipes.php'));
    exit;
}

$pageTitle = 'Recipes';
$current = 'recipes';
$db = getDB();

$error = null;

// POST: Update or Delete recipe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrf_verify();
    $action = $_POST['action'];

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim((string)($_POST['title'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        $cuisine_id = (int)($_POST['cuisine_id'] ?? 0);
        $description = trim((string)($_POST['description'] ?? ''));
        $image_url = trim((string)($_POST['image_url'] ?? ''));
        $calories = $_POST['calories'] !== '' ? (int)$_POST['calories'] : null;
        $prep_time = (int)($_POST['prep_time'] ?? 0);
        $cook_time = (int)($_POST['cook_time'] ?? 0);
        $is_veg = isset($_POST['is_veg']) ? 1 : 0;
        $is_high_protein = isset($_POST['is_high_protein']) ? 1 : 0;

        if ($id <= 0 || !$title || !$slug || $cuisine_id <= 0) {
            $error = 'Title, slug and cuisine are required.';
        } else {
            try {
                $stmt = $db->prepare("UPDATE recipes SET title = ?, slug = ?, cuisine_id = ?, description = ?, image_url = ?, calories = ?, prep_time = ?, cook_time = ?, is_veg = ?, is_high_protein = ? WHERE id = ?");
                $stmt->execute([$title, $slug, $cuisine_id, $description ?: null, $image_url ?: null, $calories, $prep_time, $cook_time, $is_veg, $is_high_protein, $id]);
                flash_set('success', 'Recipe updated successfully.');
                header('Location: ' . admin_url('recipes.php'));
                exit;
            } catch (PDOException $e) {
                $error = 'Slug already in use or invalid data.';
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $error = 'Invalid recipe.';
        } else {
            $stmt = $db->prepare("DELETE FROM recipes WHERE id = ?");
            $stmt->execute([$id]);
            flash_set('success', 'Recipe deleted.');
            header('Location: ' . admin_url('recipes.php'));
            exit;
        }
    }
}

$recipes = $db->query("
  SELECT r.id, r.title, r.slug, r.views_count, r.is_active, r.created_at,
         c.name AS cuisine_name
  FROM recipes r
  LEFT JOIN cuisines c ON c.id = r.cuisine_id
  ORDER BY r.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$cuisines = $db->query("SELECT id, name FROM cuisines ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// View single recipe
$viewId = isset($_GET['action']) && $_GET['action'] === 'view' ? (int)($_GET['id'] ?? 0) : 0;
$viewRecipe = null;
if ($viewId > 0) {
    $stmt = $db->prepare("
      SELECT r.*, c.name AS cuisine_name
      FROM recipes r
      LEFT JOIN cuisines c ON c.id = r.cuisine_id
      WHERE r.id = ?
    ");
    $stmt->execute([$viewId]);
    $viewRecipe = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Edit form
$editId = isset($_GET['action']) && $_GET['action'] === 'edit' ? (int)($_GET['id'] ?? 0) : 0;
$editRecipe = null;
if ($editId > 0) {
    $stmt = $db->prepare("SELECT id, title, slug, cuisine_id, description, image_url, calories, prep_time, cook_time, is_veg, is_high_protein FROM recipes WHERE id = ?");
    $stmt->execute([$editId]);
    $editRecipe = $stmt->fetch(PDO::FETCH_ASSOC);
}

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 mb-0" style="font-weight:900;">Recipes</h1>
  <a class="btn btn-pink" href="<?= admin_url('add_recipe.php') ?>">
    <i class="bi bi-plus-circle me-1"></i>Add Recipe
  </a>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($viewRecipe): ?>
<div class="card card-admin mb-3">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h5 class="card-title mb-0" style="font-weight:800;"><?= e($viewRecipe['title']) ?></h5>
      <a class="btn btn-sm btn-outline-secondary" href="<?= admin_url('recipes.php') ?>">Back to list</a>
    </div>
    <?php if (!empty($viewRecipe['image_url'])): ?>
      <img src="<?= e($viewRecipe['image_url']) ?>" alt="" class="img-fluid rounded mb-2" style="max-height:200px; object-fit:cover;">
    <?php endif; ?>
    <p class="text-muted mb-1"><strong>Cuisine:</strong> <?= e($viewRecipe['cuisine_name'] ?? '—') ?> &bull; <strong>Slug:</strong> <code><?= e($viewRecipe['slug']) ?></code></p>
    <p class="mb-2"><?= e($viewRecipe['description'] ?? '') ?></p>
    <p class="small text-muted mb-0">
      Calories: <?= $viewRecipe['calories'] !== null ? (int)$viewRecipe['calories'] : '—' ?> |
      Prep: <?= (int)$viewRecipe['prep_time'] ?> min | Cook: <?= (int)$viewRecipe['cook_time'] ?> min |
      Veg: <?= !empty($viewRecipe['is_veg']) ? 'Yes' : 'No' ?> | High protein: <?= !empty($viewRecipe['is_high_protein']) ? 'Yes' : 'No' ?> |
      Views: <?= number_format((int)$viewRecipe['views_count']) ?>
    </p>
    <div class="mt-2">
      <a class="btn btn-sm btn-primary" href="<?= admin_url('recipes.php') ?>?action=edit&id=<?= (int)$viewRecipe['id'] ?>">Edit</a>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($editRecipe): ?>
<div class="card card-admin mb-3">
  <div class="card-body">
    <h5 class="card-title" style="font-weight:800;">Edit Recipe</h5>
    <form method="post" action="<?= admin_url('recipes.php') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" value="<?= (int)$editRecipe['id'] ?>">
      <div class="row g-2">
        <div class="col-12 col-md-6">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required maxlength="200" value="<?= e($editRecipe['title']) ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" class="form-control" required maxlength="200" value="<?= e($editRecipe['slug']) ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Cuisine</label>
          <select name="cuisine_id" class="form-select" required>
            <?php foreach ($cuisines as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === (int)$editRecipe['cuisine_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Image URL</label>
          <input type="url" name="image_url" class="form-control" value="<?= e($editRecipe['image_url'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="2"><?= e($editRecipe['description'] ?? '') ?></textarea>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label">Calories</label>
          <input type="number" name="calories" class="form-control" min="0" value="<?= $editRecipe['calories'] !== null ? (int)$editRecipe['calories'] : '' ?>">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label">Prep (min)</label>
          <input type="number" name="prep_time" class="form-control" min="0" value="<?= (int)$editRecipe['prep_time'] ?>">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label">Cook (min)</label>
          <input type="number" name="cook_time" class="form-control" min="0" value="<?= (int)$editRecipe['cook_time'] ?>">
        </div>
        <div class="col-6 col-md-2 d-flex align-items-end gap-2">
          <div class="form-check">
            <input type="checkbox" name="is_veg" value="1" class="form-check-input" id="edit_is_veg" <?= !empty($editRecipe['is_veg']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="edit_is_veg">Vegetarian</label>
          </div>
        </div>
        <div class="col-6 col-md-2 d-flex align-items-end">
          <div class="form-check">
            <input type="checkbox" name="is_high_protein" value="1" class="form-check-input" id="edit_high_protein" <?= !empty($editRecipe['is_high_protein']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="edit_high_protein">High protein</label>
          </div>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Update Recipe</button>
          <a class="btn btn-outline-secondary ms-2" href="<?= admin_url('recipes.php') ?>">Cancel</a>
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
            <th>Title</th>
            <th>Cuisine</th>
            <th>Views</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recipes as $r): ?>
          <tr>
            <td><?= e($r['id']) ?></td>
            <td><?= e($r['title']) ?></td>
            <td><?= e($r['cuisine_name'] ?? '—') ?></td>
            <td><?= number_format((int)$r['views_count']) ?></td>
            <td>
              <?php if (!empty($r['is_active'])): ?>
                <span class="badge bg-success">Active</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inactive</span>
              <?php endif; ?>
            </td>
            <td><?= e(date('Y-m-d', strtotime($r['created_at']))) ?></td>
            <td>
              <a class="btn btn-sm btn-outline-primary" href="<?= admin_url('recipes.php') ?>?action=view&id=<?= (int)$r['id'] ?>">View</a>
              <a class="btn btn-sm btn-primary" href="<?= admin_url('recipes.php') ?>?action=edit&id=<?= (int)$r['id'] ?>">Edit</a>
              <form method="post" action="<?= admin_url('recipes.php') ?>" class="d-inline" onsubmit="return confirm('Delete this recipe? This cannot be undone.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (empty($recipes)): ?>
      <div class="p-4 text-center text-muted">No recipes yet. <a href="<?= admin_url('add_recipe.php') ?>">Add one</a>.</div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
