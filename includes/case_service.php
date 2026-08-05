<?php
declare(strict_types=1);

function case_sla_hours(string $priority): int
{
    return match (strtolower($priority)) { 'critical'=>4, 'high'=>24, 'medium'=>72, default=>168 };
}

function case_add_event(PDO $pdo, int $caseId, string $type, string $title, ?string $details, string $actorType, ?int $actorId, string $actorName, bool $reporterVisible=true): void
{
    $stmt=$pdo->prepare('INSERT INTO case_events (grievance_id,event_type,title,details,actor_type,actor_id,actor_name,reporter_visible) VALUES (:case_id,:type,:title,:details,:actor_type,:actor_id,:actor_name,:visible)');
    $stmt->execute(['case_id'=>$caseId,'type'=>$type,'title'=>$title,'details'=>$details,'actor_type'=>$actorType,'actor_id'=>$actorId,'actor_name'=>$actorName,'visible'=>$reporterVisible?1:0]);
}

function case_queue_notification(PDO $pdo, int $caseId, string $recipientType, ?int $recipientId, string $subject, string $body): void
{
    $stmt=$pdo->prepare('INSERT INTO case_notifications (grievance_id,recipient_type,recipient_id,subject,body) VALUES (:case_id,:recipient_type,:recipient_id,:subject,:body)');
    $stmt->execute(['case_id'=>$caseId,'recipient_type'=>$recipientType,'recipient_id'=>$recipientId,'subject'=>$subject,'body'=>$body]);
}

function case_run_escalations(PDO $pdo): int
{
    $cases=$pdo->query("SELECT id,tracking_token,assigned_admin_id,escalation_level,TIMESTAMPDIFF(HOUR,due_at,NOW()) overdue_hours FROM grievances WHERE due_at IS NOT NULL AND due_at<NOW() AND escalation_level<3 AND status NOT IN ('resolved','dismissed')")->fetchAll(PDO::FETCH_ASSOC);
    $updated=0;
    foreach ($cases as $case) {
        $level=(int)$case['overdue_hours']>=72?3:((int)$case['overdue_hours']>=24?2:1);
        if ($level <= (int)$case['escalation_level']) { continue; }
        $pdo->prepare('UPDATE grievances SET escalation_level=:level WHERE id=:id')->execute(['level'=>$level,'id'=>$case['id']]);
        case_queue_notification($pdo,(int)$case['id'],'admin',$case['assigned_admin_id']?:(null),'Overdue case '.$case['tracking_token'],'This case passed its action deadline and requires immediate review. Escalation level: '.$level.'.');
        case_add_event($pdo,(int)$case['id'],'sla_escalated','Action deadline exceeded','Escalation level '.$level.' was triggered automatically.','system',null,'GRAIL System',true);
        $updated++;
    }
    return $updated;
}

function case_find_by_credentials(PDO $pdo, string $trackingToken, string $accessCode): ?array
{
    $stmt=$pdo->prepare('SELECT * FROM grievances WHERE tracking_token=:token LIMIT 1');
    $stmt->execute(['token'=>strtoupper(trim($trackingToken))]);
    $case=$stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    if (!$case) { return null; }
    $hash=(string)($case['access_code_hash']??'');
    if ($hash!=='' && !password_verify($accessCode,$hash)) { return null; }
    return $case;
}

function case_admin_identity(): array
{
    return ['id'=>isset($_SESSION['admin_id'])?(int)$_SESSION['admin_id']:null,'name'=>(string)($_SESSION['admin_name']??'Administrator'),'role'=>(string)($_SESSION['admin_role']??'case_manager')];
}
