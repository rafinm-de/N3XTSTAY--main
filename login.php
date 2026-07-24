<?php
$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/Session.php';

Session::start();

if (Session::isLoggedIn()) {
    if (Session::isAdmin()) {
        header('Location: ' . SITE_URL . '/admin/dashboard.php');
    } else {
        header('Location: ' . SITE_URL . '/index.php');
    }
    exit;
}
?>

<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem;">
    <div style="background: white; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); max-width: 400px; width: 100%;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">🏨 Login</h1>
            <p style="color: var(--gray-600);">Sign in to your account</p>
        </div>

        <form id="loginForm" method="POST" action="auth/login.php">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email Address</label>
                <input type="email" name="login_email" class="form-input" placeholder="your@email.com" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--gray-200); border-radius: 0.5rem;">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Password</label>
                <input type="password" name="login_password" class="form-input" placeholder="Enter your password" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--gray-200); border-radius: 0.5rem;">
            </div>

            <div id="loginError" class="alert alert-error" style="display: none; margin-bottom: 1rem; padding: 0.75rem; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 500;"></div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem;">
                Login
            </button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; color: var(--gray-600);">
            Don't have an account?
            <a href="index.php" style="color: var(--primary); font-weight: 600;">Go to homepage</a>
        </p>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const errorDiv = document.getElementById('loginError');
    errorDiv.style.display = 'none';

    try {
        const response = await fetch('auth/login.php', {
            method: 'POST',
            body: formData
        });

        const text = await response.text();
        const data = JSON.parse(text);

        if (data.success) {
            window.location.href = data.redirect_url;
        } else {
            errorDiv.textContent = data.message;
            errorDiv.style.display = 'block';
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
    }
});
</script>

</body>
</html>
