<footer class="footer-custom mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-utensils me-2"></i>MoodFood</h5>
                <p>Find perfect recipes based on your mood. Comfort food for stress, energy boosters for tiredness, and delicious meals for every emotional state.</p>
            </div>
            <div class="col-md-3">
                <h6>Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6>Connect</h6>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12 text-center">
                <p>&copy; <?php echo date('Y'); ?> MoodFood Recommender. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<style>
.footer-custom {
    background: var(--light-pink, #ffebf3);
    padding: 3rem 0 2rem;
    border-top: 2px solid var(--baby-pink, #ffb6c1);
    color: var(--text-dark, #5a3d5c);
}

.footer-custom h5, .footer-custom h6 {
    color: var(--dark-pink, #ff69b4);
    font-weight: 700;
}

.footer-custom a {
    color: var(--text-dark, #5a3d5c);
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-custom a:hover {
    color: var(--accent-pink, #ff1493);
}

.social-links a {
    display: inline-block;
    margin-right: 1rem;
    font-size: 1.2rem;
}
</style>

