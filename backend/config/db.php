<?php
/**
 * db.php
 * ----------------------------------------
 * Database configuration file that defines how it will connect to the database
 * - Every controller will include this file when it needs to connect to the database
 * - Make sure to update the configuration values for your database
 */

$host = "localhost";        // Database server (local by default)
$dbname = "flick_fusion";   // Database name (make sure it matches phpMyAdmin)
$username = "root";         // Database username (root by default)
$password = "";             // Database password (empty by default)

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set error mode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If there is an error, display the message
    die("Database connection failed: " . $e->getMessage());
}