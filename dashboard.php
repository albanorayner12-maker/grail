<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

// Check if logged in, otherwise redirect. 
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// --- FORCED REPAIR: INITIAL ASSESSMENT STATE PROCESSOR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $record_id = isset($_POST['record_id']) ? intval($_POST['record_id']) : 0;
    $action = $_POST['action'];

    if ($action === 'start_investigation' && $record_id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE grievances SET status = 'under investigation' WHERE id = :id");
            $result = $stmt->execute(['id' => $record_id]);
            
            if ($result && $stmt->rowCount() > 0) {
                $_SESSION['success'] = "Success! Database updated. Row ID: " . $record_id . " is now 'under investigation'.";
            } else {
                $_SESSION['success'] = "Database command executed, but no changes were required.";
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
$user_display_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Admin User";

// --- SAFE QUERIES FOR SUMMARY CARDS ---
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
    $total_users = $stmt->fetchColumn() ?: 1;

    $stmt = $pdo->query("SELECT COUNT(*) FROM grievances");
    $total_records = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->query("SELECT COUNT(*) FROM grievances WHERE LOWER(TRIM(status)) NOT IN ('completed', 'resolved', 'reviewed', 'approved')");
    $total_reports = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->query("SELECT COUNT(*) FROM grievances WHERE created_at >= NOW() - INTERVAL 7 DAY");
    $recent_activities = $stmt->fetchColumn() ?: 0;

    // Fetch records safely
    $stmt = $pdo->query("SELECT id, tracking_token, subject AS title, name AS user, created_at, status FROM grievances ORDER BY created_at DESC LIMIT 10");
    $recent_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback variables to prevent display errors if tables are missing column properties
    $total_users = 1;
    $total_records = 0;
    $total_reports = 0;
    $recent_activities = 0;
    $recent_records = [];
    $query_error = "Table layout fallback activated: " . $e->getMessage();
}
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
        body { background-color: #f4f6f9; font-family: system-ui, -apple-system, sans-serif; }
        .sidebar { min-height: calc(100vh - 56px); background-color: #2c3e50; }
        .sidebar a { color: #cbd5e1; transition: all 0.3s; text-decoration: none; display: block; }
        .sidebar a:hover, .sidebar a.active { color: #ffffff; background-color: #1a252f; border-radius: 5px;}
        .card-stat { border-left: 4px solid; }
        .border-blue { border-left-color: #0d6efd; }
        .border-green { border-left-color: #198754; }
        .border-warning { border-left-color: #ffc107; }
        .border-info { border-left-color: #0dcaf0; }
        .main-panel { display: flex; flex-direction: column; min-height: calc(100vh - 56px); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-shield-halved me-2"></i>GRAIL SYSTEM</a>
            <div class="d-flex align-items-center ms-auto">
                <span class="navbar-text text-light me-3">
                    <i class="fa-solid fa-circle-user me-1 text-primary"></i> <?= htmlspecialchars($user_display_name); ?> 
                </span>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">

            <!-- Sidebar Panel Navigation -->
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

            <!-- Content Panel Module Wrapper -->
            <div class="col-md-10 p-4 main-panel">
                
                <?php if (isset($query_error)): ?>
                    <div class="alert alert-warning py-2 shadow-sm mb-3"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= $query_error; ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-left: 4px solid #198754 !important;">
                        <i class="fa-solid fa-circle-check me-2"></i><?= $_SESSION['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
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
                                        <div class="text-xs fw-bold text-warning text-uppercase mb-1">Active Cases</div>
                                        <div class="h5 mb-0 fw-bold text-dark"><?= $total_reports; ?></div>
                                    </div>
                                    <div class="col-auto"><i class="fa-solid fa-folder-open fa-2x text-secondary opacity-50"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stat border-info shadow-sm h-100 py-2">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs fw-bold text-info text-uppercase mb-1">Recent Activity</div>
                                        <div class="h5 mb-0 fw-bold text-dark"><?= $recent_activities; ?></div>
                                    </div>
                                    <div class="col-auto"><i class="fa-solid fa-bolt fa-2x text-secondary opacity-50"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
