<?php
// Define entry point for backend includes
if (!defined('FLICK_FUSION_ENTRY_POINT')) {
  define('FLICK_FUSION_ENTRY_POINT', true);
}

require __DIR__ . '/../backend/api/omdb.php';
require __DIR__ . '/_db.php';
require_once __DIR__ . '/../backend/controllers/movies.php';

include 'partials/header.php';

// connect to DB
$pdo = db();

$userId = 1;  // temp user
$flash = '';

// Handle Add to My List button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['imdbID'])) {
  $imdbID = trim($_POST['imdbID']);

  // Add to DB + user list
  $movieId = addMovieToLocalDB($pdo, $imdbID);
  if ($movieId) {
    addMovieToUserList($pdo, $userId, $movieId, 5);
    $flash = "✅ Added to your list!";
  } else {
    $flash = "⚠️ Couldn't fetch movie details.";
  }
}

// Handle search
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = $q !== '' ? omdb_search($q) : [];
?>

<div class="container">
  <h2>Search Movies</h2>

  <?php if ($flash): ?>
    <p style="color: green;"><?= htmlspecialchars($flash) ?></p>
  <?php endif; ?>

  <form method="get" action="movies.php" class="form-box">
    <input type="text"
           name="q"
           value="<?= htmlspecialchars($q) ?>"
           placeholder="Search title..."
           required
           style="width:70%;padding:10px;">
    <button type="submit" class="btn">Search</button>
  </form>

  <?php if ($q !== ''): ?>
    <h3>Results for "<?= htmlspecialchars($q) ?>"</h3>
    <?php if ($results): ?>
      <ul style="list-style:none;padding-left:0;">
        <?php foreach ($results as $m): ?>
          <li style="margin-bottom:18px;border:1px solid #ddd;border-radius:8px;padding:12px;">
            <strong><?= htmlspecialchars($m['Title']) ?></strong>
            (<?= htmlspecialchars($m['Year']) ?>)
            <?php if (!empty($m['Poster']) && $m['Poster'] !== 'N/A'): ?>
              <br><img src="<?= htmlspecialchars($m['Poster']) ?>" width="100" alt="Poster">
            <?php endif; ?>

            <!-- Add to My List -->
            <form method="post" action="movies.php" style="margin-top:8px;">
              <input type="hidden" name="imdbID" value="<?= htmlspecialchars($m['imdbID']) ?>">
              <button type="submit" class="btn">Add to My List</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No results found.</p>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>
