<?php
require_once __DIR__ . '/config/config.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Discover mood-based recipe recommendations tailored to how you feel - happy, stressed, or tired!">
    <title>Dashboard - Mood Food Recommender</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Main Content -->
    <main class="container-custom">
        <!-- Alert Container -->
        <div id="alert-container"></div>

        <!-- Welcome Section - Cloud Callout Sticker -->
        <section class="welcome-section mb-4">
            <div class="welcome-sticker">
                <div class="welcome-cloud">
                    <span class="welcome-text">Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>! 👋</span>
                </div>
            </div>
        </section>

        <!-- Mood Selection Section -->
        <section id="mood-selection" class="mb-5">
            <h2 class="text-center mb-4">How are you feeling today? 😊</h2>
            <p class="text-center text-muted mb-4">Select your mood to discover personalized recipe recommendations</p>
            
            <div id="mood-container" class="mood-grid" role="group" aria-label="Mood selection">
                <!-- Moods will be loaded here -->
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Loading moods...</p>
                </div>
            </div>
        </section>

        <!-- Filters Section -->
        <section id="filters-section" class="filter-section" style="display: none;">
            <h3 class="mb-3">Filter Recipes</h3>
            <div class="filter-group">
                <div class="filter-category mb-3">
                    <span class="filter-label">Diet Type:</span>
                    <div class="filter-options">
                        <label class="filter-radio">
                            <input type="radio" name="diet-type" id="filter-veg" value="veg" aria-label="Show only vegetarian recipes">
                            <span>🌱 Vegetarian</span>
                        </label>
                        <label class="filter-radio">
                            <input type="radio" name="diet-type" id="filter-nonveg" value="nonveg" aria-label="Show only non-vegetarian recipes">
                            <span>🍗 Non-Vegetarian</span>
                        </label>
                        <label class="filter-radio">
                            <input type="radio" name="diet-type" id="filter-all-diet" value="all" checked aria-label="Show all recipes">
                            <span>🍽️ All</span>
                        </label>
                    </div>
                </div>
                <div class="filter-category mb-3">
                    <span class="filter-label">Nutrition:</span>
                    <div class="filter-options">
                        <label class="filter-checkbox">
                            <input type="checkbox" id="filter-high-protein" aria-label="Show only high protein recipes">
                            <span>💪 High Protein</span>
                        </label>
                    </div>
                </div>
                <div class="filter-category mb-3">
                    <span class="filter-label">Cuisine:</span>
                    <div class="filter-options">
                        <label class="filter-checkbox">
                            <input type="checkbox" id="filter-multi-cuisine" aria-label="Show recipes from multiple cuisines">
                            <span>🌍 Multi-Cuisine</span>
                        </label>
                    </div>
                </div>
                <button class="btn-secondary btn-sm" id="clear-filters">Clear Filters</button>
            </div>
        </section>

        <!-- Suggestions Section -->
        <section id="suggestions-section" style="display: none;">
            <h2 class="mb-4">Recommended Recipes for You ✨</h2>
            <div id="suggestions-container">
                <!-- Suggestions will be loaded here -->
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/app.js"></script>
    
    <script>
        // Show filters and suggestions when mood is selected
        const originalSelectMood = window.selectMood;
        window.selectMood = function(moodSlug) {
            originalSelectMood(moodSlug);
            document.getElementById('filters-section').style.display = 'block';
            document.getElementById('suggestions-section').style.display = 'block';
            document.getElementById('suggestions-section').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        };
        
        // Clear recipe ID from sessionStorage when on dashboard (user came back intentionally)
        document.addEventListener('DOMContentLoaded', function() {
            // Only clear if we're actually on dashboard (no recipe ID in URL)
            const urlParams = new URLSearchParams(window.location.search);
            const recipeId = urlParams.get('id');
            
            if (!recipeId) {
                // User is on dashboard, clear recipe selection
                sessionStorage.removeItem('selectedRecipeId');
            }
        });
    </script>
</body>
</html>

