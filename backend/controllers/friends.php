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
        SELECT * FROM friends
        WHERE (user_id = :u AND friend_id = :f)
           OR (user_id = :f AND friend_id = :u)
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
        INSERT INTO friends (user_id, friend_id, status)
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
            f.user_id AS requester_id,
            u.user_id AS user_id,
            u.username,
            u.avatar_url
        FROM friends f
        JOIN users u ON u.user_id = f.user_id
        WHERE f.friend_id = :uid
          AND f.status = 'pending'
        ORDER BY u.username ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Get all outgoing pending friend requests (requests the user has sent)
function getSentFriendRequests($userId) {
    global $pdo;

    $sql = "
        SELECT 
            f.friend_id AS recipient_id,
            u.user_id AS user_id,
            u.username,
            u.avatar_url
        FROM friends f
        JOIN users u ON u.user_id = f.friend_id
        WHERE f.user_id = :uid
          AND f.status = 'pending'
        ORDER BY u.username ASC
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
function acceptFriendRequest($requesterId, $receiverId) {
    global $pdo;

    $sql = "
        UPDATE friends
        SET status = 'accepted'
        WHERE user_id = :requester_id
          AND friend_id = :receiver_id
          AND status = 'pending'
    ";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'requester_id' => $requesterId,
        'receiver_id' => $receiverId
    ]);
}


// Reject a friend request (delete the pending request)
function rejectFriendRequest($requesterId, $receiverId) {
    global $pdo;

    $sql = "
        DELETE FROM friends
        WHERE user_id = :requester_id
          AND friend_id = :receiver_id
          AND status = 'pending'
    ";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'requester_id' => $requesterId,
        'receiver_id' => $receiverId
    ]);
}


// List all friends for a user
function listFriends($userId) {
    global $pdo;

    $sql = "
        SELECT 
            u.user_id AS id,
            u.username,
            u.avatar_url
        FROM friends f
        JOIN users u ON (
               (f.user_id = :uid AND u.user_id = f.friend_id)
            OR (f.friend_id = :uid AND u.user_id = f.user_id)
        )
        WHERE f.status = 'accepted'
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
            user_id AS id, 
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

function getFriendshipStatus($userId, $otherUserId) {
    global $pdo;
    
    if ($userId == $otherUserId) {
        return 'self';
    }
    
    $stmt = $pdo->prepare("
        SELECT status, user_id, friend_id
        FROM friends
        WHERE (user_id = ? AND friend_id = ?)
           OR (user_id = ? AND friend_id = ?)
        LIMIT 1
    ");
    $stmt->execute([$userId, $otherUserId, $otherUserId, $userId]);
    $friendship = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$friendship) {
        return 'none';
    }
    
    if ($friendship['status'] === 'accepted') {
        return 'friends';
    }
    
    if ($friendship['user_id'] == $userId) {
        return 'pending_sent';
    } else {
        return 'pending_received';
    }
}



