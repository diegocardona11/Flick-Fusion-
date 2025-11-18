<?php
// Tell db.php this is a valid entry point
define('FLICK_FUSION_ENTRY_POINT', true);
session_start();

// 1. Include DB + friends controller
require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/controllers/friends.php';

/**
 * IMPORTANT:
 * Set these to two REAL user IDs that exist in your `users` table.
 * You can see them in phpMyAdmin > your_database > users.
 */
$userA = 1; // user who sends the request
$userB = 2; // user who receives the request

// 2. Handle actions from the URL (?do=send, ?do=accept, ?do=reject)
$actionMessage = '';

if (isset($_GET['do'])) {
    $do = $_GET['do'];

    if ($do === 'send') {
        $ok = addFriend($userA, $userB);
        $actionMessage = $ok
            ? "✅ Sent friend request from User A (ID $userA) to User B (ID $userB)."
            : "⚠️ Could not send friend request (maybe it already exists?).";
    }

    if ($do === 'accept') {
        $pending = getPendingFriendRequests($userB);

        if (!empty($pending)) {
            // Accept the first pending request
            $requestId = $pending[0]['request_id'];
            $ok = acceptFriendRequest($requestId);
            $actionMessage = $ok
                ? "✅ User B (ID $userB) accepted request ID $requestId."
                : "⚠️ Could not accept friend request.";
        } else {
            $actionMessage = "ℹ️ No pending friend requests for User B to accept.";
        }
    }

    if ($do === 'reject') {
        $pending = getPendingFriendRequests($userB);

        if (!empty($pending)) {
            $requestId = $pending[0]['request_id'];
            $ok = rejectFriendRequest($requestId);
            $actionMessage = $ok
                ? "✅ User B (ID $userB) rejected request ID $requestId."
                : "⚠️ Could not reject friend request.";
        } else {
            $actionMessage = "ℹ️ No pending friend requests for User B to reject.";
        }
    }
}

// 3. Always show current state
$pendingForB   = getPendingFriendRequests($userB);
$friendsOfA    = listFriends($userA);
$friendsOfB    = listFriends($userB);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Friends Test</title>
</head>
<body>
    <h1>Friends System Test</h1>

    <p><strong>User A ID:</strong> <?php echo htmlspecialchars($userA); ?></p>
    <p><strong>User B ID:</strong> <?php echo htmlspecialchars($userB); ?></p>

    <?php if ($actionMessage): ?>
        <p><strong><?php echo $actionMessage; ?></strong></p>
    <?php endif; ?>

    <p>
        <a href="?do=send">📩 Send friend request (A → B)</a> |
        <a href="?do=accept">✅ Accept first pending (as B)</a> |
        <a href="?do=reject">❌ Reject first pending (as B)</a>
    </p>

    <h2>Pending requests for User B (ID <?php echo $userB; ?>)</h2>
    <pre><?php print_r($pendingForB); ?></pre>

    <h2>Friends of User A (ID <?php echo $userA; ?>)</h2>
    <pre><?php print_r($friendsOfA); ?></pre>

    <h2>Friends of User B (ID <?php echo $userB; ?>)</h2>
    <pre><?php print_r($friendsOfB); ?></pre>
</body>
</html>
