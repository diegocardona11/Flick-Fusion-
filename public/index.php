<?php
// Define entry point for backend includes 
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    define('FLICK_FUSION_ENTRY_POINT', true);
}
// ini_set('display_errors', 1);  // not shown for production
// error_reporting(E_ALL);

include 'partials/header.php';
?>

<main class="container">
  <div class="welcome-message">
    <?php if (!empty($_SESSION['user_id'])): ?>
      <!-- Logged in user greeting -->
      <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'user') ?>!</h1>
      <p class="welcome-subtitle">Discover new movies and manage your watchlist.</p>
      <div class="welcome-actions">
          <a href="dashboard.php" class="btn btn-primary">Go to My List</a>
          <a href="movies.php" class="btn btn-secondary">Search Movies</a>
      </div>
    <?php else: ?>
      <!-- Guest user greeting -->
      <h1 class="welcome-title">Welcome to Flick Fusion!</h1>
      <p class="welcome-subtitle">Discover new movies and manage your watchlist.</p>
      <div class="welcome-actions">
        <a href="login.php" class="btn btn-primary">Login</a>
        <a href="register.php" class="btn btn-secondary">Register</a>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php include 'partials/footer.php'; ?>
