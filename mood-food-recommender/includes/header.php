<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include utils for isLoggedIn function
require_once __DIR__ . '/utils.php';
?>
<header class="header-custom">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="<?php echo isLoggedIn() ? 'dashboard.php' : 'index.php'; ?>">
                <i class="fas fa-utensils me-2"></i>MoodFood
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<style>
.header-custom {
    background: var(--light-pink, #ffebf3);
    border-bottom: 2px solid var(--baby-pink, #ffb6c1);
    padding: 1rem 0;
    box-shadow: 0 2px 10px rgba(255, 182, 193, 0.2);
}

.navbar-brand {
    font-weight: 700;
    color: var(--dark-pink, #ff69b4) !important;
    font-size: 1.5rem;
}

.nav-link {
    color: var(--text-dark, #5a3d5c) !important;
    font-weight: 500;
    transition: color 0.3s ease;
}

.nav-link:hover {
    color: var(--accent-pink, #ff1493) !important;
}
</style>

