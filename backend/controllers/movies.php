<?php
// Prevents direct script access (only allow access through real app entry points)
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    http_response_code(403); // Forbidden
    exit('Access denied.');
}

// OMDb helper functions (used to fetch full movie info by IMDB ID)
require_once __DIR__ . '/../api/omdb.php';

/**
 * Add a movie to the local "movies" table if it doesn’t already exist.
 *
 * - Looks up a movie by its OMDb imdbID (stored in movies.api_id).
 * - If it exists, returns its movie_id.
 * - If it does not exist:
 *      - Fetches full data from OMDb
 *      - Inserts it into the movies table
 *      - Returns the new movie_id
 * - On failure, returns null.
 */
function addMovieToLocalDB(PDO $pdo, string $imdbID): ?int {
    // Check if this movie is already in our local database
    $q = $pdo->prepare("SELECT movie_id FROM movies WHERE api_id = ?");
    $q->execute([$imdbID]);

    if ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        // Movie already exists, just return its id
        return (int) $row['movie_id'];
    }

    // Not found locally → fetch from OMDb
    $movieData = omdb_fetch_by_id($imdbID);
    if (!$movieData) {
        // API / network error
        return null;
    }

    // Insert full details into our movies table
    $ins = $pdo->prepare("
        INSERT INTO movies (api_id, title, year, genre, description, poster_url)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $ins->execute([
        $movieData['imdbID'],
        $movieData['Title'] ?? '',
        is_numeric($movieData['Year'] ?? '') ? (int) $movieData['Year'] : null,
        $movieData['Genre'] ?? null,
        $movieData['Plot'] ?? null,
        (!empty($movieData['Poster']) && $movieData['Poster'] !== 'N/A') ? $movieData['Poster'] : null,
    ]);

    // Return the new movie_id
    return (int) $pdo->lastInsertId();
}

/**
 * Get all movies on a specific user's list, using the ratings table.
 *
 * This uses:
 *   ratings(user_id, movie_id, score_10, status, ...)
 *   movies(movie_id, title, year, poster_url, ...)
 *
 * It returns an array of rows with:
 *   - movie_id
 *   - title
 *   - year
 *   - poster_url
 *   - rating (mapped from score_10)
 *
 * Used by:
 *   - "My List" page
 *   - Compare feature
 */
function getMoviesForUser(PDO $pdo, int $userId): array {
    $sql = "
        SELECT
            r.movie_id,
            m.api_id,
            m.title,
            m.year,
            m.poster_url,
            r.score_10 AS rating
        FROM ratings AS r
        JOIN movies AS m
          ON m.movie_id = r.movie_id
        WHERE r.user_id = :uid
          AND r.status = 'watched'
        ORDER BY m.title ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Compare the movie lists of two users.
 *
 * Uses getMoviesForUser() for each user and returns a structured array:
 *
 * [
 *   'shared'      => [ movies both users have, with both ratings ],
 *   'user_only'   => [ movies only the main user has ],
 *   'friend_only' => [ movies only the friend has ],
 * ]
 *
 * Each item in 'shared' looks like:
 *   [
 *     'movie_id'      => ...,
 *     'title'         => ...,
 *     'year'          => ...,
 *     'poster_url'    => ...,
 *     'user_rating'   => ...,
 *     'friend_rating' => ...,
 *   ]
 *
 * Each item in 'user_only' / 'friend_only' includes:
 *   movie_id, title, year, poster_url, rating
 */
function getComparison(PDO $pdo, int $userID, int $friendID): array {
    // 1) Load both users' movie lists
    $userMovies   = getMoviesForUser($pdo, $userID);
    $friendMovies = getMoviesForUser($pdo, $friendID);

    // 2) Re-index lists by movie_id for quick lookups
    $userMap = [];
    foreach ($userMovies as $movie) {
        $userMap[$movie['movie_id']] = $movie;
    }

    $friendMap = [];
    foreach ($friendMovies as $movie) {
        $friendMap[$movie['movie_id']] = $movie;
    }

    // 3) Prepare result buckets
    $shared      = []; // movies both users have
    $userOnly    = []; // only on $userID's list
    $friendOnly  = []; // only on $friendID's list

    // Go through the main user's movies
    foreach ($userMap as $movieId => $movie) {
        if (isset($friendMap[$movieId])) {
            // Both users have this movie → put it in shared
            $shared[] = [
                'movie_id'      => $movieId,
                'api_id'        => $movie['api_id'] ?? null,
                'title'         => $movie['title'],
                'year'          => $movie['year'],
                'poster_url'    => $movie['poster_url'],
                'user_rating'   => $movie['rating'],                // current user's rating
                'friend_rating' => $friendMap[$movieId]['rating'],  // friend's rating
            ];
        } else {
            // Only the main user has this movie
            $userOnly[] = $movie;
        }
    }

    // Now find movies that only the friend has
    foreach ($friendMap as $movieId => $movie) {
        if (!isset($userMap[$movieId])) {
            $friendOnly[] = $movie;
        }
    }

    // 4) Return everything in one structured array
    return [
        'shared'      => $shared,
        'user_only'   => $userOnly,
        'friend_only' => $friendOnly,
    ];
}
