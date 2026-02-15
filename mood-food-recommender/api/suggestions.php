<?php
/**
 * Recipe Suggestions API Endpoint
 * GET /api/suggestions.php?mood=<mood_slug>&is_veg=<0|1>&is_high_protein=<0|1>&multi_cuisine=<0|1>
 * Returns 3-4 mood-tailored recipe suggestions
 */

require_once dirname(__DIR__) . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$mood = sanitize($_GET['mood'] ?? '');
$isVeg = isset($_GET['is_veg']) ? (int)$_GET['is_veg'] : null;
$isHighProtein = isset($_GET['is_high_protein']) ? (int)$_GET['is_high_protein'] : null;
$multiCuisine = isset($_GET['multi_cuisine']) ? (int)$_GET['multi_cuisine'] : 0;

if (empty($mood)) {
    errorResponse('Mood parameter is required', 400);
}

try {
    $db = getDB();
    
    // Build query with filters
    $where = ["r.is_active = 1"];
    $params = [];
    
    // Mood filter (check if mood is in JSON array)
    $where[] = "JSON_CONTAINS(r.mood_tags_json, ?)";
    $params[] = json_encode($mood);
    
    // Veg filter
    if ($isVeg !== null) {
        $where[] = "r.is_veg = ?";
        $params[] = $isVeg;
    }
    
    // High protein filter
    if ($isHighProtein !== null) {
        $where[] = "r.is_high_protein = ?";
        $params[] = $isHighProtein;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Get all matching recipes
    $sql = "SELECT r.*, c.name as cuisine_name, c.flag_emoji 
            FROM recipes r 
            JOIN cuisines c ON r.cuisine_id = c.id 
            WHERE $whereClause 
            ORDER BY r.views_count DESC, RAND()";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $recipes = $stmt->fetchAll();
    
    // If no exact matches, try without filters (fallback)
    if (empty($recipes)) {
        $sql = "SELECT r.*, c.name as cuisine_name, c.flag_emoji 
                FROM recipes r 
                JOIN cuisines c ON r.cuisine_id = c.id 
                WHERE r.is_active = 1 AND JSON_CONTAINS(r.mood_tags_json, ?)
                ORDER BY r.views_count DESC, RAND() 
                LIMIT 10";
        $stmt = $db->prepare($sql);
        $stmt->execute([json_encode($mood)]);
        $recipes = $stmt->fetchAll();
    }
    
    // Calculate scores and sort
    $filters = [];
    if ($isVeg !== null) $filters['is_veg'] = $isVeg;
    if ($isHighProtein !== null) $filters['is_high_protein'] = $isHighProtein;
    if ($multiCuisine) $filters['multi_cuisine'] = 1;
    
    foreach ($recipes as &$recipe) {
        $recipe['score'] = calculateRecipeScore($recipe, $mood, $filters);
    }
    unset($recipe);
    
    // Sort by score descending
    usort($recipes, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });
    
    // Multi-cuisine: Ensure variety by limiting recipes per cuisine
    if ($multiCuisine && count($recipes) > 0) {
        $suggestions = [];
        $cuisineCount = [];
        $maxPerCuisine = 1; // Max 1 recipe per cuisine for variety
        
        foreach ($recipes as $recipe) {
            $cuisineId = $recipe['cuisine_id'];
            $currentCount = $cuisineCount[$cuisineId] ?? 0;
            
            if ($currentCount < $maxPerCuisine) {
                $suggestions[] = $recipe;
                $cuisineCount[$cuisineId] = $currentCount + 1;
                
                if (count($suggestions) >= 4) {
                    break;
                }
            }
        }
        
        // If we don't have enough with variety, fill with top remaining recipes
        if (count($suggestions) < 3) {
            foreach ($recipes as $recipe) {
                if (count($suggestions) >= 4) break;
                if (!in_array($recipe, $suggestions, true)) {
                    $suggestions[] = $recipe;
                }
            }
        }
    } else {
        // Take top 3-4
        $suggestions = array_slice($recipes, 0, 4);
    }
    
    // Format response
    $formatted = [];
    foreach ($suggestions as $recipe) {
        $formatted[] = [
            'id' => $recipe['id'],
            'title' => $recipe['title'],
            'slug' => $recipe['slug'],
            'cuisine' => $recipe['cuisine_name'],
            'cuisine_emoji' => $recipe['flag_emoji'],
            'image_url' => $recipe['image_url'],
            'is_veg' => (bool)$recipe['is_veg'],
            'is_high_protein' => (bool)$recipe['is_high_protein'],
            'calories' => (int)$recipe['calories'],
            'prep_time' => (int)$recipe['prep_time'],
            'cook_time' => (int)$recipe['cook_time'],
            'mood_tags' => parseJsonField($recipe['mood_tags_json']),
            'score' => round($recipe['score'], 2)
        ];
    }
    
    $response = [
        'suggestions' => $formatted,
        'count' => count($formatted),
        'mood' => $mood,
        'filters_applied' => $filters
    ];
    
    if (count($formatted) < 3) {
        $response['note'] = 'Fewer matches found. Showing best available recipes.';
    }
    
    successResponse($response);
    
} catch (PDOException $e) {
    error_log("Suggestions API error: " . $e->getMessage());
    errorResponse('Failed to fetch suggestions', 500);
}

