<?php
/**
 * friends.php
 * ----------------------------------------
 * Handles friend-related actions:
 *   - send friend request
 *   - accept / reject friend request
 *   - list friends
 *   - search users
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Friend.php';

/**
 * Send a friend request
 */
function addFriend($userId, $friendId) {
    global $pdo;

    // Don't allow sending a request to yourself
    if ($userId == $friendId) {
        return false;
    }

    // Check if any friendship row already exists
    $check = $pdo->prepare("
        SELECT * FROM friends
        WHERE (user_id = :u AND friend_id = :f)
           OR (user_id = :f AND friend_id = :u)
        LIMIT 1
    ");
    $check->execute(['u' => $userId, 'f' => $friendId]);

    if ($check->fetch(PDO::FETCH_ASSOC)) {
        return false; // already friends or pending
    }

    // Insert new pending request
    $stmt = $pdo->prepare("
        INSERT INTO friends (user_id, friend_id, status)
        VALUES (:u, :f, 'pending')
    ");
    return $stmt->execute(['u' => $userId, 'f' => $friendId]);
}

/**
 * Get incoming pending requests (people who sent YOU a request)
 */
function getPendingFriendRequests($userId) {
    global $pdo;

    $sql = "
        SELECT 
            f.user_id AS requester_id,
            u.user_id AS user_id,
            u.username
            -- , u.avatar_url   -- Uncomment later if you add this column
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

/**
 * Get outgoing pending requests (requests YOU sent)
 */
function getSentFriendRequests($userId) {
    global $pdo;

    $sql = "
        SELECT 
            f.friend_id AS recipient_id,
            u.user_id AS user_id,
            u.username
            -- , u.avatar_url   -- Uncomment later if avatars added
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

/**
 * Accept a friend request
 */
function acceptFriendRequest($requesterId, $receiverId) {
    global $pdo;

    $sql = "
        UPDATE friends
        SET status = 'accepted'
        WHERE user_id = :requester
          AND friend_id = :receiver
          AND status = 'pending'
    ";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'requester' => $requesterId,
        'receiver'  => $receiverId
    ]);
}

/**
 * Reject a friend request (delete the row)
 */
function rejectFriendRequest($requesterId, $receiverId) {
    global $pdo;

    $sql = "
        DELETE FROM friends
        WHERE user_id = :requester
          AND friend_id = :receiver
          AND status = 'pending'
    ";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'requester' => $requesterId,
        'receiver'  => $receiverId
    ]);
}

/**
 * List all accepted friends of a user
 */
function listFriends($userId) {
    global $pdo;

    $sql = "
        SELECT 
            u.user_id AS id,
            u.username
            -- , u.avatar_url   -- Commented for now, can re-enable later
        FROM friends f
        JOIN users u ON (
               (f.user_id = :uid AND u.user_id = f.friend_id)
            OR (f.friend_id = :uid AND u.user_id = f.user_id)
        )
        WHERE f.status = 'accepted'
        ORDER BY u.username ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Search for people to add as friends
 */
function searchUsers($query, $currentUserId) {
    global $pdo;

    if (trim($query) === '') {
        return [];
    }

    $sql = "
        SELECT 
            user_id AS id,
            username
            -- , avatar_url   -- Commented for later
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

/**
 * Returns friendship status:
 *   - self
 *   - none
 *   - friends
 *   - pending_sent
 *   - pending_received
 */
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
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return 'none';
    }

    if ($row['status'] === 'accepted') {
        return 'friends';
    }

    // Determine who sent the request
    if ($row['user_id'] == $userId) {
        return 'pending_sent';
    } else {
        return 'pending_received';
    }
}
