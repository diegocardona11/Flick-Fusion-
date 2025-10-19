<?php
function db() {
  static $pdo;
  if ($pdo) return $pdo;

  // Use the same host/port as your working mysql.exe command
  $host = '127.0.0.1';   // use IP to force TCP on Windows
  $port = 3306;          // <-- change if XAMPP shows a different port
  $name = 'flickfusion';
  $user = 'root';
  $pass = '';            // XAMPP default

  $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}
