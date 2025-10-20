<?php
// Shared header and simple nav

// ini_set('display_errors', 1);  // not shown for production
// error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Flick Fusion</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Alegreya+Sans:wght@400;500;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
  <!-- Allows to see CSS changes without clearing cache -->
  <link rel="stylesheet" href="css/styles.css?v=<?= filemtime(__DIR__ . '/../css/styles.css') ?>">  
</head>
<body>
<header>
  <a href="index.php"><strong>Home</strong></a>
  <a href="movies.php">Movies</a>
  <?php if (!empty($_SESSION['user_id'])): ?>
    <a href="dashboard.php">My List</a>
    <a href="friends.php">Friends</a>
    <span>Hi, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
    <a href="logout.php">Logout</a>
  <?php else: ?>
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
  <?php endif; ?>
</header>
<hr>
