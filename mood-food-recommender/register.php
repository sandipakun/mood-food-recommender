<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Mood Food Recommender</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <header class="header">
        <div class="container-custom">
            <h1><a href="index.php" style="text-decoration: none; color: inherit;">🍽️ Mood Food Recommender</a></h1>
        </div>
    </header>

    <main class="container-custom" style="max-width: 420px; margin: 1.5rem auto; padding: 0.5rem;">
        <div class="recipe-detail" style="padding: 1.25rem;">
            <h2 class="text-center mb-2" style="font-size: 1.5rem;">Join Us! 🎉</h2>
            <p class="text-center text-muted mb-2" style="font-size: 0.85rem; margin-bottom: 0.5rem !important;">Create an account to save recipes</p>
            <div id="alert-container" style="margin-bottom: 0.75rem;"></div>
            <form id="register-form">
                <div class="mb-1">
                    <label for="username" class="form-label" style="font-size: 0.85rem; margin-bottom: 0.3rem;">Username</label>
                    <input type="text" class="form-control cute-input" id="username" name="username" required aria-required="true" minlength="3" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                </div>
                <div class="mb-1">
                    <label for="email" class="form-label" style="font-size: 0.85rem; margin-bottom: 0.3rem;">Email</label>
                    <input type="email" class="form-control cute-input" id="email" name="email" required aria-required="true" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                </div>
                <div class="mb-1">
                    <label for="password" class="form-label" style="font-size: 0.85rem; margin-bottom: 0.3rem;">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control cute-input" id="password" name="password" required aria-required="true" minlength="6" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                        <button class="password-toggle-btn toggle-password" type="button" data-target="password" aria-label="Toggle password visibility" style="padding: 0.5rem 0.75rem;">👁</button>
                    </div>
                    <small class="text-muted" style="font-size: 0.75rem;">Min 6 characters</small>
                </div>
                <div class="mb-2">
                    <label for="confirm_password" class="form-label" style="font-size: 0.85rem; margin-bottom: 0.3rem;">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control cute-input" id="confirm_password" name="confirm_password" required aria-required="true" minlength="6" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                        <button class="password-toggle-btn toggle-password" type="button" data-target="confirm_password" aria-label="Toggle confirm password visibility" style="padding: 0.5rem 0.75rem;">👁</button>
                    </div>
                </div>
                <button type="submit" class="btn-primary w-100 mb-1" style="padding: 0.6rem; font-size: 0.95rem;">Sign Up</button>
            </form>
            <p class="text-center mb-0 mt-2" style="font-size: 0.85rem;">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (!input) return;
                const isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');
                btn.classList.toggle('active', isPassword);
                btn.textContent = isPassword ? '🙈' : '👁';
            });
        });

        document.getElementById('register-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                showAlert('error', 'Passwords do not match. Please try again.');
                return;
            }
            
            const formData = new FormData(this);
            formData.append('action', 'register');
            
            try {
                const response = await fetch('api/auth.php?action=register', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('success', 'Registration successful! Redirecting...');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1000);
                } else {
                    showAlert('error', data.error || 'Registration failed');
                }
            } catch (error) {
                showAlert('error', 'Something went wrong. Please try again.');
            }
        });
    </script>
</body>
</html>

