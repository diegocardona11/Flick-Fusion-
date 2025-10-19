<?php
// Define entry point for backend includes 
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    define('FLICK_FUSION_ENTRY_POINT', true);
}
/**
 * logout.php
 * ----------------------------------------
 * Logs out the current user and redirects to home page
 *   - Calls logoutUser() from auth.php
 *   - Ends the session
 *   - Redirects to index.php
 */

require_once __DIR__ . '/../backend/controllers/auth.php';

// Start session if not already started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Call logout function to clear session
logoutUser();

// Redirect to home page
header('Location: index.php');
exit();
