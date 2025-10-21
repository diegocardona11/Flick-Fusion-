<?php
// footer.php
// Shared footer for all pages
?>
<footer class="site-footer">
    <div class="footer-content">
        <p class="footer-text">
            &copy; <?php echo date("Y"); ?> <span class="footer-brand">Flick<strong>Fusion</strong></span> • Your personal movie companion
        </p>
    </div>
</footer>
<script src="/js/app.js?v=<?= filemtime(__DIR__ . '/../js/app.js') ?>"></script>
</body>
</html>