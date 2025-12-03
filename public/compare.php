<?php
// Let backend files know this is a valid entry point
define('FLICK_FUSION_ENTRY_POINT', true);

session_start();

// 1) Include database + controllers we need
require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/controllers/friends.php';
require_once __DIR__ . '/../backend/controllers/movies.php';

// 2) Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$currentUserId = (int) $_SESSION['user_id'];

// 3) Get this user's friends so we can choose who to compare with
$friends = listFriends($currentUserId);  // from friends.php

// 4) Check if a friend has been selected in the dropdown (?friend_id=123)
$selectedFriendId = isset($_GET['friend_id']) ? (int) $_GET['friend_id'] : 0;
$comparison = null;

if ($selectedFriendId > 0) {
    // Use the compare logic from movies.php
    $comparison = getComparison($pdo, $currentUserId, $selectedFriendId);
}
?>
<?php include 'partials/header.php'; ?>

<main class="container">
    <h1>Compare Ratings</h1>
    <p>Pick a friend to see which movies you both have, and which are unique to each of you.</p>

    <!-- Friend selection form -->
    <form method="get" action="compare.php" style="margin-bottom: 1rem;">
        <label for="friend_id">Choose a friend:</label>
        <select name="friend_id" id="friend_id">
            <option value="">-- Select a friend --</option>

            <?php foreach ($friends as $friend): ?>
                <option value="<?php echo htmlspecialchars($friend['id']); ?>"
                    <?php if ($selectedFriendId === (int)$friend['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($friend['username']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Compare</button>
    </form>

    <?php if ($selectedFriendId && $comparison): ?>

        <!-- Shared movies section -->
        <section style="margin-bottom: 2rem;">
            <h2>Movies you both have</h2>

            <?php if (empty($comparison['shared'])): ?>
                <p>No shared movies yet.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($comparison['shared'] as $movie): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($movie['title']); ?></strong>
                            (<?php echo htmlspecialchars($movie['year']); ?>)
                            <br>
                            You: <?php echo htmlspecialchars($movie['user_rating']); ?>/10,
                            Friend: <?php echo htmlspecialchars($movie['friend_rating']); ?>/10
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <!-- Movies only you have -->
        <section style="margin-bottom: 2rem;">
            <h2>Your movies they haven't seen</h2>

            <?php if (empty($comparison['user_only'])): ?>
                <p>No unique movies on your list.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($comparison['user_only'] as $movie): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($movie['title']); ?></strong>
                            (<?php echo htmlspecialchars($movie['year']); ?>)
                            - Your rating: <?php echo htmlspecialchars($movie['rating']); ?>/10
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <!-- Movies only your friend has -->
        <section style="margin-bottom: 2rem;">
            <h2>Their movies you haven't seen</h2>

            <?php if (empty($comparison['friend_only'])): ?>
                <p>No unique movies on your friend's list.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($comparison['friend_only'] as $movie): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($movie['title']); ?></strong>
                            (<?php echo htmlspecialchars($movie['year']); ?>)
                            - Their rating: <?php echo htmlspecialchars($movie['rating']); ?>/10
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

    <?php elseif ($selectedFriendId && !$comparison): ?>
        <p>Could not load comparison for that friend.</p>
    <?php endif; ?>
</main>

<?php include 'partials/footer.php'; ?>
