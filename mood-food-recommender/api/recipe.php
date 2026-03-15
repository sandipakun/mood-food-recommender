<?php
/**
 * Recipe Detail API Endpoint
 * GET /api/recipe.php?id=<recipe_id>
 * Returns full recipe details including ingredients and steps
 */

require_once dirname(__DIR__) . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$recipeId = (int)($_GET['id'] ?? 0);

if ($recipeId <= 0) {
    errorResponse('Valid recipe ID is required', 400);
}

try {
    $db = getDB();
    
    // Get recipe with cuisine info
    $stmt = $db->prepare("
        SELECT r.*, c.name as cuisine_name, c.flag_emoji 
        FROM recipes r 
        JOIN cuisines c ON r.cuisine_id = c.id 
        WHERE r.id = ? AND r.is_active = 1
    ");
    $stmt->execute([$recipeId]);
    $recipe = $stmt->fetch();
    
    if (!$recipe) {
        errorResponse('Recipe not found', 404);
    }
    
    // Increment view count once per session per recipe (prevents refresh spam)
    if (!isset($_SESSION['viewed_recipes'])) {
        $_SESSION['viewed_recipes'] = [];
    }
    if (!isset($_SESSION['viewed_recipes'][$recipeId])) {
        $db->prepare("UPDATE recipes SET views_count = views_count + 1 WHERE id = ?")->execute([$recipeId]);
        $_SESSION['viewed_recipes'][$recipeId] = true;
        $recipe['views_count'] = (int)$recipe['views_count'] + 1; // so response reflects new count
    }
    
    // Parse JSON fields
    $ingredients = parseJsonField($recipe['ingredients_json']);
    $steps = parseJsonField($recipe['steps_json']);
    $moodTags = parseJsonField($recipe['mood_tags_json']);
    
    // Format response
    $response = [
        'id' => (int)$recipe['id'],
        'title' => $recipe['title'],
        'slug' => $recipe['slug'],
        'description' => $recipe['description'],
        'cuisine' => $recipe['cuisine_name'],
        'cuisine_emoji' => $recipe['flag_emoji'],
        'image_url' => $recipe['image_url'],
        'is_veg' => (bool)$recipe['is_veg'],
        'is_high_protein' => (bool)$recipe['is_high_protein'],
        'nutrition' => [
            'calories' => (int)$recipe['calories'],
            'proteins_g' => (float)$recipe['proteins_g'],
            'carbs_g' => (float)$recipe['carbs_g'],
            'fats_g' => (float)$recipe['fats_g']
        ],
        'timing' => [
            'prep_time' => (int)$recipe['prep_time'],
            'cook_time' => (int)$recipe['cook_time'],
            'servings' => (int)$recipe['servings']
        ],
        'ingredients' => $ingredients,
        'steps' => $steps,
        'mood_tags' => $moodTags,
        'views_count' => (int)$recipe['views_count']
    ];
    
    successResponse($response);
    
} catch (PDOException $e) {
    error_log("Recipe API error: " . $e->getMessage());
    errorResponse('Failed to fetch recipe', 500);
}

