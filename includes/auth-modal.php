<!-- Authentication Modal -->
<div class="modal" id="authModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="authModalTitle">Login</h3>
            <button class="modal-close" onclick="closeAuthModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Login Form -->
            <form id="loginForm" class="auth-form" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="login_email" class="form-input" placeholder="your@email.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="login_password" class="form-input" placeholder="Enter your password" required>
                </div>

                <div id="loginError" class="alert alert-error" style="display: none; margin: 0.75rem 0; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 500;"></div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Login
                </button>

                <p style="text-align: center; margin-top: 15px; color: var(--gray-600);">
                    Don't have an account?
                    <a href="#" onclick="switchAuthForm('register'); return false;"
                        style="color: var(--primary); font-weight: 600;">Sign up</a>
                </p>
            </form>

            <!-- Register Form -->
            <form id="registerForm" class="auth-form" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="register_name" class="form-input" placeholder="John Doe" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="register_email" class="form-input" placeholder="your@email.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="register_phone" class="form-input" placeholder="01XXXXXXXXX" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="register_password" class="form-input" placeholder="Min. 6 characters" required minlength="6">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="register_confirm_password" class="form-input"
                        placeholder="Re-enter password" required minlength="6">
                </div>

                <div id="registerError" class="alert alert-error" style="display: none; margin: 0.75rem 0; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 500;"></div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    Create Account
                </button>

                <p style="text-align: center; margin-top: 15px; color: var(--gray-600);">
                    Already have an account?
                    <a href="#" onclick="switchAuthForm('login'); return false;"
                        style="color: var(--primary); font-weight: 600;">Login</a>
                </p>
            </form>
        </div>
    </div>
</div>

<style>
    .auth-form {
        animation: fadeIn 0.3s ease;
    }
</style>