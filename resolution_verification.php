<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['resolution_csrf_token'])) {
    $_SESSION['resolution_csrf_token'] = bin2hex(random_bytes(32));
}

$caseId = strtoupper(trim((string) ($_POST['case_id'] ?? $_GET['case_id'] ?? '')));
$outcome = trim((string) ($_POST['outcome'] ?? ''));
$comment = trim((string) ($_POST['comment'] ?? ''));
$accessCode = trim((string) ($_POST['access_code'] ?? ''));
$error = '';
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');
    $allowedOutcomes = ['confirmed_improvement', 'unresolved'];

    if (!hash_equals($_SESSION['resolution_csrf_token'], $csrfToken)) {
        $error = 'Your form session expired. Please refresh and try again.';
    } elseif (!in_array($outcome, $allowedOutcomes, true)) {
        $error = 'Please tell us whether the action improved the situation.';
    } elseif (mb_strlen($comment) > 1000) {
        $error = 'Your optional comment must be 1,000 characters or fewer.';
    } else {
        require_once 'db.php'; require_once 'includes/case_service.php';
        $case=case_find_by_credentials($pdo,$caseId,$accessCode);
        if (!$case) { $error='The Case ID or private access code is incorrect.'; }
        else {
            $pdo->prepare('INSERT INTO resolution_feedback (grievance_id,outcome,comment) VALUES (:id,:outcome,:comment)')->execute(['id'=>$case['id'],'outcome'=>$outcome,'comment'=>$comment]);
            case_add_event($pdo,(int)$case['id'],'resolution_feedback','Reporter verified the outcome',str_replace('_',' ',$outcome).($comment!==''?': '.$comment:''),'reporter',null,'Reporter',true);
            $_SESSION['resolution_csrf_token'] = bin2hex(random_bytes(32)); $submitted = true;
        }
    }
}

require_once 'includes/header.php';
?>

<style>
  .verification-page { max-width: 900px; }
  .verification-hero { position: relative; overflow: hidden; padding: clamp(2.25rem,6vw,4rem); background: var(--cream); border: 1px solid rgba(84,140,47,.22); border-radius: 26px; box-shadow: 0 18px 45px rgba(16,73,17,.12); }
  .verification-hero::after { content: ""; position: absolute; width: 280px; height: 280px; right: -105px; top: -135px; border-radius: 50%; background: var(--mint); opacity: .48; }
  .verification-hero-content { position: relative; z-index: 1; }
  .verification-eyebrow { display: inline-flex; align-items: center; gap: .5rem; padding: .45rem .85rem; color: var(--deep-green); background: var(--mint); border-radius: 999px; font-size: .88rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
  .verification-title { color: var(--deep-green); font-size: clamp(2.1rem,5vw,3.35rem); letter-spacing: -.035em; }
  .verification-intro { max-width: 690px; margin-inline: auto; color: var(--deep-green); font-size: clamp(1.02rem,2vw,1.16rem); line-height: 1.7; opacity: .78; }
  .verification-card { padding: clamp(1.4rem,4vw,2.5rem); background: var(--cream); border: 1px solid rgba(84,140,47,.22); border-radius: 20px; box-shadow: 0 10px 28px rgba(16,73,17,.08); }
  .verification-card .form-label { color: var(--deep-green); font-weight: 700; }
  .verification-card .form-control { color: var(--deep-green) !important; background: #fffdf8 !important; border: 1px solid rgba(84,140,47,.38) !important; border-radius: 11px !important; }
  .verification-card .form-control:focus { border-color: var(--green) !important; box-shadow: 0 0 0 .2rem rgba(84,140,47,.15) !important; }
  .outcome-option { display: block; height: 100%; padding: 1.15rem; cursor: pointer; background: #fffdf8; border: 2px solid rgba(84,140,47,.22); border-radius: 14px; transition: border-color .2s ease, background .2s ease, transform .2s ease; }
  .outcome-option:hover { transform: translateY(-2px); border-color: var(--green); }
  .outcome-option:has(input:checked) { background: rgba(167,243,208,.3); border-color: var(--green); }
  .outcome-option input { accent-color: var(--green); }
  .outcome-title { color: var(--deep-green); font-weight: 800; }
  .outcome-copy { color: var(--deep-green); line-height: 1.55; opacity: .75; }
  .privacy-message { padding: 1rem; color: var(--deep-green); background: rgba(255,212,73,.2); border-left: 4px solid var(--yellow); border-radius: 8px; }
  .verification-submit { min-height: 52px; color: var(--cream); background: var(--green); border: 2px solid var(--green); border-radius: 12px; font-weight: 700; }
  .verification-submit:hover { color: var(--cream); background: var(--deep-green); border-color: var(--deep-green); }
  .success-icon { width: 76px; height: 76px; display: grid; place-items: center; margin: 0 auto 1rem; color: var(--deep-green); background: var(--mint); border-radius: 50%; font-size: 2rem; }
  .verification-link { color: var(--deep-green); font-weight: 700; }
  @media (max-width: 575.98px) { .verification-hero { border-radius: 20px; } }
</style>

<div class="container verification-page py-4 py-md-5">
  <header class="verification-hero theme-page-hero text-center mb-4">
    <div class="verification-hero-content theme-page-hero-content">
      <span class="verification-eyebrow theme-eyebrow mb-3"><i class="fas fa-clipboard-check"></i> Resolution verification</span>
      <h1 class="verification-title theme-page-title fw-bold mb-3">Did the Resolution Help?</h1>
      <p class="verification-intro theme-page-intro mb-0">An administrator closing a case does not automatically mean the concern improved. Privately tell GRAIL what happened after the action was completed.</p>
    </div>
  </header>

  <?php if ($submitted): ?>
    <section class="verification-card text-center" aria-labelledby="verification-success-title">
      <div class="success-icon"><i class="fas fa-check"></i></div>
      <h2 id="verification-success-title" class="h3 fw-bold mb-3" style="color: var(--deep-green);">Your response was recorded</h2>
      <p class="mb-4" style="color: var(--deep-green); opacity: .78;">Case <strong><?= htmlspecialchars($caseId) ?></strong> remains administratively closed, while your separate reporter outcome is recorded as <strong><?= $outcome === 'confirmed_improvement' ? 'improvement confirmed' : 'unresolved' ?></strong>.</p>
      <div class="d-flex flex-wrap justify-content-center gap-3">
        <a href="track_case.php?case_id=<?= urlencode($caseId) ?>" class="btn verification-submit px-4"><i class="fas fa-arrow-left me-2"></i>Return to Case</a>
        <a href="analytics.php" class="btn verification-submit px-4"><i class="fas fa-chart-column me-2"></i>View Public Outcomes</a>
      </div>
    </section>
  <?php else: ?>
    <?php if ($error !== ''): ?>
      <div class="alert alert-danger" role="alert"><i class="fas fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="resolution_verification.php" class="verification-card">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['resolution_csrf_token']) ?>">

      <div class="mb-4">
        <label for="case_id" class="form-label">Case ID</label>
        <input id="case_id" name="case_id" type="text" class="form-control form-control-lg" value="<?= htmlspecialchars($caseId) ?>" placeholder="GRL-A1B2-C3D4" autocomplete="off" required>
        <div class="form-text">Your Case ID connects this response without asking you to disclose your identity.</div>
      </div>

      <div class="mb-4">
        <label for="access_code" class="form-label">Private access code</label>
        <input id="access_code" name="access_code" type="password" inputmode="numeric" class="form-control form-control-lg" autocomplete="off" required>
        <div class="form-text">This prevents another person from submitting feedback using only your Case ID.</div>
      </div>

      <fieldset class="mb-4">
        <legend class="h5 fw-bold mb-3" style="color: var(--deep-green);">What happened after the action was completed?</legend>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="outcome-option">
              <span class="d-flex gap-3 align-items-start">
                <input class="form-check-input mt-1" type="radio" name="outcome" value="confirmed_improvement" <?= $outcome === 'confirmed_improvement' ? 'checked' : '' ?> required>
                <span><span class="outcome-title d-block mb-1"><i class="fas fa-thumbs-up me-2"></i>The situation improved</span><span class="outcome-copy d-block small">The completed action addressed the concern or made conditions meaningfully better.</span></span>
              </span>
            </label>
          </div>
          <div class="col-md-6">
            <label class="outcome-option">
              <span class="d-flex gap-3 align-items-start">
                <input class="form-check-input mt-1" type="radio" name="outcome" value="unresolved" <?= $outcome === 'unresolved' ? 'checked' : '' ?> required>
                <span><span class="outcome-title d-block mb-1"><i class="fas fa-triangle-exclamation me-2"></i>The concern is unresolved</span><span class="outcome-copy d-block small">The action did not solve the concern, or the problem has continued or returned.</span></span>
              </span>
            </label>
          </div>
        </div>
      </fieldset>

      <div class="mb-4">
        <label for="comment" class="form-label">Private explanation <span class="fw-normal">(optional)</span></label>
        <textarea id="comment" name="comment" class="form-control" rows="5" maxlength="1000" placeholder="Briefly explain what improved or what remains unresolved."><?= htmlspecialchars($comment) ?></textarea>
        <div class="form-text">This response is intended for authorized case reviewers and aggregated outcome reporting.</div>
      </div>

      <div class="privacy-message mb-4"><i class="fas fa-shield-halved me-2"></i>Your feedback does not change the administrative record. It creates a separate reporter-confirmed outcome so institutional statistics do not treat every closed case as successful.</div>

      <button type="submit" class="btn verification-submit w-100"><i class="fas fa-paper-plane me-2"></i>Submit Resolution Feedback</button>
    </form>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
