<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = $_SESSION['report_form_error'] ?? '';
unset($_SESSION['report_form_error']);
$old = $_SESSION['report_form_old'] ?? [];
unset($_SESSION['report_form_old']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $error = 'Your form session expired. Please refresh the page and try again.';
    } else {
        $isAnonymous = isset($_POST['is_anonymous']);
        $reporterName = $isAnonymous ? 'Anonymous' : trim((string) ($_POST['reporter_name'] ?? ''));
        $reporterEmail = $isAnonymous ? null : trim((string) ($_POST['reporter_email'] ?? ''));
        $reporterPhone = $isAnonymous ? null : trim((string) ($_POST['reporter_phone'] ?? ''));
        $userType = $isAnonymous ? 'Anonymous' : trim((string) ($_POST['user_type'] ?? ''));
        $reporterId = $isAnonymous ? null : trim((string) ($_POST['reporter_id'] ?? ''));

        $category = trim((string) ($_POST['category'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $incidentDate = trim((string) ($_POST['incident_date'] ?? '')) ?: null;
        $incidentLocation = trim((string) ($_POST['incident_location'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $riskDanger = isset($_POST['risk_danger']);
        $riskOngoing = isset($_POST['risk_ongoing']);
        $riskRepeated = isset($_POST['risk_repeated']);
        $riskRetaliation = isset($_POST['risk_retaliation']);
        $riskMultiplePeople = isset($_POST['risk_multiple_people']);
        $riskUrgent = isset($_POST['risk_urgent']);
        $riskScore = ($riskDanger ? 4 : 0) + ($riskOngoing ? 2 : 0) + ($riskRepeated ? 1 : 0) + ($riskRetaliation ? 2 : 0) + ($riskMultiplePeople ? 1 : 0) + ($riskUrgent ? 3 : 0);
        $suggestedPriority = ($riskDanger || $riskUrgent) ? 'critical' : ($riskScore >= 4 ? 'high' : ($riskScore >= 2 ? 'medium' : 'low'));

        $allowedCategories = ['harassment', 'discrimination', 'safety', 'academic', 'administrative', 'financial', 'technology', 'other'];
        $allowedUserTypes = ['Student', 'Instructor', 'Staff', 'Other'];

        if (!$isAnonymous && ($reporterName === '' || $reporterEmail === '' || $userType === '')) {
            $error = 'Please provide your name, email address, and community role, or choose anonymous reporting.';
        } elseif (!$isAnonymous && !filter_var($reporterEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (!$isAnonymous && !in_array($userType, $allowedUserTypes, true)) {
            $error = 'Please select a valid community role.';
        } elseif (!in_array($category, $allowedCategories, true)) {
            $error = 'Please select a valid concern category.';
        } elseif ($subject === '' || mb_strlen($subject) > 200) {
            $error = 'Please provide a short subject of no more than 200 characters.';
        } elseif (mb_strlen($description) < 20) {
            $error = 'Please describe what happened using at least 20 characters.';
        } elseif ($incidentDate !== null && $incidentDate > date('Y-m-d')) {
            $error = 'The incident date cannot be in the future.';
        }

        $evidencePath = null;
        $evidenceMetadata = null;

        if ($error === '') {
            try {
                if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($_FILES['evidence']['error'] !== UPLOAD_ERR_OK) {
                        throw new RuntimeException('The supporting file could not be uploaded.');
                    }

                    if ((int) $_FILES['evidence']['size'] > 5 * 1024 * 1024) {
                        throw new RuntimeException('The supporting file must be 5 MB or smaller.');
                    }

                    $allowedMimeTypes = [
                        'application/pdf' => 'pdf',
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'application/msword' => 'doc',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                    ];
                    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $fileInfo->file($_FILES['evidence']['tmp_name']);

                    if (!isset($allowedMimeTypes[$mimeType])) {
                        throw new RuntimeException('Use a PDF, JPG, PNG, DOC, or DOCX supporting file.');
                    }

                    $uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'evidence';
                    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                        throw new RuntimeException('The evidence storage folder is unavailable.');
                    }

                    $storedFileName = 'evidence_' . bin2hex(random_bytes(16)) . '.' . $allowedMimeTypes[$mimeType];
                    $absoluteTarget = $uploadDirectory . DIRECTORY_SEPARATOR . $storedFileName;

                    if (!move_uploaded_file($_FILES['evidence']['tmp_name'], $absoluteTarget)) {
                        throw new RuntimeException('The supporting file could not be saved.');
                    }

                    $evidencePath = $storedFileName;
                    $evidenceMetadata = [
                        'stored_name'=>$storedFileName,
                        'original_name'=>basename((string)$_FILES['evidence']['name']),
                        'mime_type'=>$mimeType,
                        'file_size'=>(int)$_FILES['evidence']['size'],
                        'sha256'=>hash_file('sha256',$absoluteTarget),
                    ];
                }

                require_once 'db.php';
                require_once 'includes/case_service.php';
                do {
                    $trackingToken = 'GRL-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(2)));
                    $tokenCheck = $pdo->prepare('SELECT COUNT(*) FROM grievances WHERE tracking_token = :token');
                    $tokenCheck->execute(['token' => $trackingToken]);
                } while ((int) $tokenCheck->fetchColumn() > 0);

                $accessCode = (string) random_int(100000, 999999);
                $slaHours = case_sla_hours($suggestedPriority);
                $insert = $pdo->prepare(
                    'INSERT INTO grievances
                    (tracking_token, name, email, phone, user_type, id_number, is_anonymous, category, subject, incident_date, location, description, evidence, risk_danger, risk_ongoing, risk_repeated, risk_retaliation, risk_multiple_people, risk_urgent, risk_score, priority, status, access_code_hash, sla_hours, due_at, created_at)
                    VALUES
                    (:tracking_token, :name, :email, :phone, :user_type, :id_number, :is_anonymous, :category, :subject, :incident_date, :location, :description, :evidence, :risk_danger, :risk_ongoing, :risk_repeated, :risk_retaliation, :risk_multiple_people, :risk_urgent, :risk_score, :priority, :status, :access_code_hash, :sla_hours, DATE_ADD(NOW(),INTERVAL :sla_hours_due HOUR), NOW())'
                );
                $insert->execute([
                    'tracking_token' => $trackingToken,
                    'name' => $reporterName,
                    'email' => $reporterEmail,
                    'phone' => $reporterPhone,
                    'user_type' => $userType,
                    'id_number' => $reporterId,
                    'is_anonymous' => $isAnonymous ? 1 : 0,
                    'category' => $category,
                    'subject' => $subject,
                    'incident_date' => $incidentDate,
                    'location' => $incidentLocation,
                    'description' => $description,
                    'evidence' => $evidencePath,
                    'risk_danger' => $riskDanger ? 1 : 0,
                    'risk_ongoing' => $riskOngoing ? 1 : 0,
                    'risk_repeated' => $riskRepeated ? 1 : 0,
                    'risk_retaliation' => $riskRetaliation ? 1 : 0,
                    'risk_multiple_people' => $riskMultiplePeople ? 1 : 0,
                    'risk_urgent' => $riskUrgent ? 1 : 0,
                    'risk_score' => $riskScore,
                    'priority' => $suggestedPriority,
                    'status' => 'unreviewed',
                    'access_code_hash' => password_hash($accessCode, PASSWORD_DEFAULT),
                    'sla_hours' => $slaHours,
                    'sla_hours_due' => $slaHours,
                ]);

                $caseDatabaseId=(int)$pdo->lastInsertId();
                case_add_event($pdo,$caseDatabaseId,'submitted','Report submitted','The report was received and assigned a private Case ID.','reporter',null,$isAnonymous?'Anonymous reporter':$reporterName,true);
                case_queue_notification($pdo,$caseDatabaseId,'admin',null,'New '.$suggestedPriority.' priority grievance','Case '.$trackingToken.' requires administrative triage.');
                if ($evidenceMetadata) {
                    $evidenceInsert=$pdo->prepare('INSERT INTO evidence_files (grievance_id,stored_name,original_name,mime_type,file_size,sha256,scan_status) VALUES (:case_id,:stored_name,:original_name,:mime_type,:file_size,:sha256,:scan_status)');
                    $evidenceInsert->execute($evidenceMetadata+['case_id'=>$caseDatabaseId,'scan_status'=>'pending']);
                }

                $_SESSION['submitted_case_id'] = $trackingToken;
                $_SESSION['submitted_case_anonymous'] = $isAnonymous;
                $_SESSION['submitted_case_access_code'] = $accessCode;
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                unset($_SESSION['report_form_old']);
                header('Location: thankyou.php');
                exit();
            } catch (Throwable $exception) {
                if ($evidencePath !== null) {
                    $uploadedFile = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'evidence' . DIRECTORY_SEPARATOR . basename($evidencePath);
                    if (is_file($uploadedFile)) {
                        unlink($uploadedFile);
                    }
                }
                $error = $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'The report could not be saved. Please try again or contact support.';            }
        }
    }

    if ($error !== '') {
        $_SESSION['report_form_old'] = $old;
        $_SESSION['report_form_error'] = $error;
        header('Location: submit_report.php');
        exit();
    }
}

function old_value(array $old, string $key): string
{
    return htmlspecialchars((string) ($old[$key] ?? ''), ENT_QUOTES, 'UTF-8');
}

require_once 'includes/header.php';
?>

<style>
  .report-page { max-width: 1040px; }
  .report-hero { position: relative; overflow: hidden; padding: clamp(2rem,5vw,3.5rem); background: var(--cream); border: 1px solid rgba(84,140,47,.22); border-radius: 26px; box-shadow: 0 18px 45px rgba(16,73,17,.12); }
  .report-hero::after { content: ""; position: absolute; width: 260px; height: 260px; right: -95px; top: -125px; border-radius: 50%; background: var(--mint); opacity: .48; }
  .report-hero-content { position: relative; z-index: 1; }
  .report-eyebrow { display: inline-flex; align-items: center; gap: .5rem; padding: .45rem .85rem; border-radius: 999px; color: var(--deep-green); background: var(--mint); font-size: .88rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
  .report-title { color: var(--deep-green); font-size: clamp(2.1rem,5vw,3.35rem); letter-spacing: -.035em; }
  .report-intro { max-width: 720px; margin-inline: auto; color: var(--deep-green); font-size: clamp(1rem,2vw,1.15rem); line-height: 1.7; opacity: .78; }
  .report-card { padding: clamp(1.35rem,4vw,2.5rem); background: var(--cream); border: 1px solid rgba(84,140,47,.22); border-radius: 20px; box-shadow: 0 10px 28px rgba(16,73,17,.08); }
  .section-heading { display: flex; align-items: center; gap: .8rem; padding-bottom: .8rem; margin-bottom: 1.25rem; color: var(--deep-green); border-bottom: 1px solid rgba(84,140,47,.2); }
  .section-heading i { width: 42px; height: 42px; display: grid; place-items: center; flex: 0 0 42px; background: var(--mint); border-radius: 50%; }
  .form-label { color: var(--deep-green); font-weight: 700; }
  .required::after { content: " *"; color: #b42318; }
  .form-control, .form-select { min-height: 48px; color: var(--deep-green) !important; background: #fffdf8 !important; border: 1px solid rgba(84,140,47,.35) !important; border-radius: 11px !important; }
  textarea.form-control { min-height: 150px; }
  .form-control:focus, .form-select:focus { border-color: var(--green) !important; box-shadow: 0 0 0 .2rem rgba(84,140,47,.15) !important; }
  .anonymous-option { padding: 1rem; background: rgba(167,243,208,.28); border: 1px solid rgba(84,140,47,.22); border-radius: 13px; }
  .identity-fields { transition: opacity .2s ease; }
  .identity-fields.is-disabled { opacity: .45; }
  .privacy-note { padding: 1rem; color: var(--deep-green); background: rgba(255,212,73,.22); border-left: 4px solid var(--yellow); border-radius: 8px; }
  .submit-report-btn { min-height: 54px; color: var(--cream); background: var(--green); border: 2px solid var(--green); border-radius: 12px; font-weight: 700; }
  .submit-report-btn:hover { color: var(--cream); background: var(--deep-green); border-color: var(--deep-green); }
  .risk-option { height: 100%; padding: 1rem; background: rgba(167,243,208,.14); border: 1px solid rgba(84,140,47,.22); border-radius: 12px; }
  .risk-option .form-check-input { margin-top: .3rem; }
  .risk-option .form-check-label { color: var(--deep-green); font-weight: 700; }
  .concern-guide { padding: 1.1rem; background: rgba(167,243,208,.18); border: 1px solid rgba(84,140,47,.24); border-radius: 14px; }
  .concern-guide summary { display: flex; align-items: center; gap: .65rem; color: var(--deep-green); font-weight: 800; cursor: pointer; list-style: none; }
  .concern-guide summary::-webkit-details-marker { display: none; }
  .concern-guide summary::after { content: "+"; margin-left: auto; font-size: 1.35rem; line-height: 1; }
  .concern-guide[open] summary::after { content: "−"; }
  .guide-item { height: 100%; padding: .85rem; background: #fffdf8; border-left: 3px solid var(--green); border-radius: 8px; }
  .guide-item strong { display: block; color: var(--deep-green); margin-bottom: .15rem; }
  .guide-item span { display: block; color: var(--deep-green); font-size: .88rem; line-height: 1.45; opacity: .75; }
  .category-help { padding: .8rem 1rem; color: var(--deep-green); background: rgba(255,212,73,.22); border-radius: 9px; font-size: .9rem; }
  @media (max-width: 575.98px) { .report-hero { border-radius: 20px; } }
</style>

<div class="container report-page py-4 py-md-5">
  <header class="report-hero text-center mb-4">
    <div class="report-hero-content">
      <span class="report-eyebrow mb-3"><i class="fas fa-shield-halved"></i> Secure submission</span>
      <h1 class="report-title fw-bold mb-3">Report a Concern</h1>
      <p class="report-intro mb-0">Tell us what happened using the essential details below. After submission, you will receive a private Case ID for tracking progress.</p>
    </div>
  </header>

  <?php if ($error !== ''): ?>
    <div class="alert alert-danger" role="alert"><i class="fas fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form action="submit_report.php" method="POST" enctype="multipart/form-data" id="grievanceForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <section class="report-card mb-4" aria-labelledby="identity-title">
      <h2 id="identity-title" class="section-heading h4 fw-bold"><i class="fas fa-user-shield"></i>Your identity</h2>

      <div class="form-check anonymous-option mb-4">
        <input class="form-check-input" type="checkbox" id="anonymous" name="is_anonymous" value="1" <?= isset($old['is_anonymous']) ? 'checked' : '' ?>>
        <label class="form-check-label fw-bold" for="anonymous">Submit anonymously</label>
        <div class="small mt-1">Your identity and contact details will not be stored. Save the Case ID shown after submission because it is your only tracking reference.</div>
      </div>

      <div id="identityFields" class="identity-fields">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="reporter_name" class="form-label required">Full name</label>
            <input id="reporter_name" name="reporter_name" type="text" class="form-control" maxlength="150" autocomplete="name" value="<?= old_value($old, 'reporter_name') ?>">
          </div>
          <div class="col-md-6">
            <label for="reporter_email" class="form-label required">Email address</label>
            <input id="reporter_email" name="reporter_email" type="email" class="form-control" maxlength="190" autocomplete="email" value="<?= old_value($old, 'reporter_email') ?>">
            <div class="form-text">Used only if the case team needs clarification.</div>
          </div>
          <div class="col-md-6">
            <label for="user_type" class="form-label required">Community role</label>
            <select id="user_type" name="user_type" class="form-select">
              <option value="">Select your role</option>
              <?php foreach (['Student', 'Instructor', 'Staff', 'Other'] as $role): ?>
                <option value="<?= $role ?>" <?= (($old['user_type'] ?? '') === $role) ? 'selected' : '' ?>><?= $role ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="reporter_id" class="form-label">ID number <span class="fw-normal">(optional)</span></label>
            <input id="reporter_id" name="reporter_id" type="text" class="form-control" maxlength="50" value="<?= old_value($old, 'reporter_id') ?>">
          </div>
          <div class="col-md-3">
            <label for="reporter_phone" class="form-label">Phone <span class="fw-normal">(optional)</span></label>
            <input id="reporter_phone" name="reporter_phone" type="tel" class="form-control" maxlength="30" autocomplete="tel" value="<?= old_value($old, 'reporter_phone') ?>">
          </div>
        </div>
      </div>
    </section>

    <section class="report-card mb-4" aria-labelledby="incident-title">
      <h2 id="incident-title" class="section-heading h4 fw-bold"><i class="fas fa-file-lines"></i>What happened</h2>
      <details class="concern-guide mb-4">
        <summary><i class="fas fa-circle-info"></i>Review the concern types before choosing</summary>
        <p class="mt-3 mb-3" style="color:var(--deep-green);opacity:.76">Choose the category that best describes the main issue. Administrators can correct the classification during review if necessary.</p>
        <div class="row g-2">
          <div class="col-md-6"><div class="guide-item"><strong>Harassment</strong><span>Bullying, intimidation, unwanted conduct, threats, or behavior creating a hostile environment.</span></div></div>
          <div class="col-md-6"><div class="guide-item"><strong>Discrimination</strong><span>Unfair treatment based on identity, disability, religion, gender, age, background, or another protected characteristic.</span></div></div>
          <div class="col-md-6"><div class="guide-item"><strong>Safety concern</strong><span>Unsafe facilities, hazards, violence, health risks, dangerous equipment, or an immediate threat to wellbeing.</span></div></div>
          <div class="col-md-6"><div class="guide-item"><strong>Academic issue</strong><span>Grades, assessment, instruction, classroom treatment, academic policy, scheduling, or learning-related concerns.</span></div></div>
          <div class="col-md-6"><div class="guide-item"><strong>Administrative</strong><span>Delayed documents, enrollment, records, office procedures, staff service, or institutional process concerns.</span></div></div>
          <div class="col-md-6"><div class="guide-item"><strong>Financial</strong><span>Fees, billing, refunds, scholarships, payments, financial assistance, or unexplained charges.</span></div></div>
          <div class="col-md-6"><div class="guide-item"><strong>Technology</strong><span>Accounts, system access, data privacy, online platforms, connectivity, or institution-provided technology.</span></div></div>
          <div class="col-md-6"><div class="guide-item"><strong>Other</strong><span>Use this only when none of the listed categories reasonably describes the main concern.</span></div></div>
        </div>
      </details>
      <div class="row g-3">
        <div class="col-md-6">
          <label for="category" class="form-label required">Concern category</label>
          <select id="category" name="category" class="form-select" required>
            <option value="">Select a category</option>
            <?php
              $categories = [
                'harassment' => 'Harassment', 'discrimination' => 'Discrimination',
                'safety' => 'Safety concern', 'academic' => 'Academic issue',
                'administrative' => 'Administrative', 'financial' => 'Financial',
                'technology' => 'Technology', 'other' => 'Other',
              ];
              foreach ($categories as $value => $label):
            ?>
              <option value="<?= $value ?>" <?= (($old['category'] ?? '') === $value) ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
          <div id="categoryHelp" class="category-help mt-2" aria-live="polite"><i class="fas fa-lightbulb me-2"></i>Select a category to see a short explanation.</div>
        </div>
        <div class="col-md-6">
          <label for="incident_date" class="form-label">Incident date <span class="fw-normal">(if known)</span></label>
          <input id="incident_date" name="incident_date" type="date" class="form-control" max="<?= date('Y-m-d') ?>" value="<?= old_value($old, 'incident_date') ?>">
        </div>
        <div class="col-12">
          <label for="subject" class="form-label required">Short subject</label>
          <input id="subject" name="subject" type="text" class="form-control" maxlength="200" required placeholder="Briefly summarize the concern" value="<?= old_value($old, 'subject') ?>">
        </div>
        <div class="col-12">
          <label for="description" class="form-label required">Detailed description</label>
          <textarea id="description" name="description" class="form-control" required minlength="20" placeholder="Describe what happened, who was involved, and any important context."><?= old_value($old, 'description') ?></textarea>
          <div class="form-text">Include facts you remember. Avoid sharing passwords or unrelated sensitive information.</div>
        </div>
        <div class="col-12">
          <label for="incident_location" class="form-label">Location <span class="fw-normal">(optional)</span></label>
          <input id="incident_location" name="incident_location" type="text" class="form-control" maxlength="200" placeholder="Building, room, office, online platform, or other location" value="<?= old_value($old, 'incident_location') ?>">
        </div>
      </div>
    </section>

    <section class="report-card mb-4" aria-labelledby="risk-title">
      <h2 id="risk-title" class="section-heading h4 fw-bold"><i class="fas fa-triangle-exclamation"></i>Urgency and safety check</h2>
      <p class="mb-4" style="color:var(--deep-green);opacity:.78">Select every statement that applies. These structured answers create an initial priority suggestion; an administrator reviews and confirms it.</p>
      <?php $riskQuestions=['risk_danger'=>'Is anyone currently in danger?','risk_ongoing'=>'Is the incident ongoing?','risk_repeated'=>'Has this happened repeatedly?','risk_retaliation'=>'Is there a threat of retaliation?','risk_multiple_people'=>'Does it affect multiple people?','risk_urgent'=>'Is urgent intervention required?']; ?>
      <div class="row g-3"><?php foreach($riskQuestions as $name=>$label): ?><div class="col-md-6"><div class="risk-option form-check"><input class="form-check-input" type="checkbox" id="<?= $name ?>" name="<?= $name ?>" value="1" <?= isset($old[$name])?'checked':'' ?>><label class="form-check-label" for="<?= $name ?>"><?= htmlspecialchars($label) ?></label></div></div><?php endforeach; ?></div>
      <div class="form-text mt-3">If someone is in immediate danger, contact the appropriate emergency service instead of waiting for this process.</div>
    </section>

    <section class="report-card mb-4" aria-labelledby="evidence-title">
      <h2 id="evidence-title" class="section-heading h4 fw-bold"><i class="fas fa-paperclip"></i>Supporting evidence</h2>
      <label for="evidence" class="form-label">Attach one file <span class="fw-normal">(optional)</span></label>
      <input id="evidence" name="evidence" type="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
      <div class="form-text">PDF, JPG, PNG, DOC, or DOCX; maximum 5 MB.</div>
    </section>

    <div class="privacy-note mb-4"><i class="fas fa-lock me-2"></i>Your report is submitted securely. Structured safety answers create an initial priority suggestion; an administrator must review and confirm or change it with an auditable reason.</div>

    <div class="form-check mb-4">
      <input class="form-check-input" type="checkbox" id="confirm" required>
      <label class="form-check-label" for="confirm">I confirm that this report is accurate to the best of my knowledge.</label>
    </div>

    <button type="submit" class="btn submit-report-btn w-100"><i class="fas fa-paper-plane me-2"></i>Submit Report and Generate Case ID</button>
  </form>
</div>

<script>
  (() => {
    const anonymous = document.getElementById('anonymous');
    const identityFields = document.getElementById('identityFields');
    const requiredIdentity = ['reporter_name', 'reporter_email', 'user_type'];
    const allIdentity = [...requiredIdentity, 'reporter_id', 'reporter_phone'];

    function updateIdentityFields() {
      const hidden = anonymous.checked;
      identityFields.classList.toggle('is-disabled', hidden);
      allIdentity.forEach((id) => {
        const field = document.getElementById(id);
        field.disabled = hidden;
        field.required = !hidden && requiredIdentity.includes(id);
      });
    }

    anonymous.addEventListener('change', updateIdentityFields);
    updateIdentityFields();

    const category = document.getElementById('category');
    const categoryHelp = document.getElementById('categoryHelp');
    const categoryDescriptions = {
      harassment: 'Choose Harassment for bullying, intimidation, threats, or other unwanted conduct.',
      discrimination: 'Choose Discrimination for unfair treatment connected to identity or a protected characteristic.',
      safety: 'Choose Safety concern for hazards, dangerous conditions, violence, or risks to health and wellbeing.',
      academic: 'Choose Academic issue for grading, instruction, assessment, scheduling, or learning-related concerns.',
      administrative: 'Choose Administrative for records, enrollment, document processing, office service, or institutional procedures.',
      financial: 'Choose Financial for fees, billing, payments, refunds, scholarships, or financial assistance.',
      technology: 'Choose Technology for accounts, access, privacy, connectivity, or institutional systems.',
      other: 'Choose Other only when none of the available categories reasonably fits the main concern.'
    };
    function updateCategoryHelp() {
      categoryHelp.innerHTML = '<i class="fas fa-lightbulb me-2"></i>' + (categoryDescriptions[category.value] || 'Select a category to see a short explanation.');
    }
    category.addEventListener('change', updateCategoryHelp);
    updateCategoryHelp();
  })();
</script>

<?php require_once 'includes/footer.php'; ?>
