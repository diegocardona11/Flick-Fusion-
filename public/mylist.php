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
    <h2>My Movie List</h2>
    <p>Welcome, <strong><?= $username ?></strong>! This is your personal movie list.</p>

    <?php if ($flash): ?>
        <p class="flash-message"><?= htmlspecialchars($flash) ?></p>
    <?php endif; ?>

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
                                <label>Rating:</label>
                                <input type="number" name="score" min="1" max="10" value="<?= (int)($row['rating'] ?? '') ?>" class="rating-input" placeholder="-">
                                <button type="submit" name="update_rating" class="btn">Update</button>
                            </form>

                            <!-- Remove from List -->
                            <form method="post" action="mylist.php" class="form-inline">
                                <input type="hidden" name="movie_id" value="<?= (int)$row['movie_id'] ?>">
                                <button type="submit" name="remove_movie" class="btn btn-danger">Remove</button>
                            </form>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You haven’t added any movies yet. <a href="movies.php">Go add some!</a></p>
    <?php endif; ?>
</main>

<?php include 'partials/footer.php'; ?>