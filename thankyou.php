<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$submittedCaseId = $_SESSION['submitted_case_id'] ?? '';
$submittedAnonymously = (bool) ($_SESSION['submitted_case_anonymous'] ?? false);
$accessCode = (string) ($_SESSION['submitted_case_access_code'] ?? '');
unset($_SESSION['submitted_case_id'], $_SESSION['submitted_case_anonymous'], $_SESSION['submitted_case_access_code']);
require_once 'includes/header.php';
?>

<style>
    :root { --cream:#fff8ef; --yellow:#ffd449; --mint:#a7f3d0; --green:#548c2f; --deep-green:#104911; }
    html, body { background: var(--cream) !important; color: var(--deep-green) !important; }
    .thanks-card { max-width: 720px; margin-inline: auto; padding: clamp(2rem,6vw,4rem); text-align: center; background: var(--cream); border: 1px solid rgba(84,140,47,.22); border-radius: 26px; box-shadow: 0 18px 45px rgba(16,73,17,.12); }
    .thanks-icon { width: 88px; height: 88px; display: grid; place-items: center; margin: 0 auto 1.25rem; color: var(--deep-green); background: var(--mint); border-radius: 50%; font-size: 2.5rem; }
    .thanks-card h1 { font-size: clamp(2rem,5vw,3rem); }
    .thanks-card p { font-size: 1.1rem; line-height: 1.7; opacity: .8; }
    .case-id-panel { padding: 1.25rem; background: rgba(167,243,208,.3); border: 2px dashed var(--green); border-radius: 16px; }
    .case-id-label { color: var(--deep-green); font-size: .82rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .case-id-value { display: block; color: var(--deep-green); font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: clamp(1.35rem,4vw,2rem); font-weight: 800; letter-spacing: .04em; overflow-wrap: anywhere; }
    .thanks-link { min-height: 52px; display: inline-flex; align-items: center; justify-content: center; color: var(--cream); background: var(--green); border: 2px solid var(--green); border-radius: 12px; font-weight: 700; text-decoration: none; }
    .thanks-link:hover { color: var(--cream); background: var(--deep-green); border-color: var(--deep-green); }
    .copy-case-btn { color: var(--deep-green); background: var(--yellow); border: 2px solid var(--yellow); }
    .copy-case-btn:hover { color: var(--deep-green); background: var(--cream); border-color: var(--green); }
</style>

<main class="container py-5">
    <section class="thanks-card" aria-labelledby="thanks-title">
        <div class="thanks-icon"><i class="fa-solid fa-circle-check"></i></div>
        <h1 id="thanks-title" class="fw-bold mb-3">Report Submitted</h1>
        <p class="mb-4">Your report has been securely recorded. Save the Case ID below to check its progress.</p>

        <?php if ($submittedCaseId !== ''): ?>
            <div class="case-id-panel mb-4" aria-label="Your case tracking ID">
                <span class="case-id-label">Your Case ID</span>
                <code id="caseId" class="case-id-value my-2"><?= htmlspecialchars($submittedCaseId) ?></code>
                <button id="copyCaseId" type="button" class="btn copy-case-btn fw-bold px-3"><i class="fa-regular fa-copy me-2"></i>Copy Case ID</button>
            </div>
            <p class="small mb-2"><strong>Private access code:</strong> <?= htmlspecialchars($accessCode) ?></p>
            <p class="small mb-4"><strong>Important:</strong> Save both the Case ID and access code. They are required to view messages, submit appeals, or add case updates.</p>
        <?php else: ?>
            <div class="alert alert-warning mb-4">No new Case ID is available in this session. If you already submitted a report, use the Case ID you saved.</div>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?= $base_url ?>track_case.php<?= $submittedCaseId !== '' ? '?case_id=' . urlencode($submittedCaseId) : '' ?>" class="thanks-link px-4"><i class="fas fa-magnifying-glass me-2"></i>Track This Case</a>
            <a href="<?= $base_url ?>case_portal.php" class="thanks-link px-4"><i class="fas fa-lock me-2"></i>Open Private Portal</a>
            <a href="<?= $base_url ?>index.php" class="thanks-link px-4"><i class="fas fa-house me-2"></i>Return Home</a>
        </div>
    </section>
</main>

<?php if ($submittedCaseId !== ''): ?>
<script>
document.getElementById('copyCaseId').addEventListener('click', async function () {
    await navigator.clipboard.writeText(document.getElementById('caseId').textContent.trim());
    this.innerHTML = '<i class="fa-solid fa-check me-2"></i>Copied';
});
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
