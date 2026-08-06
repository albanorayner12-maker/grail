<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

$preview_mode = !isset($_SESSION['admin_logged_in']) || !empty($_SESSION['admin_preview']) || (isset($_GET['preview']) && $_GET['preview'] === '1');
$user_display_name = trim((string) ($_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? $_SESSION['admin_username'] ?? ''));
$is_guest_preview = $user_display_name === '';
if ($is_guest_preview) { $user_display_name = 'Preview User'; }

$sample_records = [
    ['id'=>1,'tracking_token'=>'GRL-2026-1042','title'=>'Laboratory equipment concern','user'=>'Faculty Member','created_at'=>'2026-07-30 09:15:00','status'=>'pending','category'=>'safety','priority'=>'high','incident_date'=>'2026-07-29','location'=>'Science Laboratory','description'=>'Several electrical outlets beside the laboratory workbench are damaged and may pose a safety risk. Photos were submitted for review.','evidence'=>''],
    ['id'=>2,'tracking_token'=>'GRL-2026-1038','title'=>'Classroom scheduling conflict','user'=>'Department Staff','created_at'=>'2026-07-29 14:30:00','status'=>'under investigation','category'=>'academic','priority'=>'medium','incident_date'=>'2026-07-28','location'=>'Registrar Office','description'=>'Two classes were assigned to the same room and time. The affected schedules and room assignment notice require review.','evidence'=>''],
    ['id'=>3,'tracking_token'=>'GRL-2026-1031','title'=>'Network access request','user'=>'Faculty Member','created_at'=>'2026-07-28 11:05:00','status'=>'resolved','category'=>'technology','priority'=>'medium','incident_date'=>'2026-07-27','location'=>'Faculty Office','description'=>'The institutional network account could not access required teaching resources.','evidence'=>''],
    ['id'=>4,'tracking_token'=>'GRL-2026-1026','title'=>'Document processing inquiry','user'=>'Office Personnel','created_at'=>'2026-07-20 08:40:00','status'=>'unreviewed','category'=>'administrative','priority'=>'low','incident_date'=>'2026-07-18','location'=>'Administration Building','description'=>'A submitted document has remained unprocessed beyond the published service period.','evidence'=>''],
];
$allowed_statuses = ['unreviewed','pending','under investigation','on hold','resolved','dismissed'];
$allowed_priorities = ['low','medium','high','critical'];
$status_activity = [];

if ($preview_mode) {
    if (!isset($_SESSION['dashboard_preview_records'])) { $_SESSION['dashboard_preview_records'] = $sample_records; }
    if (!isset($_SESSION['dashboard_preview_activity'])) { $_SESSION['dashboard_preview_activity'] = []; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $record_id = filter_input(INPUT_POST, 'record_id', FILTER_VALIDATE_INT);
        $csrf_valid = hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''));

        if (!$csrf_valid) {
            $_SESSION['dashboard_notice'] = 'The request expired. Please try again.';
        } elseif ($action === 'reset_preview') {
            $_SESSION['dashboard_preview_records'] = $sample_records;
            $_SESSION['dashboard_preview_activity'] = [];
            $_SESSION['dashboard_notice'] = 'Preview data was reset.';
        } elseif ($action === 'update_status' && $record_id) {
            $new_status = strtolower(trim((string)($_POST['new_status'] ?? '')));
            $justification = trim((string)($_POST['justification'] ?? ''));
            $new_priority = strtolower(trim((string)($_POST['new_priority'] ?? '')));
            $priority_justification = trim((string)($_POST['priority_justification'] ?? ''));
            if (!in_array($new_status, $allowed_statuses, true) || !in_array($new_priority, $allowed_priorities, true) || mb_strlen($justification) < 10) {
                $_SESSION['dashboard_notice'] = 'Select a valid status and provide a justification of at least 10 characters.';
            } else {
            foreach ($_SESSION['dashboard_preview_records'] as &$record) {
                if ((int) $record['id'] === $record_id) {
                    $old_status = strtolower((string)$record['status']);
                    $old_priority = strtolower((string)($record['priority'] ?? 'medium'));
                    if ($old_priority !== $new_priority && mb_strlen($priority_justification) < 10) { $_SESSION['dashboard_notice']='Explain the priority change using at least 10 characters.'; break; }
                    if ($old_status === $new_status && $old_priority === $new_priority) { $_SESSION['dashboard_notice'] = 'Change the status or priority before saving.'; break; }
                    $record['status'] = $new_status;
                    $record['priority'] = $new_priority;
                    array_unshift($_SESSION['dashboard_preview_activity'], ['grievance_id'=>$record_id,'tracking_token'=>$record['tracking_token'],'old_status'=>$old_status,'new_status'=>$new_status,'old_priority'=>$old_priority,'new_priority'=>$new_priority,'priority_justification'=>$priority_justification,'justification'=>$justification,'changed_by'=>$user_display_name,'created_at'=>date('Y-m-d H:i:s')]);
                    $_SESSION['dashboard_preview_activity'] = array_slice($_SESSION['dashboard_preview_activity'], 0, 8);
                    $_SESSION['dashboard_notice'] = $record['tracking_token'] . ' was updated to ' . ucwords($new_status) . '.';
                    break;
                }
            }
            unset($record);
            }
        }
        header('Location: dashboard.php'); exit();
    }
    $all_records = $_SESSION['dashboard_preview_records'];
} else {
    require_once 'db.php';
    if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }

    //follow this in SQL
    $pdo->exec("CREATE TABLE IF NOT EXISTS grievance_status_history (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        grievance_id INT NOT NULL,
        old_status VARCHAR(50) NOT NULL,
        new_status VARCHAR(50) NOT NULL,
        justification TEXT NOT NULL,
        old_priority VARCHAR(30) NULL,
        new_priority VARCHAR(30) NULL,
        priority_justification TEXT NULL,
        changed_by VARCHAR(150) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_grievance_id (grievance_id), INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    foreach (['old_priority'=>'VARCHAR(30) NULL','new_priority'=>'VARCHAR(30) NULL','priority_justification'=>'TEXT NULL'] as $column=>$definition) {
        $exists=$pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='grievance_status_history' AND COLUMN_NAME=:column"); $exists->execute(['column'=>$column]);
        if (!(int)$exists->fetchColumn()) { $pdo->exec("ALTER TABLE grievance_status_history ADD COLUMN `$column` $definition"); }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
        $record_id = filter_input(INPUT_POST, 'record_id', FILTER_VALIDATE_INT);
        $new_status = strtolower(trim((string)($_POST['new_status'] ?? '')));
        $justification = trim((string)($_POST['justification'] ?? ''));
        $new_priority = strtolower(trim((string)($_POST['new_priority'] ?? '')));
        $priority_justification = trim((string)($_POST['priority_justification'] ?? ''));
        $csrf_valid = hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''));
        if (!$csrf_valid || !$record_id || !in_array($new_status, $allowed_statuses, true) || !in_array($new_priority, $allowed_priorities, true) || mb_strlen($justification) < 10) {
            $_SESSION['dashboard_notice'] = 'The update was not saved. Select a valid status and provide a justification of at least 10 characters.';
        } else {
            $stmt = $pdo->prepare("SELECT status, priority, tracking_token FROM grievances WHERE id=:id");
            $stmt->execute(['id'=>$record_id]); $current = $stmt->fetch(PDO::FETCH_ASSOC);
            $priority_changed = $current && strtolower((string)$current['priority']) !== $new_priority;
            if ($priority_changed && mb_strlen($priority_justification) < 10) {
                $_SESSION['dashboard_notice'] = 'The priority change requires a justification of at least 10 characters.';
            } elseif (!$current || (strtolower((string)$current['status']) === $new_status && !$priority_changed)) {
                $_SESSION['dashboard_notice'] = 'The record was not found or no decision changed.';
            } else {
                $pdo->beginTransaction();
                try {
                    $pdo->prepare("UPDATE grievances SET status=:status, priority=:priority WHERE id=:id")->execute(['status'=>$new_status,'priority'=>$new_priority,'id'=>$record_id]);
                    $pdo->prepare("INSERT INTO grievance_status_history (grievance_id,old_status,new_status,justification,old_priority,new_priority,priority_justification,changed_by) VALUES (:id,:old,:new,:why,:old_priority,:new_priority,:priority_why,:admin)")->execute(['id'=>$record_id,'old'=>strtolower((string)$current['status']),'new'=>$new_status,'why'=>$justification,'old_priority'=>strtolower((string)$current['priority']),'new_priority'=>$new_priority,'priority_why'=>$priority_justification,'admin'=>$user_display_name]);
                    $pdo->commit();
                    $_SESSION['dashboard_notice'] = ($current['tracking_token'] ?: 'Record') . ' was updated to ' . ucwords($new_status) . '.';
                } catch (Throwable $e) { if ($pdo->inTransaction()) { $pdo->rollBack(); } $_SESSION['dashboard_notice'] = 'The status update could not be saved.'; }
            }
        }
        header('Location: dashboard.php?live=1'); exit();
    }
    $stmt = $pdo->query("SELECT g.id, g.tracking_token, g.subject AS title, g.name AS user, g.created_at, g.status, g.category, g.priority, g.incident_date, g.location, g.description, g.evidence, g.risk_danger, g.risk_ongoing, g.risk_repeated, g.risk_retaliation, g.risk_multiple_people, g.risk_urgent, g.risk_score, EXISTS(SELECT 1 FROM grievance_status_history h WHERE h.grievance_id=g.id) AS admin_action_taken, (SELECT h.changed_by FROM grievance_status_history h WHERE h.grievance_id=g.id ORDER BY h.created_at DESC,h.id DESC LIMIT 1) AS last_action_by, (SELECT h.created_at FROM grievance_status_history h WHERE h.grievance_id=g.id ORDER BY h.created_at DESC,h.id DESC LIMIT 1) AS last_action_at FROM grievances g ORDER BY g.created_at DESC");
    $all_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $status_activity = $pdo->query("SELECT h.*, g.tracking_token FROM grievance_status_history h LEFT JOIN grievances g ON g.id=h.grievance_id ORDER BY h.created_at DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
}
$status_activity = $preview_mode ? $_SESSION['dashboard_preview_activity'] : $status_activity;
if ($preview_mode) {
    $actedIds = array_map('intval', array_column($status_activity, 'grievance_id'));
    foreach ($all_records as &$record) {
        $record['admin_action_taken'] = in_array((int)$record['id'], $actedIds, true) ? 1 : 0;
        $record['last_action_by'] = null;
        $record['last_action_at'] = null;
        foreach ($status_activity as $activity) {
            if ((int)($activity['grievance_id'] ?? 0) === (int)$record['id']) {
                $record['last_action_by'] = $activity['changed_by'] ?? 'Administrator';
                $record['last_action_at'] = $activity['created_at'] ?? null;
                break;
            }
        }
    }
    unset($record);
}
foreach ($all_records as &$record) {
    $submittedAt = strtotime((string)$record['created_at']);
    $record['deadline_at'] = date('Y-m-d H:i:s', strtotime('+7 days', $submittedAt));
    $record['is_overdue'] = !(bool)($record['admin_action_taken'] ?? false) && time() > strtotime($record['deadline_at']);
}
unset($record);
usort($all_records, static fn($a,$b) => ((int)$b['is_overdue'] <=> (int)$a['is_overdue']) ?: strcmp((string)$b['created_at'], (string)$a['created_at']));

$filter = strtolower(trim((string) ($_GET['status'] ?? 'all')));
$allowed_filters = array_merge(['all'], $allowed_statuses);
if (!in_array($filter, $allowed_filters, true)) { $filter = 'all'; }
$recent_records = $filter === 'all' ? $all_records : array_values(array_filter($all_records, static fn($row) => strtolower(trim((string)$row['status'])) === $filter));
$total_records = count($all_records);
$total_users = count(array_unique(array_column($all_records, 'user')));
$total_reports = count(array_filter($all_records, static fn($row) => in_array(strtolower(trim((string)$row['status'])), ['pending','unreviewed'], true)));
$resolved_records = count(array_filter($all_records, static fn($row) => in_array(strtolower(trim((string)$row['status'])), ['resolved','completed','approved','reviewed'], true)));
$overdue_records = count(array_filter($all_records, static fn($row) => !empty($row['is_overdue'])));
$notice = $_SESSION['dashboard_notice'] ?? '';
unset($_SESSION['dashboard_notice']);

function status_meta(string $status): array {
    $status = strtolower(trim($status));
    return match ($status) {
        'resolved','completed','approved','reviewed' => ['Resolved','resolved','fa-circle-check'],
        'under investigation' => ['Investigating','investigating','fa-magnifying-glass-chart'],
        'on hold' => ['On hold','hold','fa-pause-circle'],
        'dismissed' => ['Dismissed','dismissed','fa-ban'],
        'unreviewed' => ['Unreviewed','unreviewed','fa-circle-question'],
        default => ['Pending','pending','fa-clock'],
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>GRAIL System | Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --cream: #fff8ef;
        --yellow: #ffd449;
        --mint: #a7f3d0;
        --green: #548c2f;
        --deep-green: #104911;
        --border: rgba(84, 140, 47, 0.24);
    }

    * { box-sizing: border-box; }

    body {
        min-height: 100vh;
        margin: 0;
        color: var(--deep-green);
        background-color: var(--cream);
        background-image:
            radial-gradient(circle at 0 12%, rgba(167, 243, 208, 0.45), transparent 28%),
            radial-gradient(circle at 100% 85%, rgba(84, 140, 47, 0.15), transparent 30%);
        font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
        font-size: 18px;
        line-height: 1.6;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 4px 12px 24px;
        margin-bottom: 22px;
        color: var(--cream);
        border-bottom: 1px solid rgba(255, 248, 239, 0.16);
        text-decoration: none;
        font-size: 1.4rem;
        letter-spacing: 0.02em;
    }

    .brand i { color: var(--yellow); }

    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        margin-bottom: 28px;
        color: var(--cream);
        background: rgba(167, 243, 208, 0.12);
        border: 1px solid rgba(167, 243, 208, 0.18);
        border-radius: 14px;
    }

    .sidebar-user i { color: var(--yellow); font-size: 2rem; }
    .user-name { overflow: hidden; font-weight: 750; line-height: 1.2; text-overflow: ellipsis; white-space: nowrap; }
    .user-role { color: var(--mint); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }

    .layout {
        min-height: 100vh;
    }

    .sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        z-index: 1030;
        width: 285px;
        height: 100vh;
        padding: 30px 18px;
        overflow-y: auto;
        color: var(--cream);
        background: var(--deep-green);
        box-shadow: 10px 0 26px rgba(16, 73, 17, 0.13);
    }

    .side-label {
        padding: 0 16px;
        margin-bottom: 16px;
        color: var(--mint);
        font-size: 0.85rem;
        font-weight: 800;
        letter-spacing: 0.11em;
        text-transform: uppercase;
    }

    .side-link {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 56px;
        padding: 14px 17px;
        margin-bottom: 7px;
        color: rgba(255, 248, 239, 0.86);
        border-radius: 13px;
        text-decoration: none;
        font-size: 1.05rem;
        font-weight: 650;
        transition: 0.2s;
    }

    .side-link i { width: 24px; text-align: center; font-size: 1.15rem; }
    .side-link:hover { color: var(--cream); background: rgba(167, 243, 208, 0.12); }
    .side-link.active { color: var(--deep-green); background: var(--mint); }
    .side-footer { margin-top: auto; }
    .logout { color: var(--deep-green); background: var(--yellow); }
    .logout:hover { color: var(--deep-green); background: var(--cream); }

    .content { min-width: 0; margin-left: 285px; padding: clamp(24px, 3vw, 48px); }

    .hero {
        position: relative;
        overflow: hidden;
        padding: clamp(32px, 5vw, 54px);
        background: var(--cream);
        border: 1px solid var(--border);
        border-radius: 26px;
        box-shadow: 0 16px 38px rgba(16, 73, 17, 0.1);
    }

    .hero::after {
        content: "";
        position: absolute;
        right: -70px;
        top: -110px;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: var(--mint);
        opacity: 0.55;
    }

    .hero-inner { position: relative; z-index: 1; }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 9px 15px;
        color: var(--deep-green);
        background: var(--mint);
        border-radius: 999px;
        font-size: 0.95rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .hero h1 { font-size: clamp(2.15rem, 4vw, 3.25rem); letter-spacing: -0.035em; }
    .hero-copy { max-width: 760px; font-size: 1.15rem; line-height: 1.75; opacity: 0.8; }

    .preview-chip {
        display: inline-flex;
        align-items: center;
        padding: 10px 14px;
        background: rgba(255, 212, 73, 0.38);
        border: 1px solid var(--border);
        border-radius: 11px;
        font-size: 1rem;
    }

    .stat-card,
    .panel {
        background: var(--cream);
        border: 1px solid var(--border);
        border-radius: 20px;
        box-shadow: 0 8px 22px rgba(16, 73, 17, 0.07);
    }

    .stat-card { height: 100%; min-height: 145px; padding: 26px; transition: 0.25s; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 14px 28px rgba(16, 73, 17, 0.12); }
    .stat-icon { width: 66px; height: 66px; display: grid; place-items: center; flex: 0 0 66px; color: var(--deep-green); background: var(--mint); border-radius: 50%; font-size: 1.5rem; }
    .stat-label { font-size: 0.92rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; opacity: 0.72; }
    .stat-value { font-size: 2.3rem; font-weight: 850; line-height: 1.2; }

    .panel { overflow: hidden; }
    .panel-head { padding: 26px 28px; border-bottom: 1px solid var(--border); }
    .panel-head h2 { font-size: 1.5rem; }
    .panel-head p { font-size: 1rem !important; }

    .filter-select {
        min-width: 220px;
        min-height: 52px;
        padding-inline: 16px 42px;
        color: var(--deep-green);
        background-color: var(--cream);
        border: 1px solid var(--border);
        border-radius: 11px;
        font-size: 1rem;
    }

    .filter-select:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(84, 140, 47, 0.15); }
    .table { --bs-table-bg: transparent; --bs-table-hover-bg: rgba(167, 243, 208, 0.16); color: var(--deep-green); font-size: 1rem; }
    .table th { padding: 18px; color: var(--deep-green); background: rgba(167, 243, 208, 0.3); border-color: var(--border); font-size: 0.9rem; letter-spacing: 0.04em; text-transform: uppercase; white-space: nowrap; }
    .table td { padding: 20px 18px; border-color: rgba(84, 140, 47, 0.13); vertical-align: middle; }
    .case-table-scroll { width: 100%; max-width: 100%; overflow-x: auto; overflow-y: hidden; cursor: grab; scrollbar-width: thin; scrollbar-color: var(--green) rgba(84,140,47,.12); -webkit-overflow-scrolling: touch; overscroll-behavior-inline: contain; }
    .case-table-scroll:active { cursor: grabbing; }
    .case-table-scroll:focus-visible { outline: 3px solid rgba(84,140,47,.35); outline-offset: -3px; }
    .case-table-scroll::-webkit-scrollbar { height: 12px; }
    .case-table-scroll::-webkit-scrollbar-track { background: rgba(84,140,47,.12); }
    .case-table-scroll::-webkit-scrollbar-thumb { background: var(--green); border: 3px solid var(--cream); border-radius: 999px; }
    .case-table-scroll .table { width: 1900px; min-width: 1900px; table-layout: auto; }
    .case-table-scroll .table th:nth-child(1), .case-table-scroll .table td:nth-child(1) { min-width: 165px; }
    .case-table-scroll .table th:nth-child(2), .case-table-scroll .table td:nth-child(2) { min-width: 285px; }
    .case-table-scroll .table th:nth-child(3), .case-table-scroll .table td:nth-child(3) { min-width: 210px; }
    .case-table-scroll .table th:nth-child(4), .case-table-scroll .table td:nth-child(4) { min-width: 150px; }
    .case-table-scroll .table th:nth-child(5), .case-table-scroll .table td:nth-child(5) { min-width: 235px; }
    .case-table-scroll .table th:nth-child(6), .case-table-scroll .table td:nth-child(6) { min-width: 145px; }
    .case-table-scroll .table th:nth-child(7), .case-table-scroll .table td:nth-child(7) { min-width: 190px; }
    .case-table-scroll .table th:nth-child(8), .case-table-scroll .table td:nth-child(8) { min-width: 245px; }
    .case-table-scroll .table th:nth-child(9), .case-table-scroll .table td:nth-child(9) { min-width: 185px; }
    .case-table-scroll .table th:last-child, .case-table-scroll .table td:last-child { position: sticky; right: 0; z-index: 2; background: var(--cream); box-shadow: -8px 0 12px rgba(16,73,17,.08); }
    .case-table-scroll .table th:last-child { z-index: 3; background: #d9f2d7; }
    .table-scroll-guide { display:flex; align-items:center; justify-content:space-between; gap:14px; padding:12px 18px; color:var(--deep-green); background:rgba(167,243,208,.3); border-bottom:1px solid var(--border); font-size:.9rem; font-weight:750; }
    .table-scroll-controls { display:flex; gap:8px; flex:0 0 auto; }
    .table-scroll-btn { width:42px; height:38px; display:grid; place-items:center; padding:0; color:var(--deep-green); background:var(--cream); border:1px solid var(--green); border-radius:9px; }
    .table-scroll-btn:hover:not(:disabled) { color:var(--cream); background:var(--green); }
    .table-scroll-btn:disabled { opacity:.35; cursor:not-allowed; }
    .tracking { color: var(--green); font-size: 1.02rem; font-weight: 800; white-space: nowrap; }

    .status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 0.9rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-resolved { color: var(--cream); background: var(--green); }
    .status-investigating { color: var(--deep-green); background: var(--mint); }
    .status-pending { color: #674c00; background: rgba(255, 212, 73, 0.55); }
    .status-unreviewed { color: var(--deep-green); background: rgba(167, 243, 208, 0.45); border: 1px solid var(--border); }
    .status-hold { color: #674c00; background: #fff0bd; }
    .status-dismissed { color: #842029; background: #f8d7da; }
    .action-btn { width: 48px; height: 48px; display: inline-grid; place-items: center; padding: 0; border-radius: 11px; font-size: 1.1rem; }
    .review-btn { min-height: 48px; padding: 10px 14px; border-radius: 11px; font-weight: 800; white-space: nowrap; }
    .concern-link { color: var(--deep-green); font-weight: 750; text-decoration-thickness: 1px; text-underline-offset: 3px; }
    .detail-box { height: 100%; padding: 14px; background: rgba(167,243,208,.16); border: 1px solid var(--border); border-radius: 11px; }
    .detail-label { display: block; margin-bottom: 3px; font-size: .76rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; opacity: .66; }
    .statement-box { padding: 16px; white-space: pre-wrap; background: #fffdf8; border-left: 4px solid var(--green); border-radius: 9px; }
    .notice { padding: 16px 18px; color: var(--deep-green); background: var(--mint); border: 1px solid var(--border); border-radius: 12px; font-size: 1.05rem; }
    .empty { padding: 60px 24px; text-align: center; }
    .empty i { color: var(--green); font-size: 2.5rem; }
    .deadline { display: inline-flex; align-items: center; gap: 7px; font-size: .9rem; font-weight: 750; white-space: nowrap; }
    .deadline-overdue { padding: 7px 10px; color: #842029; background: #f8d7da; border-radius: 9px; }
    .overdue-row { --bs-table-bg: rgba(248,215,218,.22); }
    .overdue-alert { padding: 18px 20px; color: #842029; background: #f8d7da; border: 1px solid #f1aeb5; border-radius: 14px; }
    .activity-item { display: grid; grid-template-columns: 48px 1fr auto; gap: 15px; padding: 18px 0; border-bottom: 1px solid rgba(84,140,47,.14); }
    .activity-item:last-child { border-bottom: 0; }
    .activity-icon { width: 48px; height: 48px; display: grid; place-items: center; color: var(--deep-green); background: var(--mint); border-radius: 50%; }
    .activity-reason { padding: 9px 12px; margin-top: 8px; background: rgba(167,243,208,.18); border-left: 3px solid var(--green); border-radius: 7px; font-size: .93rem; }
    .modal-content { color: var(--deep-green); background: var(--cream); border: 1px solid var(--border); border-radius: 18px; }
    .modal-header { border-bottom-color: var(--border); }
    .modal-footer { border-top-color: var(--border); }
    .justification-note { padding: 12px 14px; background: rgba(255,212,73,.28); border-radius: 10px; font-size: .9rem; }

    @media (max-width: 991.98px) {
        .sidebar { width: 88px; padding: 24px 12px; overflow-x: hidden; }
        .brand { justify-content: center; padding-inline: 0; }
        .brand span, .user-details, .side-label, .link-text { display: none; }
        .brand i { margin: 0 !important; font-size: 1.45rem; }
        .sidebar-user { justify-content: center; padding: 12px 8px; }
        .sidebar-user i { font-size: 1.65rem; }
        .side-link { justify-content: center; gap: 0; padding-inline: 10px; }
        .side-link i { width: auto; }
        .content { margin-left: 88px; }
        .content { padding: 22px; }
    }

    @media (max-width: 575.98px) {
        body { font-size: 17px; }
        .hero { border-radius: 20px; }
        .panel-head { align-items: stretch !important; flex-direction: column; }
        .filter-select { width: 100%; }
        .table td, .table th { padding: 15px; }
    }

    @media (prefers-reduced-motion: reduce) {
        * { transition-duration: 0.01ms !important; }
    }
</style>
<link rel="stylesheet" href="assets/css/admin-theme.css">
<style>
    .sidebar .brand,
    .sidebar .brand:hover {
        color: var(--cream) !important;
        background: transparent !important;
        border-radius: 0 !important;
    }

    .sidebar .logout,
    .sidebar .logout:hover {
        color: var(--deep-green) !important;
        background: var(--yellow) !important;
    }

    @media (max-width: 767.98px) {
        .sidebar { min-height: 100vh !important; }
    }
</style>
</head>
<body>
<div class="layout">
  <aside class="sidebar d-flex flex-column">
    <a class="brand fw-bold" href="index.php"><i class="fa-solid fa-shield-halved"></i><span>GRAIL SYSTEM</span></a>
    <div class="sidebar-user" title="<?= htmlspecialchars($user_display_name,ENT_QUOTES,'UTF-8') ?>">
      <i class="fa-solid fa-circle-user"></i>
      <div class="user-details">
        <div class="user-name"><?= htmlspecialchars($user_display_name,ENT_QUOTES,'UTF-8') ?></div>
        <div class="user-role">Administrator</div>
      </div>
    </div>
    <div class="side-label">Workspace</div>
    <nav aria-label="Dashboard navigation">
      <a class="side-link active" href="dashboard.php" title="Dashboard"><i class="fa-solid fa-gauge"></i><span class="link-text">Dashboard</span></a>
      <a class="side-link" href="reports.php" title="Reports"><i class="fa-solid fa-chart-pie"></i><span class="link-text">Reports</span></a>
      <a class="side-link" href="records.php" title="Records"><i class="fa-solid fa-folder-open"></i><span class="link-text">Records</span></a>
      <a class="side-link" href="analytics.php" title="Analytics"><i class="fa-solid fa-chart-line"></i><span class="link-text">Analytics</span></a>
      <a class="side-link" href="generate_reports.php" title="Generate Report"><i class="fa-solid fa-file-export"></i><span class="link-text">Generate Report</span></a>
    </nav>
    <div class="side-footer mt-auto"><a class="side-link logout" href="logout.php" title="Logout"><i class="fa-solid fa-right-from-bracket"></i><span class="link-text">Logout</span></a></div>
  </aside>

  <main class="content">
    <?php if ($notice): ?>
        <div class="notice alert alert-dismissible fade show mb-4" role="status">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?>
            <button class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <section class="hero mb-4">
      <div class="hero-inner">
        <span class="eyebrow mb-3"><i class="fa-solid fa-chart-line"></i>System overview</span>
        <h1 class="fw-bold mb-2">Welcome, <?= htmlspecialchars($user_display_name,ENT_QUOTES,'UTF-8') ?>.</h1>
        <p class="hero-copy mb-3">Monitor grievances, review pending cases, and keep every concern moving toward a transparent resolution.</p>
        <?php if ($preview_mode): ?>
          
        <?php endif; ?>
      </div>
    </section>

    <?php if ($overdue_records > 0): ?><div class="overdue-alert mb-4" role="alert"><div class="d-flex align-items-start gap-3"><i class="fa-solid fa-triangle-exclamation fa-xl mt-2"></i><div><h2 class="h5 fw-bold mb-1"><?= $overdue_records ?> report<?= $overdue_records===1?' has':'s have' ?> passed the seven-day action deadline</h2><p class="mb-0">These reports have no recorded administrative action. They are prioritized at the top of the queue, and the delay warning is visible to the reporter.</p></div></div></div><?php endif; ?>

    <?php
    $stats = [
        ['Overdue action', $overdue_records, 'fa-triangle-exclamation'],
        ['Total records', $total_records, 'fa-folder-open'],
        ['Needs review', $total_reports, 'fa-file-circle-exclamation'],
        ['Resolved', $resolved_records, 'fa-circle-check'],
    ];
    ?>
    <section class="row g-3 g-xl-4 mb-4" aria-label="Statistics">
        <?php foreach ($stats as [$label, $value, $icon]): ?>
            <div class="col-sm-6 col-xl-3">
                <article class="stat-card d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="stat-label mb-1"><?= htmlspecialchars($label) ?></div>
                        <div class="stat-value"><?= (int) $value ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid <?= htmlspecialchars($icon) ?>"></i>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="panel" aria-labelledby="records-heading">
      <div class="panel-head d-flex align-items-center justify-content-between gap-3">
        <div>
          <h2 id="records-heading" class="fw-bold mb-1">
            <i class="fa-solid fa-list-check me-2"></i>Case action queue
          </h2>
          <p class="mb-0 opacity-75"><?= count($recent_records) ?> of <?= $total_records ?> records shown</p>
        </div>
        <div class="d-flex gap-2">
          <form method="get" action="dashboard.php">
            <label class="visually-hidden" for="statusFilter">Filter status</label>
            <select id="statusFilter" name="status" class="form-select filter-select" onchange="this.form.submit()">
              <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All statuses</option>
              <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
              <option value="under investigation" <?= $filter === 'under investigation' ? 'selected' : '' ?>>Investigating</option>
              <option value="unreviewed" <?= $filter === 'unreviewed' ? 'selected' : '' ?>>Unreviewed</option>
              <option value="resolved" <?= $filter === 'resolved' ? 'selected' : '' ?>>Resolved</option>
              <option value="on hold" <?= $filter === 'on hold' ? 'selected' : '' ?>>On hold</option>
              <option value="dismissed" <?= $filter === 'dismissed' ? 'selected' : '' ?>>Dismissed</option>
            </select>
          </form>
          <?php if ($preview_mode): ?>
            <form method="post" action="dashboard.php">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
              <button class="btn btn-outline-success action-btn" name="action" value="reset_preview" title="Reset preview data" aria-label="Reset preview data">
                <i class="fa-solid fa-rotate-left"></i>
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
      <div class="table-scroll-guide">
    
        <div class="table-scroll-controls" aria-label="Table scrolling controls">
          <button type="button" class="table-scroll-btn" id="scrollTableLeft" aria-label="Scroll table left"><i class="fa-solid fa-chevron-left"></i></button>
          <button type="button" class="table-scroll-btn" id="scrollTableRight" aria-label="Scroll table right"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
      </div>
      <div class="table-responsive case-table-scroll" tabindex="0" role="region" aria-label="Case action queue. Swipe or drag horizontally to view every column.">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Tracking ID</th>
              <th>Concern</th>
              <th>Submitted by</th>
              <th>Date</th>
              <th>Action deadline</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Last action</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$recent_records): ?>
              <tr>
                <td colspan="9">
                  <div class="empty">
                    <i class="fa-solid fa-filter-circle-xmark mb-3"></i>
                    <h3 class="h5 fw-bold">No matching records</h3>
                    <p class="mb-0 opacity-75">Choose a different status filter.</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>

            <?php foreach ($recent_records as $row): ?>
              <?php [$status_label, $status_class, $status_icon] = status_meta((string) $row['status']); ?>
              <tr class="<?= !empty($row['is_overdue']) ? 'overdue-row' : '' ?>">
                <td><span class="tracking"><?= htmlspecialchars($row['tracking_token'] ?: 'GRL-' . $row['id'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?php if(!$preview_mode): ?><a class="concern-link" href="view.php?id=<?= (int)$row['id'] ?>"><?= htmlspecialchars($row['title'],ENT_QUOTES,'UTF-8') ?></a><?php else: ?><button type="button" class="btn btn-link concern-link p-0 text-start preview-details" data-bs-toggle="modal" data-bs-target="#detailsModal" data-record='<?= htmlspecialchars(json_encode($row),ENT_QUOTES,'UTF-8') ?>'><?= htmlspecialchars($row['title'],ENT_QUOTES,'UTF-8') ?></button><?php endif; ?></td>
                <td><i class="fa-solid fa-user-pen me-2 opacity-50"></i><?= htmlspecialchars($row['user'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(date('M d, Y', strtotime($row['created_at']))) ?></td>
                <td><?php if(!empty($row['is_overdue'])): ?><span class="deadline deadline-overdue"><i class="fa-solid fa-triangle-exclamation"></i>Overdue since <?= htmlspecialchars(date('M d',strtotime($row['deadline_at']))) ?></span><?php elseif(!empty($row['admin_action_taken'])): ?><span class="deadline text-success"><i class="fa-solid fa-check"></i>Action recorded</span><?php else: ?><span class="deadline"><i class="fa-regular fa-calendar"></i><?= htmlspecialchars(date('M d, Y',strtotime($row['deadline_at']))) ?></span><?php endif; ?></td>
                <td><span class="status <?= in_array(strtolower((string)($row['priority']??'')),['high','critical'],true)?'status-dismissed':'status-unreviewed' ?>"><i class="fa-solid fa-flag"></i><?= htmlspecialchars(ucfirst((string)($row['priority']??'medium'))) ?></span></td>
                <td>
                  <span class="status status-<?= $status_class ?>">
                    <i class="fa-solid <?= $status_icon ?>"></i><?= $status_label ?>
                  </span>
                </td>
                <td>
                  <?php if (!empty($row['last_action_at'])): ?>
                    <div class="fw-bold"><i class="fa-solid fa-user-shield me-1"></i><?= htmlspecialchars((string)$row['last_action_by'], ENT_QUOTES, 'UTF-8') ?></div>
                    <time class="small opacity-75" datetime="<?= htmlspecialchars(date('c', strtotime((string)$row['last_action_at'])), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(date('M d, Y g:i:s A', strtotime((string)$row['last_action_at'])), ENT_QUOTES, 'UTF-8') ?></time>
                  <?php else: ?>
                    <span class="small opacity-75"><i class="fa-solid fa-hourglass-start me-1"></i>No action recorded</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if (!$preview_mode): ?><a href="view.php?id=<?= (int)$row['id'] ?>" class="btn btn-outline-secondary review-btn me-1" title="Review full report"><i class="fa-solid fa-eye me-2"></i>Review</a><?php else: ?><button type="button" class="btn btn-outline-secondary review-btn me-1 preview-details" data-bs-toggle="modal" data-bs-target="#detailsModal" data-record='<?= htmlspecialchars(json_encode($row),ENT_QUOTES,'UTF-8') ?>'><i class="fa-solid fa-eye me-2"></i>Review</button><?php endif; ?>
                  <button type="button" class="btn btn-outline-success action-btn status-action" data-bs-toggle="modal" data-bs-target="#statusModal" data-record-id="<?= (int)$row['id'] ?>" data-tracking="<?= htmlspecialchars($row['tracking_token'],ENT_QUOTES,'UTF-8') ?>" data-current-status="<?= htmlspecialchars(strtolower((string)$row['status']),ENT_QUOTES,'UTF-8') ?>" data-current-priority="<?= htmlspecialchars(strtolower((string)($row['priority']??'medium')),ENT_QUOTES,'UTF-8') ?>" title="Record case decision" aria-label="Record decision for <?= htmlspecialchars($row['tracking_token']) ?>"><i class="fa-solid fa-pen-to-square"></i></button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="panel mt-4" aria-labelledby="activity-heading">
      <div class="panel-head"><h2 id="activity-heading" class="fw-bold mb-1"><i class="fa-solid fa-clock-rotate-left me-2"></i>Status activity</h2><p class="mb-0 opacity-75">Audited reasons for recent administrative decisions</p></div>
      <div class="px-4">
        <?php if (!$status_activity): ?><div class="empty"><i class="fa-solid fa-clipboard-check mb-3"></i><h3 class="h5 fw-bold">No status changes yet</h3><p class="mb-0 opacity-75">Administrative decisions will appear here.</p></div><?php endif; ?>
        <?php foreach ($status_activity as $activity): ?>
          <article class="activity-item"><div class="activity-icon"><i class="fa-solid fa-arrow-right-arrow-left"></i></div><div><div class="fw-bold"><?= htmlspecialchars($activity['tracking_token'] ?: 'Record',ENT_QUOTES,'UTF-8') ?>: <?= htmlspecialchars(ucwords((string)$activity['old_status'])) ?> → <?= htmlspecialchars(ucwords((string)$activity['new_status'])) ?></div><?php if(!empty($activity['new_priority']) && ($activity['old_priority']??'')!==$activity['new_priority']): ?><div class="small fw-bold mt-1">Priority: <?= htmlspecialchars(ucfirst((string)$activity['old_priority'])) ?> → <?= htmlspecialchars(ucfirst((string)$activity['new_priority'])) ?></div><?php endif; ?><div class="activity-reason"><strong>Action justification:</strong> <?= nl2br(htmlspecialchars((string)$activity['justification'],ENT_QUOTES,'UTF-8')) ?><?php if(!empty($activity['priority_justification'])): ?><br><strong>Priority justification:</strong> <?= nl2br(htmlspecialchars((string)$activity['priority_justification'],ENT_QUOTES,'UTF-8')) ?><?php endif; ?></div><div class="small opacity-75 mt-1">Changed by <?= htmlspecialchars((string)$activity['changed_by'],ENT_QUOTES,'UTF-8') ?></div></div><time class="small opacity-75 text-nowrap"><?= htmlspecialchars(date('M d, Y g:i A',strtotime((string)$activity['created_at']))) ?></time></article>
        <?php endforeach; ?>
      </div>
    </section>
  </main>
</div>

<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalTitle" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><h2 class="modal-title h4 fw-bold" id="detailsModalTitle">Review submitted report</h2><div class="small opacity-75" id="detailTracking"></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div class="row g-3 mb-4"><div class="col-md-6"><div class="detail-box"><span class="detail-label">Reporter</span><strong id="detailReporter"></strong></div></div><div class="col-md-3"><div class="detail-box"><span class="detail-label">Category</span><strong id="detailCategory"></strong></div></div><div class="col-md-3"><div class="detail-box"><span class="detail-label">Suggested priority</span><strong id="detailPriority"></strong></div></div><div class="col-md-6"><div class="detail-box"><span class="detail-label">Incident date</span><strong id="detailIncidentDate"></strong></div></div><div class="col-md-6"><div class="detail-box"><span class="detail-label">Incident location</span><strong id="detailLocation"></strong></div></div></div><h3 class="h6 fw-bold text-uppercase">Structured risk assessment</h3><div class="detail-box mb-4" id="detailRisk"></div><h3 class="h6 fw-bold text-uppercase">Reporter statement</h3><div class="statement-box mb-4" id="detailDescription"></div><h3 class="h6 fw-bold text-uppercase">Supporting evidence</h3><div id="detailEvidence" class="detail-box"></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button><button type="button" class="btn btn-success" id="detailsTakeAction"><i class="fa-solid fa-gavel me-2"></i>Decide and take action</button></div></div></div></div>

<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post" action="<?= $preview_mode ? 'dashboard.php' : 'dashboard.php?live=1' ?>"><div class="modal-header"><div><h2 class="modal-title h4 fw-bold" id="statusModalTitle">Record case decision</h2><div class="small opacity-75" id="statusCaseLabel"></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><input type="hidden" name="action" value="update_status"><input type="hidden" name="record_id" id="statusRecordId"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><div class="row g-3 mb-3"><div class="col-sm-6"><label for="newStatus" class="form-label fw-bold">Status decision</label><select id="newStatus" name="new_status" class="form-select" required><?php foreach($allowed_statuses as $option): ?><option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars(ucwords($option)) ?></option><?php endforeach; ?></select></div><div class="col-sm-6"><label for="newPriority" class="form-label fw-bold">Confirm priority</label><select id="newPriority" name="new_priority" class="form-select" required><?php foreach($allowed_priorities as $option): ?><option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars(ucfirst($option)) ?></option><?php endforeach; ?></select></div></div><div class="mb-3" id="priorityReasonGroup" hidden><label for="priorityJustification" class="form-label fw-bold">Why are you changing the priority?</label><textarea id="priorityJustification" name="priority_justification" class="form-control" rows="3" minlength="10" maxlength="1000" placeholder="Explain the risk evidence supporting this priority change."></textarea><div class="form-text">Required when the selected priority differs from the system suggestion.</div></div><div class="mb-3"><label for="statusJustification" class="form-label fw-bold">Administrative action justification</label><textarea id="statusJustification" name="justification" class="form-control" rows="4" minlength="10" maxlength="1000" required placeholder="Explain why this status is appropriate and what action was taken or is required."></textarea><div class="form-text">Required for every administrative decision · Minimum 10 characters</div></div><div class="justification-note"><i class="fa-solid fa-shield-halved me-2"></i>Status and priority decisions become part of the permanent case activity record.</div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk me-2"></i>Save audited decision</button></div></form></div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const caseTableScroll = document.querySelector('.case-table-scroll');
if (caseTableScroll) {
  let dragging = false, startX = 0, startScrollLeft = 0;
  caseTableScroll.addEventListener('pointerdown', event => {
    if (event.pointerType === 'mouse' && event.button === 0 && !event.target.closest('a,button,input,select,textarea,label')) {
      dragging = true;
      startX = event.clientX;
      startScrollLeft = caseTableScroll.scrollLeft;
      caseTableScroll.setPointerCapture(event.pointerId);
    }
  });
  caseTableScroll.addEventListener('pointermove', event => {
    if (dragging) caseTableScroll.scrollLeft = startScrollLeft - (event.clientX - startX);
  });
  caseTableScroll.addEventListener('pointerup', () => { dragging = false; });
  caseTableScroll.addEventListener('pointercancel', () => { dragging = false; });
  const leftButton = document.getElementById('scrollTableLeft');
  const rightButton = document.getElementById('scrollTableRight');
  const updateScrollButtons = () => {
    const maxScroll = Math.max(0, caseTableScroll.scrollWidth - caseTableScroll.clientWidth);
    leftButton.disabled = caseTableScroll.scrollLeft <= 1;
    rightButton.disabled = caseTableScroll.scrollLeft >= maxScroll - 1;
  };
  leftButton.addEventListener('click', () => caseTableScroll.scrollBy({left: -420, behavior: 'smooth'}));
  rightButton.addEventListener('click', () => caseTableScroll.scrollBy({left: 420, behavior: 'smooth'}));
  caseTableScroll.addEventListener('scroll', updateScrollButtons, {passive:true});
  window.addEventListener('resize', updateScrollButtons);
  updateScrollButtons();
}
document.querySelectorAll('.status-action').forEach(button => button.addEventListener('click', () => {
  document.getElementById('statusRecordId').value = button.dataset.recordId;
  document.getElementById('statusCaseLabel').textContent = button.dataset.tracking + ' · Current: ' + button.dataset.currentStatus.replace(/\b\w/g, c => c.toUpperCase());
  const select = document.getElementById('newStatus');
  select.value = button.dataset.currentStatus;
  const prioritySelect = document.getElementById('newPriority');
  prioritySelect.dataset.original = button.dataset.currentPriority;
  prioritySelect.value = button.dataset.currentPriority;
  document.getElementById('statusJustification').value = '';
  document.getElementById('priorityJustification').value = '';
  document.getElementById('priorityReasonGroup').hidden = true;
  document.getElementById('priorityJustification').required = false;
}));
document.getElementById('newPriority').addEventListener('change', event => {
  const changed = event.target.value !== event.target.dataset.original;
  document.getElementById('priorityReasonGroup').hidden = !changed;
  document.getElementById('priorityJustification').required = changed;
});
let reviewedRecordId = null;
document.querySelectorAll('.preview-details').forEach(button => button.addEventListener('click', () => {
  const record = JSON.parse(button.dataset.record); reviewedRecordId = String(record.id);
  document.getElementById('detailTracking').textContent = record.tracking_token + ' · ' + record.title;
  document.getElementById('detailReporter').textContent = record.user || 'Anonymous';
  document.getElementById('detailCategory').textContent = record.category || 'Not specified';
  document.getElementById('detailPriority').textContent = record.priority || 'Not specified';
  document.getElementById('detailIncidentDate').textContent = record.incident_date || 'Not specified';
  document.getElementById('detailLocation').textContent = record.location || 'Not specified';
  document.getElementById('detailDescription').textContent = record.description || 'No statement provided.';
  const risks = [['risk_danger','Anyone currently in danger'],['risk_ongoing','Incident is ongoing'],['risk_repeated','Repeated incident'],['risk_retaliation','Threat of retaliation'],['risk_multiple_people','Multiple people affected'],['risk_urgent','Urgent intervention required']].filter(([key]) => Number(record[key] || 0) === 1).map(([,label]) => label);
  document.getElementById('detailRisk').innerHTML = '<strong>Risk score: ' + Number(record.risk_score || 0) + '</strong><br>' + (risks.length ? risks.map(label => '<span class="badge-soft me-1 mt-2">' + label + '</span>').join('') : '<span class="opacity-75">No structured risk indicators selected.</span>');
  document.getElementById('detailEvidence').innerHTML = record.evidence ? '<i class="fa-solid fa-paperclip me-2"></i>Evidence file attached' : '<span class="opacity-75"><i class="fa-solid fa-circle-info me-2"></i>No supporting file was attached.</span>';
}));
document.getElementById('detailsTakeAction').addEventListener('click', () => {
  const trigger = [...document.querySelectorAll('.status-action')].find(button => button.dataset.recordId === reviewedRecordId);
  bootstrap.Modal.getInstance(document.getElementById('detailsModal')).hide();
  setTimeout(() => trigger?.click(), 250);
});
const requestedActionId = new URLSearchParams(window.location.search).get('action_for');
if (requestedActionId) {
  const requestedTrigger = [...document.querySelectorAll('.status-action')].find(button => button.dataset.recordId === requestedActionId);
  if (requestedTrigger) setTimeout(() => requestedTrigger.click(), 150);
}
</script>
</body>
</html>
