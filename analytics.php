<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$user_display_name = trim((string) ($_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? $_SESSION['admin_username'] ?? ''));
if ($user_display_name === '') { $user_display_name = 'Preview User'; }
require_once 'includes/case_data.php';
$caseRecords = grail_case_records();
$overdueCases = count(array_filter($caseRecords, static fn($r) => !empty($r['is_overdue'])));

$totalCases = count($caseRecords);
$administrativelyClosed = count(array_filter($caseRecords, static fn($r)=>in_array(strtolower((string)$r['status']),['resolved','dismissed','completed','approved'],true)));
$actionCompleted = count(array_filter($caseRecords, static fn($r)=>!empty($r['admin_action_taken'])));
$reporterConfirmed = 0; $reporterUnresolved = 0;
if (isset($_SESSION['admin_logged_in']) && empty($_SESSION['admin_preview'])) {
    require_once 'db.php';
    $feedbackCounts=$pdo->query('SELECT outcome,COUNT(*) total FROM resolution_feedback GROUP BY outcome')->fetchAll(PDO::FETCH_KEY_PAIR);
    $reporterConfirmed=(int)($feedbackCounts['confirmed_improvement']??0);
    $reporterUnresolved=(int)($feedbackCounts['unresolved']??0);
}
$closedWithoutConfirmation = max(0,$administrativelyClosed-$reporterConfirmed-$reporterUnresolved);
$administrativeClosureRate = $totalCases ? round(($administrativelyClosed / $totalCases) * 100, 1) : 0;
$reporterConfirmedRate = $administrativelyClosed ? round(($reporterConfirmed / $administrativelyClosed) * 100, 1) : 0;
$confirmationGap = round($administrativeClosureRate - $reporterConfirmedRate, 1);

$categories=[];
foreach($caseRecords as $record){$label=ucfirst((string)($record['category']??'Other'));$categories[$label]=($categories[$label]??0)+1;}
if(!$categories){$categories=['No data'=>1];}
$palette=['#104911','#548c2f','#ffd449','#a7f3d0','#8bc34a','#ffb74d','#4db6ac','#9575cd'];
$categoryColors=[];$colorIndex=0;foreach($categories as $label=>$count){$categoryColors[$label]=$palette[$colorIndex++%count($palette)];}
$categoryTotal = array_sum($categories);
$pieStops = [];
$pieStart = 0;
foreach ($categories as $category => $count) {
    $pieEnd = $pieStart + (($count / $categoryTotal) * 100);
    $pieStops[] = $categoryColors[$category] . ' ' . round($pieStart, 2) . '% ' . round($pieEnd, 2) . '%';
    $pieStart = $pieEnd;
}
$pieGradient = implode(', ', $pieStops);
$largestCategory = (string)array_search(max($categories), $categories, true);
$generatedAt = date('F j, Y \a\t g:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>GRAIL System | Analytics</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/admin-theme.css">
<style>
  :root { --cream:#fff8ef; --yellow:#ffd449; --mint:#a7f3d0; --green:#548c2f; --deep-green:#104911; --border:rgba(84,140,47,.24); }
  * { box-sizing:border-box; }
  body { min-height:100vh; margin:0; color:var(--deep-green); background-color:var(--cream); background-image:radial-gradient(circle at 0 12%,rgba(167,243,208,.45),transparent 28%),radial-gradient(circle at 100% 85%,rgba(84,140,47,.15),transparent 30%); font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif; font-size:18px; line-height:1.6; }
  .sidebar { position:fixed; inset:0 auto 0 0; z-index:1030; width:285px; height:100vh; padding:30px 18px; overflow-y:auto; color:var(--cream); background:var(--deep-green)!important; box-shadow:10px 0 26px rgba(16,73,17,.13); }
  .brand { display:flex; align-items:center; gap:11px; padding:4px 12px 24px; margin-bottom:22px; color:var(--cream)!important; border-bottom:1px solid rgba(255,248,239,.16); text-decoration:none; font-size:1.4rem; letter-spacing:.02em; background:transparent!important; }
  .brand i { color:var(--yellow); }
  .sidebar-user { display:flex; align-items:center; gap:12px; padding:14px; margin-bottom:28px; color:var(--cream); background:rgba(167,243,208,.12); border:1px solid rgba(167,243,208,.18); border-radius:14px; }
  .sidebar-user i { color:var(--yellow); font-size:2rem; }
  .user-name { overflow:hidden; font-weight:750; line-height:1.2; text-overflow:ellipsis; white-space:nowrap; }
  .user-role { color:var(--mint); font-size:.78rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
  .side-label { padding:0 16px; margin-bottom:16px; color:var(--mint); font-size:.85rem; font-weight:800; letter-spacing:.11em; text-transform:uppercase; }
  .side-link { display:flex; align-items:center; gap:14px; min-height:56px; padding:14px 17px; margin-bottom:7px; color:rgba(255,248,239,.86)!important; border-radius:13px!important; text-decoration:none; font-size:1.05rem; font-weight:650; transition:.2s; }
  .side-link i { width:24px; text-align:center; font-size:1.15rem; }
  .side-link:hover { color:var(--cream)!important; background:rgba(167,243,208,.12)!important; }
  .side-link.active { color:var(--deep-green)!important; background:var(--mint)!important; }
  .side-footer { margin-top:auto; }
  .sidebar .logout,
  .sidebar .logout:hover { color:var(--deep-green)!important; background:var(--yellow)!important; }
  .content { min-width:0; margin-left:285px; padding:clamp(24px,3vw,48px); }
  .hero { position:relative; overflow:hidden; padding:clamp(30px,5vw,52px); background:var(--cream); border:1px solid var(--border); border-radius:26px; box-shadow:0 16px 38px rgba(16,73,17,.1); }
  .hero::after { content:""; position:absolute; right:-70px; top:-110px; width:280px; height:280px; border-radius:50%; background:var(--mint); opacity:.55; }
  .hero-inner { position:relative; z-index:1; }
  .eyebrow { display:inline-flex; align-items:center; gap:9px; padding:9px 15px; color:var(--deep-green); background:var(--mint); border-radius:999px; font-size:.95rem; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
  .hero h1 { font-size:clamp(2.15rem,4vw,3.25rem); letter-spacing:-.035em; }
  .hero-copy { max-width:760px; font-size:1.15rem; line-height:1.75; opacity:.8; }
  .print-btn { min-height:52px; padding:12px 20px; color:var(--deep-green); background:var(--yellow); border:0; border-radius:12px; font-weight:800; box-shadow:0 7px 18px rgba(16,73,17,.13); }
  .print-btn:hover { color:var(--deep-green); background:#ffe17c; transform:translateY(-1px); }
  .metric-card,.panel { background:var(--cream); border:1px solid var(--border); border-radius:20px; box-shadow:0 8px 22px rgba(16,73,17,.07); }
  .metric-card { height:100%; min-height:150px; padding:24px; }
  .metric-icon { width:58px; height:58px; display:grid; place-items:center; flex:0 0 58px; color:var(--deep-green); background:var(--mint); border-radius:50%; font-size:1.3rem; }
  .metric-card.warning .metric-icon { background:rgba(255,212,73,.6); }
  .metric-label { font-size:.84rem; font-weight:800; letter-spacing:.05em; text-transform:uppercase; opacity:.7; }
  .metric-value { font-size:2.35rem; font-weight:850; line-height:1.1; }
  .panel { height:100%; padding:clamp(24px,3vw,34px); }
  .panel-title { font-size:1.4rem; font-weight:800; }
  .pie-layout { display:grid; grid-template-columns:minmax(210px,280px) 1fr; align-items:center; gap:clamp(24px,4vw,48px); }
  .pie-chart { position:relative; width:min(100%,280px); aspect-ratio:1; margin:auto; border-radius:50%; background:conic-gradient(<?= htmlspecialchars($pieGradient, ENT_QUOTES, 'UTF-8') ?>); box-shadow:inset 0 0 0 1px rgba(16,73,17,.1),0 14px 30px rgba(16,73,17,.14); }
  .pie-chart::after { content:"<?= (int)$categoryTotal ?>\A reports"; white-space:pre; position:absolute; inset:25%; display:grid; place-items:center; text-align:center; color:var(--deep-green); background:var(--cream); border-radius:50%; font-weight:850; line-height:1.2; box-shadow:0 0 0 1px var(--border); }
  .legend-item { display:grid; grid-template-columns:14px 1fr auto; align-items:center; gap:11px; padding:11px 0; border-bottom:1px solid rgba(84,140,47,.14); }
  .legend-item:last-child { border-bottom:0; }
  .legend-dot { width:14px; height:14px; border-radius:4px; }
  .legend-value { font-weight:800; }
  .insight { padding:22px; color:var(--cream); background:var(--deep-green); border-radius:16px; }
  .insight p { color:var(--mint); }
  .rate-row { display:grid; grid-template-columns:minmax(165px,1fr) minmax(150px,2fr) 66px; align-items:center; gap:14px; }
  .rate-label { font-weight:750; }
  .rate-track { height:12px; overflow:hidden; background:rgba(84,140,47,.14); border-radius:999px; }
  .rate-fill { height:100%; border-radius:inherit; }
  .rate-fill.closure { width:<?= $administrativeClosureRate ?>%; background:var(--green); }
  .rate-fill.confirmed { width:<?= $reporterConfirmedRate ?>%; background:var(--yellow); }
  .rate-value { font-weight:850; text-align:right; }
  .report-meta { font-size:.88rem; opacity:.68; }
  @media (max-width:991.98px) { .sidebar{width:88px;padding:24px 12px;overflow-x:hidden}.brand{justify-content:center;padding-inline:0}.brand span,.user-details,.side-label,.link-text{display:none}.brand i{font-size:1.45rem}.sidebar-user{justify-content:center;padding:12px 8px}.sidebar-user i{font-size:1.65rem}.side-link{justify-content:center;gap:0;padding-inline:10px}.side-link i{width:auto}.content{margin-left:88px;padding:22px}.pie-layout{grid-template-columns:1fr} }
  @media (max-width:575.98px) { body{font-size:17px}.hero{border-radius:20px}.hero-actions{width:100%}.print-btn{width:100%}.rate-row{grid-template-columns:1fr 60px}.rate-track{grid-column:1/-1;grid-row:2} }
  @media print {
    @page { size:A4 portrait; margin:14mm; }
    * { box-shadow:none!important; text-shadow:none!important; }
    html,body { width:100%; min-height:0; background:#fff!important; color:#173b1b!important; font-family:Arial,Helvetica,sans-serif!important; font-size:10pt; line-height:1.4; }
    .sidebar,.print-btn,.eyebrow { display:none!important; }
    .layout,.content { display:block!important; width:100%!important; min-width:0!important; margin:0!important; padding:0!important; }
    .hero { margin:0 0 7mm!important; padding:0 0 5mm!important; background:#fff!important; border:0!important; border-bottom:2px solid #104911!important; border-radius:0!important; }
    .hero::after { display:none!important; }
    .hero-inner { display:block!important; }
    .hero h1 { margin:0 0 1.5mm!important; color:#104911!important; font-size:22pt!important; letter-spacing:0!important; }
    .hero-copy { max-width:none!important; margin:0 0 1mm!important; font-size:10.5pt!important; line-height:1.45!important; opacity:1!important; }
    .report-meta { color:#4d5f50!important; font-size:8.5pt!important; opacity:1!important; }

    .analytics-kpis { display:grid!important; grid-template-columns:repeat(2,minmax(0,1fr))!important; gap:4mm!important; margin:0 0 6mm!important; }
    .analytics-kpis > [class*="col-"] { width:auto!important; max-width:none!important; padding:0!important; margin:0!important; }
    .metric-card { min-height:0!important; padding:4mm!important; background:#f7faf7!important; border:1px solid #a9b9aa!important; border-radius:2mm!important; break-inside:avoid; }
    .metric-icon { width:10mm!important; height:10mm!important; flex:0 0 10mm!important; color:#104911!important; background:#dcecdf!important; font-size:12pt!important; print-color-adjust:exact; -webkit-print-color-adjust:exact; }
    .metric-label { font-size:7.5pt!important; letter-spacing:.04em!important; opacity:1!important; }
    .metric-value { margin-top:1mm; font-size:19pt!important; line-height:1!important; }

    .main-analysis { display:block!important; margin:0!important; }
    .main-analysis > [class*="col-"] { width:100%!important; max-width:none!important; padding:0!important; margin:0 0 6mm!important; }
    .panel { height:auto!important; margin-bottom:6mm!important; padding:5mm!important; background:#fff!important; border:1px solid #a9b9aa!important; border-radius:2mm!important; break-inside:avoid; }
    .panel-title { color:#104911!important; font-size:13pt!important; }
    .panel p { color:#344d37!important; opacity:1!important; }
    .pie-layout { display:grid!important; grid-template-columns:52mm 1fr!important; gap:8mm!important; align-items:center!important; }
    .pie-chart { width:48mm!important; height:48mm!important; margin:0 auto!important; print-color-adjust:exact; -webkit-print-color-adjust:exact; }
    .pie-chart::after { background:#fff!important; border:1px solid #a9b9aa!important; font-size:9pt!important; print-color-adjust:exact; -webkit-print-color-adjust:exact; }
    .legend-item { padding:2mm 0!important; border-color:#d7dfd8!important; }
    .legend-dot,.rate-fill { print-color-adjust:exact; -webkit-print-color-adjust:exact; }
    .rate-row { grid-template-columns:48mm 1fr 18mm!important; gap:3mm!important; margin-bottom:4mm!important; }
    .rate-track { height:3mm!important; background:#e5ebe6!important; print-color-adjust:exact; -webkit-print-color-adjust:exact; }
    .insight { margin-top:4mm!important; padding:4mm!important; color:#173b1b!important; background:#eef5ef!important; border-left:3px solid #548c2f!important; border-radius:1mm!important; print-color-adjust:exact; -webkit-print-color-adjust:exact; }
    .insight h3,.insight p,.insight strong { color:#173b1b!important; }
    main > .panel:last-child { margin-bottom:0!important; }
  }
</style>
</head>
<body>
<div class="layout">
  <aside class="sidebar d-flex flex-column">
    <a class="brand fw-bold" href="index.php"><i class="fa-solid fa-shield-halved"></i><span>GRAIL SYSTEM</span></a>
    <div class="sidebar-user" title="<?= htmlspecialchars($user_display_name,ENT_QUOTES,'UTF-8') ?>">
      <i class="fa-solid fa-circle-user"></i><div class="user-details"><div class="user-name"><?= htmlspecialchars($user_display_name,ENT_QUOTES,'UTF-8') ?></div><div class="user-role">Administrator</div></div>
    </div>
    <div class="side-label">Workspace</div>
    <nav aria-label="Dashboard navigation">
      <a class="side-link" href="dashboard.php" title="Dashboard"><i class="fa-solid fa-gauge"></i><span class="link-text">Dashboard</span></a>
      <a class="side-link" href="reports.php" title="Reports"><i class="fa-solid fa-chart-pie"></i><span class="link-text">Reports</span></a>
      <a class="side-link" href="records.php" title="Records"><i class="fa-solid fa-folder-open"></i><span class="link-text">Records</span></a>
      <a class="side-link active" href="analytics.php" title="Analytics" aria-current="page"><i class="fa-solid fa-chart-line"></i><span class="link-text">Analytics</span></a>
      <a class="side-link" href="generate_reports.php" title="Generate Report"><i class="fa-solid fa-file-export"></i><span class="link-text">Generate Report</span></a>
    </nav>
    <div class="side-footer mt-auto"><a class="side-link logout" href="logout.php" title="Logout"><i class="fa-solid fa-right-from-bracket"></i><span class="link-text">Logout</span></a></div>
  </aside>

  <main class="content">
    <header class="hero mb-4">
      <div class="hero-inner d-flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
          <span class="eyebrow mb-3"><i class="fa-solid fa-chart-pie"></i>Decision analytics</span>
          <h1 class="fw-bold mb-2">Resolution Analytics</h1>
          <p class="hero-copy mb-1">See where concerns concentrate and whether closed cases produced meaningful improvement.</p>
          <p class="report-meta mb-0"><i class="fa-regular fa-clock me-1"></i>Report generated <?= htmlspecialchars($generatedAt) ?></p>
        </div>
        <div class="hero-actions"><button type="button" class="btn print-btn" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print analytics</button></div>
      </div>
    </header>

    <section class="analytics-kpis row g-3 g-xl-4 mb-4" aria-label="Key analytics">
      <div class="col-sm-6 col-xl-3"><article class="metric-card warning d-flex align-items-center justify-content-between gap-3"><div><div class="metric-label mb-1">Overdue action</div><div class="metric-value"><?= $overdueCases ?></div></div><div class="metric-icon"><i class="fa-solid fa-clock-rotate-left"></i></div></article></div>
      <div class="col-sm-6 col-xl-3"><article class="metric-card d-flex align-items-center justify-content-between gap-3"><div><div class="metric-label mb-1">Total reports</div><div class="metric-value"><?= $totalCases ?></div></div><div class="metric-icon"><i class="fa-solid fa-file-lines"></i></div></article></div>
      <div class="col-sm-6 col-xl-3"><article class="metric-card d-flex align-items-center justify-content-between gap-3"><div><div class="metric-label mb-1">Cases closed</div><div class="metric-value"><?= $administrativelyClosed ?></div></div><div class="metric-icon"><i class="fa-solid fa-folder-closed"></i></div></article></div>
      <div class="col-sm-6 col-xl-3"><article class="metric-card d-flex align-items-center justify-content-between gap-3"><div><div class="metric-label mb-1">Improvement confirmed</div><div class="metric-value"><?= $reporterConfirmed ?></div></div><div class="metric-icon"><i class="fa-solid fa-thumbs-up"></i></div></article></div>
      <div class="col-sm-6 col-xl-3"><article class="metric-card warning d-flex align-items-center justify-content-between gap-3"><div><div class="metric-label mb-1">Still unresolved</div><div class="metric-value"><?= $reporterUnresolved ?></div></div><div class="metric-icon"><i class="fa-solid fa-triangle-exclamation"></i></div></article></div>
    </section>

    <section class="main-analysis row g-4 mb-4">
      <div class="col-xl-7">
        <article class="panel" aria-labelledby="category-title">
          <div class="mb-4"><h2 id="category-title" class="panel-title mb-1"><i class="fa-solid fa-chart-pie me-2"></i>Report category mix</h2><p class="mb-0 opacity-75">Distribution of all concerns in the current reporting set.</p></div>
          <div class="pie-layout">
            <div class="pie-chart" role="img" aria-label="Pie chart: Academic 8, Administrative 5, Facilities 4, Technology 3"></div>
            <div>
              <?php foreach ($categories as $category => $count): $percent = round(($count / $categoryTotal) * 100); ?>
                <div class="legend-item"><span class="legend-dot" style="background:<?= $categoryColors[$category] ?>"></span><span><?= htmlspecialchars($category) ?></span><span class="legend-value"><?= $count ?> <small class="opacity-50">(<?= $percent ?>%)</small></span></div>
              <?php endforeach; ?>
            </div>
          </div>
        </article>
      </div>
      <div class="col-xl-5">
        <article class="panel" aria-labelledby="performance-title">
          <h2 id="performance-title" class="panel-title mb-1"><i class="fa-solid fa-bullseye me-2"></i>Outcome performance</h2>
          <p class="mb-4 opacity-75">Closure and verified success remain separate.</p>
          <div class="rate-row mb-4"><span class="rate-label">Administrative closure</span><div class="rate-track"><div class="rate-fill closure"></div></div><span class="rate-value"><?= $administrativeClosureRate ?>%</span></div>
          <div class="rate-row mb-4"><span class="rate-label">Confirmed improvement</span><div class="rate-track"><div class="rate-fill confirmed"></div></div><span class="rate-value"><?= $reporterConfirmedRate ?>%</span></div>
          <div class="insight mt-4"><h3 class="h5 fw-bold mb-2"><i class="fa-solid fa-lightbulb me-2"></i>Analyst insight</h3><p class="mb-0"><strong><?= htmlspecialchars($largestCategory) ?></strong> is the largest report category. There is a <strong><?= $confirmationGap ?>-point gap</strong> between administrative closure and reporter-confirmed improvement, indicating where follow-up should be prioritized.</p></div>
        </article>
      </div>
    </section>

    <section class="panel" aria-labelledby="accountability-title">
      <div class="row align-items-center g-4">
        <div class="col-md-8"><h2 id="accountability-title" class="panel-title mb-2"><i class="fa-solid fa-scale-balanced me-2"></i>How GRAIL defines success</h2><p class="mb-0 opacity-75">A case may be administratively closed after required actions are completed. It is counted as a successful outcome only when the reporter confirms that the situation meaningfully improved.</p></div>
        <div class="col-md-4 text-md-end"><span class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill" style="background:var(--mint);font-weight:800"><i class="fa-solid fa-list-check"></i><?= $actionCompleted ?> actions completed</span><div class="report-meta mt-2"><?= $closedWithoutConfirmation ?> awaiting confirmation</div></div>
      </div>
    </section>
  </main>
</div>
</body>
</html>
