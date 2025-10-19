<?php
require_once __DIR__ . '/../api/omdb.php'; // uses your OMDb key

// 1) Ensure movie exists locally; return movies.movie_id (or null on failure)
function addMovieToLocalDB(PDO $pdo, string $imdbID): ?int {
  $q = $pdo->prepare("SELECT movie_id FROM movies WHERE api_id = ?");
  $q->execute([$imdbID]);
  if ($row = $q->fetch()) return (int)$row['movie_id'];

  // fetch details from OMDb
  $url = "https://www.omdbapi.com/?apikey=" . omdb_api_key() . "&i=" . urlencode($imdbID) . "&plot=short";
  $json = @file_get_contents($url);
  if (!$json) return null;
  $m = json_decode($json, true);
  if (!$m || empty($m['imdbID'])) return null;

  $ins = $pdo->prepare("
    INSERT INTO movies (api_id, title, year, genre, description, poster_url)
    VALUES (?, ?, ?, ?, ?, ?)
  ");
  $ins->execute([
    $m['imdbID'],
    $m['Title'] ?? '',
    is_numeric($m['Year'] ?? '') ? (int)$m['Year'] : null,
    $m['Genre'] ?? null,
    $m['Plot'] ?? null,
    (!empty($m['Poster']) && $m['Poster'] !== 'N/A') ? $m['Poster'] : null
  ]);
  return (int)$pdo->lastInsertId();
}

// 2) Link movie to user (default rating = 5). Safe if already linked.
function addMovieToUserList(PDO $pdo, int $userId, int $movieId, int $defaultScore = 5): void {
  $stmt = $pdo->prepare("
    INSERT INTO ratings (user_id, movie_id, score_10)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE score_10 = score_10
  ");
  $stmt->execute([$userId, $movieId, max(1, min(10, $defaultScore))]);
}

// 3) Get all movies for user (for “My List” page)
function getMoviesForUser(PDO $pdo, int $userId): array {
  $stmt = $pdo->prepare("
    SELECT m.movie_id, m.title, m.year, m.poster_url, r.score_10
    FROM ratings r
    JOIN movies m ON m.movie_id = r.movie_id
    WHERE r.user_id = ?
    ORDER BY m.title
  ");
  $stmt->execute([$userId]);
  return $stmt->fetchAll();
}

// 4) Update a rating (1–10)
function updateRating(PDO $pdo, int $userId, int $movieId, int $score): void {
  $score = max(1, min(10, $score));
  $stmt = $pdo->prepare("UPDATE ratings SET score_10 = ? WHERE user_id = ? AND movie_id = ?");
  $stmt->execute([$score, $userId, $movieId]);
}

// 5) Remove movie from user’s list
function removeMovieFromUserList(PDO $pdo, int $userId, int $movieId): void {
  $stmt = $pdo->prepare("DELETE FROM ratings WHERE user_id = ? AND movie_id = ?");
  $stmt->execute([$userId, $movieId]);
}
