<?php
session_start();
require_once 'db.php';

// Security check: Only logged-in administrators can access this view
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Ensure an ID is passed in the URL string
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "No record ID provided.";
    header("Location: dashboard.php");
    exit();
}

$id = intval($_GET['id']);

try {
    // Retrieve all database columns for this specific record row
    $stmt = $pdo->prepare("SELECT * FROM grievances WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        $_SESSION['error'] = "Record not found.";
        header("Location: dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database Error: " . $e->getMessage();
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRAIL SYSTEM | View Record #<?= $record['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .view-card { background-color: #ffffff; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .detail-label { font-weight: 600; color: #2e7d32; font-size: 14px; margin-bottom: 2px; text-uppercase; tracking-wide; }
        .detail-value { color: #333; font-size: 16px; margin-bottom: 20px; background-color: #f8f9fa; padding: 10px 15px; border-radius: 8px; border-left: 3px solid #2e7d32; }
        .description-box { min-height: 120px; background-color: #f8f9fa; border-radius: 8px; padding: 15px; color: #333; line-height: 1.6; border-left: 3px solid #2e7d32; white-space: pre-wrap; }
        .top-navbar { background-color: #2c3e50; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark top-navbar shadow-sm mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="dashboard.php"><i class="fa-solid fa-shield-halved me-2"></i>GRAIL SYSTEM PANEL</a>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard</a>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <div class="view-card p-4 p-md-5">
                    
                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <div>
                            <h2 class="h3 fw-bold mb-1 text-dark">Grievance Details #<?= $record['id'] ?></h2>
                            <p class="text-muted small mb-0">Submitted on: <?= date('M d, Y h:i A', strtotime($record['created_at'])) ?></p>
                        </div>
                        <div class="mt-2 mt-sm-0">
                            <?php if ($record['status'] == 'Completed'): ?>
                                <span class="badge bg-success fs-6 px-3 py-2 rounded-pill">Completed</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill">Pending Review</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-secondary border-bottom pb-1"><i class="fa-solid fa-circle-user me-2"></i>Reporter Identity</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-label">Is Anonymous?</div>
                            <div class="detail-value"><?= $record['is_anonymous'] ? 'Yes (Identity Masked)' : 'No' ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value"><?= htmlspecialchars($record['name'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">User Classification</div>
                            <div class="detail-value"><?= htmlspecialchars($record['user_type'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">ID Number</div>
                            <div class="detail-value"><?= htmlspecialchars($record['id_number'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Email Address</div>
                            <div class="detail-value"><?= htmlspecialchars($record['email'] ?? 'N/A') ?></div>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-secondary border-bottom pb-1" style="margin-top: 15px;"><i class="fa-solid fa-file-invoice me-2"></i>Grievance Specifics</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-label">Grievance Type / Category</div>
                            <div class="detail-value text-capitalize"><?= htmlspecialchars($record['category']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Subject Title</div>
                            <div class="detail-value fw-bold"><?= htmlspecialchars($record['subject']) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Date of Incident</div>
                            <div class="detail-value"><?= date('M d, Y', strtotime($record['incident_date'])) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Priority Level</div>
                            <div class="detail-value text-capitalize fw-bold <?= $record['priority'] == 'high' || $record['priority'] == 'critical' ? 'text-danger' : 'text-dark' ?>">
                                <?= htmlspecialchars($record['priority'] ?? 'medium') ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Location of Incident</div>
                            <div class="detail-value"><?= htmlspecialchars($record['location'] ?: 'Not Specified') ?></div>
                        </div>
                    </div>

                    <div class="mb-4" style="margin-top: 15px;">
                        <div class="detail-label fw-bold">Full Statement Description</div>
                        <div class="description-box"><?= htmlspecialchars($record['description']) ?></div>
                    </div>

                    <div class="mb-4 border-top pt-4">
                        <div class="detail-label mb-2"><i class="fa-solid fa-paperclip me-2"></i>Supporting Attachments</div>
                        <?php if (!empty($record['evidence']) && file_exists($record['evidence'])): ?>
                            <div class="p-3 border rounded bg-light d-flex align-items-center justify-content-between">
                                <span class="text-truncate me-2"><i class="fa-regular fa-file-lines me-2 text-primary fs-5"></i><?= basename($record['evidence']) ?></span>
                                <a href="<?= htmlspecialchars($record['evidence']) ?>" download class="btn btn-success btn-sm px-3"><i class="fa-solid fa-download me-1"></i> Download File</a>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small italic mb-0">No supporting proof documents or files uploaded for this report file entry.</p>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-4 mt-4">
                        <?php if ($record['status'] !== 'Completed'): ?>
                            <a href="approve.php?id=<?= $record['id'] ?>" class="btn btn-success"><i class="fa-solid fa-check me-1"></i> Resolve and Complete</a>
                        <?php endif; ?>
                        <a href="delete.php?id=<?= $record['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to permanently delete this report data row?')"><i class="fa-solid fa-trash me-1"></i> Delete Record</a>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>