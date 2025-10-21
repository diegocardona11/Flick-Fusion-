<?php
/**
 * ratings.php
 * ----------------------------------------
 * Handles rating-related actions
 *   - adding a new rating
 *   - updating an existing rating
 *   - retrieving ratings for a movie
 */

// Prevents direct script access
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    http_response_code(403); // Forbidden
    exit('Access denied.');
}

require_once __DIR__ . '/../api/omdb.php'; // uses your OMDb key
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Rating.php';

// Add a new rating
function addRating($userId, $movieId, $score, $comment) {   
    // TODO: Implement logic to add a new rating
}

// 4) Update a rating
function updateRating(PDO $pdo, int $userId, int $movieId, int $score): bool {
  $score = max(1, min(10, $score));
  $stmt = $pdo->prepare("
    UPDATE ratings
    SET score_10 = ?, updated_at = CURRENT_TIMESTAMP
    WHERE user_id = ? AND movie_id = ?
  ");
  return $stmt->execute([$score, $userId, $movieId]);
}


// Delete a rating
function deleteRating($ratingId) {
    // TODO: Implement logic to delete a rating
}

// Retrieve ratings for a specific movie
function getRatingsForMovie($movieId) {
    // TODO: Implement logic to retrieve ratings for a specific movie
}

