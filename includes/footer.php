<?php // includes/footer.php ?>
</main>

<style>
    /* Footer uses the same green as the navbar */
    .site-footer {
        margin-top: 3rem;
        color: #fff8ef !important;
        background: linear-gradient(135deg, #104911, #548c2f) !important;
        border-top: 1px solid rgba(255, 248, 239, .18);
        box-shadow: 0 -10px 20px rgba(31, 107, 62, .12);
    }
    .site-footer p, .site-footer a { color: #fff8ef !important; }
    .site-footer a:hover { color: #ffd449 !important; }
</style>

<footer class="site-footer">
    <!-- Changed d-flex and alignment classes to text-center -->
    <div class="container py-4 text-center">
        <p class="mb-0">&copy; <?= date('Y'); ?> GRAIL SYSTEM. All Rights Reserved.</p>
        <nav class="footer-links" aria-label="Footer navigation">
            <!-- Your centered links can go here later -->
        </nav>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
