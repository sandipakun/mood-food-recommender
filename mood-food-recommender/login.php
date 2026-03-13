<?php require_once __DIR__ . '/config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mood Food Recommender</title>
    
    <script>
        window.APP_BASE_URL = '<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>';
    </script>
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

    <main class="container-custom" style="max-width: 450px; margin: 2rem auto; padding: 1rem;">
        <div class="recipe-detail" style="padding: 1.5rem;">
            <h2 class="text-center mb-3" style="font-size: 1.75rem;">Welcome Back! 👋</h2>
            <p class="text-center text-muted mb-3" style="font-size: 0.9rem;">Login to save your favorite recipes</p>
            
            <div id="alert-container"></div>
            
            <form id="login-form">
                <div class="mb-2">
                    <label for="username" class="form-label" style="font-size: 0.9rem; margin-bottom: 0.4rem;">Username or Email</label>
                    <input type="text" class="form-control" id="username" name="username" required aria-required="true" style="padding: 0.6rem 0.8rem;">
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label" style="font-size: 0.9rem; margin-bottom: 0.4rem;">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control cute-input" id="password" name="password" required aria-required="true" style="padding: 0.6rem 0.8rem;">
                        <button class="password-toggle-btn toggle-password" type="button" data-target="password" aria-label="Toggle password visibility" style="padding: 0.6rem 0.8rem;">👁</button>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary w-100 mb-2" style="padding: 0.7rem;">Login</button>
            </form>
            
            <p class="text-center mb-0" style="font-size: 0.9rem;">
                Don't have an account? <a href="register.php">Sign up here</a>
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

        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'login');
            
            try {
                const response = await fetch(API_BASE + '/auth.php?action=login', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('success', 'Login successful! Redirecting...');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1000);
                } else {
                    showAlert('error', data.error || 'Login failed');
                }
            } catch (error) {
                showAlert('error', 'Something went wrong. Please try again.');
            }
        });
    </script>
</body>
</html>

