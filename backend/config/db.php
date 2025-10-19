<?php
// Prevent direct script access
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    http_response_code(403); // Forbidden
    exit('Access denied.');
}
/**
 * db.php
 * ----------------------------------------
 * Database configuration file that defines how it will connect to the database
 * - Controllers include this file when they need DB access
 * - Values can be provided via .env (root of project) or fallback defaults below
 */

// Load env vars from project root .env, if present
$envPath = __DIR__ . '/../../.env';
$env = [];
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath, false, INI_SCANNER_RAW) ?: [];
}

// Read configuration from env with defaults (XAMPP/WAMP typical)
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$dbname = $env['DB_NAME'] ?? 'flick_fusion';
$username = $env['DB_USER'] ?? 'root';
$password = $env['DB_PASS'] ?? '';

// Build DSN and connect
$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Error message for common Windows dev setups
    $hint = "";
    if (str_contains($e->getMessage(), '2002')) {
        $hint = " (Is MySQL running? Check your DB_HOST/DB_PORT in .env; default is 127.0.0.1:3306)";
    }
    die("Database connection failed: " . $e->getMessage() . $hint);
}