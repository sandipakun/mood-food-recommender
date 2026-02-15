<?php
/**
 * Subscription API Endpoint
 * GET  /api/subscription.php - Get current subscription status
 * POST /api/subscription.php?action=toggle - Toggle premium (admin/testing)
 */

require_once dirname(__DIR__) . '/config/config.php';

requireAuth();

$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

switch ($action) {
    case 'status':
        handleGetStatus();
        break;
    
    case 'toggle':
        handleTogglePremium();
        break;
    
    default:
        errorResponse('Invalid action', 400);
}

function handleGetStatus() {
    $user = getCurrentUser();
    $db = getDB();
    
    $stmt = $db->prepare("SELECT is_premium, premium_expires_at FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $subscription = $stmt->fetch();
    
    successResponse([
        'is_premium' => (bool)$subscription['is_premium'],
        'premium_expires_at' => $subscription['premium_expires_at'],
        'features' => [
            'unlimited_saved_recipes' => (bool)$subscription['is_premium'],
            'custom_meal_plans' => (bool)$subscription['is_premium'],
            'ad_free' => (bool)$subscription['is_premium']
        ]
    ]);
}

function handleTogglePremium() {
    // For testing/admin purposes - toggle premium status
    $user = getCurrentUser();
    $db = getDB();
    
    $stmt = $db->prepare("UPDATE users SET is_premium = NOT is_premium WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Update session
    $stmt = $db->prepare("SELECT is_premium FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $newPremiumStatus = (bool)$stmt->fetchColumn();
    $_SESSION['is_premium'] = $newPremiumStatus;
    
    successResponse(['is_premium' => $newPremiumStatus], 'Premium status updated');
}

