<?php
require __DIR__ . '/../backend/api/omdb.php';

// get query from ?q=
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = $q !== '' ? omdb_search($q) : [];
?>

<?php include 'partials/header.php'; ?>

  <div class="container">
    <h2>Search Movies</h2>


    <form method="get" action="movies.php" class="form-box">
      <input type="text" name="q" value="<?=htmlspecialchars($q)?>" placeholder="Search title..." required style="width:70%;padding:10px;">
      <button type="submit" class="btn">Search</button>
    </form>


    <?php if ($q !== ''): ?>
      <h3>Results for "<?=htmlspecialchars($q)?>"</h3>
      <?php if ($results): ?>
        <ul>
          <?php foreach ($results as $m): ?>
            <li style="margin-bottom:18px;">
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
  </div>

<?php include 'partials/footer.php'; ?>
