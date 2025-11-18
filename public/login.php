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

    <div class="container">
        <h2>Login</h2>

        <!-- Display errors if any exist -->
        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <strong>Login failed:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="login.php" class="form-box">
            <div class="form-group">
                <label for="identifier">Username or Email</label>
                <input 
                    type="text" 
                    id="identifier" 
                    name="identifier" 
                    value="<?php echo htmlspecialchars($identifier); ?>" 
                    required
                    placeholder="Enter your username or email"
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    placeholder="Enter your password"
                >
            </div>

               <div style="margin-bottom: 18px;">
                   <input type="checkbox" id="showPassword" onclick="togglePassword('password', 'showPassword')">
                   <label for="showPassword" style="font-size: 14px; cursor:pointer;">Show Password</label>
               </div>

            <button type="submit" class="btn">Login</button>
        </form>

    <script>
    function togglePassword(inputId, checkboxId) {
        var input = document.getElementById(inputId);
        var checkbox = document.getElementById(checkboxId);
        if (input && checkbox) {
            input.type = checkbox.checked ? 'text' : 'password';
        }
    }
    </script>

        <!-- Link to registration page for new users -->
        <div class="login-link">
            Don't have an account? <a href="register.php">Register here</a>.
        </div>
    </div>

<?php include 'partials/footer.php'; ?>
