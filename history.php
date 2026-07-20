<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Set display name
$user_display_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Derayner";

// Fetches processed records using database keyword handling
$stmt = $pdo->query("SELECT id, subject AS title, name AS user, created_at, status FROM grievances WHERE LOWER(TRIM(status)) IN ('completed', 'resolved', 'reviewed', 'approved') ORDER BY created_at DESC");
$history_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived History Log</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: calc(100vh - 56px); background-color: #2c3e50; }
        .sidebar a { color: #cbd5e1; text-decoration: none; display: block; transition: all 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #ffffff; background-color: #1a252f; border-radius: 5px; }
        .main-panel { display: flex; flex-direction: column; min-height: calc(100vh - 56px); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-shield-halved me-2"></i>GRAIL SYSTEM</a>
            <div class="ms-auto"><span class="navbar-text text-light"><i class="fa-solid fa-circle-user text-primary me-1"></i> <?= htmlspecialchars($user_display_name); ?></span></div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            
            <div class="col-md-2 p-3 sidebar d-flex flex-column">
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item mb-1"><a href="dashboard.php" class="nav-link py-2 px-3"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a></li>
                    <li class="nav-item mb-1"><a href="reports.php" class="nav-link py-2 px-3"><i class="fa-solid fa-chart-pie me-2"></i> Reports</a></li>
                    <li class="nav-item mb-1"><a href="records.php" class="nav-link py-2 px-3"><i class="fa-solid fa-folder-open me-2"></i> Records</a></li>
                    <li class="nav-item mb-1"><a href="history.php" class="nav-link active py-2 px-3"><i class="fa-solid fa-clock-rotate-left me-2"></i> History</a></li>
                </ul>
                <hr class="text-secondary">
                <div class="logout-box">
                    <a href="logout.php" class="btn btn-danger w-100 text-start">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                    </a>
                </div>
            </div>

            <div class="col-md-10 p-4 main-panel">
                <h3 class="fw-bold text-dark">Archived History Log</h3>
                <p class="text-muted">Reviewing all resolved and finalized grievance entries records.</p>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h6 class="m-0 fw-bold text-success"><i class="fa-solid fa-box-archive me-2"></i>Resolved Cases Overview</h6></div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>User</th>
                                    <th>Date Created</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($history_records) > 0): ?>
                                    <?php foreach ($history_records as $row): ?>
                                        <tr>
                                            <td class="fw-bold">#<?= htmlspecialchars($row['id']) ?></td>
                                            <td><?= htmlspecialchars($row['title']) ?></td>
                                            <td><?= htmlspecialchars($row['user']) ?></td>
                                            <td><?= htmlspecialchars(date('M d, Y', strtotime($row['created_at']))) ?></td>
                                            <td><span class="badge bg-success" style="padding: 6px 12px; border-radius: 30px;">Reviewed</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted"><i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50"></i>No resolved history logs found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <footer class="mt-auto pt-3 border-top text-center text-muted small">
                    <p class="mb-0">&copy; <?= date('Y'); ?> GRAIL SYSTEM. All rights reserved.</p>
                </footer>
            </div>

        </div>
    </div>
</body>
</html>