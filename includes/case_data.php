<?php
function grail_case_records(): array
{
    $fallback = [
        ['id'=>1,'tracking_token'=>'GRL-2026-1042','title'=>'Laboratory equipment concern','subject'=>'Laboratory equipment concern','user'=>'Faculty Member','name'=>'Faculty Member','created_at'=>'2026-07-30 09:15:00','status'=>'pending','category'=>'safety','priority'=>'high'],
        ['id'=>2,'tracking_token'=>'GRL-2026-1038','title'=>'Classroom scheduling conflict','subject'=>'Classroom scheduling conflict','user'=>'Department Staff','name'=>'Department Staff','created_at'=>'2026-07-29 14:30:00','status'=>'under investigation','category'=>'academic','priority'=>'medium'],
        ['id'=>3,'tracking_token'=>'GRL-2026-1031','title'=>'Network access request','subject'=>'Network access request','user'=>'Faculty Member','name'=>'Faculty Member','created_at'=>'2026-07-28 11:05:00','status'=>'resolved','category'=>'technology','priority'=>'medium'],
        ['id'=>4,'tracking_token'=>'GRL-2026-1026','title'=>'Document processing inquiry','subject'=>'Document processing inquiry','user'=>'Office Personnel','name'=>'Office Personnel','created_at'=>'2026-07-20 08:40:00','status'=>'unreviewed','category'=>'administrative','priority'=>'low'],
    ];
    $records = $_SESSION['dashboard_preview_records'] ?? $fallback;
    if (isset($_SESSION['admin_logged_in']) && empty($_SESSION['admin_preview'])) {
        require_once __DIR__ . '/../db.php';
        try {
            $records = $pdo->query("SELECT g.id,g.tracking_token,g.subject AS title,g.subject,g.name AS user,g.name,g.created_at,g.status,g.category,g.priority,g.due_at,g.escalation_level,g.assigned_admin_id,(EXISTS(SELECT 1 FROM grievance_status_history h WHERE h.grievance_id=g.id) OR EXISTS(SELECT 1 FROM case_events e WHERE e.grievance_id=g.id AND e.actor_type='admin')) AS admin_action_taken FROM grievances g ORDER BY g.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $records = $pdo->query("SELECT id,tracking_token,subject AS title,subject,name AS user,name,created_at,status,category,priority,0 AS admin_action_taken FROM grievances ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        $acted = array_map('intval', array_column($_SESSION['dashboard_preview_activity'] ?? [], 'grievance_id'));
        foreach ($records as &$record) { $record['admin_action_taken'] = in_array((int)($record['id'] ?? 0), $acted, true) ? 1 : 0; }
        unset($record);
    }
    foreach ($records as &$record) {
        $record['title'] = $record['title'] ?? $record['subject'] ?? '';
        $record['subject'] = $record['subject'] ?? $record['title'];
        $record['user'] = $record['user'] ?? $record['name'] ?? '';
        $record['name'] = $record['name'] ?? $record['user'];
        $record['deadline_at'] = !empty($record['due_at']) ? $record['due_at'] : date('Y-m-d H:i:s', strtotime('+7 days', strtotime((string)$record['created_at'])));
        $record['is_overdue'] = empty($record['admin_action_taken']) && time() > strtotime($record['deadline_at']);
    }
    unset($record);
    usort($records, static fn($a,$b) => ((int)$b['is_overdue'] <=> (int)$a['is_overdue']) ?: strcmp((string)$b['created_at'], (string)$a['created_at']));
    return $records;
}
