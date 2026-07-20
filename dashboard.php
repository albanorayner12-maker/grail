<?php
session_start();
require_once 'db.php';

// Check if logged in, otherwise redirect. 
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// --- FORCED REPAIR: INITIAL ASSESSMENT STATE PROCESSOR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $record_id = isset($_POST['record_id']) ? intval($_POST['record_id']) : 0;
    $action = $_POST['action'];

    if ($action === 'start_investigation' && $record_id > 0) {
        try {
            // Forcefully overwrite whatever text string is inside the status column right now
            $stmt = $pdo->prepare("UPDATE grievances SET status = 'under investigation' WHERE id = :id");
            $result = $stmt->execute(['id' => $record_id]);
            
            if ($result && $stmt->rowCount() > 0) {
                $_SESSION['success'] = "Success! Database updated. Row ID: " . $record_id . " is now 'under investigation'.";
            } else {
                // If it executed but changed 0 rows, the ID might not exist or it was already 'under investigation'
                $_SESSION['success'] = "Database command executed, but no changes were required (or case already updated).";
            }
            
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "SQL Engine Error: " . $e->getMessage();
            header("Location: dashboard.php");
            exit();
        }
    }
}


// Set display name
$user_display_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Derayner";

// Check if the secure submission session flag is present
$show_thank_you = false;
if (isset($_SESSION['show_thank_you']) && $_SESSION['show_thank_you'] === true) {
    $show_thank_you = true;
    unset($_SESSION['show_thank_you']); 
}

// --- QUERIES FOR SUMMARY CARDS ---
$stmt = $pdo->query("SELECT COUNT(*) FROM admins");
$total_users = $stmt->fetchColumn() ?: 1;

$stmt = $pdo->query("SELECT COUNT(*) FROM grievances");
$total_records = $stmt->fetchColumn() ?: 0;

// Dynamic check: count anything that is NOT resolved yet
$stmt = $pdo->query("SELECT COUNT(*) FROM grievances WHERE LOWER(TRIM(status)) NOT IN ('completed', 'resolved', 'reviewed', 'approved')");
$total_reports = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->query("SELECT COUNT(*) FROM grievances WHERE created_at >= NOW() - INTERVAL 7 DAY");
$recent_activities = $stmt->fetchColumn() ?: 0;

// --- DATA TABLE QUERY (Selecting tracking_token from your database table) ---
$stmt = $pdo->query("SELECT id, tracking_token, subject AS title, name AS user, created_at, status FROM grievances ORDER BY created_at DESC");
$recent_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRAIL SYSTEM - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: calc(100vh - 56px); background-color: #2c3e50; }
        .sidebar a { color: #cbd5e1; transition: all 0.3s; text-decoration: none; display: block; }
        .sidebar a:hover, .sidebar a.active { color: #ffffff; background-color: #1a252f; border-radius: 5px;}
        .card-stat { border-left: 4px solid; }
        .border-blue { border-left-color: #0d6efd; }
        .border-green { border-left-color: #198754; }
        .border-warning { border-left-color: #ffc107; }
        .border-info { border-left-color: #0dcaf0; }
        .main-panel { display: flex; flex-direction: column; min-height: calc(100vh - 56px); }
        
        .success-icon-circle {
            width: 80px; height: 80px; background-color: #d1e7dd; color: #0f5132;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px auto; font-size: 2.5rem;
        }
        .inline-action-form { display: inline-block; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold tracking-wide" href="#"><i class="fa-solid fa-shield-halved me-2"></i>GRAIL SYSTEM</a>
            <div class="d-flex align-items-center ms-auto">
                <span class="navbar-text text-light me-3">
                    <i class="fa-solid fa-circle-user me-1 text-primary"></i> <?= htmlspecialchars($user_display_name); ?> 
                </span>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">

            <div class="col-md-2 p-3 sidebar d-flex flex-column">
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item mb-1"><a href="dashboard.php" class="nav-link active py-2 px-3"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a></li>
                    <li class="nav-item mb-1"><a href="reports.php" class="nav-link py-2 px-3"><i class="fa-solid fa-chart-pie me-2"></i> Reports</a></li>
                    <li class="nav-item mb-1"><a href="records.php" class="nav-link py-2 px-3"><i class="fa-solid fa-folder-open me-2"></i> Records</a></li>
                    <li class="nav-item mb-1"><a href="history.php" class="nav-link py-2 px-3"><i class="fa-solid fa-clock-rotate-left me-2"></i> History</a></li>
                </ul>
                <hr class="text-secondary">
                <div class="logout-box">
                    <a href="logout.php" class="btn btn-danger w-100 text-start"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
                </div>
            </div>

            <div class="col-md-10 p-4 main-panel">
                
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-left: 4px solid #198754 !important;">
                            <i class="fa-solid fa-circle-check me-2"></i><?= $_SESSION['success']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-left: 4px solid #dc3545 !important;">
                            <i class="fa-solid fa-circle-exclamation me-2"></i><?= $_SESSION['error']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="card card-stat border-blue shadow-sm h-100 py-2">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col me-2">
                                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Users</div>
                                            <div class="h5 mb-0 fw-bold text-dark"><?= $total_users; ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fa-solid fa-users fa-2x text-secondary opacity-50"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-stat border-green shadow-sm h-100 py-2">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col me-2">
                                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Records</div>
                                            <div class="h5 mb-0 fw-bold text-dark"><?= $total_records; ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fa-solid fa-database fa-2x text-secondary opacity-50"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-stat border-warning shadow-sm h-100 py-2">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col me-2">
                                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Reports (Pending)</div>
                                            <div class="h5 mb-0 fw-bold text-dark"><?= $total_reports; ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fa-solid fa-file-circle-exclamation fa-2x text-secondary opacity-50"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-stat border-info shadow-sm h-100 py-2">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col me-2">
                                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Recent Activities</div>
                                            <div class="h5 mb-0 fw-bold text-dark"><?= $recent_activities; ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fa-solid fa-bell fa-2x text-secondary opacity-50"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-table-list me-2"></i>Latest Records Breakdown</h6>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tracking ID</th>
                                        <th>Title</th>
                                        <th>User</th>
                                        <th>Date Created</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recent_records) > 0): ?>
                                        <?php foreach ($recent_records as $row): ?>
                                            <?php 
                                            // Handle whitespace, null, or completely empty columns cleanly
                                            $db_status = isset($row['status']) ? trim(strtolower($row['status'])) : '';
                                            $current_status = ($db_status !== '') ? $db_status : 'pending'; 
                                            ?>
                                            <tr>
                                                <td class="fw-bold text-primary">
                                                    <?= !empty($row['tracking_token']) ? htmlspecialchars($row['tracking_token']) : "GRL-ID-" . htmlspecialchars($row['id']) ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['title']) ?></td>
                                                <td><i class="fa-solid fa-user-pen me-2 text-muted"></i><?= htmlspecialchars($row['user']) ?></td>
                                                <td><?= htmlspecialchars(date('M d, Y', strtotime($row['created_at']))) ?></td>
                                                <td>
                                                    <?php if (in_array($current_status, ['completed', 'resolved', 'reviewed', 'approved'])): ?>
                                                       <span class="badge" style="background-color: #198754 !important; color: #ffffff; padding: 6px 12px; border-radius: 30px; font-weight: 600;">Resolved</span>
                                                    <?php elseif ($current_status === 'under investigation'): ?>
                                                        <span class="badge bg-primary text-white" style="padding: 6px 12px; border-radius: 30px; font-weight: 600;">Investigating</span>
                                                    <?php elseif ($current_status === 'unreviewed'): ?>
                                                        <span class="badge bg-info text-dark" style="padding: 6px 12px; border-radius: 30px; font-weight: 600;">Unreviewed</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark" style="padding: 6px 12px; border-radius: 30px; font-weight: 600;">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="view.php?id=<?= urlencode($row['id']) ?>" class="btn btn-outline-primary btn-sm me-1" title="View"><i class="fa-solid fa-eye"></i></a>
                                                    
                                                    <?php if ($current_status === 'pending' || $current_status === 'unreviewed'): ?>
                                                        <form action="dashboard.php" method="POST" class="inline-action-form me-1">
                                                            <input type="hidden" name="record_id" value="<?= $row['id'] ?>">
                                                            <button type="submit" name="action" value="start_investigation" class="btn btn-outline-warning btn-sm" title="Accept & Investigate" onclick="return confirm('Advance this case to investigation mapping status?')">
                                                                <i class="fa-solid fa-magnifying-glass-chart"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!in_array($current_status, ['completed', 'resolved', 'reviewed', 'approved'])): ?>
                                                        <a href="approve.php?id=<?= urlencode($row['id']) ?>" class="btn btn-outline-success btn-sm me-1" title="Resolve"><i class="fa-solid fa-check"></i></a>
                                                    <?php endif; ?>
                                                    
                                                    <a href="delete.php?id=<?= urlencode($row['id']) ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to permanently delete this report?')" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No system records found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>