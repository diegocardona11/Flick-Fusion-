<?php
// Prevents direct script access
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    http_response_code(403); // Forbidden
    exit('Access denied.');
}

require_once __DIR__ . '/../api/omdb.php'; // uses your OMDb key

// 1) Add a movie to local DB if it doesn’t exist
function addMovieToLocalDB(PDO $pdo, string $imdbID): ?int {
  $q = $pdo->prepare("SELECT movie_id FROM movies WHERE api_id = ?");
  $q->execute([$imdbID]);
  if ($row = $q->fetch(PDO::FETCH_ASSOC)) {
    return (int)$row['movie_id']; // It exists, return its ID
  } 

  // If not found, fetch from OMDb
  $movieData = omdb_fetch_by_id($imdbID);
  if (!$movieData) { 
    return null; // network/API failure
  }

  // Insert into local DB
  $ins = $pdo->prepare("
    INSERT INTO movies (api_id, title, year, genre, description, poster_url)
    VALUES (?, ?, ?, ?, ?, ?)
  ");
  $ins->execute([
    $movieData['imdbID'],
    $movieData['Title'] ?? '',
    is_numeric($movieData['Year'] ?? '') ? (int)$movieData['Year'] : null,
    $movieData['Genre'] ?? null,
    $movieData['Plot'] ?? null,
    (!empty($movieData['Poster']) && $movieData['Poster'] !== 'N/A') ? $movieData['Poster'] : null
  ]);

  return (int)$pdo->lastInsertId();
}

// 2) Link movie to user
function addMovieToUserList(PDO $pdo, int $userId, int $movieId, int $defaultScore = 5): void {
  $score = max(1, min(10, $defaultScore));
  $stmt = $pdo->prepare("
    INSERT INTO ratings (user_id, movie_id, score_10)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE score_10 = score_10, updated_at = CURRENT_TIMESTAMP
  ");
  $stmt->execute([$userId, $movieId, $score]);
}

// 3) Get all movies for user (for dashboard)
function getMoviesForUser(PDO $pdo, int $userId): array {
  $stmt = $pdo->prepare("
    SELECT m.movie_id, m.title, m.year, m.poster_url, r.score_10
    FROM ratings r
    JOIN movies m ON m.movie_id = r.movie_id
    WHERE r.user_id = ?
    ORDER BY m.title
  ");
  $stmt->execute([$userId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 5) Remove movie from user’s list
function removeMovieFromUserList(PDO $pdo, int $userId, int $movieId): bool {
  $stmt = $pdo->prepare("DELETE FROM ratings WHERE user_id = ? AND movie_id = ?");
  return $stmt->execute([$userId, $movieId]);
}
