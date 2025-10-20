<?php
// Define entry point for backend includes
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
  define('FLICK_FUSION_ENTRY_POINT', true);
}

require __DIR__ . '/../backend/api/omdb.php';
require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/controllers/movies.php';
include 'partials/header.php';

// $pdo is now available from db.php
$userId = $_SESSION['user_id'] ?? null;
$flash = '';

// Redirect to login if user is not authenticated
if (!$userId) {
    header('Location: login.php');
    exit;
}

// ---------------------------
// Handle POST Actions
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // ADD TO LIST
  if (!empty($_POST['imdbID'])) {
    $imdbID = trim($_POST['imdbID']);
    $movieId = addMovieToLocalDB($pdo, $imdbID);
    if ($movieId) {
      addMovieToUserList($pdo, $userId, $movieId, 5);
      $flash = "✅ Added to your list!";
    } else {
      $flash = "⚠️ Couldn't fetch movie details.";
    }
  }

  // UPDATE RATING
  if (!empty($_POST['movie_id']) && isset($_POST['score'])) {
    $movieId = (int)$_POST['movie_id'];
    $score   = (int)$_POST['score'];
    if (updateRating($pdo, $userId, $movieId, $score)) {
      $flash = "⭐ Rating updated.";
    } else {
      $flash = "⚠️ Could not update rating.";
    }
  }

  // REMOVE FROM LIST
  if (!empty($_POST['remove_movie_id'])) {
    $movieId = (int)$_POST['remove_movie_id'];
    if (removeMovieFromUserList($pdo, $userId, $movieId)) {
      $flash = "🗑️ Removed from your list.";
    } else {
      $flash = "⚠️ Could not remove movie.";
    }
  }

  // Optional: PRG pattern to avoid resubmission on refresh
  // Keep q in the URL if user was searching
  $qParam = isset($_GET['q']) ? ('?q=' . urlencode($_GET['q'])) : '';
  header("Location: movies.php{$qParam}&flash=" . urlencode($flash));
  exit;
}

// pick up flash via GET (from redirect)
if (isset($_GET['flash'])) {
  $flash = $_GET['flash'];
}

// ---------------------------
// Search
// ---------------------------
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = $q !== '' ? omdb_search($q) : [];

// ---------------------------
// My List for this user
// ---------------------------
$myList = getMoviesForUser($pdo, $userId);
?>

<div class="container">
  <h2>Search Movies</h2>

  <?php if ($flash): ?>
    <p class="flash-message"><?= htmlspecialchars($flash) ?></p>
  <?php endif; ?>

  <form method="get" action="movies.php" class="form-box">
    <input
      type="text"
      name="q"
      value="<?= htmlspecialchars($q) ?>"
      placeholder="Search title..."
      required
    >
    <button type="submit" class="btn">Search</button>
  </form>

  <?php if ($q !== ''): ?>
    <h3>Results for "<?= htmlspecialchars($q) ?>"</h3>
    <?php if ($results): ?>
      <ul class="movie-list">
        <?php foreach ($results as $m): ?>
          <li class="movie-list-item">
            <?php if (!empty($m['Poster']) && $m['Poster'] !== 'N/A'): ?>
              <img src="<?= htmlspecialchars($m['Poster']) ?>" alt="Poster">
            <?php endif; ?>
            <div class="movie-details">
              <strong><?= htmlspecialchars($m['Title']) ?></strong>
              (<?= htmlspecialchars($m['Year']) ?>)
              <!-- Add to My List -->
              <form method="post" action="movies.php" class="movie-actions">
                <input type="hidden" name="imdbID" value="<?= htmlspecialchars($m['imdbID']) ?>">
                <button type="submit" class="btn">Add to My List</button>
              </form>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No results found.</p>
    <?php endif; ?>
  <?php endif; ?>

  <!-- My List Section -->
  <hr class="section-divider">
  <h2>My List</h2>

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
              <form method="post" action="movies.php" class="form-inline">
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
              <form method="post" action="movies.php" class="form-inline">
                <input type="hidden" name="remove_movie_id" value="<?= (int)$row['movie_id'] ?>">
                <button type="submit" class="btn btn-danger">Remove</button>
              </form>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>You haven’t added any movies yet.</p>
  <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>
