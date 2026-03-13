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
    <meta name="description" content="View full recipe details with ingredients, instructions, and nutrition information">
    <title>Recipe Details - Mood Food Recommender</title>
    
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
        <div id="recipe-detail-container">
            <div class="loading">
                <div class="spinner"></div>
                <p>Loading recipe...</p>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/app.js"></script>
    
    <script>
        // Load recipe on page load
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const recipeId = urlParams.get('id');
            
            if (recipeId) {
                // Save recipe ID to sessionStorage
                sessionStorage.setItem('selectedRecipeId', recipeId);
                loadRecipeDetail(recipeId);
            } else {
                // Try to restore from sessionStorage
                const savedRecipeId = sessionStorage.getItem('selectedRecipeId');
                if (savedRecipeId) {
                    // Update URL without reload
                    window.history.replaceState({}, '', `recipe.php?id=${savedRecipeId}`);
                    loadRecipeDetail(savedRecipeId);
                } else {
                    document.getElementById('recipe-detail-container').innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">🔍</div>
                            <p>Recipe not found. Please select a recipe from the dashboard.</p>
                            <a href="dashboard.php" class="btn-primary">Go to Dashboard</a>
                        </div>
                    `;
                }
            }
        });
    </script>
</body>
</html>

