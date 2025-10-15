<?php
/**
 * dashboard.php
 * ----------------------------------------
 * User dashboard - displays personalized content for logged-in users
 *   - Shows welcome message with username
 *   - Requires active session (redirects to login if not logged in)
 */

// Start session if not already started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Check if user is logged in, redirect to login page if not
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get username from session
$username = htmlspecialchars($_SESSION['username'] ?? 'User');
?>

<?php include 'partials/header.php'; ?>

<div class="container">
    <h2>Dashboard</h2>
    <p>Welcome, <strong><?php echo $username; ?></strong>!</p>
    
    <div class="dashboard-content">
        <p>You are now logged in to Flick Fusion.</p>
        <p>From here you can:</p>
        <ul>
            <li><a href="movies.php">Search for movies</a></li>
            <li><a href="friends.php">Manage your friends</a></li>
            <li>View your movie ratings and reviews</li>
        </ul>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
