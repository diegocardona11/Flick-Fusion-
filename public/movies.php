<?php
require __DIR__ . '/../backend/api/omdb.php';
require __DIR__ . '/_db.php';
require_once __DIR__ . '/../backend/controllers/movies.php';
include 'partials/header.php';

// make a single, global connection for this page
$pdo = db();               // ✅ <-- this line fixes "undefined $pdo"

$userId = 1;               // temp test user
$flash = '';

// Handle Add to My List button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['imdbID'])) {
    $imdbID = trim($_POST['imdbID']);

    // Step 1: ensure movie exists locally
    $movieId = addMovieToLocalDB($pdo, $imdbID);

    // Step 2: link to user (default rating = 5)
    if ($movieId) {
        addMovieToUserList($pdo, $userId, $movieId, 5);
        $flash = "✅ Added to your list!";
    } else {
        $flash = "⚠️ Couldn't fetch movie details.";
    }
}


  // Check if movie already exists
  $stmt = $pdo->prepare("SELECT movie_id FROM movies WHERE api_id = ?");
  $stmt->execute([$imdbID]);
  $movie = $stmt->fetch();

  if (!$movie) {
    // Fetch movie details from OMDb
    $url = "https://www.omdbapi.com/?apikey=" . omdb_api_key() . "&i=" . urlencode($imdbID);
    $json = file_get_contents($url);
    $data = json_decode($json, true);

    // Insert movie into database
    $stmt = $pdo->prepare("
      INSERT INTO movies (api_id, title, year, genre, description, poster_url)
      VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
      $data['imdbID'],
      $data['Title'] ?? '',
      is_numeric($data['Year'] ?? '') ? (int)$data['Year'] : null,
      $data['Genre'] ?? '',
      $data['Plot'] ?? '',
      ($data['Poster'] ?? '') !== 'N/A' ? $data['Poster'] : null
    ]);
    $movieId = $pdo->lastInsertId();
  } else {
    $movieId = $movie['movie_id'];
  }

  // Link to user (default score = 5)
  $stmt = $pdo->prepare("
    INSERT INTO ratings (user_id, movie_id, score_10)
    VALUES (?, ?, 5)
    ON DUPLICATE KEY UPDATE score_10 = score_10
  ");
  $stmt->execute([$userId, $movieId]);
  $flash = "✅ Added to your list!";


// Handle search
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = $q ? omdb_search($q) : [];
?>
<main>
  <h1>Search Movies</h1>

  <?php if ($flash): ?>
    <p style="color: green;"><?= htmlspecialchars($flash) ?></p>
  <?php endif; ?>

  <form method="get" action="/movies.php">
    <input type="text" name="q" placeholder="Search for a movie..." value="<?= htmlspecialchars($q) ?>">
    <button type="submit">Search</button>
  </form>

  <?php if ($q): ?>
    <h2>Results for "<?= htmlspecialchars($q) ?>"</h2>
    <?php if ($results): ?>
      <?php foreach ($results as $movie): ?>
        <div style="border:1px solid #ddd; margin:10px; padding:10px; border-radius:8px;">
          <strong><?= htmlspecialchars($movie['Title']) ?></strong> (<?= htmlspecialchars($movie['Year']) ?>)<br>
          <?php if (!empty($movie['Poster']) && $movie['Poster'] !== 'N/A'): ?>
            <img src="<?= htmlspecialchars($movie['Poster']) ?>" width="100"><br>
          <?php endif; ?>
          <form method="post">
            <input type="hidden" name="imdbID" value="<?= htmlspecialchars($movie['imdbID']) ?>">
            <button type="submit">Add to My List</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No results found.</p>
    <?php endif; ?>
  <?php endif; ?>
</main>
<?php include 'partials/footer.php'; ?>
