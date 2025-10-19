<?php
function db() {
  static $pdo;
  if ($pdo) return $pdo;

  $host = '127.0.0.1';  // use IP instead of localhost for XAMPP
  $port = 3306;         // or 3307 if you changed it in XAMPP
  $name = 'flickfusion';
  $user = 'root';
  $pass = '';

  $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}
