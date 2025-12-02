<?php
// Define entry point for backend includes 
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    define('FLICK_FUSION_ENTRY_POINT', true);
}
/**
 * register.php
 * ----------------------------------------
 * User registration page with form validation
 *   - Collects username, email, password, and confirm password
 *   - Validates inputs (non-empty, email format, password match)
 *   - Calls registerUser() to create account from auth.php
 *   - Auto-login on success and redirect to dashboard.php
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/controllers/auth.php'; // authentication controller

// Start session (needed for auto-login and error messages)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Initialize variables for form data and errors
$username = '';
$email = '';
$errors = [];
$success = false;

// ==== Handle form submission ====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect and trim form data
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // ==== VALIDATION: username ====
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 4 || strlen($username) > 20) {
        $errors[] = 'Username must be between 4 and 20 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]*$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }

    // ==== VALIDATION: email ====
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // ==== VALIDATION: password ====
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one letter and one number.';
    }


    // ==== VALIDATION: confirm password ====
    if (empty($confirm_password)) {
        $errors[] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // ATTEMPT REGISTRATION (if no validation errors)
    if (empty($errors)) {
        $registration_result = registerUser($pdo, $username, $email, $password);
        
        if ($registration_result) {
            // Registration successful, auto-login the user
            $loginSuccess = loginUser($pdo, $username, $password);

            if ($loginSuccess) {
                // Redirect to dashboard after successful login
                header('Location: mylist.php');
                exit();
            } else {
                // Registration succeeded but login failed
                $success = true;
                $errors[] = 'Registration succeeded but auto-login failed. Please log in manually.';
            }
        } else {
            // Registration failed (likely due to duplicate username/email)
            $errors[] = 'Registration failed. Username or email may already be taken.';
        }
    }
}
?>

<?php include 'partials/header.php'; ?>

    <div class="container auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon" aria-hidden="true">FlickFusion</div>
                <h2 class="auth-title">Create your account</h2>
                <p class="auth-subtitle">Join with a username and email</p>
                <div class="auth-light-separator" aria-hidden="true"></div>
            </div>

            <!-- Display errors if any exist -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error auth-alert">
                    <strong>Registration failed</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="alert-close" aria-label="Close" onclick="this.closest('.alert').remove()">✕</button>
                </div>
            <?php endif; ?>

            <!-- Display success message if registration succeeded but auto-login failed -->
            <?php if ($success && !empty($errors)): ?>
                <div class="alert alert-success auth-alert">
                    <strong>Account successfully created!</strong> Please <a href="login.php">log in</a>.
                    <button type="button" class="alert-close" aria-label="Close" onclick="this.closest('.alert').remove()">✕</button>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST" action="register.php" class="auth-form" novalidate>
                <div class="form-field">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?php echo htmlspecialchars($username); ?>" 
                        required
                        placeholder="Choose a username"
                        autofocus
                    >
                    <small class="field-hint">4–20 characters, letters, numbers, underscore</small>
                </div>

                <div class="form-field">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($email); ?>" 
                        required
                        placeholder="you@example.com"
                    >
                </div>

                <div class="form-field">
                    <div class="field-label-row">
                        <label for="password">Password</label>
                        <button type="button" class="link-button" id="toggleRegPassBtn">Show</button>
                    </div>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="••••••••"
                    >
                    <small class="field-hint">Minimum 8 characters, include letters and numbers</small>
                </div>

                <div class="form-field">
                    <div class="field-label-row">
                        <label for="confirm_password">Confirm Password</label>
                        <button type="button" class="link-button" id="toggleRegConfirmBtn">Show</button>
                    </div>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                        placeholder="••••••••"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>

                <div class="auth-divider"><span>or</span></div>
                <div class="auth-actions-row">
                    <a class="btn btn-secondary btn-block" href="login.php">Sign in instead</a>
                </div>
            </form>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toggleRegPassBtn = document.getElementById('toggleRegPassBtn');
                const regPassInput = document.getElementById('password');
                const toggleRegConfirmBtn = document.getElementById('toggleRegConfirmBtn');
                const regConfirmInput = document.getElementById('confirm_password');

                if (toggleRegPassBtn && regPassInput) {
                    toggleRegPassBtn.addEventListener('click', function() {
                        const isText = regPassInput.type === 'text';
                        regPassInput.type = isText ? 'password' : 'text';
                        toggleRegPassBtn.textContent = isText ? 'Show' : 'Hide';
                    });
                }

                if (toggleRegConfirmBtn && regConfirmInput) {
                    toggleRegConfirmBtn.addEventListener('click', function() {
                        const isText = regConfirmInput.type === 'text';
                        regConfirmInput.type = isText ? 'password' : 'text';
                        toggleRegConfirmBtn.textContent = isText ? 'Show' : 'Hide';
                    });
                }
            });
            </script>

        </div>
    </div>

<?php include 'partials/footer.php'; ?>