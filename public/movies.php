<?php
require __DIR__ . '/../backend/api/omdb.php';

// get query from ?q=
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = $q !== '' ? omdb_search($q) : [];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Movies | Flick Fusion</title>
</head>
<body>
  <h1>Search Movies</h1>

  <form method="get" action="/movies.php">
    <input type="text" name="q" value="<?=htmlspecialchars($q)?>" placeholder="Search title..." required>
    <button type="submit">Search</button>
  </form>

  <?php if ($q !== ''): ?>
    <h2>Results for "<?=htmlspecialchars($q)?>"</h2>
    <?php if ($results): ?>
      <ul>
        <?php foreach ($results as $m): ?>
          <li>
            <strong><?=htmlspecialchars($m['Title'])?></strong> (<?=htmlspecialchars($m['Year'])?>)
            <?php if ($m['Poster'] !== "N/A"): ?>
              <br><img src="<?=htmlspecialchars($m['Poster'])?>" width="100">
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No results found.</p>
    <?php endif; ?>
  <?php endif; ?>
</body>
</html>
