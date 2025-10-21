<?php
// Prevent direct script access
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    http_response_code(403); // Forbidden
    exit('Access denied.');
}

// backend/api/omdb.php
// This handles OMDb API connections (movie data fetching)

/**
 * Returns your OMDb API key from the .env file
 * @return string|null The API key or null if not found
 */
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

/**
 * Searches for movies by title.
 * @param string $q The search query (movie title)
 * @return array List of movies matching the search
 */
function omdb_search(string $q): array {
  if ($q === '') return [];

  $apiKey = omdb_api_key();
  if ($apiKey === null) {
    // API key is missing, cannot perform search
    return [];
  }

  // Build the request URL
  $url = 'https://www.omdbapi.com/?apikey=' . urlencode($apiKey)
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

/** Fetches detailed movie information by IMDb ID
 * @param string $imdbID The IMDb ID of the movie
 * @return array|null The movie details as an associative array, or null if not found
 */
function omdb_fetch_by_id(string $imdbID): ?array {
  $apiKey = omdb_api_key();

  $url = 'https://www.omdbapi.com/?apikey=' . urlencode(omdb_api_key())
       . '&i=' . urlencode($imdbID) . '&plot=short';

  $json = @file_get_contents($url);
  if ($json === false) return null;

  $data = json_decode($json, true);

  // OMDb returns Response: "False" for bad IDs, so check for a valid imdbID
  return !empty($data['imdbID']) ? $data : null;
}
