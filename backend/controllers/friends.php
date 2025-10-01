<?php
/**
 * friends.php
 * ----------------------------------------
 * Handles friend-related actions
 *   - add friend request
 *   - accept/reject friend request
 *   - list friends
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Friend.php';

// Send a friend request
function addFriend($userId, $friendId) {    
    // TODO: Implement logic to send a friend request
}
    
// Remove a friend
function removeFriend($userId, $friendId) {
    // TODO: Implement logic to remove a friend
}

// Accept a friend request
function acceptFriendRequest($requestId) {
    // TODO: Implement logic to accept a friend request
}

// Reject a friend request
function rejectFriendRequest($requestId) {
    // TODO: Implement logic to reject a friend request
}

// List all friends for a user
function listFriends($userId) {
    // TODO: Implement logic to list all friends for a user
}