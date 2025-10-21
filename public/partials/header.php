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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Flick Fusion</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Alegreya+Sans:wght@400;500;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
  
  <!-- Allows to see CSS changes without clearing cache -->
  <link rel="stylesheet" href="css/styles.css?v=<?= filemtime(__DIR__ . '/../css/styles.css') ?>"> 
</head>
<body>

<header class="main-header">
  <div class="header-content container">
    <a href="index.php" class="logo"><strong>Flick Fusion</strong></a>

    <nav class="main-nav">
          <?php if (!empty($_SESSION['user_id'])): ?>
              <a href="index.php">Home</a>
              <a href="movies.php">Movies</a>
              <a href="mylist.php">My List</a>
          <?php else: ?>
              <a href="index.php">Explore</a>
              <a href="movies.php">Search</a>
          <?php endif; ?>
    </nav>

    <div class="header-actions">
      <!-- Search Bar -->
      <form action="movies.php" method="get" class="header-search">
          <input type="text" name="q" placeholder="Search movies..." class="search-input">
          <button type="submit" class="search-button">
              <!-- Search Icon SVG -->
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          </button>
      </form>

      <!-- User Auth Section -->
      <div class="user-auth">
          <?php if (!empty($_SESSION['user_id'])): ?>
            <div class="user-menu">
              <button id="user-menu-btn" class="user-menu-trigger">
                <!-- User Icon SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-3-3.87"></path><path d="M4 21v-2a4 4 0 0 1 3-3.87"></path><circle cx="12" cy="7" r="4"></circle></svg>
              </button>
              <div id="user-menu-dropdown" class="user-menu-dropdown">
                <div class="dropdown-header">
                  Signed in as <br><strong><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></strong>
                </div>
                <a href="profile.php">My Profile</a>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
          </div>
          <?php else: ?>
            <a href="login.php" class="button button-secondary">Login</a>
            <a href="register.php" class="button button-primary">Register</a>
          <?php endif; ?>
  </div>
</header>

<!-- This div will wrap all main page content -->
 <div class="main-content">
