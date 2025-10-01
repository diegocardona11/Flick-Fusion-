<?php
// header.php
// Shared header and navigation for all pages
// Starts the session to track logged-in user state

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flick Fusion</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <header>
    <h1>Flick Fusion</h1>

    <!-- Global Navigation Bar -->
      <nav class="navbar">
            <a href="index.php">Home</a> |
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a> |
                <a href="movie.php">Movies</a> |
                <a href="friends.php">Friends</a> |
                <a href="compare.php">Compare</a> |
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a> |
                <a href="register.php">Register</a>
            <?php endif; ?>
      </nav>
      <hr>
    </header>
</body>