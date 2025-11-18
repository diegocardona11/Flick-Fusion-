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
    global $pdo;

    // Prevent sending request to yourself
    if ($userId == $friendId) {
        return false;
    }

    // Check if a relationship already exists
    $check = $pdo->prepare("
        SELECT * FROM friend_requests
        WHERE (requester_id = :u AND receiver_id = :f)
           OR (requester_id = :f AND receiver_id = :u)
        LIMIT 1
    ");

    $check->execute([
        'u' => $userId,
        'f' => $friendId
    ]);

    $existing = $check->fetch(PDO::FETCH_ASSOC);

    // If already friends or pending, do nothing
    if ($existing) {
        return false;
    }

    // Insert new friend request
    $stmt = $pdo->prepare("
        INSERT INTO friend_requests (requester_id, receiver_id, status)
        VALUES (:u, :f, 'pending')
    ");

    return $stmt->execute([
        'u' => $userId,
        'f' => $friendId
    ]);
}
// Get all pending friend requests for a user (incoming requests)
function getPendingFriendRequests($userId) {
    global $pdo;

    $sql = "
        SELECT 
            fr.id AS request_id,
            u.user_id AS user_id,   -- <-- changed from u.id
            u.username
        FROM friend_requests fr
        JOIN users u ON u.user_id = fr.requester_id  -- <-- changed from u.id
        WHERE fr.receiver_id = :uid
          AND fr.status = 'pending'
        ORDER BY fr.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    
// Remove a friend
function removeFriend($userId, $friendId) {
    // TODO: Implement logic to remove a friend
}


// Accept a friend request
function acceptFriendRequest($requestId) {
    global $pdo;

    $sql = "
        UPDATE friend_requests
        SET status = 'accepted'
        WHERE id = :id
          AND status = 'pending'
    ";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $requestId]);
}


// Reject a friend request
function rejectFriendRequest($requestId) {
    global $pdo;

    $sql = "
        UPDATE friend_requests
        SET status = 'declined'
        WHERE id = :id
          AND status = 'pending'
    ";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $requestId]);
}


// List all friends for a user
function listFriends($userId) {
    global $pdo;

    $sql = "
        SELECT 
            u.user_id AS id,       -- alias as id for convenience
            u.username
        FROM friend_requests fr
        JOIN users u ON (
               (fr.requester_id = :uid AND u.user_id = fr.receiver_id)
            OR (fr.receiver_id = :uid AND u.user_id = fr.requester_id)
        )
        WHERE fr.status = 'accepted'
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Search for users to add as friends
function searchUsers($query, $currentUserId) {
    global $pdo;

    if (trim($query) === '') {
        return [];
    }

    $sql = "
        SELECT 
            user_id AS id,       -- alias again
            username
        FROM users
        WHERE user_id != :current_id
          AND username LIKE :q
        ORDER BY username ASC
        LIMIT 20
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'current_id' => $currentUserId,
        'q'          => '%' . $query . '%'
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



