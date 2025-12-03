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

// AJAX: fetch friend avatar preview by id
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    && $_SERVER['REQUEST_METHOD'] === 'GET'
    && isset($_GET['ajax_friend'])) {
    header('Content-Type: application/json');
    $fid = isset($_GET['friend_id']) ? (int) $_GET['friend_id'] : 0;
    if ($fid <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid friend id']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT username, avatar_url FROM users WHERE user_id = ?");
        $stmt->execute([$fid]);
        $fr = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fr) {
            echo json_encode(['success' => false, 'error' => 'User not found']);
            exit;
        }
        $av = !empty($fr['avatar_url']) ? json_decode($fr['avatar_url'], true) : null;
        echo json_encode([
            'success' => true,
            'friend' => [
                'username' => $fr['username'] ?? 'Friend',
                'emoji' => $av['emoji'] ?? null,
                'color' => $av['color'] ?? null,
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Lookup failed']);
    }
    exit;
}

// 3) Get this user's friends so we can choose who to compare with
$friends = listFriends($currentUserId);  // from friends.php

// 4) Check if a friend has been selected in the dropdown (?friend_id=123)
$selectedFriendId = isset($_GET['friend_id']) ? (int) $_GET['friend_id'] : 0;
$selectedFriendName = '';
$comparison = null;
$currentUserAvatar = null;
$friendAvatar = null;

// Always load current user's avatar/username for the "You" side
try {
    $stmtMe = $pdo->prepare("SELECT username, avatar_url FROM users WHERE user_id = ?");
    $stmtMe->execute([$currentUserId]);
    $me = $stmtMe->fetch(PDO::FETCH_ASSOC);
    if ($me) {
        $av = !empty($me['avatar_url']) ? json_decode($me['avatar_url'], true) : null;
        $currentUserAvatar = [
            'emoji' => $av['emoji'] ?? null,
            'color' => $av['color'] ?? null,
            'username' => $me['username'] ?? ($_SESSION['username'] ?? 'You')
        ];
    }
} catch (Exception $e) {
    // ignore
}

// Resolve selected friend's display name if chosen
if ($selectedFriendId > 0) {
    foreach ($friends as $fr) {
        if ((int)$fr['id'] === $selectedFriendId) {
            $selectedFriendName = $fr['username'] ?? '';
            break;
        }
    }
}

if ($selectedFriendId > 0) {
    // Use the compare logic from movies.php
    $comparison = getComparison($pdo, $currentUserId, $selectedFriendId);

    // Fetch avatar data for friend
    try {
        $stmt = $pdo->prepare("SELECT username, avatar_url FROM users WHERE user_id = ?");
        $stmt->execute([$selectedFriendId]);
        $fr = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fr) {
            $av = !empty($fr['avatar_url']) ? json_decode($fr['avatar_url'], true) : null;
            $friendAvatar = [
                'emoji' => $av['emoji'] ?? null,
                'color' => $av['color'] ?? null,
                'username' => $fr['username'] ?? $selectedFriendName
            ];
        }
    } catch (Exception $e) {
        // Silently ignore avatar loading issues
    }
}
?>
<?php include 'partials/header.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>Compare Ratings</h1>
        <p>Pick a friend to see shared titles and differences in your lists.</p>
    </div>

    <!-- Centered Avatars and Friend Picker -->
    <section id="comparePeopleBox" class="form-box compare-people-box" style="display:flex; align-items:center; justify-content:center; position:relative;">
            <?php if ($selectedFriendId && ($currentUserAvatar || $friendAvatar)): ?>
                <div class="compare-people">
                    <div class="compare-person">
                        <div class="friend-avatar <?php echo !empty($currentUserAvatar['emoji']) ? 'emoji-avatar' : 'default-avatar'; ?>"
                             <?php if (!empty($currentUserAvatar['emoji']) && !empty($currentUserAvatar['color'])): ?>
                                 style="background: <?php echo htmlspecialchars($currentUserAvatar['color']); ?>"
                             <?php endif; ?>>
                            <?php if (!empty($currentUserAvatar['emoji'])): ?>
                                <span class="avatar-emoji"><?php echo htmlspecialchars($currentUserAvatar['emoji']); ?></span>
                            <?php else: ?>
                                <span class="friend-emoji"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['username'] ?? 'Y', 0, 1))); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="friend-name">You</div>
                    </div>
                    <div class="vs-pill">vs</div>
                    <div id="friendPickerToggle" class="compare-person clickable" role="button" tabindex="0" aria-haspopup="listbox" aria-expanded="false">
                        <div class="friend-avatar <?php echo !empty($friendAvatar['emoji']) ? 'emoji-avatar' : 'default-avatar'; ?>"
                             <?php if (!empty($friendAvatar['emoji']) && !empty($friendAvatar['color'])): ?>
                                 style="background: <?php echo htmlspecialchars($friendAvatar['color']); ?>"
                             <?php endif; ?>>
                            <?php if (!empty($friendAvatar['emoji'])): ?>
                                <span class="avatar-emoji"><?php echo htmlspecialchars($friendAvatar['emoji']); ?></span>
                            <?php else: ?>
                                <span class="friend-emoji"><?php echo htmlspecialchars(strtoupper(substr($selectedFriendName ?: 'F', 0, 1))); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="friend-name"><?php echo htmlspecialchars($selectedFriendName); ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="compare-people">
                    <div class="compare-person">
                        <div class="friend-avatar <?php echo !empty($currentUserAvatar['emoji']) ? 'emoji-avatar' : 'default-avatar'; ?>"
                             <?php if (!empty($currentUserAvatar['emoji']) && !empty($currentUserAvatar['color'])): ?>
                                 style="background: <?php echo htmlspecialchars($currentUserAvatar['color']); ?>"
                             <?php endif; ?>>
                            <?php if (!empty($currentUserAvatar['emoji'])): ?>
                                <span class="avatar-emoji"><?php echo htmlspecialchars($currentUserAvatar['emoji']); ?></span>
                            <?php else: ?>
                                <span class="friend-emoji"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['username'] ?? 'Y', 0, 1))); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="friend-name">You</div>
                    </div>
                    <div class="vs-pill">vs</div>
                    <div id="friendPickerToggle" class="compare-person clickable" role="button" tabindex="0" aria-haspopup="listbox" aria-expanded="false">
                        <div class="friend-avatar default-avatar">
                            <span class="friend-emoji">?</span>
                        </div>
                        <div class="friend-name" style="color: var(--text-secondary);">Friend</div>
                    </div>
                </div>
            <?php endif; ?>
        
        <!-- Friend picker menu -->
        <div id="friendPickerMenu" class="friend-picker-menu" role="listbox" aria-label="Select a friend" hidden>
            <?php if (!empty($friends)): ?>
                <?php foreach ($friends as $fr): ?>
                    <?php
                        // Load avatar JSON if available for this friend (optional future enhancement)
                        $fid = (int)$fr['id'];
                        $fname = $fr['username'] ?? 'Friend';
                        $initial = strtoupper(substr($fname, 0, 1));
                    ?>
                    <button type="button" class="friend-picker-item" data-friend-id="<?php echo htmlspecialchars($fid); ?>">
                        <span class="friend-picker-avatar friend-avatar default-avatar"><span class="friend-emoji"><?php echo htmlspecialchars($initial); ?></span></span>
                        <span class="friend-picker-name"><?php echo htmlspecialchars($fname); ?></span>
                    </button>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="friend-picker-empty">No friends yet.</div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($selectedFriendId && $comparison): ?>


        <!-- Shared movies section (full width) -->
        <section class="form-box compare-scroll">
            <h2 class="section-title">Shared Movies</h2>
            <?php if (empty($comparison['shared'])): ?>
                <p class="no-results">No shared movies yet.</p>
            <?php else: ?>
                <ul class="results-list">
                    <?php foreach ($comparison['shared'] as $movie): ?>
                        <li>
                            <div>
                                    <?php if (!empty($movie['api_id'])): ?>
                                        <a href="#" class="view-details-link" data-imdb-id="<?php echo htmlspecialchars($movie['api_id']); ?>" data-in-user-list="1">
                                            <strong><?php echo htmlspecialchars($movie['title']); ?></strong> (<?php echo htmlspecialchars($movie['year']); ?>)
                                        </a>
                                    <?php else: ?>
                                        <strong><?php echo htmlspecialchars($movie['title']); ?></strong> (<?php echo htmlspecialchars($movie['year']); ?>)
                                    <?php endif; ?>
                                    <div style="color: var(--text-secondary);">You: <?php echo htmlspecialchars($movie['user_rating']); ?>/10 • <?php echo htmlspecialchars($selectedFriendName ?: 'Friend'); ?>: <?php echo htmlspecialchars($movie['friend_rating']); ?>/10</div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <!-- Two-column unique lists -->
        <div class="compare-grid">
            <section class="form-box compare-scroll">
                <h3 class="section-title">Your Movies <?php echo htmlspecialchars($selectedFriendName ?: 'Friend'); ?> Hasn't Seen</h3>
                <?php if (empty($comparison['user_only'])): ?>
                    <p class="no-results">No unique movies on your list.</p>
                <?php else: ?>
                    <ul class="results-list">
                        <?php foreach ($comparison['user_only'] as $movie): ?>
                            <li>
                                <div>
                                        <?php if (!empty($movie['api_id'])): ?>
                                            <a href="#" class="view-details-link" data-imdb-id="<?php echo htmlspecialchars($movie['api_id']); ?>" data-in-user-list="1">
                                                <strong><?php echo htmlspecialchars($movie['title']); ?></strong> (<?php echo htmlspecialchars($movie['year']); ?>)
                                            </a>
                                        <?php else: ?>
                                            <strong><?php echo htmlspecialchars($movie['title']); ?></strong> (<?php echo htmlspecialchars($movie['year']); ?>)
                                        <?php endif; ?>
                                        <div style="color: var(--text-secondary);">Your rating: <?php echo htmlspecialchars($movie['rating']); ?>/10</div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <section class="form-box compare-scroll">
                <h3 class="section-title"><?php echo htmlspecialchars($selectedFriendName ?: 'Friend'); ?>'s Movies You Haven't Seen</h3>
                <?php if (empty($comparison['friend_only'])): ?>
                    <p class="no-results">No unique movies on your friend's list.</p>
                <?php else: ?>
                    <ul class="results-list">
                        <?php foreach ($comparison['friend_only'] as $movie): ?>
                            <li>
                                <div>
                                        <?php if (!empty($movie['api_id'])): ?>
                                            <a href="#" class="view-details-link" data-imdb-id="<?php echo htmlspecialchars($movie['api_id']); ?>" data-in-user-list="0">
                                                <strong><?php echo htmlspecialchars($movie['title']); ?></strong> (<?php echo htmlspecialchars($movie['year']); ?>)
                                            </a>
                                        <?php else: ?>
                                            <strong><?php echo htmlspecialchars($movie['title']); ?></strong> (<?php echo htmlspecialchars($movie['year']); ?>)
                                        <?php endif; ?>
                                        <div style="color: var(--text-secondary);"><?php echo htmlspecialchars($selectedFriendName ?: 'Friend'); ?>'s rating: <?php echo htmlspecialchars($movie['rating']); ?>/10</div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </div>

    <?php elseif ($selectedFriendId && !$comparison): ?>
        <p>Could not load comparison for that friend.</p>
    <?php endif; ?>
</main>

<script>
// Modal + details fetch to mirror movies page behavior, with Add to Watchlist
document.addEventListener('DOMContentLoaded', function() {
    const isLoggedIn = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;
    const currentUserAvatar = <?php echo json_encode($currentUserAvatar ?? null); ?>;
    const comparePeopleBox = document.getElementById('comparePeopleBox');
    const friendToggle = document.getElementById('friendPickerToggle');
    const friendMenu = document.getElementById('friendPickerMenu');

    // Friend picker interactions
    if (friendToggle && friendMenu) {
        const openMenu = () => {
            friendMenu.hidden = false;
            friendToggle.setAttribute('aria-expanded', 'true');
            positionMenu();
        };
        const closeMenu = () => {
            friendMenu.hidden = true;
            friendToggle.setAttribute('aria-expanded', 'false');
        };
        const positionMenu = () => {
            const rect = friendToggle.getBoundingClientRect();
            const parentRect = comparePeopleBox.getBoundingClientRect();
            const top = rect.bottom - parentRect.top + 8;
            const left = rect.left - parentRect.left - 40;
            friendMenu.style.top = `${top}px`;
            friendMenu.style.left = `${Math.max(8, left)}px`;
        };
        friendToggle.addEventListener('click', (e) => {
            e.preventDefault();
            friendMenu.hidden ? openMenu() : closeMenu();
        });
        friendToggle.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                friendMenu.hidden ? openMenu() : closeMenu();
            }
        });
        document.addEventListener('click', (e) => {
            if (!friendMenu.hidden && !friendMenu.contains(e.target) && !friendToggle.contains(e.target)) {
                closeMenu();
            }
        });
        window.addEventListener('resize', () => { if (!friendMenu.hidden) positionMenu(); });
        window.addEventListener('scroll', () => { if (!friendMenu.hidden) positionMenu(); }, true);
        friendMenu.addEventListener('click', (e) => {
            const item = e.target.closest('.friend-picker-item');
            if (!item) return;
            const fid = item.getAttribute('data-friend-id');
            if (!fid) return;
            window.location.href = `compare.php?friend_id=${encodeURIComponent(fid)}`;
        });
    }

    document.body.addEventListener('click', async function(e) {
        const link = e.target.closest('.view-details-link');
        if (!link) return;
        e.preventDefault();
        const imdbId = link.getAttribute('data-imdb-id');
        const inUserList = link.getAttribute('data-in-user-list') === '1';
        if (!imdbId) return;

        showModal('Loading...', '<p>Fetching movie details...</p>', null, null);
        try {
            const resp = await fetch(`movies.php?ajax_details=1&imdb_id=${encodeURIComponent(imdbId)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await resp.json();
            if (data.success && data.details) {
                const d = data.details;
                const poster = d.poster ? `<img src="${escapeHtml(d.poster)}" alt="Poster" style="max-width: 200px; margin-bottom: 1rem;">` : '';
                const content = `
                    ${poster}
                    <p><strong>Year:</strong> ${escapeHtml(d.year)}</p>
                    <p><strong>Genre:</strong> ${escapeHtml(d.genre)}</p>
                    <p><strong>Director:</strong> ${escapeHtml(d.director)}</p>
                    <p><strong>Actors:</strong> ${escapeHtml(d.actors)}</p>
                    <p><strong>Runtime:</strong> ${escapeHtml(d.runtime)}</p>
                    <p><strong>Plot:</strong> ${escapeHtml(d.plot)}</p>
                `;
                showModal(d.title, content, imdbId, inUserList);
            } else {
                showModal('Error', '<p>Could not load movie details.</p>', null, null);
            }
        } catch (err) {
            console.error('Details fetch failed', err);
            showModal('Error', '<p>Failed to fetch movie details.</p>', null, null);
        }
    });

    async function addToWatchlist(imdbId, button) {
        try {
            button.disabled = true;
            const formData = new FormData();
            formData.append('ajax_add', '1');
            formData.append('imdb_id', imdbId);
            const response = await fetch('movies.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');
                button.textContent = '✓ Added!';
                setTimeout(() => {
                    button.textContent = 'In Watchlist';
                }, 800);
                button.disabled = true;
            } else {
                button.disabled = false;
                alert(data.error || 'Could not add to watchlist');
            }
        } catch (e) {
            console.error(e);
            button.disabled = false;
            alert('An error occurred. Please try again.');
        }
    }

    function showModal(title, content, imdbId, inUserList) {
        let modal = document.getElementById('movieDetailsModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'movieDetailsModal';
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="modal-close">&times;</span>
                    <h2 id="modalTitle"></h2>
                    <div id="modalBody"></div>
                    <div id="modalActions" style="margin-top: 1.5rem; text-align: center;"></div>
                </div>`;
            document.body.appendChild(modal);
            modal.querySelector('.modal-close').addEventListener('click', () => modal.style.display = 'none');
            modal.addEventListener('click', (ev) => { if (ev.target === modal) modal.style.display = 'none'; });
        }
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalBody').innerHTML = content;
        const actions = document.getElementById('modalActions');
        if (isLoggedIn && imdbId) {
            if (inUserList) {
                actions.innerHTML = '<button class="btn btn-success" disabled>In Your List</button>';
            } else {
                actions.innerHTML = `<button id="modalAddBtn" class="btn btn-primary">Add to Watchlist</button>`;
                const addBtn = document.getElementById('modalAddBtn');
                addBtn.addEventListener('click', () => addToWatchlist(imdbId, addBtn));
            }
        } else {
            actions.innerHTML = '';
        }
        modal.style.display = 'block';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }
});
</script>

<?php include 'partials/footer.php'; ?>
