<?php
// Suppress error display for production (errors still logged)
ini_set('display_errors', '0');
error_reporting(E_ALL);

if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    define('FLICK_FUSION_ENTRY_POINT', true);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/controllers/friends.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$searchQuery = trim($_GET['search'] ?? '');

// Get all friends
$friends = listFriends($userId);

// Filter friends by search query if provided
if (!empty($searchQuery)) {
    $friends = array_filter($friends, function($friend) use ($searchQuery) {
        return stripos($friend['username'], $searchQuery) !== false;
    });
}

// Get pending friend requests (incoming)
$pendingRequests = getPendingFriendRequests($userId);

include 'partials/header.php';
?>

<main class="container friends-page">
    <div class="friends-header">
        <h1>My Friends</h1>
        <p>Manage your friends and see who's in your network.</p>
    </div>

    <!-- Search Bar -->
    <div class="friends-search">
        <form method="get" action="friends.php" class="search-form">
            <input 
                type="text" 
                name="search" 
                value="<?= htmlspecialchars($searchQuery) ?>" 
                placeholder="Search friends by username..." 
                class="form-input"
            >
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($searchQuery): ?>
                <a href="friends.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Pending Friend Requests Section -->
    <?php if (count($pendingRequests) > 0): ?>
        <section class="friends-section">
            <h2 class="section-title">Pending Requests (<?= count($pendingRequests) ?>)</h2>
            <div class="friends-grid">
                <?php foreach ($pendingRequests as $request): ?>
                    <?php
                    $avatarData = null;
                    if (!empty($request['avatar_url'])) {
                        $avatarData = json_decode($request['avatar_url'], true);
                    }
                    ?>
                    <div class="friend-card">
                        <a href="profile.php?user_id=<?= $request['requester_id'] ?>" class="friend-avatar-link">
                            <?php if ($avatarData && isset($avatarData['emoji'])): ?>
                                <div class="friend-avatar emoji-avatar" style="background-color: <?= htmlspecialchars($avatarData['color']) ?>;">
                                    <span class="avatar-emoji"><?= $avatarData['emoji'] ?></span>
                                </div>
                            <?php else: ?>
                                <div class="friend-avatar default-avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </a>
                        <div class="friend-info">
                            <a href="profile.php?user_id=<?= $request['requester_id'] ?>" class="friend-name">
                                <?= htmlspecialchars($request['username']) ?>
                            </a>
                        </div>
                        <div class="friend-actions">
                            <form method="post" action="profile.php?user_id=<?= $request['requester_id'] ?>" style="display: inline;">
                                <input type="hidden" name="requester_id" value="<?= $request['requester_id'] ?>">
                                <button type="submit" name="accept_friend" class="btn btn-primary btn-sm">Accept</button>
                            </form>
                            <form method="post" action="profile.php?user_id=<?= $request['requester_id'] ?>" style="display: inline;">
                                <input type="hidden" name="requester_id" value="<?= $request['requester_id'] ?>">
                                <button type="submit" name="reject_friend" class="btn btn-secondary btn-sm">Decline</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Friends List Section -->
    <section class="friends-section">
        <h2 class="section-title">
            <?php if ($searchQuery): ?>
                Search Results for "<?= htmlspecialchars($searchQuery) ?>" (<?= count($friends) ?>)
            <?php else: ?>
                All Friends (<?= count($friends) ?>)
            <?php endif; ?>
        </h2>

        <?php if (count($friends) > 0): ?>
            <div class="friends-grid">
                <?php foreach ($friends as $friend): ?>
                    <?php
                    $avatarData = null;
                    if (!empty($friend['avatar_url'])) {
                        $avatarData = json_decode($friend['avatar_url'], true);
                    }
                    ?>
                    <div class="friend-card">
                        <a href="profile.php?user_id=<?= $friend['user_id'] ?>" class="friend-avatar-link">
                            <?php if ($avatarData && isset($avatarData['emoji'])): ?>
                                <div class="friend-avatar emoji-avatar" style="background-color: <?= htmlspecialchars($avatarData['color']) ?>;">
                                    <span class="avatar-emoji"><?= $avatarData['emoji'] ?></span>
                                </div>
                            <?php else: ?>
                                <div class="friend-avatar default-avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </a>
                        <div class="friend-info">
                            <a href="profile.php?user_id=<?= $friend['user_id'] ?>" class="friend-name">
                                <?= htmlspecialchars($friend['username']) ?>
                            </a>
                        </div>
                        <div class="friend-actions">
                            <a href="profile.php?user_id=<?= $friend['user_id'] ?>" class="btn btn-primary btn-sm">View Profile</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <?php if ($searchQuery): ?>
                    <p>No friends found matching "<?= htmlspecialchars($searchQuery) ?>".</p>
                    <a href="friends.php" class="btn btn-secondary">View All Friends</a>
                <?php else: ?>
                    <p>You haven't added any friends yet.</p>
                    <p>Find people to connect with!</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include 'partials/footer.php'; ?>
