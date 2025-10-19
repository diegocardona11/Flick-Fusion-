<?php
// Prevent direct script access
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    http_response_code(403); // Forbidden
    exit('Access denied.');
}

// OMDb helper

function omdb_api_key(): ?string { 
  $env = __DIR__ . '/../../.env';
  if (file_exists($env)) {
    $vars = parse_ini_file($env, false, INI_SCANNER_RAW);
    if (!empty($vars['OMDB_API_KEY'])) return $vars['OMDB_API_KEY'];
  }
  return null; // Return null if no key is found. 
}

function omdb_search(string $query): array {
  $apiKey = omdb_api_key();
  if ($apiKey === null) {
    // No API key configured = no request
    error_log("OMDb API key is missing.");
    return [];
  }

  $q = urlencode($query);
  $url = "http://www.omdbapi.com/?apikey=" . $apiKey . "&type=movie&s={$q}";
  $json = @file_get_contents($url);

  if ($json === false) return [];
  
  $data = json_decode($json, true);
  return $data['Search'] ?? []; // array of results or empty
}
