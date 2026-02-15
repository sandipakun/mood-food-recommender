<?php
/**
 * Moods API Endpoint
 * GET /api/moods.php
 * Returns list of all available moods
 */

require_once dirname(__DIR__) . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

try {
    $db = getDB();
    $stmt = $db->query("SELECT id, name, slug, description, icon, color, food_intent FROM moods ORDER BY id ASC");
    $moods = $stmt->fetchAll();
    
    successResponse($moods);
} catch (PDOException $e) {
    error_log("Moods API error: " . $e->getMessage());
    errorResponse('Failed to fetch moods', 500);
}

