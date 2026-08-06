<?php
if (session_status()===PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
require_once 'db.php'; require_once 'includes/case_service.php';
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token']=bin2hex(random_bytes(32)); }
$caseId=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST,'case_id',FILTER_VALIDATE_INT);
if (!$caseId) { header('Location: dashboard.php'); exit; }
$admin=case_admin_identity(); $notice=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!hash_equals($_SESSION['csrf_token'],(string)($_POST['csrf_token']??''))) { $error='Session expired.'; }
    else {
        $action=(string)($_POST['action']??''); $details=trim((string)($_POST['details']??''));
        try {
            if ($action==='assign' && in_array($admin['role'],['super_admin','supervisor','case_manager'],true)) {
                $assignedId=(int)($_POST['assigned_admin_id']??0);
                $pdo->beginTransaction();
                $pdo->prepare('UPDATE case_assignments SET active=0,ended_at=NOW() WHERE grievance_id=:id AND active=1')->execute(['id'=>$caseId]);
                $pdo->prepare('INSERT INTO case_assignments (grievance_id,admin_id,assigned_by,assignment_reason) VALUES (:id,:admin,:by,:reason)')->execute(['id'=>$caseId,'admin'=>$assignedId,'by'=>$admin['id'],'reason'=>$details]);
                $pdo->prepare('UPDATE grievances SET assigned_admin_id=:admin WHERE id=:id')->execute(['admin'=>$assignedId,'id'=>$caseId]);
                $nameStmt=$pdo->prepare('SELECT name FROM admins WHERE id=:id'); $nameStmt->execute(['id'=>$assignedId]); $assignedName=(string)$nameStmt->fetchColumn();
                case_add_event($pdo,$caseId,'assigned','Case assigned to '.$assignedName,$details,'admin',$admin['id'],$admin['name'],true); case_queue_notification($pdo,$caseId,'admin',$assignedId,'Case assigned','A grievance has been assigned to you.');
                $pdo->commit(); $notice='Assignment saved.';
            } elseif ($action==='conflict') {
                $pdo->prepare('INSERT INTO case_conflicts (grievance_id,admin_id,conflict_type,explanation) VALUES (:id,:admin,:type,:explanation)')->execute(['id'=>$caseId,'admin'=>$admin['id'],'type'=>(string)($_POST['conflict_type']??'other'),'explanation'=>$details]);
                $pdo->prepare("UPDATE grievances SET conflict_status='declared',assigned_admin_id=NULL WHERE id=:id")->execute(['id'=>$caseId]);
                case_add_event($pdo,$caseId,'conflict_declared','Conflict of interest declared','Case removed from the current assignee pending reassignment.','admin',$admin['id'],$admin['name'],true); $notice='Conflict declared and assignment cleared.';
            } elseif ($action==='message') {
                $pdo->prepare("INSERT INTO case_messages (grievance_id,sender_type,sender_id,sender_name,message) VALUES (:id,'admin',:admin,:name,:message)")->execute(['id'=>$caseId,'admin'=>$admin['id'],'name'=>$admin['name'],'message'=>$details]);
                case_add_event($pdo,$caseId,'admin_message','Administrator sent a reporter message',null,'admin',$admin['id'],$admin['name'],true); case_queue_notification($pdo,$caseId,'reporter',null,'New case message','A new message is available in the private case portal.'); $notice='Message sent to the case inbox.';
            } elseif ($action==='sla') {
                $hours=max(1,min(2160,(int)($_POST['sla_hours']??168)));
                $pdo->prepare('UPDATE grievances SET sla_hours=:hours,due_at=DATE_ADD(NOW(),INTERVAL :due HOUR),escalation_level=0 WHERE id=:id')->execute(['hours'=>$hours,'due'=>$hours,'id'=>$caseId]);
                case_add_event($pdo,$caseId,'deadline_changed','Action deadline updated',$details,'admin',$admin['id'],$admin['name'],true); $notice='SLA deadline updated.';
            }
        } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); $error='The case update could not be saved.'; }
    }
}
$stmt=$pdo->prepare('SELECT g.*,a.name assigned_name,a.role assigned_role FROM grievances g LEFT JOIN admins a ON a.id=g.assigned_admin_id WHERE g.id=:id'); $stmt->execute(['id'=>$caseId]); $case=$stmt->fetch(PDO::FETCH_ASSOC); if(!$case){http_response_code(404);exit('Case not found.');}
$admins=$pdo->query('SELECT id,name,role,department FROM admins WHERE is_active=1 ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$stmt=$pdo->prepare('SELECT * FROM case_conflicts WHERE grievance_id=:id ORDER BY created_at DESC');$stmt->execute(['id'=>$caseId]);$conflicts=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Manage <?= htmlspecialchars($case['tracking_token']) ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><style>body{background:#fff8ef;color:#104911}.card{border-color:rgba(84,140,47,.25);border-radius:16px}.form-control,.form-select{min-height:48px}</style></head><body><main class="container py-5"><a href="view.php?id=<?= $caseId ?>" class="btn btn-outline-success mb-4">← Back to case</a><h1 class="fw-bold"><?= htmlspecialchars($case['tracking_token']) ?> management</h1><p>Assigned to: <strong><?= htmlspecialchars($case['assigned_name']?:'Unassigned') ?></strong> · Conflict status: <strong><?= htmlspecialchars(ucfirst($case['conflict_status'])) ?></strong></p><?php if($notice):?><div class="alert alert-success"><?=htmlspecialchars($notice)?></div><?php endif;?><?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif;?><div class="row g-4">
<div class="col-lg-6"><section class="card p-4 h-100"><h2 class="h4 fw-bold">Assignment and role</h2><form method="post"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>"><input type="hidden" name="case_id" value="<?=$caseId?>"><input type="hidden" name="action" value="assign"><label class="form-label">Assign investigator</label><select class="form-select mb-3" name="assigned_admin_id" required><?php foreach($admins as $person):?><option value="<?=$person['id']?>"><?=htmlspecialchars($person['name'].' — '.ucwords(str_replace('_',' ',$person['role'])).($person['department']?' / '.$person['department']:''))?></option><?php endforeach;?></select><label class="form-label">Assignment reason</label><textarea class="form-control mb-3" name="details" required></textarea><button class="btn btn-success">Save assignment</button></form></section></div>
<div class="col-lg-6"><section class="card p-4 h-100"><h2 class="h4 fw-bold">Conflict of interest</h2><form method="post"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>"><input type="hidden" name="case_id" value="<?=$caseId?>"><input type="hidden" name="action" value="conflict"><select class="form-select mb-3" name="conflict_type"><option value="personal">Personal relationship</option><option value="departmental">Departmental involvement</option><option value="supervisory">Supervisory relationship</option><option value="other">Other conflict</option></select><textarea class="form-control mb-3" name="details" minlength="10" placeholder="Explain the conflict" required></textarea><button class="btn btn-danger">Declare and recuse</button></form></section></div>
<div class="col-lg-6"><section class="card p-4 h-100"><h2 class="h4 fw-bold">Reporter inbox</h2><form method="post"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>"><input type="hidden" name="case_id" value="<?=$caseId?>"><input type="hidden" name="action" value="message"><textarea class="form-control mb-3" name="details" minlength="10" placeholder="Message visible to the reporter" required></textarea><button class="btn btn-success">Send secure message</button></form></section></div>
<div class="col-lg-6"><section class="card p-4 h-100"><h2 class="h4 fw-bold">SLA deadline</h2><form method="post"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>"><input type="hidden" name="case_id" value="<?=$caseId?>"><input type="hidden" name="action" value="sla"><label class="form-label">Hours from now</label><input class="form-control mb-3" type="number" name="sla_hours" min="1" max="2160" value="<?= (int)$case['sla_hours'] ?>"><textarea class="form-control mb-3" name="details" minlength="10" placeholder="Required reason for deadline change" required></textarea><button class="btn btn-warning">Update deadline</button></form></section></div>
</div></main></body></html>
