<?php
// Prevent direct script access
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    http_response_code(403); // Forbidden
    exit('Access denied.');
}
/**
 * auth.php
 * ----------------------------------------
 * Authentication controller that handles user account actions
 *   - registerUser(): inserts new user into DB with hashed password
 *   - loginUser(): verifies credentials, starts session
 *   - logoutUser(): ends user session
 * ----------------------------------------
 * Uses: db.php for connection, User.php model for structure
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

/**
 * Create a new account
 * --------------------------------
 * @param string $username Desired username (must be unique)
 * @param string $email User's email address (must be unique)
 * @param string $password User's password (must be at least 8 characters)
 * @return bool True on success, false on failure (e.g. username/email taken)
 * --------------------------------
 * Notes:
 * - ensures required fields are not empty
 * - hashes password securely with password_hash()
 * - inserts the user into the DB
 * - handles duplicate username/email errors
 */
function registerUser(PDO $pdo, $username, $email, $password) {

    // Basic validation guard
    if (empty($username) || empty($email) || empty($password)) {
        return false;
    }   

    // Hash password with a modern default algorithm
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute insert statement (try to insert new user)
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, email, password_hash) 
            VALUES (:username, :email, :password_hash)"
        );
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $passwordHash
        ]);
        return true; // Registration successful
    } catch (PDOException $e) {
        // Handle duplicate username/email or other DB errors
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}

/**
 * Log in an existing user
 * --------------------------------
 * @param string $identifier    Username or Email
 * @param string $password      Plain text password
 * @return bool                 True on success, false on failure (invalid credentials)
 * --------------------------------
 * Notes:
 * - Looks up user in DB by username or email
 * - Verifies password against stored hash
 * - Starts a session and stores user info in $_SESSION on success
 */
function loginUser(PDO $pdo, $identifier, $password) {

    // Find user by username or email
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE username = :id_username OR email = :id_email
        LIMIT 1
    ");
    $stmt->execute([
        ':id_username' => $identifier, 
        ':id_email' => $identifier
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password hash
    if ($user && password_verify($password, $user['password_hash'])) {
        // Start session and store user info
        if (session_status() !== PHP_SESSION_ACTIVE) { 
            session_start();
        }
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];

        return true; // Login successful
    } else {
        return false; // Invalid credentials
    }
}

/** 
 * End the current session and log the user out 
 * --------------------------------
 * @return void
 * --------------------------------
 */
function logoutUser() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();    // remove all session data
        session_destroy();  // end the session
    }
}