<?php
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Add recipe';
$current = 'add_recipe';

$db = getDB();
$cuisines = $db->query("SELECT id, name, slug FROM cuisines ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$moods = $db->query("SELECT id, name, slug, icon FROM moods ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 mb-0" style="font-weight:900;">Add recipe</h1>
  <a class="btn btn-outline-pink" href="<?= admin_url('recipes.php') ?>">
    <i class="bi bi-arrow-left me-1"></i>Back to recipes
  </a>
</div>

<div class="card card-admin">
  <div class="card-body">
    <p class="text-muted mb-0">
      Use the form below to add a new recipe. Required: title, slug, cuisine, description. You can attach mood tags and nutrition info.
    </p>
  </div>
</div>

<div class="card card-admin mt-3">
  <div class="card-body">
    <form action="<?= rtrim(BASE_URL, '/') ?>/api/admin/recipes.php?action=create" method="post" enctype="multipart/form-data" id="add-recipe-form" data-admin-recipes-url="<?= admin_url('recipes.php') ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control" required maxlength="200" placeholder="e.g. Spicy Chicken Curry">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Slug <span class="text-danger">*</span></label>
          <input type="text" name="slug" class="form-control" required maxlength="200" placeholder="e.g. spicy-chicken-curry">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Cuisine <span class="text-danger">*</span></label>
          <select name="cuisine_id" class="form-select" required>
            <option value="">— Select —</option>
            <?php foreach ($cuisines as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Image URL</label>
          <input type="url" name="image_url" class="form-control" placeholder="https://...">
        </div>
        <div class="col-12">
          <label class="form-label">Description <span class="text-danger">*</span></label>
          <textarea name="description" class="form-control" rows="3" required placeholder="Short description of the dish."></textarea>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Prep (min)</label>
          <input type="number" name="prep_time" class="form-control" min="0" value="0">
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Cook (min)</label>
          <input type="number" name="cook_time" class="form-control" min="0" value="0">
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Servings</label>
          <input type="number" name="servings" class="form-control" min="1" value="1">
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Calories</label>
          <input type="number" name="calories" class="form-control" min="0" placeholder="—">
        </div>
        <div class="col-12 col-md-6">
          <div class="form-check">
            <input type="checkbox" name="is_veg" value="1" class="form-check-input" id="is_veg">
            <label class="form-check-label" for="is_veg">Vegetarian</label>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="form-check">
            <input type="checkbox" name="is_high_protein" value="1" class="form-check-input" id="is_high_protein">
            <label class="form-check-label" for="is_high_protein">High protein</label>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label">Mood tags</label>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($moods as $m): ?>
              <div class="form-check">
                <input type="checkbox" name="mood_slugs[]" value="<?= e($m['slug']) ?>" class="form-check-input" id="mood_<?= (int)$m['id'] ?>">
                <label class="form-check-label" for="mood_<?= (int)$m['id'] ?>"><?= e($m['icon'] ? $m['icon'] . ' ' . $m['name'] : $m['name']) ?></label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label">Ingredients (JSON array of {qty, unit, item})</label>
          <textarea name="ingredients_json" class="form-control font-monospace" rows="4" placeholder='[{"qty":"2","unit":"cups","item":"rice"},...]'></textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Steps (JSON array of strings)</label>
          <textarea name="steps_json" class="form-control font-monospace" rows="4" placeholder='["Step 1...","Step 2..."]'></textarea>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-pink">
            <i class="bi bi-check-lg me-1"></i>Create recipe
          </button>
          <a class="btn btn-outline-secondary ms-2" href="<?= admin_url('recipes.php') ?>">Cancel</a>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
(function() {
  var form = document.getElementById('add-recipe-form');
  var adminRecipesUrl = form.getAttribute('data-admin-recipes-url') || '<?= admin_url("recipes.php") ?>';
  form.addEventListener('submit', function(e) {
    var slug = document.querySelector('input[name="slug"]').value;
    if (!/^[a-z0-9\-]+$/.test(slug)) {
      e.preventDefault();
      alert('Slug must contain only lowercase letters, numbers and hyphens.');
      return false;
    }
    e.preventDefault();
    var fd = new FormData(form);
    var moodSlugs = [];
    form.querySelectorAll('input[name="mood_slugs[]"]:checked').forEach(function(cb) { moodSlugs.push(cb.value); });
    fd.set('mood_tags_json', JSON.stringify(moodSlugs));
    fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
      .then(function(result) {
        if (result.ok && result.data && result.data.success) {
          window.location = adminRecipesUrl + '?created=1';
        } else {
          alert((result.data && result.data.error) || 'Failed to create recipe.');
        }
      })
      .catch(function() { alert('Request failed. Try again.'); });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
