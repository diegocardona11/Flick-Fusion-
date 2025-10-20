<?php
// backend/api/omdb.php
// This handles OMDb API connections (movie data fetching)

// 1️Return your OMDb API key from the .env file
function omdb_api_key(): ?string {
  // Path to the .env file at the project root
  $envPath = __DIR__ . '/../../.env';

  if (!file_exists($envPath)) {
    // .env file does not exist, log an error
    error_log('Security Notice: .env file not found. Cannot load OMDb API key.');
    return null;
  }

  // Parse the .env file
  $env = parse_ini_file($envPath);

  if (empty($env['OMDB_API_KEY'])) {
    // OMDB_API_KEY is not set in the .env file, log an error
    error_log('Configuration Error: OMDB_API_KEY not found in .env file.');
    return null;
  }

  return $env['OMDB_API_KEY'];
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
