<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$user_display_name = trim((string) ($_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? $_SESSION['admin_username'] ?? ''));
if ($user_display_name === '') { $user_display_name = 'Preview User'; }

require_once 'includes/case_data.php';
$report_records = array_values(array_filter(grail_case_records(), static fn($r) => in_array(strtolower((string)$r['status']), ['pending','unreviewed'], true)));
$overdue_count = count(array_filter($report_records, static fn($r) => !empty($r['is_overdue'])));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>GRAIL System | Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root { --cream:#fff8ef; --yellow:#ffd449; --mint:#a7f3d0; --green:#548c2f; --deep-green:#104911; --border:rgba(84,140,47,.24); }
    * { box-sizing:border-box; }
    body { min-height:100vh; margin:0; color:var(--deep-green); background-color:var(--cream); background-image:radial-gradient(circle at 0 12%,rgba(167,243,208,.45),transparent 28%),radial-gradient(circle at 100% 85%,rgba(84,140,47,.15),transparent 30%); font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif; font-size:18px; line-height:1.6; }
    .layout { min-height:100vh; }
    .sidebar { position:fixed; inset:0 auto 0 0; z-index:1030; width:285px; height:100vh; padding:30px 18px; overflow-y:auto; color:var(--cream); background:var(--deep-green); box-shadow:10px 0 26px rgba(16,73,17,.13); }
    .brand { display:flex; align-items:center; gap:11px; padding:4px 12px 24px; margin-bottom:22px; color:var(--cream); border-bottom:1px solid rgba(255,248,239,.16); text-decoration:none; font-size:1.4rem; letter-spacing:.02em; }
    .brand i { color:var(--yellow); }
    .brand:hover { color:var(--cream); }
    .sidebar-user { display:flex; align-items:center; gap:12px; padding:14px; margin-bottom:28px; color:var(--cream); background:rgba(167,243,208,.12); border:1px solid rgba(167,243,208,.18); border-radius:14px; }
    .sidebar-user i { color:var(--yellow); font-size:2rem; }
    .user-name { overflow:hidden; font-weight:750; line-height:1.2; text-overflow:ellipsis; white-space:nowrap; }
    .user-role { color:var(--mint); font-size:.78rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
    .side-label { padding:0 16px; margin-bottom:16px; color:var(--mint); font-size:.85rem; font-weight:800; letter-spacing:.11em; text-transform:uppercase; }
    .side-link { display:flex; align-items:center; gap:14px; min-height:56px; padding:14px 17px; margin-bottom:7px; color:rgba(255,248,239,.86); border-radius:13px; text-decoration:none; font-size:1.05rem; font-weight:650; transition:.2s; }
    .side-link i { width:24px; text-align:center; font-size:1.15rem; }
    .side-link:hover { color:var(--cream); background:rgba(167,243,208,.12); }
    .side-link.active { color:var(--deep-green); background:var(--mint); }
    .side-footer { margin-top:auto; }
    .logout,.logout:hover { color:var(--deep-green); background:var(--yellow); }
    .content { min-width:0; margin-left:285px; padding:clamp(24px,3vw,48px); }
    .hero { position:relative; overflow:hidden; padding:clamp(32px,5vw,54px); background:var(--cream); border:1px solid var(--border); border-radius:26px; box-shadow:0 16px 38px rgba(16,73,17,.1); }
    .hero::after { content:""; position:absolute; right:-70px; top:-110px; width:280px; height:280px; border-radius:50%; background:var(--mint); opacity:.55; }
    .hero-inner { position:relative; z-index:1; }
    .eyebrow { display:inline-flex; align-items:center; gap:9px; padding:9px 15px; color:var(--deep-green); background:var(--mint); border-radius:999px; font-size:.95rem; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
    .hero h1 { font-size:clamp(2.15rem,4vw,3.25rem); letter-spacing:-.035em; }
    .hero-copy { max-width:760px; font-size:1.15rem; line-height:1.75; opacity:.8; }
    .stat-card,.panel { background:var(--cream); border:1px solid var(--border); border-radius:20px; box-shadow:0 8px 22px rgba(16,73,17,.07); }
    .stat-card { height:100%; min-height:135px; padding:24px; }
    .stat-icon { width:58px; height:58px; display:grid; place-items:center; flex:0 0 58px; color:var(--deep-green); background:var(--mint); border-radius:50%; font-size:1.35rem; }
    .stat-label { font-size:.85rem; font-weight:800; letter-spacing:.05em; text-transform:uppercase; opacity:.72; }
    .stat-value { font-size:2rem; font-weight:850; line-height:1.2; }
    .panel { overflow:hidden; }
    .panel-head { padding:26px 28px; border-bottom:1px solid var(--border); }
    .panel-head h2 { font-size:1.5rem; }
    .search-box { min-width:250px; min-height:50px; padding:10px 15px; color:var(--deep-green); background:#fffdf8; border:1px solid var(--border); border-radius:11px; }
    .search-box:focus { outline:0; border-color:var(--green); box-shadow:0 0 0 3px rgba(84,140,47,.15); }
    .table { --bs-table-bg:transparent; --bs-table-hover-bg:rgba(167,243,208,.16); color:var(--deep-green); font-size:1rem; }
    .table th { padding:18px; color:var(--deep-green); background:rgba(167,243,208,.3); border-color:var(--border); font-size:.88rem; letter-spacing:.04em; text-transform:uppercase; white-space:nowrap; }
    .table td { padding:20px 18px; border-color:rgba(84,140,47,.13); vertical-align:middle; }
    .tracking { color:var(--green); font-weight:800; white-space:nowrap; }
    .status,.priority { display:inline-flex; align-items:center; gap:7px; padding:8px 12px; border-radius:999px; font-size:.86rem; font-weight:800; white-space:nowrap; }
    .status { color:#674c00; background:rgba(255,212,73,.55); }
    .priority-high { color:#842029; background:#f8d7da; }
    .priority-medium { color:#674c00; background:rgba(255,212,73,.45); }
    .priority-low { color:var(--deep-green); background:rgba(167,243,208,.55); }
    .action-btn { width:44px; height:44px; display:inline-grid; place-items:center; padding:0; border-radius:11px; }
    .preview-note { color:var(--deep-green); background:rgba(167,243,208,.38); border:1px solid var(--border); border-radius:12px; }
    @media (max-width:991.98px) {
        .sidebar { width:88px; padding:24px 12px; overflow-x:hidden; }
        .brand { justify-content:center; padding-inline:0; }
        .brand span,.user-details,.side-label,.link-text { display:none; }
        .brand i { font-size:1.45rem; }
        .sidebar-user { justify-content:center; padding:12px 8px; }
        .sidebar-user i { font-size:1.65rem; }
        .side-link { justify-content:center; gap:0; padding-inline:10px; }
        .side-link i { width:auto; }
        .content { margin-left:88px; padding:22px; }
    }
    @media (max-width:575.98px) { body { font-size:17px; } .hero { border-radius:20px; } .panel-head { align-items:stretch!important; flex-direction:column; } .search-box { width:100%; min-width:0; } .table td,.table th { padding:15px; } }
</style>
</head>
<body>
<div class="layout">
  <aside class="sidebar d-flex flex-column">
    <a class="brand fw-bold" href="index.php"><i class="fa-solid fa-shield-halved"></i><span>GRAIL SYSTEM</span></a>
    <div class="sidebar-user" title="<?= htmlspecialchars($user_display_name,ENT_QUOTES,'UTF-8') ?>">
      <i class="fa-solid fa-circle-user"></i>
      <div class="user-details"><div class="user-name"><?= htmlspecialchars($user_display_name,ENT_QUOTES,'UTF-8') ?></div><div class="user-role">Administrator</div></div>
    </div>
    <div class="side-label">Workspace</div>
    <nav aria-label="Dashboard navigation">
      <a class="side-link" href="dashboard.php" title="Dashboard"><i class="fa-solid fa-gauge"></i><span class="link-text">Dashboard</span></a>
      <a class="side-link active" href="reports.php" title="Reports"><i class="fa-solid fa-chart-pie"></i><span class="link-text">Reports</span></a>
      <a class="side-link" href="records.php" title="Records"><i class="fa-solid fa-folder-open"></i><span class="link-text">Records</span></a>
      <a class="side-link" href="analytics.php" title="Analytics"><i class="fa-solid fa-chart-line"></i><span class="link-text">Analytics</span></a>
      <a class="side-link" href="generate_reports.php" title="Generate Report"><i class="fa-solid fa-file-export"></i><span class="link-text">Generate Report</span></a>
    </nav>
    <div class="side-footer mt-auto"><a class="side-link logout" href="logout.php" title="Logout"><i class="fa-solid fa-right-from-bracket"></i><span class="link-text">Logout</span></a></div>
  </aside>

  <main class="content">
    <section class="hero mb-4">
      <div class="hero-inner">
        <span class="eyebrow mb-3"><i class="fa-solid fa-file-circle-exclamation"></i>Reports workspace</span>
        <h1 class="fw-bold mb-2">Pending reports</h1>
        <p class="hero-copy mb-0">Review incoming grievances, assess their priority, and prepare each concern for the next stage of resolution.</p>
      </div>
    </section>

   
    <section class="row g-4 mb-4">
      <div class="col-sm-6 col-xl-4"><div class="stat-card d-flex align-items-center gap-3"><div class="stat-icon"><i class="fa-solid fa-inbox"></i></div><div><div class="stat-label">Awaiting review</div><div class="stat-value"><?= count($report_records) ?></div></div></div></div>
      <div class="col-sm-6 col-xl-4"><div class="stat-card d-flex align-items-center gap-3"><div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div><div><div class="stat-label">High priority</div><div class="stat-value"><?= count(array_filter($report_records, static fn($r) => $r['priority'] === 'High')) ?></div></div></div></div>
      <div class="col-sm-6 col-xl-4"><div class="stat-card d-flex align-items-center gap-3"><div class="stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div><div><div class="stat-label">Overdue action</div><div class="stat-value"><?= $overdue_count ?></div></div></div></div>
    </section>

    <section class="panel">
      <div class="panel-head d-flex justify-content-between align-items-center gap-3">
        <div><h2 class="fw-bold mb-1">Awaiting review</h2><p class="mb-0 opacity-75">Sample grievance reports requiring attention.</p></div>
        <label><span class="visually-hidden">Search reports</span><input id="reportSearch" class="search-box" type="search" placeholder="Search reports..."></label>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="reportsTable">
          <thead><tr><th>Tracking ID</th><th>Concern</th><th>Submitted by</th><th>Date created</th><th>Action deadline</th><th>Priority</th><th>Status</th><th class="text-center">Preview</th></tr></thead>
          <tbody>
          <?php foreach ($report_records as $row): ?>
            <tr style="<?= !empty($row['is_overdue']) ? 'background:#f8d7da' : '' ?>">
              <td><span class="tracking"><?= htmlspecialchars($row['tracking_token'],ENT_QUOTES,'UTF-8') ?></span></td>
              <td class="fw-semibold"><?= htmlspecialchars($row['title'],ENT_QUOTES,'UTF-8') ?></td>
              <td><i class="fa-solid fa-user-pen me-2 opacity-50"></i><?= htmlspecialchars($row['user'],ENT_QUOTES,'UTF-8') ?></td>
              <td><?= htmlspecialchars(date('M d, Y',strtotime($row['created_at'])),ENT_QUOTES,'UTF-8') ?></td>
              <td><span class="status"><i class="fa-solid <?= !empty($row['is_overdue']) ? 'fa-triangle-exclamation' : 'fa-calendar' ?>"></i><?= !empty($row['is_overdue']) ? 'Overdue since ' : '' ?><?= htmlspecialchars(date('M d, Y',strtotime($row['deadline_at']))) ?></span></td>
              <td><span class="priority priority-<?= strtolower($row['priority']) ?>"><?= htmlspecialchars(ucfirst($row['priority']),ENT_QUOTES,'UTF-8') ?></span></td>
              <td><span class="status"><i class="fa-solid fa-clock"></i>Pending</span></td>
              <td class="text-center"><button type="button" class="btn btn-outline-success action-btn" title="Preview report" data-bs-toggle="modal" data-bs-target="#reportModal" data-report="<?= htmlspecialchars($row['tracking_token'].' — '.$row['title'],ENT_QUOTES,'UTF-8') ?>"><i class="fa-solid fa-eye"></i></button></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4"><div class="modal-header"><h2 class="modal-title fs-5 fw-bold">Report preview</h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p id="modalReportTitle" class="fw-semibold mb-2"></p><p class="mb-0 opacity-75">This is a design-only preview. Full report details will be available when database integration is enabled.</p></div><div class="modal-footer"><button type="button" class="btn btn-success" data-bs-dismiss="modal">Close</button></div></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('reportSearch').addEventListener('input', function () {
  const query = this.value.toLowerCase();
  document.querySelectorAll('#reportsTable tbody tr').forEach(row => { row.hidden = !row.textContent.toLowerCase().includes(query); });
});
document.getElementById('reportModal').addEventListener('show.bs.modal', function (event) {
  document.getElementById('modalReportTitle').textContent = event.relatedTarget.dataset.report;
});
</script>
</body>
</html>
