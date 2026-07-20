<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$user_display_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Derayner";

// Fetch only 'Pending' records for the Reports view
$stmt = $pdo->query("SELECT id, subject AS title, name AS user, created_at, status FROM grievances WHERE LOWER(TRIM(status)) = 'pending' ORDER BY created_at DESC");
$report_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRAIL SYSTEM - Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: calc(100vh - 56px); background-color: #2c3e50; }
        .sidebar a { color: #cbd5e1; transition: all 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #ffffff; background-color: #1a252f; border-radius: 5px;}
        .main-panel { display: flex; flex-direction: column; min-height: calc(100vh - 56px); }
        .content { flex: 1; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold tracking-wide" href="dashboard.php"><i class="fa-solid fa-shield-halved me-2"></i>GRAIL SYSTEM</a>
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
                    <li class="nav-item mb-1">
                        <a href="dashboard.php" class="nav-link py-2 px-3">
                            <i class="fa-solid fa-gauge me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="reports.php" class="nav-link active py-2 px-3">
                            <i class="fa-solid fa-chart-pie me-2"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="records.php" class="nav-link py-2 px-3">
                            <i class="fa-solid fa-folder-open me-2"></i> Records
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="history.php" class="nav-link py-2 px-3">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i> History
                        </a>
                    </li>
                </ul>
                <hr class="text-secondary">
                <div class="logout-box">
                    <a href="logout.php" class="btn btn-danger w-100 text-start">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                    </a>
                </div>
            </div>

            <div class="col-md-10 p-4 main-panel">
                <div class="content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h3 fw-bold mb-0 text-dark">Pending Reports</h2>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-file-circle-exclamation me-2 text-warning"></i>Awaiting Review</h6>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>User</th>
                                        <th>Date Created</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($report_records) > 0): ?>
                                        <?php foreach ($report_records as $row): ?>
                                            <tr>
                                                <td class="fw-bold">#<?= htmlspecialchars($row['id']) ?></td>
                                                <td><?= htmlspecialchars($row['title']) ?></td>
                                                <td><i class="fa-solid fa-user-pen me-2 text-muted"></i><?= htmlspecialchars($row['user']) ?></td>
                                                <td><?= htmlspecialchars(date('M d, Y', strtotime($row['created_at']))) ?></td>
                                                <td><span class="badge bg-warning text-dark">Pending</span></td>
                                                <td class="text-center">
                                                    <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm me-1" title="View"><i class="fa-solid fa-eye"></i></a>
                                                    <a href="approve.php?id=<?= $row['id'] ?>" class="btn btn-outline-success btn-sm me-1" title="Resolve"><i class="fa-solid fa-check"></i></a>
                                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this report?')" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No pending reports found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <footer class="mt-auto pt-3 border-top text-center text-muted small">
                    <p class="mb-0">&copy; <?= date('Y'); ?> GRAIL SYSTEM. All rights reserved.</p>
                </footer>
            </div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>