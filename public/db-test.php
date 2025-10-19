<?php
// db-test.php — simple MySQL connection test

try {
  // connect to your database (same info from XAMPP)
  $pdo = new PDO(
    "mysql:host=localhost;dbname=flickfusion;charset=utf8mb4",
    "root", // username
    ""      // password (leave empty if none)
  );

  echo "✅ Connected to MySQL successfully!";
} catch (PDOException $e) {
  echo "❌ Connection failed: " . $e->getMessage();
}
