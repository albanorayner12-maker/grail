<?php
session_start();
$isAdmin = !empty($_SESSION['admin_logged_in']);
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

// Ensure an ID is passed in the URL string
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "No record ID provided.";
    header("Location: dashboard.php");
    exit();
}

$id = intval($_GET['id']);
$record = null;
$caseMessages = [];
$secureEvidence = [];

if ($isAdmin) {
    require_once 'db.php';
    require_once 'includes/case_service.php';
    try {
        $stmt = $pdo->prepare("SELECT * FROM grievances WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $exception) {
        $record = null;
    }
} else {
    require_once 'includes/case_data.php';
    foreach (grail_case_records() as $previewRecord) {
        if ((int)($previewRecord['id'] ?? 0) === $id) { $record = $previewRecord; break; }
    }
}

if (!$record) {
    http_response_code(404);
    exit('This case preview is unavailable. Use a valid preview record ID or sign in as an administrator.');
}
$record = array_merge(['is_anonymous'=>1,'name'=>'Protected','user_type'=>'Not disclosed','id_number'=>null,'email'=>null,'incident_date'=>null,'location'=>null,'description'=>'This read-only preview hides sensitive case information. Sign in as an authorized administrator to view the complete record.','evidence'=>null,'risk_score'=>0],$record);

$inboxNotice = '';
$inboxError = '';
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'send_case_message') {
    $message = trim((string)($_POST['message'] ?? ''));
    $csrfValid = hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''));
    if (!$csrfValid) {
        $inboxError = 'Your session expired. Refresh the page and try again.';
    } elseif (mb_strlen($message) < 10) {
        $inboxError = 'Write a message containing at least 10 characters.';
    } else {
        $admin = case_admin_identity();
        $messageInsert = $pdo->prepare("INSERT INTO case_messages (grievance_id,sender_type,sender_id,sender_name,message) VALUES (:case_id,'admin',:admin_id,:admin_name,:message)");
        $messageInsert->execute(['case_id'=>$id,'admin_id'=>$admin['id'],'admin_name'=>$admin['name'],'message'=>$message]);
        case_add_event($pdo,$id,'admin_message','Administrator sent a reporter message',null,'admin',$admin['id'],$admin['name'],true);
        case_queue_notification($pdo,$id,'reporter',null,'New case message','A new administrator message is available in the private case portal.');
        $inboxNotice = 'Your message was added to the reporter’s private case inbox.';
    }
}

if ($isAdmin) {
    try {
        $messageQuery = $pdo->prepare('SELECT sender_type,sender_name,message,created_at,read_by_reporter_at FROM case_messages WHERE grievance_id=:case_id AND reporter_visible=1 ORDER BY created_at ASC,id ASC');
        $messageQuery->execute(['case_id'=>$id]);
        $caseMessages = $messageQuery->fetchAll(PDO::FETCH_ASSOC);
        $evidenceStmt=$pdo->prepare('SELECT id,original_name FROM evidence_files WHERE grievance_id=:id ORDER BY created_at');
        $evidenceStmt->execute(['id'=>$record['id']]);
        $secureEvidence=$evidenceStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $exception) {
        $caseMessages=[]; $secureEvidence=[];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRAIL SYSTEM | View Record #<?= $record['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .view-card { background-color: #ffffff; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .detail-label { font-weight: 600; color: #2e7d32; font-size: 14px; margin-bottom: 2px; text-uppercase; tracking-wide; }
        .detail-value { color: #333; font-size: 16px; margin-bottom: 20px; background-color: #f8f9fa; padding: 10px 15px; border-radius: 8px; border-left: 3px solid #2e7d32; }
        .description-box { min-height: 120px; background-color: #f8f9fa; border-radius: 8px; padding: 15px; color: #333; line-height: 1.6; border-left: 3px solid #2e7d32; white-space: pre-wrap; }
        .top-navbar { background-color: #2c3e50; }
        .inbox-thread { max-height: 440px; padding: 18px; overflow-y: auto; background: #f7faf7; border: 1px solid #dbe8dc; border-radius: 14px; }
        .inbox-message { width: min(82%, 680px); padding: 13px 16px; margin-bottom: 14px; background: #a7f3d0; border-radius: 14px 14px 14px 4px; }
        .inbox-message.admin { margin-left: auto; background: #fff0bd; border-radius: 14px 14px 4px 14px; }
        .inbox-meta { color: #4d6350; font-size: .78rem; }
    </style>
    <link rel="stylesheet" href="assets/css/admin-theme.css">
</head>
<body>

    <nav class="navbar navbar-dark top-navbar shadow-sm mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="dashboard.php"><i class="fa-solid fa-shield-halved me-2"></i>GRAIL SYSTEM PANEL</a>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard</a>
        </div>
    </nav>

    <div class="container mb-5">
        <?php if (!$isAdmin): ?><div class="alert alert-info"><i class="fa-solid fa-eye me-2"></i><strong>Public read-only preview:</strong> reporter identity, evidence, private messages, and administrative controls are protected.</div><?php endif; ?>
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <div class="view-card p-4 p-md-5">
                    
                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <div>
                            <h2 class="h3 fw-bold mb-1 text-dark">Grievance Details #<?= $record['id'] ?></h2>
                            <p class="text-muted small mb-0">Submitted on: <?= date('M d, Y h:i A', strtotime($record['created_at'])) ?></p>
                        </div>
                        <div class="mt-2 mt-sm-0">
                            <?php if ($record['status'] == 'Completed'): ?>
                                <span class="badge bg-success fs-6 px-3 py-2 rounded-pill">Completed</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill">Pending Review</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-secondary border-bottom pb-1"><i class="fa-solid fa-circle-user me-2"></i>Reporter Identity</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-label">Is Anonymous?</div>
                            <div class="detail-value"><?= $record['is_anonymous'] ? 'Yes (Identity Masked)' : 'No' ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value"><?= htmlspecialchars($isAdmin ? ($record['name'] ?? 'N/A') : 'Protected in public preview') ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">User Classification</div>
                            <div class="detail-value"><?= htmlspecialchars($record['user_type'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">ID Number</div>
                            <div class="detail-value"><?= htmlspecialchars($isAdmin ? ($record['id_number'] ?? 'N/A') : 'Protected') ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Email Address</div>
                            <div class="detail-value"><?= htmlspecialchars($isAdmin ? ($record['email'] ?? 'N/A') : 'Protected') ?></div>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-secondary border-bottom pb-1" style="margin-top: 15px;"><i class="fa-solid fa-file-invoice me-2"></i>Grievance Specifics</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-label">Grievance Type / Category</div>
                            <div class="detail-value text-capitalize"><?= htmlspecialchars($record['category']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Subject Title</div>
                            <div class="detail-value fw-bold"><?= htmlspecialchars($record['subject']) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Date of Incident</div>
                            <div class="detail-value"><?= !empty($record['incident_date']) ? date('M d, Y', strtotime($record['incident_date'])) : 'Not specified' ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Priority Level</div>
                            <div class="detail-value text-capitalize fw-bold <?= $record['priority'] == 'high' || $record['priority'] == 'critical' ? 'text-danger' : 'text-dark' ?>">
                                <?= htmlspecialchars($record['priority'] ?? 'medium') ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Location of Incident</div>
                            <div class="detail-value"><?= htmlspecialchars($record['location'] ?: 'Not Specified') ?></div>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-secondary border-bottom pb-1" style="margin-top: 15px;"><i class="fa-solid fa-triangle-exclamation me-2"></i>Structured Risk Assessment</h5>
                    <?php $riskItems=['risk_danger'=>'Anyone currently in danger','risk_ongoing'=>'Incident is ongoing','risk_repeated'=>'Incident happened repeatedly','risk_retaliation'=>'Threat of retaliation','risk_multiple_people'=>'Multiple people affected','risk_urgent'=>'Urgent intervention required']; ?>
                    <div class="detail-value"><div class="fw-bold mb-2">System risk score: <?= (int)($record['risk_score'] ?? 0) ?> · Suggested priority: <span class="text-uppercase"><?= htmlspecialchars($record['priority'] ?? 'medium') ?></span></div><div class="d-flex flex-wrap gap-2"><?php $hasRisk=false; foreach($riskItems as $field=>$label): if(!empty($record[$field])): $hasRisk=true; ?><span class="badge bg-warning text-dark px-3 py-2"><?= htmlspecialchars($label) ?></span><?php endif; endforeach; if(!$hasRisk): ?><span class="text-muted">No structured risk indicators were selected.</span><?php endif; ?></div></div>

                    <div class="mb-4" style="margin-top: 15px;">
                        <div class="detail-label fw-bold">Full Statement Description</div>
                        <div class="description-box"><?= htmlspecialchars($record['description']) ?></div>
                    </div>

                    <div class="mb-4 border-top pt-4">
                        <div class="detail-label mb-2"><i class="fa-solid fa-paperclip me-2"></i>Supporting Attachments</div>
                        <?php if ($secureEvidence): ?>
                            <div class="p-3 border rounded bg-light d-flex align-items-center justify-content-between">
                                <div><?php foreach($secureEvidence as $file): ?><a class="d-block" href="evidence_download.php?id=<?= (int)$file['id'] ?>"><i class="fa-regular fa-file-lines me-2"></i><?= htmlspecialchars($file['original_name']) ?></a><?php endforeach; ?></div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small italic mb-0">No supporting proof documents or files uploaded for this report file entry.</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($isAdmin): ?><section class="border-top pt-4 mt-4" aria-labelledby="reporter-inbox-title">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <h5 id="reporter-inbox-title" class="fw-bold mb-1 text-secondary"><i class="fa-solid fa-comments me-2"></i>Reporter Inbox</h5>
                                <p class="text-muted mb-0">Messages are tied to the case. Anonymous reporters appear only as “Reporter.”</p>
                            </div>
                            <span class="badge bg-success"><?= count($caseMessages) ?> message<?= count($caseMessages)===1?'':'s' ?></span>
                        </div>

                        <?php if ($inboxNotice): ?><div class="alert alert-success"><?= htmlspecialchars($inboxNotice) ?></div><?php endif; ?>
                        <?php if ($inboxError): ?><div class="alert alert-danger"><?= htmlspecialchars($inboxError) ?></div><?php endif; ?>

                        <div class="inbox-thread mb-3" aria-live="polite">
                            <?php if (!$caseMessages): ?><p class="text-muted text-center my-4">No case messages yet. Send the first private update below.</p><?php endif; ?>
                            <?php foreach ($caseMessages as $caseMessage): ?>
                                <article class="inbox-message <?= $caseMessage['sender_type']==='admin'?'admin':'' ?>">
                                    <div class="fw-bold"><?= htmlspecialchars($caseMessage['sender_type']==='reporter'?'Reporter':$caseMessage['sender_name']) ?></div>
                                    <div><?= nl2br(htmlspecialchars($caseMessage['message'])) ?></div>
                                    <div class="inbox-meta mt-1">
                                        <?= htmlspecialchars(date('M d, Y g:i A',strtotime($caseMessage['created_at']))) ?>
                                        <?php if ($caseMessage['sender_type']==='admin'): ?> · <?= $caseMessage['read_by_reporter_at']?'Read by reporter':'Not yet read' ?><?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <form method="post" action="view.php?id=<?= $id ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="send_case_message">
                            <label for="caseMessage" class="form-label fw-bold">Message the reporter privately</label>
                            <textarea id="caseMessage" name="message" class="form-control mb-2" rows="4" minlength="10" maxlength="4000" placeholder="Ask for information or provide a case update without requesting the reporter’s identity." required></textarea>
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <small class="text-muted">The reporter reads and answers through the Case ID and private access code.</small>
                                <button class="btn btn-success"><i class="fa-solid fa-paper-plane me-2"></i>Send to Reporter Inbox</button>
                            </div>
                        </form>
                    </section><?php endif; ?>

                    <?php if ($isAdmin): ?><div class="d-flex justify-content-end gap-2 border-top pt-4 mt-4">
                        <a href="case_manage.php?id=<?= $record['id'] ?>" class="btn btn-warning"><i class="fa-solid fa-user-shield me-1"></i> Assignment, Inbox & SLA</a>
                        <a href="dashboard.php?action_for=<?= $record['id'] ?>" class="btn btn-success"><i class="fa-solid fa-gavel me-1"></i> Decide and Take Action</a>
                        <a href="delete.php?id=<?= $record['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to permanently delete this report data row?')"><i class="fa-solid fa-trash me-1"></i> Delete Record</a>
                    </div><?php endif; ?>

                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
