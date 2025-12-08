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
$activeTab = $_GET['tab'] ?? 'friends';
$searchQuery = trim($_GET['search'] ?? '');
$findQuery = trim($_GET['find'] ?? '');

// Get all friends
$friends = listFriends($userId);

// Filter friends by search query if provided
if (!empty($searchQuery)) {
    $friends = array_filter($friends, function($friend) use ($searchQuery) {
        return stripos($friend['username'], $searchQuery) !== false;
    });
}

// Search for users to add as friends
$searchResults = [];
if (!empty($findQuery)) {
    $searchResults = searchUsers($findQuery, $userId);
}

// Get pending friend requests (incoming)
$pendingRequests = getPendingFriendRequests($userId);
// Get sent friend requests (outgoing)
$sentRequests = getSentFriendRequests($userId);


include 'partials/header.php';
?>

<main class="container friends-page">
    <div class="friends-header">
        <h1>My Friends</h1>
        <p>Manage your friends and see who's in your network.</p>
    </div>

    <!-- Tab Navigation -->
    <div class="friends-tabs">
        <a href="?tab=friends" class="tab-link <?= $activeTab === 'friends' ? 'active' : '' ?>">My Friends</a>
        <a href="?tab=find" class="tab-link <?= $activeTab === 'find' ? 'active' : '' ?>">Find Friends</a>
    </div>

    <?php if ($activeTab === 'find'): ?>
        <!-- Find Friends Section -->
        <div class="friends-search">
            <form method="get" action="friends.php" class="search-form">
                <input type="hidden" name="tab" value="find">
                <input 
                    type="text" 
                    name="find" 
                    value="<?= htmlspecialchars($findQuery) ?>" 
                    placeholder="Search for users by username..." 
                    class="form-input"
                    autofocus
                >
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($findQuery): ?>
                    <a href="friends.php?tab=find" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <section class="friends-section">
            <h2 class="section-title">Search Results</h2>
            <?php if (count($searchResults) > 0): ?>
                <div class="friends-grid">
                    <?php foreach ($searchResults as $user): 
                        $friendshipStatus = getFriendshipStatus($userId, $user['id']);
                        $avatarData = null;
                        if (!empty($user['avatar_url'])) {
                            $avatarData = json_decode($user['avatar_url'], true);
                        }
                    ?>
                        <div class="friend-card">
                            <a class="card-link" href="profile.php?user_id=<?= $user['id'] ?>">
                                <div class="friend-actions">
                                    <div class="status-row">
                                        <?php if ($friendshipStatus === 'friends'): ?>
                                            <span class="status-pill pill-friends" aria-label="Accepted friend">Friends</span>
                                        <?php elseif ($friendshipStatus === 'pending_sent'): ?>
                                            <span class="status-pill pill-sent" aria-label="Outgoing friend request awaiting response">Request Sent</span>
                                        <?php elseif ($friendshipStatus === 'pending_received'): ?>
                                            <span class="status-pill pill-pending" aria-label="Incoming friend request needs response">Needs Response</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="friend-avatar-link">
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
                                </div>
                                <div class="friend-info">
                                    <span class="friend-name">
                                        <?= htmlspecialchars($user['username']) ?>
                                    </span>
                                </div>
                            </a>
                            <div class="friend-actions">
                                <?php if ($friendshipStatus === 'pending_received'): ?>
                                    <div class="decision-row">
                                        <form method="post" action="profile.php?user_id=<?= $user['id'] ?>" style="display:inline;">
                                            <input type="hidden" name="requester_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="accept_friend" class="btn btn-primary btn-sm">Accept</button>
                                        </form>
                                        <form method="post" action="profile.php?user_id=<?= $user['id'] ?>" style="display:inline;">
                                            <input type="hidden" name="requester_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="reject_friend" class="btn btn-secondary btn-sm">Decline</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="decision-row">
                                        <form method="post" action="profile.php?user_id=<?= $user['id'] ?>" style="display:inline;">
                                            <button type="submit" name="add_friend" class="btn btn-primary btn-sm">Add Friend</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($findQuery): ?>
                <div class="empty-state">
                    <p>No users found matching "<?= htmlspecialchars($findQuery) ?>".</p>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Enter a username to search for friends.</p>
                </div>
            <?php endif; ?>
        </section>

    <?php else: ?>
        <!-- My Friends Tab (default) -->
        <!-- Search Bar -->
        <div class="friends-search">
            <form method="get" action="friends.php" class="search-form">
                <input type="hidden" name="tab" value="friends">
                <input 
                    type="text" 
                    name="search" 
                    value="<?= htmlspecialchars($searchQuery) ?>" 
                    placeholder="Search my friends by username..." 
                    class="form-input"
                >
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($searchQuery): ?>
                    <a href="friends.php?tab=friends" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

    <!-- Friends List Section -->
    <section class="friends-section">
        <h2 class="section-title">
            <?php if ($searchQuery): ?>
                Search Results for "<?= htmlspecialchars($searchQuery) ?>" (<?= count($friends) + count($pendingRequests) + count($sentRequests) ?>)
            <?php else: ?>
                All Friends & Requests (<?= count($friends) + count($pendingRequests) + count($sentRequests) ?>)
            <?php endif; ?>
        </h2>

        <?php 
        $hasAny = count($friends) > 0 || count($pendingRequests) > 0 || count($sentRequests) > 0;
        if ($hasAny): 
        ?>
                            <!-- Pending Requests (Need Action) -->
            <div class="friends-grid">
                <?php foreach ($pendingRequests as $request): ?>
                                        <?php if ($searchQuery && stripos($request['username'], $searchQuery) === false) continue; ?>
                    <?php
                    $avatarData = null;
                    if (!empty($request['avatar_url'])) {
                        $avatarData = json_decode($request['avatar_url'], true);
                    }
                    ?>
                    <div class="friend-card">
                        <a class="card-link" href="profile.php?user_id=<?= $request['requester_id'] ?>">
                            <div class="friend-actions">
                                <div class="status-row">
                                    <span class="status-pill pill-pending" aria-label="Incoming friend request needs response">Needs Response</span>
                                </div>
                            </div>
                            <div class="friend-avatar-link">
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
                            </div>
                            <div class="friend-info">
                                <span class="friend-name">
                                    <?= htmlspecialchars($request['username']) ?>
                                </span>
                            </div>
                        </a>
                        <div class="friend-actions">
                            <div class="decision-row">
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
                    </div>
                <?php endforeach; ?>

                <!-- Accepted Friends -->
                <?php foreach ($friends as $friend): ?>
                    <?php if ($searchQuery && stripos($friend['username'], $searchQuery) === false) continue; ?>
                    <?php
                    $avatarData = null;
                    if (!empty($friend['avatar_url'])) {
                        $avatarData = json_decode($friend['avatar_url'], true);
                    }
                    ?>
                    <div class="friend-card">
                        <a class="card-link" href="profile.php?user_id=<?= $friend['id'] ?>">
                            <div class="friend-actions">
                                <div class="status-row">
                                    <span class="status-pill pill-friends" aria-label="Accepted friend">Friends</span>
                                </div>
                            </div>
                            <div class="friend-avatar-link">
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
                            </div>
                            <div class="friend-info">
                                <span class="friend-name">
                                    <?= htmlspecialchars($friend['username']) ?>
                                </span>
                            </div>
                        </a>
                        <div class="friend-actions">
                            <div class="decision-row">
                                <div class="friend-dropdown"> /* Dropdown for friend actions: remove friend */
                                    <button class="btn btn-primary btn-sm friend-status-btn-styled" onclick="toggleFriendMenu(event, <?= $friend['id'] ?>)">
                                        Friends
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dropdown-arrow">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </button>
                                    <div class="friend-dropdown-menu" id="friendMenu<?= $friend['id'] ?>">
                                        <form method="post" action="profile.php?user_id=<?= $friend['id'] ?>">
                                            <button type="submit" name="remove_friend" class="dropdown-item dropdown-item-danger">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="8.5" cy="7" r="4"></circle>
                                                    <line x1="18" y1="8" x2="23" y2="13"></line>
                                                    <line x1="23" y1="8" x2="18" y2="13"></line>
                                                </svg>
                                                Unfriend
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Sent Requests (Awaiting Response) -->
                    <?php foreach ($sentRequests as $request): ?>
                                                <?php if ($searchQuery && stripos($request['username'], $searchQuery) === false) continue; ?>
                        <?php
                        $avatarData = null;
                        if (!empty($request['avatar_url'])) {
                            $avatarData = json_decode($request['avatar_url'], true);
                        }
                        ?>
                        <div class="friend-card">
                            <a class="card-link" href="profile.php?user_id=<?= $request['recipient_id'] ?>">
                                <div class="friend-actions">
                                    <div class="status-row">
                                        <span class="status-pill pill-sent" aria-label="Outgoing friend request awaiting response">Request Sent</span>
                                    </div>
                                </div>
                                <div class="friend-avatar-link">
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
                                </div>
                                <div class="friend-info">
                                    <span class="friend-name">
                                        <?= htmlspecialchars($request['username']) ?>
                                    </span>
                                </div>
                            </a>
                            <div class="friend-actions"></div>
                        </div>
                    <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <?php if ($searchQuery): ?>
                    <p>No friends found matching "<?= htmlspecialchars($searchQuery) ?>".</p>
                    <a href="friends.php?tab=friends" class="btn btn-secondary">View All Friends</a>
                <?php else: ?>
                    <p>You haven't added any friends yet.</p>
                    <a href="friends.php?tab=find" class="btn btn-primary">Find Friends</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</main>

<?php include 'partials/footer.php'; ?>
