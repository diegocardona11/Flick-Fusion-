<?php
// Define entry point for backend includes 
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    define('FLICK_FUSION_ENTRY_POINT', true);
}

/**
 * profile.php
 * ----------------------------------------
 * User profile page - basic skeleton
 */

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/controllers/auth.php';

// Ensure user is logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$currentUsername = $_SESSION['username'];
$currentEmail = $_SESSION['email'];

include 'partials/header.php';
?>

<main class="container profile-page">
    <section class="profile-header">
        <div class="profile-avatar">
            <!-- User Icon SVG -->
            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M4 21v-2a4 4 0 0 1 3-3.87"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <div class="profile-info">
            <h1 class="profile-username"><?= htmlspecialchars($currentUsername) ?></h1>
            <p class="profile-email"><?= htmlspecialchars($currentEmail) ?></p>
        </div>
    </section>

    <section class="profile-content">
        <p>Profile features coming soon...</p>
    </section>
</main>

<?php include 'partials/footer.php'; ?>
