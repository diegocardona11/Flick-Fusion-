<?php
// footer.php
// Shared footer for all pages
?>
<footer>
    <p>&copy; <?php echo date("Y"); ?> Flick Fusion. All rights reserved.</p>
</footer>
<script src="/js/app.js?v=<?= filemtime(__DIR__ . '/../js/app.js') ?>"></script>
</body>
</html>