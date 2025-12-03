<?php
/**
 * ratings.php
 * ----------------------------------------
 * Handles rating-related actions
 *   - adding a new rating
 *   - updating an existing rating
 *   - retrieving ratings for a movie/user
 */

// Prevents direct script access
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    http_response_code(403); // Forbidden
    exit('Access denied.');
}

require_once __DIR__ . '/../api/omdb.php';   // OMDb helper (if needed)
require_once __DIR__ . '/../config/db.php';  // DB connection
require_once __DIR__ . '/../models/Rating.php';

/**
 * Add or update a movie on a user's list (watchlist or watched).
 *
 * - If no score is provided, defaults to 5.
 * - Uses INSERT ... ON DUPLICATE KEY UPDATE so we don't create duplicates.
 */
function addMovieToUserList(PDO $pdo, int $userId, int $movieId, string $status = 'watchlist', ?int $score = null): bool {
    try {
        // If no score provided, default to 5
        if ($score === null) {
            $score = 5;
        }

        $stmt = $pdo->prepare("
            INSERT INTO ratings (user_id, movie_id, score_10, status)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                score_10 = VALUES(score_10),
                status   = VALUES(status)
        ");

        return $stmt->execute([$userId, $movieId, $score, $status]);
    } catch (PDOException $e) {
        error_log('Error in addMovieToUserList: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get all movies for a specific user (with rating + status).
 *
 * NOTE:
 * We wrap this in function_exists so we don't clash with the
 * getMoviesForUser() function defined in movies.php (used by Compare).
 */
if (!function_exists('getMoviesForUser')) {
    function getMoviesForUser(PDO $pdo, int $userId): array {
        $stmt = $pdo->prepare("
            SELECT
                m.movie_id,
                m.title,
                m.year,
                m.poster_url,
                r.score_10 AS rating,
                r.status
            FROM ratings r
            JOIN movies m ON m.movie_id = r.movie_id
            WHERE r.user_id = ?
            ORDER BY m.title
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * Update the rating for a user's movie.
 * - Forces score between 1 and 10.
 * - Sets status to 'watched'.
 */
function updateRating(PDO $pdo, int $userId, int $movieId, int $score): bool {
    // Clamp score to 1–10
    $score = max(1, min(10, $score));

    $stmt = $pdo->prepare("
        UPDATE ratings
        SET score_10 = ?, status = 'watched', updated_at = CURRENT_TIMESTAMP
        WHERE user_id = ? AND movie_id = ?
    ");

    return $stmt->execute([$score, $userId, $movieId]);
}

/**
 * Remove a movie completely from a user's list.
 */
function removeMovieFromUserList(PDO $pdo, int $userId, int $movieId): bool {
    $stmt = $pdo->prepare("DELETE FROM ratings WHERE user_id = ? AND movie_id = ?");
    return $stmt->execute([$userId, $movieId]);
}
