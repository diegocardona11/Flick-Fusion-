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
<!-- Confirm Unfriend Modal -->
<div id="confirmModal" class="custom-confirm" style="display:none;">
    <div class="confirm-content" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
        <button id="confirmClose" class="modal-close" aria-label="Close">×</button>
        <div class="confirm-header" style="display:flex; gap:1rem; align-items:center;">
            <div>
                <div id="confirmMessage" class="confirm-message" style="color:var(--text-secondary); font-size:0.95rem; margin-top:4px;">Are you sure you want to remove this friend?</div>
            </div>
        </div>
        <div class="confirm-actions" style="margin-top:1rem;">
            <button id="confirmCancel" class="btn btn-secondary">Cancel</button>
            <button id="confirmOK" class="btn btn-danger">Unfriend</button>
        </div>
    </div>
</div>

<script src="js/app.js?v=<?= filemtime(__DIR__ . '/../js/app.js') ?>"></script>
</body>
</html>