<?php
/** omdb.php
 * ----------------------------------------
 * Handles interaction with the OMDB API
 *   - used for fetching movie data (title, year, description, etc.)
 */
 
function fetchMovieData($title) {
    $apiKey = "YOUR_API_KEY"; // Replace with real API key
    $url = "http://www.omdbapi.com/?t=" . urlencode($title) . "&apikey=" . $apiKey;

    $response = file_get_contents($url);
    return json_decode($response, true);
}
