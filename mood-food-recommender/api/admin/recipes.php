<?php
/**
 * Admin Recipe CRUD API
 * GET    /api/admin/recipes.php - List all recipes
 * GET    /api/admin/recipes.php?id=<id> - Get single recipe
 * POST   /api/admin/recipes.php?action=create - Create recipe
 * POST   /api/admin/recipes.php?action=update - Update recipe
 * DELETE /api/admin/recipes.php?action=delete&id=<id> - Delete recipe
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/admin/includes/config.php';

if (!function_exists('admin_is_logged_in') || !admin_is_logged_in()) {
    requireAuth();
}

// Image upload configuration
define('IMAGE_UPLOAD_DIR', ASSETS_PATH . '/images/recipes/');
define('IMAGE_MAX_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {
    case 'list':
        if ($method !== 'GET') errorResponse('Method not allowed', 405);
        handleListRecipes();
        break;
    
    case 'get':
        if ($method !== 'GET') errorResponse('Method not allowed', 405);
        handleGetRecipe();
        break;
    
    case 'create':
        if ($method !== 'POST') errorResponse('Method not allowed', 405);
        handleCreateRecipe();
        break;
    
    case 'update':
        if ($method !== 'POST') errorResponse('Method not allowed', 405);
        handleUpdateRecipe();
        break;
    
    case 'delete':
        if ($method !== 'DELETE' && $method !== 'POST') errorResponse('Method not allowed', 405);
        handleDeleteRecipe();
        break;
    
    default:
        errorResponse('Invalid action', 400);
}

function handleListRecipes() {
    $db = getDB();
    $stmt = $db->query("
        SELECT r.*, c.name as cuisine_name 
        FROM recipes r 
        JOIN cuisines c ON r.cuisine_id = c.id 
        ORDER BY r.created_at DESC
    ");
    successResponse($stmt->fetchAll());
}

function handleGetRecipe() {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) errorResponse('Invalid ID', 400);
    
    $db = getDB();
    $stmt = $db->prepare("
        SELECT r.*, c.name as cuisine_name 
        FROM recipes r 
        JOIN cuisines c ON r.cuisine_id = c.id 
        WHERE r.id = ?
    ");
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();
    
    if (!$recipe) errorResponse('Recipe not found', 404);
    successResponse($recipe);
}

function handleCreateRecipe() {
    $db = getDB();
    
    // Validate required fields (mood_tags_json can be empty array or come from mood_slugs[])
    $required = ['title', 'cuisine_id'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            errorResponse("Field '$field' is required", 400);
        }
    }
    $moodTagsJson = $_POST['mood_tags_json'] ?? null;
    if ($moodTagsJson === null || $moodTagsJson === '') {
        $moodSlugs = $_POST['mood_slugs'] ?? [];
        $moodTagsJson = is_array($moodSlugs) ? json_encode(array_values($moodSlugs)) : '[]';
    }
    
    // Handle image upload if provided
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleImageUpload($_FILES['image']);
        if (!$uploadResult['success']) {
            errorResponse($uploadResult['error'], 400);
        }
        $imagePath = $uploadResult['path'];
    } elseif (isset($_POST['image_url']) && !empty($_POST['image_url'])) {
        // Allow external URL as fallback
        $imagePath = $_POST['image_url'];
    }
    
    // Build insert query
    $fields = ['title', 'slug', 'cuisine_id', 'description', 'image_url', 'is_veg', 'is_high_protein', 
               'calories', 'proteins_g', 'carbs_g', 'fats_g', 'prep_time', 'cook_time', 'servings',
               'ingredients_json', 'steps_json', 'mood_tags_json'];
    
    $slug = $_POST['slug'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $_POST['title']));
    
    $values = [];
    $placeholders = [];
    foreach ($fields as $field) {
        if ($field === 'slug') {
            $values[] = $slug;
        } elseif ($field === 'image_url') {
            $values[] = $imagePath;
        } elseif ($field === 'mood_tags_json') {
            $values[] = $moodTagsJson;
        } else {
            $values[] = $_POST[$field] ?? null;
        }
        $placeholders[] = '?';
    }
    
    try {
        $db->beginTransaction();
        
        $sql = "INSERT INTO recipes (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        
        $recipeId = $db->lastInsertId();
        $db->commit();
        
        successResponse(['id' => $recipeId], 'Recipe created');
    } catch (Exception $e) {
        $db->rollBack();
        // Clean up uploaded file if database insert fails
        if ($imagePath && strpos($imagePath, '/assets/images/recipes/') === 0) {
            $fullPath = ROOT_PATH . $imagePath;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
        error_log("Recipe creation error: " . $e->getMessage());
        errorResponse('Failed to create recipe', 500);
    }
}

function handleUpdateRecipe() {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) errorResponse('Invalid ID', 400);
    
    $db = getDB();
    
    // Get current recipe to check for existing image
    $stmt = $db->prepare("SELECT image_url FROM recipes WHERE id = ?");
    $stmt->execute([$id]);
    $currentRecipe = $stmt->fetch();
    
    if (!$currentRecipe) {
        errorResponse('Recipe not found', 404);
    }
    
    $oldImagePath = $currentRecipe['image_url'];
    $newImagePath = null;
    
    // Handle image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleImageUpload($_FILES['image']);
        if (!$uploadResult['success']) {
            errorResponse($uploadResult['error'], 400);
        }
        $newImagePath = $uploadResult['path'];
    } elseif (isset($_POST['image_url']) && !empty($_POST['image_url'])) {
        // Allow external URL as fallback
        $newImagePath = $_POST['image_url'];
    }
    
    // Build update query dynamically
    $fields = ['title', 'slug', 'cuisine_id', 'description', 'image_url', 'is_veg', 'is_high_protein',
               'calories', 'proteins_g', 'carbs_g', 'fats_g', 'prep_time', 'cook_time', 'servings',
               'ingredients_json', 'steps_json', 'mood_tags_json', 'is_active'];
    
    $updates = [];
    $values = [];
    foreach ($fields as $field) {
        if ($field === 'image_url' && $newImagePath !== null) {
            $updates[] = "$field = ?";
            $values[] = $newImagePath;
        } elseif (isset($_POST[$field])) {
            $updates[] = "$field = ?";
            $values[] = $_POST[$field];
        }
    }
    
    if (empty($updates)) {
        errorResponse('No fields to update', 400);
    }
    
    try {
        $db->beginTransaction();
        
        $values[] = $id;
        $sql = "UPDATE recipes SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        
        $db->commit();
        
        // Delete old image file if it was replaced and is a local file
        if ($newImagePath && $oldImagePath && $oldImagePath !== $newImagePath) {
            if (strpos($oldImagePath, '/assets/images/recipes/') === 0) {
                $oldFullPath = ROOT_PATH . $oldImagePath;
                if (file_exists($oldFullPath)) {
                    @unlink($oldFullPath);
                }
            }
        }
        
        successResponse(null, 'Recipe updated');
    } catch (Exception $e) {
        $db->rollBack();
        // Clean up uploaded file if database update fails
        if ($newImagePath && strpos($newImagePath, '/assets/images/recipes/') === 0) {
            $fullPath = ROOT_PATH . $newImagePath;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
        error_log("Recipe update error: " . $e->getMessage());
        errorResponse('Failed to update recipe', 500);
    }
}

function handleDeleteRecipe() {
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    if ($id <= 0) errorResponse('Invalid ID', 400);
    
    $db = getDB();
    
    // Get recipe image path before deletion
    $stmt = $db->prepare("SELECT image_url FROM recipes WHERE id = ?");
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();
    
    if (!$recipe) {
        errorResponse('Recipe not found', 404);
    }
    
    try {
        $db->beginTransaction();
        
        $stmt = $db->prepare("DELETE FROM recipes WHERE id = ?");
        $stmt->execute([$id]);
        
        $db->commit();
        
        // Delete associated image file if it's a local file
        if ($recipe['image_url'] && strpos($recipe['image_url'], '/assets/images/recipes/') === 0) {
            $imagePath = ROOT_PATH . $recipe['image_url'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }
        
        successResponse(null, 'Recipe deleted');
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Recipe deletion error: " . $e->getMessage());
        errorResponse('Failed to delete recipe', 500);
    }
}

/**
 * Handle image upload with security validation
 * @param array $file $_FILES array element
 * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
 */
function handleImageUpload($file) {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        return [
            'success' => false,
            'error' => $errorMessages[$file['error']] ?? 'Unknown upload error'
        ];
    }
    
    // Check file size
    if ($file['size'] > IMAGE_MAX_SIZE) {
        return [
            'success' => false,
            'error' => 'File size exceeds maximum allowed size of 2MB'
        ];
    }
    
    // Validate MIME type using finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedMimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($detectedMimeType, ALLOWED_MIME_TYPES)) {
        return [
            'success' => false,
            'error' => 'Invalid file type. Allowed types: JPEG, PNG, WebP'
        ];
    }
    
    // Validate file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return [
            'success' => false,
            'error' => 'Invalid file extension. Allowed: jpg, jpeg, png, webp'
        ];
    }
    
    // Ensure upload directory exists
    if (!file_exists(IMAGE_UPLOAD_DIR)) {
        if (!mkdir(IMAGE_UPLOAD_DIR, 0755, true)) {
            return [
                'success' => false,
                'error' => 'Failed to create upload directory'
            ];
        }
    }
    
    // Generate unique filename using hash
    $fileHash = hash_file('sha256', $file['tmp_name']);
    $uniqueFilename = substr($fileHash, 0, 16) . '_' . time() . '.' . $extension;
    $destinationPath = IMAGE_UPLOAD_DIR . $uniqueFilename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
        return [
            'success' => false,
            'error' => 'Failed to save uploaded file'
        ];
    }
    
    // Verify file was saved correctly
    if (!file_exists($destinationPath)) {
        return [
            'success' => false,
            'error' => 'File upload verification failed'
        ];
    }
    
    // Return relative path for database storage
    $relativePath = '/assets/images/recipes/' . $uniqueFilename;
    
    return [
        'success' => true,
        'path' => $relativePath
    ];
}

