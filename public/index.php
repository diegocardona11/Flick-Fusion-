<?php
// show PHP errors while developing (remove later)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include shared header (with dynamic navbar and session)
include 'partials/header.php';
?>
<!DOCTYPE html>
<!-- Main Content Area -->
  <main>
    <h1>Hello Flick Fusion!</h1>
    <p>If you can read this, PHP is working ✅</p>
  </main>

  <?php if (isset($_SESSION['user_id'])): ?>
      <p>Welcome back, user #<?php echo htmlspecialchars($_SESSION['username']); ?>!
        Go to your <a href="dashboard.php">Dashboard</a>.
  <?php else: ?>
    <p><a href="login.php">Login</a> or <a href="register.php">Register</a> to start tracking your movies.</p>
  <?php endif; ?>
</main>

<?php include 'partials/footer.php'; ?>