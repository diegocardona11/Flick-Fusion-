<?php
/**
 * register.php
 * ----------------------------------------
 * User registration page with form validation
 *   - Collects username, email, password, and confirm password
 *   - Validates inputs (non-empty, email format, password match)
 *   - Calls registerUser() to create account from auth.php
 *   - Auto-login on success and redirect to dashboard.php
 */


require_once __DIR__ . '/../../backend/controllers/auth.php'; // authentication controller

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
        $registration_result = registerUser($username, $email, $password);
        
        if ($registrationSuccess) {
            // Registration successful, auto-login the user
            $loginSuccess = loginUser($username, $password);

            if ($loginSuccess) {
                // Redirect to dashboard after successful login
                header('Location: dashboard.php');
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

    <div class="container">
        <h2>Register</h2>

        <!-- Display errors if any exist -->
        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Display success message if registration succeeded but auto-login failed -->
        <?php if ($success && !empty($errors)): ?>
            <div class="success-box">
                <strong>Account successfully created!</strong> Please <a href="login.php">log in</a>.
            </div>
        <?php endif; ?>
    </div>

    <!-- Registration Form -->
    <form method="POST" action="register.php" class="form-box">
        <div class="form-group">
            <label for="username">Username</label>
            <input 
                type="text" 
                id="username" 
                name="username" 
                value="<?php echo htmlspecialchars($username); ?>" 
                required
                placeholder="Enter username (3-20 characters)"
            >
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="<?php echo htmlspecialchars($email); ?>" 
                required
                placeholder="Enter a valid email address"
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
                placeholder="At least 8 characters, including letters and numbers"
            >
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                required
                placeholder="Re-enter your password"
            >
        </div>

        <button type="submit" class="btn">Create Account</button>
    </form>

    <!-- Link to login page for existing users -->
     <div class="login-link">
        Already have an account? <a href="login.php">Log in here</a>.
     </div>
    </div>
</body>

<?php include 'partials/footer.php'; ?>

</html>