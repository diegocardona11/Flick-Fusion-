<?php
// Shared header + simple nav
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Flick Fusion</title>
  <style>
    body { font-family: system-ui, Arial, sans-serif; margin: 20px; }
    header a { margin-right: 12px; }
    hr { margin: 10px 0 20px; }
  </style>
</head>
<body>
<header>
  <a href="/index.php"><strong>Home</strong></a>
  <a href="/movies.php">Movies</a>
  <?php if (!empty($_SESSION['user_id'])): ?>
    <a href="/dashboard.php">My List</a>
    <a href="/friends.php">Friends</a>
    <span>Hi, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
    <a href="/logout.php">Logout</a>
  <?php else: ?>
    <a href="/login.php">Login</a>
    <a href="/register.php">Register</a>
  <?php endif; ?>
</header>
<hr>
