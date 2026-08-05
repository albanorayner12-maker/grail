<?php
// includes/footer.php
?>
    </main>

   <footer class="site-footer">
        <div class="container py-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <p class="mb-0">&copy; <?= date('Y'); ?> GRAIL SYSTEM. All Rights Reserved.</p>
            <nav class="footer-links" aria-label="Footer navigation">
                <a href="<?= $base_url ?>index.php">Home</a>
                <a href="<?= $base_url ?>submit_report.php">File Report</a>
                <a href="<?= $base_url ?>track_case.php">Track Case</a>
                <a href="<?= $base_url ?>support.php">Support</a>
                <a href="<?= $base_url ?>login.php">Admin</a>
            </nav>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
