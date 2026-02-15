<?php
/**
 * Utility Functions
 */

/**
 * Sanitize input string
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Generate error response
 */
function errorResponse($message, $statusCode = 400) {
    jsonResponse(['error' => $message], $statusCode);
}

/**
 * Generate success response
 */
function successResponse($data, $message = null) {
    $response = ['success' => true, 'data' => $data];
    if ($message) {
        $response['message'] = $message;
    }
    jsonResponse($response);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is premium
 */
function isPremium() {
    return isset($_SESSION['is_premium']) && $_SESSION['is_premium'] == 1;
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isLoggedIn()) {
        errorResponse('Authentication required', 401);
    }
}

/**
 * Require premium
 */
function requirePremium() {
    requireAuth();
    if (!isPremium()) {
        errorResponse('Premium subscription required', 403);
    }
}

/**
 * Parse JSON from database field
 */
function parseJsonField($jsonString) {
    if (empty($jsonString)) {
        return [];
    }
    $decoded = json_decode($jsonString, true);
    return $decoded ? $decoded : [];
}

/**
 * Calculate recipe mood score
 * Algorithm: mood match 60%, filters 30%, popularity 10%
 */
function calculateRecipeScore($recipe, $targetMood, $filters = []) {
    $score = 0;
    
    // Mood match (60%)
    $moodTags = parseJsonField($recipe['mood_tags_json']);
    if (in_array($targetMood, $moodTags)) {
        $score += 60;
    } else {
        // Partial match for multi-mood recipes
        $score += 20;
    }
    
    // Filter match (30%)
    $filterScore = 0;
    if (isset($filters['is_veg']) && $recipe['is_veg'] == $filters['is_veg']) {
        $filterScore += 15;
        $score += 15;
        $filterScore += 15;
    }
    if (isset($filters['is_high_protein']) && $recipe['is_high_protein'] == $filters['is_high_protein']) {
        $filterScore += 15;
        $score += 15;
    }
    if ($filterScore == 0 && !empty($filters)) {
        // No filter match, reduce score
        $score -= 10;
    }
    
    // Popularity (10%) - based on views
    $popularityScore = min(10, ($recipe['views_count'] / 100) * 10);
    $score += $popularityScore;
    
    return max(0, $score); // Ensure non-negative
}

