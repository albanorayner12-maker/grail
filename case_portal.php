<?php
if (session_status()===PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';
require_once 'includes/case_service.php';
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token']=bin2hex(random_bytes(32)); }

$error=''; $notice='';
if (isset($_POST['login_case'])) {
    $case=case_find_by_credentials($pdo,(string)($_POST['tracking_token']??''),(string)($_POST['access_code']??''));
    if ($case) { $_SESSION['reporter_case_id']=(int)$case['id']; $_SESSION['reporter_case_token']=$case['tracking_token']; header('Location: case_portal.php'); exit; }
    $error='The Case ID or private access code is incorrect.';
}
if (isset($_GET['logout'])) { unset($_SESSION['reporter_case_id'],$_SESSION['reporter_case_token']); header('Location: case_portal.php'); exit; }
$caseId=(int)($_SESSION['reporter_case_id']??0);
if ($caseId && $_SERVER['REQUEST_METHOD']==='POST' && !isset($_POST['login_case'])) {
    if (!hash_equals($_SESSION['csrf_token'],(string)($_POST['csrf_token']??''))) { $error='Your session expired. Refresh and try again.'; }
    else {
        $action=(string)($_POST['action']??''); $text=trim((string)($_POST['text']??''));
        if (mb_strlen($text)<10) { $error='Please provide at least 10 characters.'; }
        elseif ($action==='message') {
            $pdo->prepare("INSERT INTO case_messages (grievance_id,sender_type,sender_name,message) VALUES (:id,'reporter','Reporter',:message)")->execute(['id'=>$caseId,'message'=>$text]);
            case_add_event($pdo,$caseId,'reporter_message','Reporter sent a case message',null,'reporter',null,'Reporter',true);
            case_queue_notification($pdo,$caseId,'admin',null,'Reporter replied','A new reporter message requires review.'); $notice='Your message was sent securely.';
        } elseif (in_array($action,['appeal','reopen'],true)) {
            $pdo->prepare('INSERT INTO case_appeals (grievance_id,appeal_type,reason) VALUES (:id,:type,:reason)')->execute(['id'=>$caseId,'type'=>$action,'reason'=>$text]);
            if ($action==='reopen') { $pdo->prepare("UPDATE grievances SET status='under investigation',reopened_at=NOW(),resolved_at=NULL WHERE id=:id")->execute(['id'=>$caseId]); }
            case_add_event($pdo,$caseId,$action,$action==='reopen'?'Case reopened by reporter':'Reporter submitted an appeal',$text,'reporter',null,'Reporter',true);
            case_queue_notification($pdo,$caseId,'admin',null,ucfirst($action).' requested','The reporter submitted a '.$action.' request.'); $notice='Your request was recorded for review.';
        } elseif (in_array($action,['confirmed_improvement','unresolved'],true)) {
            $pdo->prepare('INSERT INTO resolution_feedback (grievance_id,outcome,comment) VALUES (:id,:outcome,:comment)')->execute(['id'=>$caseId,'outcome'=>$action,'comment'=>$text]);
            case_add_event($pdo,$caseId,'resolution_feedback','Reporter verified the outcome',str_replace('_',' ',$action).': '.$text,'reporter',null,'Reporter',true); $notice='Your resolution feedback was recorded.';
        }
    }
}
$case=null; $events=[]; $messages=[]; $evidence=[];
if ($caseId) {
    $stmt=$pdo->prepare('SELECT * FROM grievances WHERE id=:id'); $stmt->execute(['id'=>$caseId]); $case=$stmt->fetch(PDO::FETCH_ASSOC);
    if (!$case) { unset($_SESSION['reporter_case_id']); }
    else {
        $stmt=$pdo->prepare('SELECT * FROM case_events WHERE grievance_id=:id AND reporter_visible=1 ORDER BY created_at DESC'); $stmt->execute(['id'=>$caseId]); $events=$stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt=$pdo->prepare('SELECT * FROM case_messages WHERE grievance_id=:id AND reporter_visible=1 ORDER BY created_at ASC'); $stmt->execute(['id'=>$caseId]); $messages=$stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt=$pdo->prepare('SELECT id,original_name,created_at,scan_status FROM evidence_files WHERE grievance_id=:id ORDER BY created_at'); $stmt->execute(['id'=>$caseId]); $evidence=$stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo->prepare("UPDATE case_messages SET read_by_reporter_at=COALESCE(read_by_reporter_at,NOW()) WHERE grievance_id=:id AND sender_type='admin'")->execute(['id'=>$caseId]);
    }
}
require_once 'includes/header.php';
?>
<style>.portal{max-width:1000px}.portal-card{background:var(--cream);border:1px solid rgba(84,140,47,.25);border-radius:18px;padding:clamp(20px,4vw,32px);box-shadow:0 8px 24px rgba(16,73,17,.08)}.event{padding:14px 0;border-bottom:1px solid rgba(84,140,47,.15)}.message{max-width:80%;padding:12px 15px;margin-bottom:12px;border-radius:14px;background:var(--mint)}.message.reporter{margin-left:auto;background:rgba(255,212,73,.45)}</style>
<main class="container portal py-5">
<?php if(!$case): ?>
  <section class="portal-card"><h1 class="fw-bold mb-2">Private Case Portal</h1><p>Enter both credentials provided after submission.</p><?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?><form method="post" class="row g-3"><div class="col-md-7"><label class="form-label" for="trackingToken">Case ID</label><input class="form-control" id="trackingToken" name="tracking_token" required></div><div class="col-md-5"><label class="form-label" for="accessCode">Private access code</label><input class="form-control" id="accessCode" name="access_code" inputmode="numeric" required></div><div><button class="btn btn-success" name="login_case" value="1">Open private case</button></div></form></section>
<?php else: ?>
  <div class="d-flex justify-content-between align-items-center mb-4"><div><h1 class="fw-bold mb-1"><?= htmlspecialchars($case['tracking_token']) ?></h1><div>Status: <strong><?= htmlspecialchars(ucwords($case['status'])) ?></strong> · Deadline: <?= htmlspecialchars(date('M j, Y g:i A',strtotime($case['due_at']?:'+7 days'))) ?></div></div><a class="btn btn-outline-secondary" href="?logout=1">Lock portal</a></div>
  <?php if($notice): ?><div class="alert alert-success"><?= htmlspecialchars($notice) ?></div><?php endif; ?><?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <section class="portal-card mb-4"><h2 class="h4 fw-bold">Secure case inbox</h2><div class="my-3"><?php if(!$messages): ?><p class="opacity-75">No messages yet.</p><?php endif; ?><?php foreach($messages as $message): ?><div class="message <?= $message['sender_type']==='reporter'?'reporter':'' ?>"><strong><?= htmlspecialchars($message['sender_name']) ?></strong><div><?= nl2br(htmlspecialchars($message['message'])) ?></div><small><?= htmlspecialchars(date('M j, Y g:i A',strtotime($message['created_at']))) ?></small></div><?php endforeach; ?></div><form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><input type="hidden" name="action" value="message"><label class="form-label" for="messageText">Send a private update</label><textarea class="form-control mb-2" id="messageText" name="text" rows="3" minlength="10" required></textarea><button class="btn btn-success">Send message</button></form></section>
  <div class="row g-4"><div class="col-lg-6"><section class="portal-card h-100"><h2 class="h4 fw-bold">Case timeline</h2><?php foreach($events as $event): ?><article class="event"><strong><?= htmlspecialchars($event['title']) ?></strong><?php if($event['details']): ?><div><?= nl2br(htmlspecialchars($event['details'])) ?></div><?php endif; ?><small><?= htmlspecialchars($event['actor_name']) ?> · <?= htmlspecialchars(date('M j, Y g:i A',strtotime($event['created_at']))) ?></small></article><?php endforeach; ?></section></div><div class="col-lg-6"><section class="portal-card h-100"><h2 class="h4 fw-bold">Reporter controls</h2><form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><label class="form-label" for="requestText">Explanation</label><textarea class="form-control mb-3" id="requestText" name="text" minlength="10" required></textarea><div class="d-flex flex-wrap gap-2"><button class="btn btn-warning" name="action" value="appeal">Appeal decision</button><button class="btn btn-danger" name="action" value="reopen">Reopen case</button><button class="btn btn-success" name="action" value="confirmed_improvement">Confirm improvement</button><button class="btn btn-outline-danger" name="action" value="unresolved">Still unresolved</button></div></form><?php if($evidence): ?><h3 class="h5 fw-bold mt-4">Evidence</h3><?php foreach($evidence as $file): ?><a class="d-block" href="evidence_download.php?id=<?= (int)$file['id'] ?>"><?= htmlspecialchars($file['original_name']) ?></a><?php endforeach; ?><?php endif; ?></section></div></div>
<?php endif; ?></main>
<?php require_once 'includes/footer.php'; ?>
