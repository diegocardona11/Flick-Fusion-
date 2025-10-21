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

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/controllers/movies.php';

// Get user data
$username = htmlspecialchars($_SESSION['username'] ?? 'User');
$userId = $_SESSION['user_id'];

// Get user's movie list
$myList = getMoviesForUser($pdo, $userId);

?>
<?php include 'partials/header.php'; ?>


<div class="container">
    <h2>My Movie List</h2>
    <p>Welcome, <strong><?= $username ?></strong>! This is your personal movie list.</p>

    <!-- My List Section -->
    <hr class="section-divider">

    <?php if ($myList): ?>
      <ul class="movie-list">
        <?php foreach ($myList as $row): ?>
          <li class="movie-list-item">
            <?php if (!empty($row['poster_url'])): ?>
              <img src="<?= htmlspecialchars($row['poster_url']) ?>" alt="Poster">
            <?php endif; ?>
            <div class="movie-details">
              <strong><?= htmlspecialchars($row['title']) ?></strong>
              <?php if (!empty($row['year'])): ?>
                (<?= (int)$row['year'] ?>)
              <?php endif; ?>
              <div class="movie-actions">
                <!-- Update Rating -->
                <form method="post" action="mylist.php" class="form-inline">
                  <input type="hidden" name="movie_id" value="<?= (int)$row['movie_id'] ?>">
                  <label>
                    Rating:
                    <input
                      type="number"
                      name="score"
                      min="1"
                      max="10"
                      value="<?= (int)($row['score_10'] ?? 5) ?>"
                      class="rating-input"
                    >
                  </label>
                  <button type="submit" class="btn">Update</button>
                </form>

                <!-- Remove from List -->
                <form method="post" action="mylist.php" class="form-inline">
                  <input type="hidden" name="remove_movie_id" value="<?= (int)$row['movie_id'] ?>">
                  <button type="submit" class="btn btn-danger">Remove</button>
                </form>
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>You haven’t added any movies yet. <a href="movies.php">Go add some!</a></p>
    <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>
