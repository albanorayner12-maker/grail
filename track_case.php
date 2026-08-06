<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$caseId = isset($_POST['tracking_id'])
    ? trim((string) $_POST['tracking_id'])
    : trim((string) ($_GET['case_id'] ?? ''));

$caseId = strtoupper($caseId);
$hasSearch = $caseId !== '';
$isValidCaseId = !$hasSearch || (bool) preg_match('/^GRL-[A-Z0-9]+(?:-[A-Z0-9]+)+$/', $caseId);
$previewCase = null;
if ($hasSearch && $isValidCaseId) {
    $caseRecord = null;
    foreach (($_SESSION['dashboard_preview_records'] ?? []) as $row) {
        if (strtoupper((string)($row['tracking_token'] ?? '')) === $caseId) { $caseRecord = $row; break; }
    }
    $hasAdminAction = false;
    if ($caseRecord) {
        foreach (($_SESSION['dashboard_preview_activity'] ?? []) as $activity) {
            if (($activity['tracking_token'] ?? '') === $caseId) { $hasAdminAction = true; break; }
        }
    } elseif (!str_starts_with($caseId, 'GRL-DEMO-')) {
        require_once 'db.php';
        try {
            $stmt = $pdo->prepare("SELECT id, tracking_token, status, created_at FROM grievances WHERE tracking_token=:token LIMIT 1");
            $stmt->execute(['token'=>$caseId]); $caseRecord = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            if ($caseRecord) {
                try { $actionStmt=$pdo->prepare("SELECT (EXISTS(SELECT 1 FROM grievance_status_history WHERE grievance_id=:id1) OR EXISTS(SELECT 1 FROM case_events WHERE grievance_id=:id2 AND actor_type='admin'))"); $actionStmt->execute(['id1'=>$caseRecord['id'],'id2'=>$caseRecord['id']]); $hasAdminAction=(bool)$actionStmt->fetchColumn(); }
                catch (PDOException $e) { $hasAdminAction=!in_array(strtolower((string)$caseRecord['status']),['unreviewed','pending'],true); }
            }
        } catch (PDOException $e) { $caseRecord = null; }
    }
    if (str_starts_with($caseId, 'GRL-DEMO-') && !$caseRecord) { $caseRecord=['tracking_token'=>$caseId,'status'=>'pending','created_at'=>date('Y-m-d H:i:s')]; }
    if ($caseRecord) {
        $status = strtolower(trim((string)($caseRecord['status'] ?? 'pending')));
        $deadlineAt = strtotime('+7 days', strtotime((string)$caseRecord['created_at']));
        $isOverdue = !$hasAdminAction && time() > $deadlineAt;
        $investigating = in_array($status,['under investigation','on hold','resolved'],true);
        $finished = in_array($status,['resolved','dismissed'],true);
        $previewCase = ['id'=>$caseId,'status'=>ucwords($status),'deadline'=>date('F j, Y',$deadlineAt),'overdue'=>$isOverdue,'steps'=>[
            ['title'=>'Report submitted','description'=>'The report has been received and assigned a Case ID.','state'=>'complete','label'=>'Complete'],
            ['title'=>'Initial administrative action','description'=>$hasAdminAction?'An administrator has reviewed the report and recorded an action.':'The administrator has seven days from submission to review and act on this report.','state'=>$hasAdminAction?'complete':'current','label'=>$hasAdminAction?'Complete':'Awaiting action'],
            ['title'=>'Investigation','description'=>'The assigned administrator reviews the report, evidence, and relevant information.','state'=>$finished?'complete':($investigating?'current':'upcoming'),'label'=>$finished?'Complete':($investigating?'In progress':'Not started')],
            ['title'=>'Resolution','description'=>'The final administrative outcome appears here when case handling is complete.','state'=>$finished?'complete':'upcoming','label'=>$finished?'Complete':'Not completed'],
        ]];
    }
}

require_once 'includes/header.php';
?>

<style>
  .tracker-page { max-width: 960px; }

  .tracker-hero {
    position: relative;
    overflow: hidden;
    padding: clamp(2.25rem, 6vw, 4rem);
    background: var(--cream);
    border: 1px solid rgba(84,140,47,.22);
    border-radius: 26px;
    box-shadow: 0 18px 45px rgba(16,73,17,.12);
  }

  .tracker-hero::after {
    content: "";
    position: absolute;
    width: 280px;
    height: 280px;
    right: -105px;
    top: -135px;
    border-radius: 50%;
    background: var(--mint);
    opacity: .48;
  }

  .tracker-hero-content { position: relative; z-index: 1; }

  .tracker-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .45rem .85rem;
    color: var(--deep-green);
    background: var(--mint);
    border-radius: 999px;
    font-size: .88rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
  }

  .tracker-title {
    color: var(--deep-green);
    font-size: clamp(2.15rem, 5vw, 3.4rem);
    letter-spacing: -.035em;
  }

  .tracker-intro {
    max-width: 680px;
    margin-inline: auto;
    color: var(--deep-green);
    font-size: clamp(1.02rem, 2vw, 1.18rem);
    line-height: 1.7;
    opacity: .78;
  }

  .tracker-card {
    padding: clamp(1.35rem, 4vw, 2.25rem);
    background: var(--cream);
    border: 1px solid rgba(84,140,47,.22);
    border-radius: 20px;
    box-shadow: 0 10px 28px rgba(16,73,17,.08);
  }

  .case-input {
    min-height: 54px;
    color: var(--deep-green) !important;
    background: #fffdf8 !important;
    border: 1px solid rgba(84,140,47,.4) !important;
    border-radius: 12px 0 0 12px !important;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 1rem;
    letter-spacing: .03em;
  }

  .case-input:focus {
    border-color: var(--green) !important;
    box-shadow: 0 0 0 .2rem rgba(84,140,47,.15) !important;
  }

  .track-button {
    min-height: 54px;
    color: var(--cream);
    background: var(--green);
    border: 2px solid var(--green);
    border-radius: 0 12px 12px 0;
    font-weight: 700;
  }

  .track-button:hover { color: var(--cream); background: var(--deep-green); border-color: var(--deep-green); }
  .case-summary {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding-bottom: 1.25rem;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid rgba(84,140,47,.2);
  }

  .case-id-label, .status-label { color: var(--deep-green); font-size: .78rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; opacity: .68; }
  .case-id-value { color: var(--deep-green); font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: clamp(1.15rem,3vw,1.5rem); font-weight: 800; }
  .status-badge { display: inline-flex; align-items: center; gap: .45rem; padding: .5rem .8rem; color: var(--deep-green); background: var(--mint); border-radius: 999px; font-weight: 700; }

  .progress-list { list-style: none; padding: 0; margin: 0; }
  .progress-step { position: relative; display: grid; grid-template-columns: 44px 1fr; gap: 1rem; padding-bottom: 1.6rem; }
  .progress-step:last-child { padding-bottom: 0; }
  .progress-step:not(:last-child)::before { content: ""; position: absolute; left: 21px; top: 42px; bottom: 0; width: 2px; background: rgba(84,140,47,.22); }
  .step-icon { position: relative; z-index: 1; width: 44px; height: 44px; display: grid; place-items: center; color: var(--deep-green); background: var(--cream); border: 2px solid rgba(84,140,47,.35); border-radius: 50%; }
  .progress-step.complete .step-icon { color: var(--cream); background: var(--green); border-color: var(--green); }
  .progress-step.current .step-icon { background: var(--yellow); border-color: var(--yellow); }
  .progress-step.complete::before { background: var(--green); }
  .step-title { color: var(--deep-green); font-size: 1.05rem; }
  .step-description { color: var(--deep-green); line-height: 1.6; opacity: .76; }
  .step-label { color: var(--green); font-size: .8rem; font-weight: 700; }
  .verification-callout { padding: 1.15rem; margin-top: 1.75rem; color: var(--cream); background: var(--deep-green); border-radius: 14px; }
  .verification-callout p { color: var(--mint); }
  .verification-button { color: var(--deep-green); background: var(--yellow); border: 2px solid var(--yellow); border-radius: 10px; font-weight: 700; }
  .verification-button:hover { color: var(--deep-green); background: var(--cream); border-color: var(--cream); }
  .deadline-card { padding: 1rem; margin-bottom: 1.4rem; color: var(--deep-green); background: rgba(167,243,208,.25); border: 1px solid rgba(84,140,47,.25); border-radius: 12px; }
  .deadline-card.overdue { color: #842029; background: #f8d7da; border-color: #f1aeb5; }

  .support-link { color: var(--deep-green); font-weight: 700; }

  @media (max-width: 575.98px) {
    .tracker-hero { border-radius: 20px; }
    .tracker-search-group { display: block; }
    .case-input, .track-button { width: 100% !important; border-radius: 12px !important; }
    .track-button { margin-top: .75rem; }
  }
</style>

<div class="container tracker-page py-4 py-md-5">
  <header class="tracker-hero theme-page-hero text-center mb-4">
    <div class="tracker-hero-content theme-page-hero-content">
      <span class="tracker-eyebrow theme-eyebrow mb-3"><i class="fas fa-magnifying-glass"></i> Case tracking</span>
      <h1 class="tracker-title theme-page-title fw-bold mb-3">Track Your Report</h1>
      <p class="tracker-intro theme-page-intro mb-0">Enter the Case ID provided after submission to view the report’s current stage. No personal information is required.</p>
    </div>
  </header>

  <section class="tracker-card mb-4" aria-labelledby="case-search-title">
    <h2 id="case-search-title" class="h4 fw-bold mb-2" style="color: var(--deep-green);">Enter your Case ID</h2>
    <p class="mb-3" style="color: var(--deep-green); opacity: .72;">Case IDs look like <strong>GRL-A1B2-C3D4</strong>. Preview IDs generated by the report form also work.</p>
    <form action="track_case.php" method="POST">
      <label for="tracking_id" class="visually-hidden">Case ID</label>
      <div class="input-group tracker-search-group">
        <input id="tracking_id" name="tracking_id" type="text" class="form-control case-input" value="<?= htmlspecialchars($caseId) ?>" placeholder="GRL-A1B2-C3D4" autocomplete="off" spellcheck="false" required>
        <button type="submit" class="btn track-button px-4"><i class="fas fa-magnifying-glass me-2"></i>Track Case</button>
      </div>
    </form>
  </section>

  <?php if ($hasSearch && !$isValidCaseId): ?>
    <div class="alert alert-warning tracker-card" role="alert">
      <h2 class="h5 fw-bold"><i class="fas fa-circle-exclamation me-2"></i>Check the Case ID</h2>
      <p class="mb-0">Enter the complete reference exactly as shown after submission, including the letters, numbers, and hyphens.</p>
    </div>
  <?php elseif ($hasSearch && $previewCase === null): ?>
    <div class="alert alert-warning tracker-card" role="alert"><h2 class="h5 fw-bold"><i class="fas fa-circle-exclamation me-2"></i>Case not found</h2><p class="mb-0">No report matches that Case ID. Check the reference and try again.</p></div>
  <?php elseif ($previewCase !== null): ?>
    <section class="tracker-card" aria-labelledby="case-progress-title">
      <div class="case-summary">
        <div>
          <span class="case-id-label d-block mb-1">Case ID</span>
          <span class="case-id-value"><?= htmlspecialchars($previewCase['id']) ?></span>
        </div>
        <div class="text-sm-end">
          <span class="status-label d-block mb-1">Current status</span>
          <span class="status-badge"><i class="fas fa-clock"></i><?= htmlspecialchars($previewCase['status']) ?></span>
        </div>
      </div>

      <div class="deadline-card <?= $previewCase['overdue'] ? 'overdue' : '' ?>" role="<?= $previewCase['overdue'] ? 'alert' : 'status' ?>">
        <?php if ($previewCase['overdue']): ?><strong><i class="fas fa-triangle-exclamation me-2"></i>Administrative action is overdue.</strong> The seven-day action deadline passed on <?= htmlspecialchars($previewCase['deadline']) ?> without a recorded administrator action. This delay has been flagged for administrative attention.
        <?php else: ?><strong><i class="fas fa-calendar-check me-2"></i>Administrative action deadline:</strong> <?= htmlspecialchars($previewCase['deadline']) ?>. This notice will update when the administrator records an action.
        <?php endif; ?>
      </div>

      <h2 id="case-progress-title" class="h4 fw-bold mb-4" style="color: var(--deep-green);">Case progress</h2>
      <ol class="progress-list">
        <?php foreach ($previewCase['steps'] as $step): ?>
          <li class="progress-step <?= htmlspecialchars($step['state']) ?>">
            <span class="step-icon" aria-hidden="true">
              <?php if ($step['state'] === 'complete'): ?>
                <i class="fas fa-check"></i>
              <?php elseif ($step['state'] === 'current'): ?>
                <i class="fas fa-hourglass-half"></i>
              <?php else: ?>
                <i class="fas fa-circle"></i>
              <?php endif; ?>
            </span>
            <div>
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                <h3 class="step-title fw-bold mb-0"><?= htmlspecialchars($step['title']) ?></h3>
                <span class="step-label"><?= htmlspecialchars($step['label']) ?></span>
              </div>
              <p class="step-description mb-0"><?= htmlspecialchars($step['description']) ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>

      <div class="verification-callout d-md-flex align-items-center justify-content-between gap-3">
        <div>
          <h3 class="h5 fw-bold mb-1">Has an action already been completed?</h3>
          <p class="mb-3 mb-md-0">Tell GRAIL whether the resolution actually improved your situation.</p>
        </div>
        <a href="resolution_verification.php?case_id=<?= urlencode($previewCase['id']) ?>" class="btn verification-button flex-shrink-0"><i class="fas fa-clipboard-check me-2"></i>Verify Resolution</a>
      </div>
      <div class="text-center mt-3"><a href="case_portal.php" class="btn btn-outline-success"><i class="fas fa-lock me-2"></i>Open private inbox, timeline, appeals and reopening</a></div>
    </section>
  <?php endif; ?>

  <p class="text-center mt-4 mb-0">Lost your Case ID or need help? <a class="support-link" href="support.php">Visit the Support Hub</a>.</p>
</div>

<?php require_once 'includes/footer.php'; ?>
