<?php
session_start();

// 1. Include DB and controller
require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/controllers/friends.php';

// 2. Pick a user id that exists in your `users` table
$userId = 1; // change this to a real user id

$friends = listFriends($userId);

echo '<pre>';
print_r($friends);
echo '</pre>';
