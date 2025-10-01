<?php
/**
 * ratings.php
 * ----------------------------------------
 * Handles rating-related actions
 *   - adding a new rating
 *   - updating an existing rating
 *   - retrieving ratings for a movie
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Rating.php';

// Add a new rating
function addRating($userId, $movieId, $score, $comment) {   
    // TODO: Implement logic to add a new rating
}

// Update an existing rating
function updateRating($ratingId, $score, $comment) {
    // TODO: Implement logic to update an existing rating
}


// Delete a rating
function deleteRating($ratingId) {
    // TODO: Implement logic to delete a rating
}

// Retrieve ratings for a specific movie
function getRatingsForMovie($movieId) {
    // TODO: Implement logic to retrieve ratings for a specific movie
}
