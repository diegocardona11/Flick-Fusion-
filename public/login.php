<?php
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    define('FLICK_FUSION_ENTRY_POINT', true);
}
/**
 * login.php
 * ----------------------------------------
 * User login page with form validation
 *   - Collects username/email and password
 *   - Validates inputs (non-empty, email format, password match)
 *   - Calls loginUser() from auth.php
 *   - Redirect to index.php on success
 */

require_once __DIR__ . '/../backend/controllers/auth.php'; // authentication controller

// Start session (needed for login and error messages)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// If user is already logged in, redirect to index
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Initialize variables for form data and errors
$identifier = '';
$errors = [];

// ==== Handle form submission ====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect and trim form data
    $identifier = trim($_POST['identifier'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // ==== VALIDATION: identifier (username or email) ====
    if (empty($identifier)) {
        $errors[] = 'Username or email is required.';
    }

    // ==== VALIDATION: password ====
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    // ATTEMPT LOGIN (if no validation errors)
    if (empty($errors)) {
        $loginSuccess = loginUser($pdo, $identifier, $password);
        
        if ($loginSuccess) {
            // Login successful, redirect to index.php
            header('Location: index.php');
            exit();
        } else {
            // Login failed (invalid credentials)
            $errors[] = 'Invalid username/email or password. Please try again.';
        }
    }
}
?>

<?php include 'partials/header.php'; ?>

    <div class="container auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon" aria-hidden="true">FlickFusion</div>
                <h2 class="auth-title">Welcome back</h2>
                <p class="auth-subtitle">Use your username or email</p>
                <div class="auth-light-separator" aria-hidden="true"></div>
            </div>

        <!-- Display errors if any exist -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error auth-alert">
                <strong>Login failed</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="alert-close" aria-label="Close" onclick="this.closest('.alert').remove()">✕</button>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="login.php" class="auth-form" novalidate>
            <div class="form-field">
                <label for="identifier">Username or Email</label>
                <input 
                    type="text" 
                    id="identifier" 
                    name="identifier" 
                    value="<?php echo htmlspecialchars($identifier); ?>" 
                    required
                    placeholder="you@example.com"
                    autofocus
                >
            </div>

            <div class="form-field">
                <div class="field-label-row">
                    <label for="password">Password</label>
                    <button type="button" class="link-button" id="togglePassBtn">Show</button>
                </div>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    placeholder="••••••••"
                >
                <small class="field-hint">Minimum 8 characters</small>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign In</button>

            <div class="auth-divider"><span>or</span></div>
            <div class="auth-actions-row">
                <a class="btn btn-secondary btn-block" href="register.php">Create an account</a>
            </div>
        </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('togglePassBtn');
        const passInput = document.getElementById('password');
        if (toggleBtn && passInput) {
            toggleBtn.addEventListener('click', function() {
                const isText = passInput.type === 'text';
                passInput.type = isText ? 'password' : 'text';
                toggleBtn.textContent = isText ? 'Show' : 'Hide';
            });
        }
    });
    </script>

        
        </div>
    </div>

<?php include 'partials/footer.php'; ?>
