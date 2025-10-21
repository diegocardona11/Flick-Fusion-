<?php
// Define entry point for backend includes
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    define('FLICK_FUSION_ENTRY_POINT', true);
}

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/controllers/movies.php';
require_once __DIR__ . '/../backend/controllers/ratings.php';

// $pdo is now available from db.php
$userId = $_SESSION['user_id'] ?? null;
$flash = '';

// Handle "Add to List" form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['imdb_id'])) {
    $api_id = $_POST['imdb_id'];
    $local_movie_id = addMovieToLocalDB($pdo, $api_id);

    if ($local_movie_id) {
        // Get the movie title from the database
        $stmt = $pdo->prepare("SELECT title FROM movies WHERE movie_id = ?");
        $stmt->execute([$local_movie_id]);
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);
        $movieTitle = $movie['title'] ?? 'Movie';
        
        // Determine the status based on which button was clicked
        $status = isset($_POST['add_to_watchlist']) ? 'watchlist' : 'watched';
        
        if (addMovieToUserList($pdo, $userId, $local_movie_id, $status)) {
            $flash = "\"$movieTitle\" was added to your watchlist!";
        } else {
            $flash = "\"$movieTitle\" is already in your list.";
        }
    } else {
        $flash = "Could not fetch movie details from the API.";
    }

    // Redirect to the same search page to prevent form resubmission
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

// Only search if query is not empty
if ($q !== '') {
    $basic_results = omdb_search($q);

    // For each result, get detailed information
    if ($basic_results) {
        foreach ($basic_results as $movie) {
            $detailed = omdb_fetch_by_id($movie['imdbID']);
            if ($detailed) {
                $search_results[] = $detailed;
            }
        }
    }
}

include 'partials/header.php';
?>

<main class="container">
    <div class="search-page-header">
        <div class="search-header-text">
            <h1>Find Your Next Favorite Film</h1>
            <p>Search for any movie and add it to your collection.</p>
        </div>
        <form method="get" action="movies.php" class="search-form">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="e.g., The Matrix, Star Wars..." class="form-input search-input-large" required>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <!-- Display flash message if any -->
    <?php if ($flash): ?>
        <p class="flash-message"><?= htmlspecialchars($flash) ?></p>
    <?php endif; ?>

    <!-- This appears after a search has been performed -->
    <?php if ($q !== ''): ?>
        <h2 class="results-title">Results for "<?= htmlspecialchars($q) ?>"</h2>
        
        <?php if ($search_results): ?>
            <ul class="movie-results-list">
                <?php foreach ($search_results as $m): ?>
                    <li class="movie-result-item">
                        <div class="movie-info">
                            <img src="<?= htmlspecialchars($m['Poster'] !== 'N/A' ? $m['Poster'] : 'https://placehold.co/100x150/252528/A9A9A9?text=N/A') ?>" alt="Poster for <?= htmlspecialchars($m['Title']) ?>">
                            <div class="movie-details">
                                <h3><?= htmlspecialchars($m['Title']) ?> <span>(<?= htmlspecialchars($m['Year']) ?>)</span></h3>
                                <p class="plot-summary"><?= htmlspecialchars($m['Plot'] ?? 'No plot summary available.') ?></p>
                            </div>
                        </div>
                        
                        <?php if ($userId): // Only show the form if the user is logged in ?>
                            <div class="movie-actions">
                                <form method="post" action="movies.php?q=<?= urlencode($q) ?>" class="form-inline">
                                    <input type="hidden" name="imdb_id" value="<?= htmlspecialchars($m['imdbID']) ?>">
                                    <button type="submit" name="add_to_watchlist" class="btn btn-primary">Add to Watchlist</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="no-results">No movies found matching your query.</p>
        <?php endif; ?>
    <?php endif; ?>

</main>

<?php include 'partials/footer.php'; ?>
