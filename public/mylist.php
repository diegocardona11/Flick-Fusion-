<?php
// Define entry point for backend includes
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    define('FLICK_FUSION_ENTRY_POINT', true);
}
/**
 * mylist.php
 * ----------------------------------------
 * User movie list - displays users personal movie list 
 */

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
require_once __DIR__ . '/../backend/controllers/ratings.php'; 

$userId = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username'] ?? 'User');
$flash = '';

// Form Handling Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // UPDATE RATING
    if (isset($_POST['update_rating']) && !empty($_POST['movie_id'])) {
        $movieId = (int)$_POST['movie_id'];
        $score   = (int)$_POST['score'];
        if (updateRating($pdo, $userId, $movieId, $score)) {
            $flash = "Rating updated.";
        }
    }

    // REMOVE FROM LIST
    if (isset($_POST['remove_movie']) && !empty($_POST['movie_id'])) {
        $movieId = (int)$_POST['movie_id'];
        if (removeMovieFromUserList($pdo, $userId, $movieId)) {
            $flash = "Removed from your list.";
        }
    }

    header("Location: mylist.php?flash=" . urlencode($flash));
    exit;
}

if (isset($_GET['flash'])) {
    $flash = $_GET['flash'];
}

// Get user's movie list
$myList = getMoviesForUser($pdo, $userId);

include 'partials/header.php';
?>

<main class="container">
    <div class="page-header">
        <h1>My Watchlist</h1>
        <p>Welcome, <strong><?= $username ?></strong>! Manage your personal movie collection.</p>
    </div>

    <?php if ($flash): ?>
        <p class="flash-message"><?= htmlspecialchars($flash) ?></p>
    <?php endif; ?>

    <?php if ($myList): ?>
        <div class="mylist-grid">
            <?php foreach ($myList as $row): ?>
                <div class="mylist-card">
                    <div class="card-poster">
                        <?php if (!empty($row['poster_url'])): ?>
                            <img src="<?= htmlspecialchars($row['poster_url']) ?>" alt="<?= htmlspecialchars($row['title']) ?> Poster">
                        <?php else: ?>
                            <img src="https://placehold.co/200x300/252528/A9A9A9?text=No+Poster" alt="No Poster Available">
                        <?php endif; ?>
                        <?php if (!empty($row['status'])): ?>
                            <span class="status-badge status-<?= htmlspecialchars($row['status']) ?>">
                                <?= htmlspecialchars(ucfirst($row['status'])) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-content">
                        <h3 class="card-title">
                            <?= htmlspecialchars($row['title']) ?>
                            <?php if (!empty($row['year'])): ?>
                                <span class="card-year">(<?= (int)$row['year'] ?>)</span>
                            <?php endif; ?>
                        </h3>
                        
                        <div class="card-rating">
                            <div class="rating-input-group">
                                <label for="score-<?= (int)$row['movie_id'] ?>">Rating:</label>
                                <div class="rating-display">
                                    <?php if (!empty($row['rating']) && $row['status'] === 'watched'): ?>
                                        <input type="number" 
                                               id="score-<?= (int)$row['movie_id'] ?>"
                                               form="rating-form-<?= (int)$row['movie_id'] ?>"
                                               name="score" 
                                               min="1" 
                                               max="10" 
                                               value="<?= (int)$row['rating'] ?>" 
                                               class="rating-number-input">
                                        <span class="rating-max">/10</span>
                                    <?php else: ?>
                                        <input type="number" 
                                               id="score-<?= (int)$row['movie_id'] ?>"
                                               form="rating-form-<?= (int)$row['movie_id'] ?>"
                                               name="score" 
                                               min="1" 
                                               max="10" 
                                               placeholder="?"
                                               class="rating-number-input rating-unrated">
                                        <span class="rating-max">/10</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-actions">
                                <form id="rating-form-<?= (int)$row['movie_id'] ?>" method="post" action="mylist.php">
                                    <input type="hidden" name="movie_id" value="<?= (int)$row['movie_id'] ?>">
                                    <button type="submit" name="update_rating" class="btn btn-sm btn-primary">Update</button>
                                </form>
                                <form method="post" action="mylist.php">
                                    <input type="hidden" name="movie_id" value="<?= (int)$row['movie_id'] ?>">
                                    <button type="submit" name="remove_movie" class="btn btn-sm btn-danger" onclick="return confirm('Remove <?= htmlspecialchars(addslashes($row['title'])) ?> from your list?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>Your watchlist is empty.</p>
            <a href="movies.php" class="btn btn-primary">Start Adding Movies</a>
        </div>
    <?php endif; ?>
</main>

<?php include 'partials/footer.php'; ?>