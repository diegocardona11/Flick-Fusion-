<?php
/**
 * db.php
 * ----------------------------------------
 * Database configuration file that defines how it will connect to the database
 * - Every controller will include this file when it needs to connect to the database
 * - Make sure to update the configuration values for your database
 */

$host = "localhost";        // Database server (local by default)
$dbname = "flickfusion";    // Database name (make sure it matches phpMyAdmin)
$username = "root";         // Database username (root by default)
$password = "";             // Database password (empty by default)

try {
    // Create a new PDO instance
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set error mode to exceptions
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If there is an error, display the message
    echo "DB Connection failed: " . $e->getMessage();
    exit;
}