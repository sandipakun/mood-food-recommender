<?php
require_once 'config/database.php';
require_once 'includes/utils.php';
require_once 'includes/auth.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect logged-in users to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodFood - Recipe Recommendations Based on Your Mood</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animations.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-80">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">Find Perfect Recipes for Your <span class="highlight">Mood</span> 🍽️</h1>
                        <p class="hero-subtitle">Tell us how you're feeling and discover delicious recipes tailored to your emotional state. Comfort food for stress, energy boosters for tiredness, and more!</p>
                        <div class="hero-features">
                            <div class="feature-item">
                                <i class="fas fa-heart"></i>
                                <span>Mood-based recommendations</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-utensils"></i>
                                <span>Nutrition-focused recipes</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-bolt"></i>
                                <span>Quick and easy preparation</span>
                            </div>
                        </div>
                        <div class="hero-buttons mt-4">
                            <?php if (isLoggedIn()): ?>
                                <a href="dashboard.php" class="btn btn-hero-primary">Go to Dashboard</a>
                            <?php else: ?>
                                <a href="register.php" class="btn btn-hero-primary">Get Started Free</a>
                                <a href="login.php" class="btn btn-hero-secondary">Sign In</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <div class="floating-card card-1">
                            <div class="card-emoji">😊</div>
                            <p>Happy Mood Recipes</p>
                        </div>
                        <div class="floating-card card-2">
                            <div class="card-emoji">😴</div>
                            <p>Energy Boosters</p>
                        </div>
                        <div class="floating-card card-3">
                            <div class="card-emoji">😫</div>
                            <p>Comfort Foods</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">How MoodFood Works</h2>
                <p class="section-subtitle">Three simple steps to your perfect meal</p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-step">
                        <div class="step-number">1</div>
                        <h4>Select Your Mood</h4>
                        <p>Choose how you're feeling from our cute emoji-based mood selector</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-step">
                        <div class="step-number">2</div>
                        <h4>Get AI Recommendations</h4>
                        <p>Our smart algorithm suggests recipes perfect for your current mood</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-step">
                        <div class="step-number">3</div>
                        <h4>Cook & Enjoy</h4>
                        <p>Follow simple recipes with nutritional info and preparation tips</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/animations.js"></script>
</body>
</html>
