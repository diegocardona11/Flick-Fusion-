<?php
/**
 * ratings.php
 * ----------------------------------------
 * Handles rating-related actions
 *   - adding a new rating
 *   - updating an existing rating
 *   - retrieving ratings for a movie
 */

// Prevents direct script access
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    http_response_code(403); // Forbidden
    exit('Access denied.');
}

require_once __DIR__ . '/../api/omdb.php'; // uses your OMDb key
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Rating.php';

/**
 * Adds a movie to a user's list (watchlist or watched).
 * @param PDO $pdo The database connection
 * @param int $userId The user's ID
 * @param int $movieId The movie's ID
 * @param string $status The status ('watchlist' or 'watched')
 * @return bool True on success, false on failure
 */
function addMovieToUserList(PDO $pdo, int $userId, int $movieId, ?int $score = null): bool {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO ratings (user_id, movie_id, score_10) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE score_10 = VALUES(score_10)
        ");
        return $stmt->execute([$userId, $movieId, $score]);
    } catch (PDOException $e) {
        error_log("Error in addMovieToUserList: " . $e->getMessage());
        return false;
    }
}

/**
 * Gets all movies for a specific user.
 */
function getMoviesForUser(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("
        SELECT m.movie_id, m.title, m.year, m.poster_url, r.score_10 as rating, r.status
        FROM ratings r
        JOIN movies m ON m.movie_id = r.movie_id
        WHERE r.user_id = ?
        ORDER BY m.title
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Updates a rating for a user's movie.
 */
function updateRating(PDO $pdo, int $userId, int $movieId, int $score): bool {
    $score = max(1, min(10, $score));
    $stmt = $pdo->prepare("
        UPDATE ratings SET score_10 = ?, status = 'watched', updated_at = CURRENT_TIMESTAMP
        WHERE user_id = ? AND movie_id = ?
    ");
    return $stmt->execute([$score, $userId, $movieId]);
}

/**
 * Removes a movie from a user's list.
 */
function removeMovieFromUserList(PDO $pdo, int $userId, int $movieId): bool {
    $stmt = $pdo->prepare("DELETE FROM ratings WHERE user_id = ? AND movie_id = ?");
    return $stmt->execute([$userId, $movieId]);
}

