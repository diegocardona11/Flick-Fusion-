<?php
// Define entry point for backend includes
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
    define('FLICK_FUSION_ENTRY_POINT', true);
}

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/controllers/movies.php';
require_once __DIR__ . '/../backend/controllers/ratings.php';

// Handle AJAX requests
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    // Handle fetch movie details
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax_details'])) {
        $imdbId = $_GET['imdb_id'] ?? '';
        
        if (empty($imdbId)) {
            echo json_encode(['success' => false, 'error' => 'Movie ID required']);
            exit;
        }
        
        $detailed = omdb_fetch_by_id($imdbId);
        if ($detailed) {
            echo json_encode([
                'success' => true,
                'details' => [
                    'title' => $detailed['Title'] ?? '',
                    'year' => $detailed['Year'] ?? '',
                    'plot' => $detailed['Plot'] ?? 'No plot summary available.',
                    'director' => $detailed['Director'] ?? 'N/A',
                    'actors' => $detailed['Actors'] ?? 'N/A',
                    'genre' => $detailed['Genre'] ?? 'N/A',
                    'runtime' => $detailed['Runtime'] ?? 'N/A',
                    'poster' => ($detailed['Poster'] ?? 'N/A') !== 'N/A' ? $detailed['Poster'] : null
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Could not fetch details']);
        }
        exit;
    }
    
    // Handle movie search
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax_search'])) {
        $query = trim($_GET['q'] ?? '');
        $userId = $_SESSION['user_id'] ?? null;
        
        if (empty($query)) {
            echo json_encode(['results' => []]);
            exit;
        }
        
        $basic_results = omdb_search($query);
        $detailed_results = [];
        
        if ($basic_results) {
            foreach ($basic_results as $movie) {
                $inWatchlist = false;
                
                if ($userId) {
                    $stmt = $pdo->prepare("
                        SELECT r.rating_id 
                        FROM ratings r 
                        JOIN movies m ON m.movie_id = r.movie_id 
                        WHERE r.user_id = ? AND m.api_id = ?
                    ");
                    $stmt->execute([$userId, $movie['imdbID']]);
                    $inWatchlist = $stmt->fetch() !== false;
                }
                
                $detailed_results[] = [
                    'imdbID' => $movie['imdbID'],
                    'title' => $movie['Title'] ?? '',
                    'year' => $movie['Year'] ?? '',
                    'poster' => ($movie['Poster'] ?? 'N/A') !== 'N/A' ? $movie['Poster'] : null,
                    'inWatchlist' => $inWatchlist
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'results' => $detailed_results,
            'query' => $query
        ]);
        exit;
    }
    
    // Handle add to watchlist
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_add'])) {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            echo json_encode([
                'success' => false,
                'error' => 'You must be logged in to add movies to your watchlist.'
            ]);
            exit;
        }
        
        $imdbId = $_POST['imdb_id'] ?? null;
        
        if (empty($imdbId)) {
            echo json_encode([
                'success' => false,
                'error' => 'Movie ID is required.'
            ]);
            exit;
        }
        
        try {
            $localMovieId = addMovieToLocalDB($pdo, $imdbId);
            
            if (!$localMovieId) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Could not fetch movie details from the API.'
                ]);
                exit;
            }
            
            $stmt = $pdo->prepare("SELECT title FROM movies WHERE movie_id = ?");
            $stmt->execute([$localMovieId]);
            $movie = $stmt->fetch(PDO::FETCH_ASSOC);
            $movieTitle = $movie['title'] ?? 'Movie';
            
            $checkStmt = $pdo->prepare("SELECT rating_id, status FROM ratings WHERE user_id = ? AND movie_id = ?");
            $checkStmt->execute([$userId, $localMovieId]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                echo json_encode([
                    'success' => false,
                    'error' => "\"$movieTitle\" is already in your " . ($existing['status'] === 'watchlist' ? 'watchlist' : 'watched list') . ".",
                    'already_added' => true
                ]);
                exit;
            }
            
            $status = 'watchlist';
            $added = addMovieToUserList($pdo, $userId, $localMovieId, $status);
            
            if ($added) {
                echo json_encode([
                    'success' => true,
                    'message' => "\"$movieTitle\" was added to your watchlist!",
                    'movie_title' => $movieTitle
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => "Failed to add \"$movieTitle\" to your watchlist."
                ]);
            }
        } catch (Exception $e) {
            error_log("Error in add to watchlist: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'An error occurred while adding the movie.'
            ]);
        }
        exit;
    }
}

// $pdo is now available from db.php
$userId = $_SESSION['user_id'] ?? null;
$flash = '';

// Handle "Add to List" form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['imdb_id'])) {
    $api_id = $_POST['imdb_id'];
    $local_movie_id = addMovieToLocalDB($pdo, $api_id);

    if ($local_movie_id) {
        // Get the movie title from the database
        $stmt = $pdo->prepare("SELECT title FROM movies WHERE movie_id = ?");
        $stmt->execute([$local_movie_id]);
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);
        $movieTitle = $movie['title'] ?? 'Movie';
        
        // Determine the status based on which button was clicked
        $status = isset($_POST['add_to_watchlist']) ? 'watchlist' : 'watched';
        
        if (addMovieToUserList($pdo, $userId, $local_movie_id, $status)) {
            $flash = "\"$movieTitle\" was added to your watchlist!";
        } else {
            $flash = "\"$movieTitle\" is already in your list.";
        }
    } else {
        $flash = "Could not fetch movie details from the API.";
    }

    // Redirect to the same search page to prevent form resubmission
    $qParam = isset($_GET['q']) ? ('?q=' . urlencode($_GET['q'])) : '';
    header("Location: movies.php{$qParam}&flash=" . urlencode($flash));
    exit;
}

// Get flash message from redirect
if (isset($_GET['flash'])) {
    $flash = $_GET['flash'];
}

// Handle search query
$q = trim($_GET['q'] ?? '');
$search_results = [];

// Only search if query is not empty
if ($q !== '') {
    $basic_results = omdb_search($q);

    // For each result, get detailed information including plot
    if ($basic_results) {
        foreach ($basic_results as $movie) {
            $detailed = omdb_fetch_by_id($movie['imdbID']);
            if ($detailed) {
                // Check if movie is in user's watchlist
                $inWatchlist = false;
                if ($userId) {
                    $stmt = $pdo->prepare("
                        SELECT r.rating_id 
                        FROM ratings r 
                        JOIN movies m ON m.movie_id = r.movie_id 
                        WHERE r.user_id = ? AND m.api_id = ?
                    ");
                    $stmt->execute([$userId, $detailed['imdbID']]);
                    $inWatchlist = $stmt->fetch() !== false;
                }
                $detailed['inWatchlist'] = $inWatchlist;
                $search_results[] = $detailed;
            }
        }
    }
}

include 'partials/header.php';
?>

<main class="container">
    <div class="search-page-header">
        <div class="search-header-text">
            <h1>Find Your Next Favorite Film</h1>
            <p>Search for any movie and add it to your collection.</p>
        </div>
        <form id="movieSearchForm" class="search-form">
            <input type="text" id="searchInput" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="e.g., The Matrix, Star Wars..." class="form-input search-input-large" required>
            <button type="submit" class="btn btn-primary">
                <span id="searchButtonText">Search</span>
                <span id="searchSpinner" style="display: none;">Searching...</span>
            </button>
        </form>
    </div>

    <!-- Display flash message if any -->
    <?php if ($flash): ?>
        <p class="flash-message" id="flashMessage"><?= htmlspecialchars($flash) ?></p>
    <?php endif; ?>

    <!-- Loading indicator -->
    <div id="loadingIndicator" style="display: none; text-align: center; padding: 2rem;">
        <p>Searching for movies...</p>
    </div>

    <!-- Results container -->
    <div id="searchResults">
        <!-- This appears after a search has been performed -->
        <?php if ($q !== ''): ?>
            <h2 class="results-title">Results for "<?= htmlspecialchars($q) ?>"</h2>
            
            <?php if ($search_results): ?>
                <ul class="movie-results-list">
                    <?php foreach ($search_results as $m): ?>
                        <li class="movie-result-item">
                            <div class="movie-info">
                                <img src="<?= htmlspecialchars($m['Poster'] !== 'N/A' ? $m['Poster'] : 'https://placehold.co/100x150/252528/A9A9A9?text=N/A') ?>" alt="Poster for <?= htmlspecialchars($m['Title']) ?>">
                                <div class="movie-details">
                                    <h3><?= htmlspecialchars($m['Title']) ?> <span>(<?= htmlspecialchars($m['Year']) ?>)</span></h3>
                                    <button class="btn btn-secondary btn-sm view-details-btn" data-imdb-id="<?= htmlspecialchars($m['imdbID']) ?>">View Details</button>
                                </div>
                            </div>
                            
                            <?php if ($userId): // Only show the button if the user is logged in ?>
                                <div class="movie-actions">
                                    <?php if ($m['inWatchlist']): ?>
                                        <button class="btn btn-success" disabled>
                                            In Watchlist
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-primary add-to-watchlist-btn" data-imdb-id="<?= htmlspecialchars($m['imdbID']) ?>" data-title="<?= htmlspecialchars($m['Title']) ?>">
                                            Add to Watchlist
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-results">No movies found matching your query.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</main>

<script>
// Movie search AJAX functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('movieSearchForm');
    const searchInput = document.getElementById('searchInput');
    const searchButtonText = document.getElementById('searchButtonText');
    const searchSpinner = document.getElementById('searchSpinner');
    const resultsContainer = document.getElementById('searchResults');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const isLoggedIn = <?= json_encode($userId !== null) ?>;

    // Use event delegation for watchlist and details buttons
    document.body.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('add-to-watchlist-btn')) {
            handleAddToWatchlist(e);
        }
        if (e.target && e.target.classList.contains('view-details-btn')) {
            handleViewDetails(e);
        }
    });

    searchForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const query = searchInput.value.trim();
        if (!query) return;

        // Show loading state
        searchButtonText.style.display = 'none';
        searchSpinner.style.display = 'inline';
        loadingIndicator.style.display = 'block';
        resultsContainer.innerHTML = '';

        try {
            const response = await fetch(`movies.php?ajax_search=1&q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();

            // Hide loading state
            searchButtonText.style.display = 'inline';
            searchSpinner.style.display = 'none';
            loadingIndicator.style.display = 'none';

            if (data.success && data.results) {
                displayResults(data.results, data.query);
            } else {
                resultsContainer.innerHTML = '<p class="no-results">Search failed. Please try again.</p>';
            }
        } catch (error) {
            console.error('Search error:', error);
            searchButtonText.style.display = 'inline';
            searchSpinner.style.display = 'none';
            loadingIndicator.style.display = 'none';
            resultsContainer.innerHTML = '<p class="no-results">An error occurred. Please try again.</p>';
        }
    });

    function displayResults(results, query) {
        if (results.length === 0) {
            resultsContainer.innerHTML = `
                <h2 class="results-title">Results for "${escapeHtml(query)}"</h2>
                <p class="no-results">No movies found matching your query.</p>
            `;
            return;
        }

        let html = `<h2 class="results-title">Results for "${escapeHtml(query)}"</h2>`;
        html += '<ul class="movie-results-list">';

        results.forEach(movie => {
            const posterUrl = movie.poster || 'https://placehold.co/100x150/252528/A9A9A9?text=N/A';
            
            html += `
                <li class="movie-result-item">
                    <div class="movie-info">
                        <img src="${escapeHtml(posterUrl)}" alt="Poster for ${escapeHtml(movie.title)}">
                        <div class="movie-details">
                            <h3>${escapeHtml(movie.title)} <span>(${escapeHtml(movie.year)})</span></h3>
                            <button class="btn btn-secondary btn-sm view-details-btn" data-imdb-id="${escapeHtml(movie.imdbID)}">View Details</button>
                        </div>
                    </div>
            `;

            if (isLoggedIn) {
                if (movie.inWatchlist) {
                    html += `
                        <div class="movie-actions">
                            <button class="btn btn-success" disabled>
                                In Watchlist
                            </button>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="movie-actions">
                            <button class="btn btn-primary add-to-watchlist-btn" data-imdb-id="${escapeHtml(movie.imdbID)}" data-title="${escapeHtml(movie.title)}">
                                Add to Watchlist
                            </button>
                        </div>
                    `;
                }
            }

            html += '</li>';
        });

        html += '</ul>';
        resultsContainer.innerHTML = html;
    }

    async function handleAddToWatchlist(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const button = e.target;
        const imdbId = button.getAttribute('data-imdb-id');
        const movieTitle = button.getAttribute('data-title');

        if (!imdbId) {
            console.error('No IMDB ID found on button');
            return;
        }

        console.log('Adding to watchlist:', imdbId, movieTitle);

        // Disable button immediately and change text
        button.disabled = true;
        button.style.transition = 'all 0.3s ease';
        button.textContent = 'Adding...';

        try {
            const formData = new FormData();
            formData.append('ajax_add', '1');
            formData.append('imdb_id', imdbId);
            
            const response = await fetch('movies.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const text = await response.text();
            console.log('Raw response:', text);
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response was:', text);
                throw new Error('Invalid JSON response from server');
            }
            
            console.log('Parsed data:', data);

            if (data.success) {
                console.log('Success! Updating button...');
                
                // Show success message
                showFlashMessage(data.message, 'success');
                
                // Change to success state
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');
                button.textContent = '✓ Added!';
                
                console.log('Button text set to: ✓ Added!');
                
                // Change to "In Watchlist" after 1 second
                setTimeout(() => {
                    button.textContent = 'In Watchlist';
                    console.log('Button text changed to: In Watchlist');
                }, 1000);
            } else {
                console.log('Error response:', data.error);
                
                // Show error message
                showFlashMessage(data.error || 'Could not add movie to watchlist.', 'error');
                
                // If already in watchlist, show that state
                if (data.already_added) {
                    button.classList.remove('btn-primary');
                    button.classList.add('btn-success');
                    button.textContent = 'In Watchlist';
                } else {
                    // Re-enable button on other errors
                    button.disabled = false;
                    button.textContent = 'Add to Watchlist';
                }
            }
        } catch (error) {
            console.error('Error adding to watchlist:', error);
            showFlashMessage('An error occurred. Please try again.', 'error');
            button.disabled = false;
            button.textContent = 'Add to Watchlist';
        }
    }

    function showFlashMessage(message, type) {
        // Remove any existing flash messages
        const existingFlash = document.querySelector('.flash-message-ajax');
        if (existingFlash) {
            existingFlash.remove();
        }

        // Create new flash message
        const flashDiv = document.createElement('div');
        flashDiv.className = `flash-message flash-message-ajax alert alert-${type === 'success' ? 'success' : 'error'}`;
        flashDiv.innerHTML = `
            <span>${escapeHtml(message)}</span>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        `;

        // Simply prepend to container - most reliable method
        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(flashDiv, container.firstChild);
        }

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (flashDiv.parentElement) {
                flashDiv.style.opacity = '0';
                setTimeout(() => flashDiv.remove(), 300);
            }
        }, 5000);
    }

    async function handleViewDetails(e) {
        e.preventDefault();
        const button = e.target;
        const imdbId = button.getAttribute('data-imdb-id');
        
        if (!imdbId) return;
        
        // Show modal with loading state
        showModal('Loading...', '<p>Fetching movie details...</p>');
        
        try {
            const response = await fetch(`movies.php?ajax_details=1&imdb_id=${encodeURIComponent(imdbId)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            
            if (data.success && data.details) {
                const d = data.details;
                const posterImg = d.poster ? `<img src="${escapeHtml(d.poster)}" alt="Poster" style="max-width: 200px; margin-bottom: 1rem;">` : '';
                const content = `
                    ${posterImg}
                    <p><strong>Year:</strong> ${escapeHtml(d.year)}</p>
                    <p><strong>Genre:</strong> ${escapeHtml(d.genre)}</p>
                    <p><strong>Director:</strong> ${escapeHtml(d.director)}</p>
                    <p><strong>Actors:</strong> ${escapeHtml(d.actors)}</p>
                    <p><strong>Runtime:</strong> ${escapeHtml(d.runtime)}</p>
                    <p><strong>Plot:</strong> ${escapeHtml(d.plot)}</p>
                `;
                showModal(d.title, content);
            } else {
                showModal('Error', '<p>Could not load movie details.</p>');
            }
        } catch (error) {
            console.error('Error fetching details:', error);
            showModal('Error', '<p>Failed to fetch movie details.</p>');
        }
    }
    
    function showModal(title, content) {
        // Remove existing modal if any
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
                </div>
            `;
            document.body.appendChild(modal);
            
            // Close handler
            modal.querySelector('.modal-close').addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            // Click outside to close
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
        
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalBody').innerHTML = content;
        modal.style.display = 'block';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>

<?php include 'partials/footer.php'; ?>
