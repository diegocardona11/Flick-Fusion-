<?php
// Define entry point for backend includes 
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    define('FLICK_FUSION_ENTRY_POINT', true);
}

/**
 * profile.php
 * ----------------------------------------
 * User profile page - allows users to view and edit their profile
 *   - View current username, email, and avatar
 */

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/controllers/auth.php';
require_once __DIR__ . '/../backend/config/db.php';

// Ensure user is logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$currentUserId = $_SESSION['user_id'];
$currentUsername = $_SESSION['username'];
$currentEmail = $_SESSION['email'];

// Available emojis and colors for customization
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

// Handle form submissions
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle custom avatar selection
    if (isset($_POST['select_avatar'])) {
        $emoji = $_POST['avatar_emoji'] ?? '';
        $color = $_POST['avatar_color'] ?? '';
        
        // Validate emoji and color
        if (in_array($emoji, $availableEmojis) && !empty($color)) {
            $isValidColor = false;
            foreach ($availableColors as $c) {
                if ($c['hex'] === $color) {
                    $isValidColor = true;
                    break;
                }
            }
            
            if ($isValidColor) {
                // Store emoji and color as JSON
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
    
    // Handle username update
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
}

// Get current user data
$stmt = $pdo->prepare("SELECT avatar_url FROM users WHERE user_id = ?");
$stmt->execute([$currentUserId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Parse current avatar
$currentAvatar = null;
if (!empty($userData['avatar_url'])) {
    $avatarData = json_decode($userData['avatar_url'], true);
    if ($avatarData && isset($avatarData['emoji']) && isset($avatarData['color'])) {
        $currentAvatar = $avatarData;
    }
}

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
                    <div class="settings-content">
                        <p style="color: var(--text-muted, #888); margin-bottom: 1rem;">Change your display name</p>
                        <form method="POST" class="profile-form">
                            <div class="form-group">
                                <label for="new_username">New Username</label>
                                <input type="text" id="new_username" name="new_username" value="<?= htmlspecialchars($currentUsername) ?>" required minlength="3" maxlength="20">
                                <small>3-20 characters. Must start with a letter and contain only letters, numbers, and underscores.</small>
                            </div>
                            <div class="form-actions">
                                <button type="submit" name="update_username" class="btn btn-primary">Update Username</button>
                            </div>
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
