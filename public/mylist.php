<?php
/**
 * mylist.php
 * ----------------------------------------
 * User movie list - displays users personal movie list 
 */

// Define entry point for backend includes
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    define('FLICK_FUSION_ENTRY_POINT', true);
}

// Start session if not already started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Check if user is logged in, redirect to login page if not
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<?php include 'partials/header.php'; ?>


<div class="movie-container">
    <h2>My Movie List</h2>
    <p>This is where your personal movie list will be displayed.</p>
    <!-- Movie list content goes here -->
</div>

<?php include 'partials/footer.php'; ?>
