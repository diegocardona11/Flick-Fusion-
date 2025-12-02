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

// Sorting parameter (title, year; asc/desc)
$sort = $_GET['sort'] ?? 'title_asc';

// Get user's movie list
$myList = getMoviesForUser($pdo, $userId);

// Apply sorting on the list
usort($myList, function ($a, $b) use ($sort) {
    switch ($sort) {
        case 'title_asc':
            return strcasecmp($a['title'] ?? '', $b['title'] ?? '');
        case 'title_desc':
            return strcasecmp($b['title'] ?? '', $a['title'] ?? '');
        case 'year_asc':
            return ($a['year'] ?? 0) <=> ($b['year'] ?? 0);
        case 'year_desc':
            return ($b['year'] ?? 0) <=> ($a['year'] ?? 0);
        case 'rating_asc':
            return ($a['rating'] ?? 0) <=> ($b['rating'] ?? 0);
        case 'rating_desc':
            return ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0);
        default:
            return 0;
    }
});

include 'partials/header.php';
?>

<main class="container">
    <div class="page-header">
        <h1>My Movie List</h1>
        <p>Welcome, <strong><?= $username ?></strong>! Manage your personal movie collection.</p>
    </div>

    <?php if ($flash): ?>
        <p class="flash-message"><?= htmlspecialchars($flash) ?></p>
    <?php endif; ?>

    <div class="filter-controls">
        <form method="get" action="mylist.php" class="filter-form">
            <div class="filter-group">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort" class="filter-select">
                    <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Title (A–Z)</option>
                    <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Title (Z–A)</option>
                    <option value="year_asc" <?= $sort === 'year_asc' ? 'selected' : '' ?>>Year (Oldest)</option>
                    <option value="year_desc" <?= $sort === 'year_desc' ? 'selected' : '' ?>>Year (Newest)</option>
                    <option value="rating_asc" <?= $sort === 'rating_asc' ? 'selected' : '' ?>>Rating (Low→High)</option>
                    <option value="rating_desc" <?= $sort === 'rating_desc' ? 'selected' : '' ?>>Rating (High→Low)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            <?php if ($sort !== 'title_asc'): ?>
                <a href="mylist.php" class="btn btn-secondary btn-sm">Clear</a>
            <?php endif; ?>
        </form>
        <div class="filter-count">
            <span><?= count($myList) ?> movie<?= count($myList) !== 1 ? 's' : '' ?></span>
        </div>
    </div>

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