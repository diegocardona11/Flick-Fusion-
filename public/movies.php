<?php
// Define entry point for backend includes
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
  define('FLICK_FUSION_ENTRY_POINT', true);
}

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/controllers/movies.php';
require_once __DIR__ . '/../backend/controllers/ratings.php';

// $pdo is now available from db.php
$userId = $_SESSION['user_id'] ?? null;
$flash = '';

// Handle "Add to List" form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_movie'])) {
  $api_id = $_POST['imdb_id'];
  $local_movie_id = addMovieToLocalDB($pdo, $api_id);

  if ($local_movie_id) {
        $score = null;
        if (isset($_POST['add_to_watched'])) {
            $score = 7; // Default score for watched movies to be changed later
        }

        if (addMovieToUserList($pdo, $userId, $local_movie_id, $score)) {
            $flash = "Movie was added to your list!";
        } else {
            $flash = "Movie is already in your list.";
        }
    } else {
        $flash = "Could not fetch movie details from the API.";
    }

    $qParam = isset($_GET['q']) ? ('?q=' . urlencode($_GET['q'])) : '';
    header("Location: movies.php{$qParam}&flash=" . urlencode($flash));
    exit;
}

// Get flash message from redirect
if (isset($_GET['flash'])) {
    $flash = $_GET['flash'];
}

// Handle search query
$q = trim($_GET['q'] ?? '');
$search_results = [];
if ($q !== '') {
    $search_results = omdb_search($q);
}

include 'partials/header.php';
?>

<main class="container">
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
    <?php if ($search_results): ?>
      <ul class="movie-list">
        <?php foreach ($search_results as $m): ?>
          <li class="movie-list-item">
            <?php if (!empty($m['Poster']) && $m['Poster'] !== 'N/A'): ?>
                    <img src="<?= htmlspecialchars($m['Poster']) ?>" alt="Poster">
                <?php else: ?>
                    <!-- Fallback image if no poster is available -->
                    <img src="https://placehold.co/80x120/252528/A9A9A9?text=N/A" alt="No poster available">
                <?php endif; ?>
                <div class="movie-details">
                    <strong><?= htmlspecialchars($m['Title']) ?></strong>
                    (<?= htmlspecialchars($m['Year']) ?>)
                    
                    <?php if ($userId): // Only show the form if the user is logged in ?>
                        <form method="post" action="movies.php?q=<?= urlencode($q) ?>" class="movie-actions">
                            <input type="hidden" name="imdb_id" value="<?= htmlspecialchars($m['imdbID']) ?>">
                            <button type="submit" name="add_movie" class="btn">Add to My List</button>
                        </form>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
      <p>No results found.</p>
    <?php endif; ?>
  <?php endif; ?>

</div>

<?php include 'partials/footer.php'; ?>
