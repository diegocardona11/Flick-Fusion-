<?php
// backend/api/omdb.php
// This handles OMDb API connections (movie data fetching)

// 1️Return your OMDb API key
function omdb_api_key(): string {
 
  return '6754e3a3';
}

// Search movies by title
function omdb_search(string $q): array {
  if ($q === '') return [];

  // Build the request URL
  $url = 'https://www.omdbapi.com/?apikey=' . urlencode(omdb_api_key())
       . '&type=movie&s=' . urlencode($q);

  // Fetch results
  $json = @file_get_contents($url);
  if ($json === false) {
    return []; // API failed or SSL problem
  }

  // Decode response
  $data = json_decode($json, true);

  // Return list of movies (if any)
  return isset($data['Search']) && is_array($data['Search'])
    ? $data['Search']
    : [];
}

// detailed movie info by IMDb ID
function omdb_fetch_by_id(string $imdbID): ?array {
  $url = 'https://www.omdbapi.com/?apikey=' . urlencode(omdb_api_key())
       . '&i=' . urlencode($imdbID) . '&plot=short';
  $json = @file_get_contents($url);
  if ($json === false) return null;
  $data = json_decode($json, true);
  return !empty($data['imdbID']) ? $data : null;
}
