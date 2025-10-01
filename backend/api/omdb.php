<?php
// super tiny OMDb helper

function omdb_api_key(): string {
  $env = __DIR__ . '/../../.env';
  if (file_exists($env)) {
    $vars = parse_ini_file($env, false, INI_SCANNER_RAW);
    if (!empty($vars['OMDB_API_KEY'])) return $vars['OMDB_API_KEY'];
  }
  return '6754e3a3'; // fallback if you didn't make .env
}

function omdb_search(string $query): array {
  $q = urlencode($query);
  $url = "http://www.omdbapi.com/?apikey=" . omdb_api_key() . "&type=movie&s={$q}";
  $json = @file_get_contents($url);
  if ($json === false) return [];
  $data = json_decode($json, true);
  return $data['Search'] ?? []; // array of results or empty
}
