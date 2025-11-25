<?php
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    define('FLICK_FUSION_ENTRY_POINT', true);
}

/**
 * profile.php
 * ----------------------------------------
 * User profile page - allows users to view and edit their profile
 *   - View current username, email, and avatar
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/controllers/auth.php';
require_once __DIR__ . '/../backend/config/db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$currentUserId = $_SESSION['user_id'];
$currentUsername = $_SESSION['username'];
$currentEmail = $_SESSION['email'];

$availableEmojis = ['😀', '😎', '🤖', '🦊', '🐼', '🦁', '🐸', '🐙', '🦄', '🐢', '🦉', '🐝', '🦋', '🐬', '🦈', '🐧', '🦩', '🐨', '🦘', '🐯', '🦒', '🦓', '🐘', '🦏', '🐍', '🦎', '🐊', '🦖', '🦕', '🐉'];

$availableColors = [
    ['name' => 'Red', 'hex' => '#FF6B6B'],
    ['name' => 'Blue', 'hex' => '#4ECDC4'],
    ['name' => 'Sky Blue', 'hex' => '#45B7D1'],
    ['name' => 'Orange', 'hex' => '#FFA07A'],
    ['name' => 'Mint', 'hex' => '#98D8C8'],
    ['name' => 'Yellow', 'hex' => '#F7DC6F'],
    ['name' => 'Green', 'hex' => '#82E0AA'],
    ['name' => 'Purple', 'hex' => '#BB8FCE'],
    ['name' => 'Pink', 'hex' => '#F8B4D9'],
    ['name' => 'Teal', 'hex' => '#52C9A2'],
    ['name' => 'Grey', 'hex' => '#95A5A6'],
    ['name' => 'Gold', 'hex' => '#FDD835']
];

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['select_avatar'])) {
        $emoji = $_POST['avatar_emoji'] ?? '';
        $color = $_POST['avatar_color'] ?? '';
        if (in_array($emoji, $availableEmojis) && !empty($color)) {
            $isValidColor = false;
            foreach ($availableColors as $c) {
                if ($c['hex'] === $color) {
                    $isValidColor = true;
                    break;
                }
            }
            
            if ($isValidColor) {
                $avatarData = json_encode(['emoji' => $emoji, 'color' => $color]);
                $stmt = $pdo->prepare("UPDATE users SET avatar_url = ? WHERE user_id = ?");
                if ($stmt->execute([$avatarData, $currentUserId])) {
                    $successMessage = 'Avatar updated successfully!';
                } else {
                    $errorMessage = 'Failed to update avatar.';
                }
            } else {
                $errorMessage = 'Invalid color selection.';
            }
        } else {
            $errorMessage = 'Please select both an emoji and a color.';
        }
    }
    
    if (isset($_POST['update_username'])) {
        $newUsername = trim($_POST['new_username']);
        
        if (empty($newUsername)) {
            $errorMessage = 'Username cannot be empty.';
        } elseif (strlen($newUsername) < 3 || strlen($newUsername) > 20) {
            $errorMessage = 'Username must be between 3 and 20 characters.';
        } elseif (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $newUsername)) {
            $errorMessage = 'Username must start with a letter and contain only letters, numbers, and underscores.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE user_id = ?");
                if ($stmt->execute([$newUsername, $currentUserId])) {
                    $_SESSION['username'] = $newUsername;
                    $currentUsername = $newUsername;
                    $successMessage = 'Username updated successfully!';
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errorMessage = 'Username already taken.';
                } else {
                    $errorMessage = 'Failed to update username.';
                }
            }
        }
    }
    
    if (isset($_POST['update_email'])) {
        $newEmail = trim($_POST['new_email']);
        $currentPassword = $_POST['current_password_email'] ?? '';
        
        if (empty($newEmail)) {
            $errorMessage = 'Email cannot be empty.';
        } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Please enter a valid email address.';
        } elseif (empty($currentPassword)) {
            $errorMessage = 'Please enter your current password to change email.';
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
            $stmt->execute([$currentUserId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($currentPassword, $user['password_hash'])) {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE user_id = ?");
                    if ($stmt->execute([$newEmail, $currentUserId])) {
                        $_SESSION['email'] = $newEmail;
                        $currentEmail = $newEmail;
                        $successMessage = 'Email updated successfully!';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $errorMessage = 'Email already in use.';
                    } else {
                        $errorMessage = 'Failed to update email.';
                    }
                }
            } else {
                $errorMessage = 'Current password is incorrect.';
            }
        }
    }
    
    if (isset($_POST['update_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword)) {
            $errorMessage = 'Please enter your current password.';
        } elseif (empty($newPassword)) {
            $errorMessage = 'Please enter a new password.';
        } elseif (strlen($newPassword) < 8) {
            $errorMessage = 'New password must be at least 8 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $errorMessage = 'New passwords do not match.';
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
            $stmt->execute([$currentUserId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($currentPassword, $user['password_hash'])) {
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                if ($stmt->execute([$newPasswordHash, $currentUserId])) {
                    $successMessage = 'Password updated successfully!';
                } else {
                    $errorMessage = 'Failed to update password.';
                }
            } else {
                $errorMessage = 'Current password is incorrect.';
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT avatar_url FROM users WHERE user_id = ?");
$stmt->execute([$currentUserId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$currentAvatar = null;
if (!empty($userData['avatar_url'])) {
    $avatarData = json_decode($userData['avatar_url'], true);
    if ($avatarData && isset($avatarData['emoji']) && isset($avatarData['color'])) {
        $currentAvatar = $avatarData;
    }
}

$friendsStmt = $pdo->prepare("
    SELECT u.user_id, u.username, u.avatar_url
    FROM friends f
    JOIN users u ON (
        (f.user_id = ? AND u.user_id = f.friend_id)
        OR (f.friend_id = ? AND u.user_id = f.user_id)
    )
    WHERE f.status = 'accepted'
    LIMIT 10
");
$friendsStmt->execute([$currentUserId, $currentUserId]);
$friends = $friendsStmt->fetchAll(PDO::FETCH_ASSOC);

include 'partials/header.php';
?>

<main class="container profile-page">
    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <section class="profile-header">
        <div class="profile-avatar">
            <?php if ($currentAvatar): ?>
                <div class="avatar-image emoji-avatar" style="background-color: <?= htmlspecialchars($currentAvatar['color']) ?>;">
                    <span class="avatar-emoji"><?= $currentAvatar['emoji'] ?></span>
                </div>
            <?php else: ?>
                <!-- Default User Icon SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M4 21v-2a4 4 0 0 1 3-3.87"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <h1 class="profile-username"><?= htmlspecialchars($currentUsername) ?></h1>
            <p class="profile-email"><?= htmlspecialchars($currentEmail) ?></p>
        </div>
    </section>

    <section class="profile-content">
        <!-- Friends Preview -->
        <details class="settings-main-dropdown" style="margin-bottom: 1.5rem;">
            <summary class="settings-main-header">
                <span class="settings-main-title">👥 Friends (<?= count($friends) ?>)</span>
                <span class="settings-arrow">▼</span>
            </summary>
            <div class="settings-main-content">
                <?php if (empty($friends)): ?>
                    <div style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                        <p>No friends yet. Add friends to see them here!</p>
                        <a href="friends.php" class="btn btn-primary" style="margin-top: 1rem;">Find Friends</a>
                    </div>
                <?php else: ?>
                    <div class="friends-grid">
                        <?php foreach ($friends as $friend): 
                            $friendAvatar = null;
                            if (!empty($friend['avatar_url'])) {
                                $friendAvatarData = json_decode($friend['avatar_url'], true);
                                if ($friendAvatarData && isset($friendAvatarData['emoji']) && isset($friendAvatarData['color'])) {
                                    $friendAvatar = $friendAvatarData;
                                }
                            }
                        ?>
                            <div class="friend-preview-card">
                                <a href="profile.php?user_id=<?= htmlspecialchars($friend['user_id']) ?>" class="friend-avatar-link">
                                    <?php if ($friendAvatar): ?>
                                        <div class="friend-avatar emoji-avatar" style="background-color: <?= htmlspecialchars($friendAvatar['color']) ?>;">
                                            <span class="friend-emoji"><?= $friendAvatar['emoji'] ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="friend-avatar friend-avatar-default">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 21v-2a4 4 0 0 0-3-3.87"></path>
                                                <path d="M4 21v-2a4 4 0 0 1 3-3.87"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <span class="friend-name"><?= htmlspecialchars($friend['username']) ?></span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <a href="friends.php" class="btn btn-secondary">View All Friends</a>
                    </div>
                <?php endif; ?>
            </div>
        </details>

        <details class="settings-main-dropdown">
            <summary class="settings-main-header">
                <span class="settings-main-title">⚙️ Settings</span>
                <span class="settings-arrow">▼</span>
            </summary>
            <div class="settings-main-content">
                <!-- Avatar Customizer -->
                <details class="settings-dropdown">
                    <summary class="settings-header">
                        <span class="settings-title">Avatar</span>
                        <span class="settings-arrow">▼</span>
                    </summary>
                    <div class="settings-content">
                        <p style="color: var(--text-muted, #888); margin-bottom: 1rem;">Customize your avatar with an emoji and color</p>
                        <form method="POST" id="avatar-form" class="avatar-customizer">
                            <!-- Current Preview -->
                            <div class="avatar-preview-section">
                                <h3>Preview</h3>
                                <div id="avatar-preview" class="avatar-preview" style="background-color: <?= $currentAvatar ? htmlspecialchars($currentAvatar['color']) : '#FF6B6B' ?>;">
                                    <span class="avatar-emoji-large" id="preview-emoji"><?= $currentAvatar ? $currentAvatar['emoji'] : '😀' ?></span>
                                </div>
                            </div>

                            <!-- Emoji Selection -->
                            <div class="selection-section">
                                <h3>Choose Emoji</h3>
                                <div class="emoji-grid">
                                    <?php foreach ($availableEmojis as $emoji): ?>
                                        <button type="button" class="emoji-option <?= ($currentAvatar && $currentAvatar['emoji'] === $emoji) ? 'selected' : '' ?>" data-emoji="<?= htmlspecialchars($emoji) ?>">
                                            <?= $emoji ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Color Selection -->
                            <div class="selection-section">
                                <h3>Choose Color</h3>
                                <div class="color-grid">
                                    <?php foreach ($availableColors as $colorOption): ?>
                                        <button type="button" class="color-option <?= ($currentAvatar && $currentAvatar['color'] === $colorOption['hex']) ? 'selected' : '' ?>" 
                                                data-color="<?= htmlspecialchars($colorOption['hex']) ?>" 
                                                style="background-color: <?= htmlspecialchars($colorOption['hex']) ?>;"
                                                title="<?= htmlspecialchars($colorOption['name']) ?>">
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Hidden inputs to store selections -->
                            <input type="hidden" id="selected-emoji" name="avatar_emoji" value="<?= $currentAvatar ? htmlspecialchars($currentAvatar['emoji']) : '😀' ?>">
                            <input type="hidden" id="selected-color" name="avatar_color" value="<?= $currentAvatar ? htmlspecialchars($currentAvatar['color']) : '#FF6B6B' ?>">

                            <div class="form-actions">
                                <button type="submit" name="select_avatar" class="btn btn-primary">Save Avatar</button>
                            </div>
                        </form>
                    </div>
                </details>

                <!-- Username Management -->
                <details class="settings-dropdown">
                    <summary class="settings-header">
                        <span class="settings-title">Username</span>
                        <span class="settings-arrow">▼</span>
                    </summary>
                    <div class="settings-content" style="padding-top: 1.5rem;">
                        <form method="POST">
                            <div class="form-group" style="margin-bottom: 1.25rem;">
                                <label for="new_username">New Username</label>
                                <input type="text" id="new_username" name="new_username" value="<?= htmlspecialchars($currentUsername) ?>" required minlength="3" maxlength="20" placeholder="Enter new username" style="max-width: 500px;">
                                <small style="display: block; margin-top: 0.5rem; color: var(--text-secondary);">3-20 characters. Must start with a letter.</small>
                            </div>
                            <button type="submit" name="update_username" class="btn btn-primary">Update Username</button>
                        </form>
                    </div>
                </details>

                <!-- Email Management -->
                <details class="settings-dropdown">
                    <summary class="settings-header">
                        <span class="settings-title">Email Address</span>
                        <span class="settings-arrow">▼</span>
                    </summary>
                    <div class="settings-content" style="padding-top: 1.5rem;">
                        <form method="POST">
                            <div class="form-group" style="margin-bottom: 1.25rem;">
                                <label for="new_email">New Email</label>
                                <input 
                                    type="email" 
                                    id="new_email" 
                                    name="new_email" 
                                    value="<?= htmlspecialchars($currentEmail) ?>" 
                                    required
                                    placeholder="Enter your new email"
                                    style="max-width: 500px;"
                                >
                            </div>
                            <div class="form-group" style="margin-bottom: 1.25rem;">
                                <label for="current_password_email">Current Password</label>
                                <input 
                                    type="password" 
                                    id="current_password_email" 
                                    name="current_password_email" 
                                    required
                                    placeholder="Confirm your password"
                                    style="max-width: 500px;"
                                >
                            </div>
                            <button type="submit" name="update_email" class="btn btn-primary">Update Email</button>
                        </form>
                    </div>
                </details>

                <!-- Password Management -->
                <details class="settings-dropdown">
                    <summary class="settings-header">
                        <span class="settings-title">Password</span>
                        <span class="settings-arrow">▼</span>
                    </summary>
                    <div class="settings-content" style="padding-top: 1.5rem;">
                        <form method="POST">
                            <div class="form-group" style="margin-bottom: 1.25rem;">
                                <label for="current_password">Current Password</label>
                                <input 
                                    type="password" 
                                    id="current_password" 
                                    name="current_password" 
                                    required
                                    placeholder="Enter your current password"
                                    style="max-width: 500px;"
                                >
                            </div>
                            <div class="form-group" style="margin-bottom: 1.25rem;">
                                <label for="new_password">New Password</label>
                                <input 
                                    type="password" 
                                    id="new_password" 
                                    name="new_password" 
                                    required 
                                    minlength="8"
                                    placeholder="Enter new password (min. 8 characters)"
                                    style="max-width: 500px;"
                                >
                            </div>
                            <div class="form-group" style="margin-bottom: 1.25rem;">
                                <label for="confirm_password">Confirm New Password</label>
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    required 
                                    minlength="8"
                                    placeholder="Confirm your new password"
                                    style="max-width: 500px;"
                                >
                            </div>
                            <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                </details>
            </div>
        </details>
    </section>
</main>

<script>
// Avatar customizer live preview
document.addEventListener('DOMContentLoaded', function() {
    const emojiOptions = document.querySelectorAll('.emoji-option');
    const colorOptions = document.querySelectorAll('.color-option');
    const preview = document.getElementById('avatar-preview');
    const previewEmoji = document.getElementById('preview-emoji');
    const selectedEmojiInput = document.getElementById('selected-emoji');
    const selectedColorInput = document.getElementById('selected-color');
    
    // Handle emoji selection
    emojiOptions.forEach(option => {
        option.addEventListener('click', function() {
            const emoji = this.getAttribute('data-emoji');
            
            // Remove selected class from all
            emojiOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to this one
            this.classList.add('selected');
            
            // Update preview and hidden input
            previewEmoji.textContent = emoji;
            selectedEmojiInput.value = emoji;
        });
    });
    
    // Handle color selection
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            
            // Remove selected class from all
            colorOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to this one
            this.classList.add('selected');
            
            // Update preview and hidden input
            preview.style.backgroundColor = color;
            selectedColorInput.value = color;
        });
    });
});
</script>

<?php include 'partials/footer.php'; ?>
