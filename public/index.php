<?php
// Define entry point for backend includes 
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    define('FLICK_FUSION_ENTRY_POINT', true);
}
// ini_set('display_errors', 1);  // not shown for production
// error_reporting(E_ALL);

include 'partials/header.php';
?>
<main>
  <?php if (!empty($_SESSION['user_id'])): ?>
    <h1>Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'user') ?>!</h1>
    <p>Go to your <a href="dashboard.php">Dashboard</a> or <a href="/movies.php">Search Movies</a>.</p>
  <?php else: ?>
    <h1>Welcome to Flick Fusion 🎬</h1>
    <p><a href="login.php">Login</a> or 
       <a href="register.php">Register</a> to start tracking your movies.</p>
    <p>Or try the <a href="movies.php">Movies search</a> right now.</p>
  <?php endif; ?>
</main>
<?php include 'partials/footer.php'; ?>
