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

  // If not found, fetch detailed info from OMDb (only called once when adding to watchlist)
  $movieData = omdb_fetch_by_id($imdbID);
  if (!$movieData) { 
    return null; // network/API failure
  }

  // Insert into local DB with full details
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