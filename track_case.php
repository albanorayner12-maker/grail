<?php
session_start();
require_once 'db.php'; // Gagamitin ang iyong live database handler connection ($pdo)
require_once 'includes/header.php';

$search_query = isset($_POST['tracking_id']) ? trim($_POST['tracking_id']) : '';
$case_found = false;
$case_data = null;

if (!empty($search_query)) {
    try {
        // Step 1: Query natin yung record nang walang 'updated_at' para iwas database error
        $stmt = $pdo->prepare("SELECT id, tracking_token, subject, category, created_at, status FROM grievances WHERE tracking_token = :query OR id = :id_fallback LIMIT 1");
        $stmt->execute([
            'query'       => $search_query,
            'id_fallback' => numeric_fallback_checker($search_query)
        ]);
        
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($record) {
            $case_found = true;
            $raw_status = trim(strtolower($record['status'] ?? 'pending'));
            
            // --- FIXED: Added 'under investigation' to structural conditional evaluation arrays ---
            $step1_done = true; // Laging true kasi pumasok na sa system
            
            // Step 2 & 3 now perfectly track the dashboard status value change
            $step2_done = in_array($raw_status, ['under investigation', 'investigating', 'completed', 'resolved', 'reviewed', 'approved']);
            $step3_done = in_array($raw_status, ['under investigation', 'investigating', 'completed', 'resolved', 'reviewed', 'approved']);
            $step4_done = in_array($raw_status, ['completed', 'resolved', 'reviewed', 'approved']);

            // Friendly main text badge handler
            if ($step4_done) {
                $display_phase = "Reviewed & Resolved";
            } elseif ($step2_done) {
                $display_phase = "Under Investigation";
            } else {
                $display_phase = "Pending Review";
            }

            // Kuhanin ang human-readable time formatting mula sa created_at timestamp
            $time_filed = date('M d, Y | h:i A', strtotime($record['created_at']));

            // Step 3: DYNAMIC TIMELINE OBJECT DATA
            $case_data = [
                'id'             => !empty($record['tracking_token']) ? htmlspecialchars($record['tracking_token']) : "GRL-ID-" . $record['id'],
                'date_submitted' => date('M d, Y', strtotime($record['created_at'])),
                'category'       => htmlspecialchars(ucfirst($record['category'] ?? 'General')),
                'subject'        => htmlspecialchars($record['subject']),
                'current_status' => $display_phase,
                'steps' => [
                    [
                        'title' => 'Grievance Submitted', 
                        'date' => $time_filed, 
                        'desc' => 'Your submission was logged securely into the GRAIL cryptography ledger system pipeline.', 
                        'completed' => $step1_done
                    ],
                    [
                        'title' => 'Initial Assessment', 
                        'date' => $step2_done ? $time_filed : 'Awaiting Processing', 
                        'desc' => $step2_done ? 'Reviewed by routing operators and assigned for evaluation.' : 'Lined up for technical or management evaluation assignment.', 
                        'completed' => $step2_done
                    ],
                    [
                        'title' => 'Under Investigation', 
                        'date' => $step3_done ? $time_filed : 'Pending Action', 
                        'desc' => 'Assigned handlers are evaluating the conditions of the filed incident context.', 
                        'completed' => $step3_done
                    ],
                    [
                        'title' => 'Resolution & Closure', 
                        'date' => $step4_done ? $time_filed : 'Pending Action', 
                        'desc' => 'Final audit confirmation completed. Closing standard remediation remarks applied by administration.', 
                        'completed' => $step4_done
                    ],
                ]
            ];
        }
    } catch (PDOException $e) {
        $db_error = $e->getMessage();
    }
}

// Fail-safe helper para maiwasan ang pdo crash kung sakaling plain string ang tinype ng user
function numeric_fallback_checker($string) {
    $clean = preg_replace('/[^0-9]/', '', $string);
    return !empty($clean) && is_numeric($clean) ? intval($clean) : 0;
}
?>

<style>
  /* 1. STRUCTURAL ENTRANCE ANIMATIONS */
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .animate-header { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
  .animate-body { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards; opacity: 0; }

  /* 2. PREMIUM WHITE GLASS PANELS */
  .track-glass-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 14px rgba(148, 163, 184, 0.04) !important;
  }

  /* 3. MINIMALIST SEARCH BAR DESIGN */
  .form-control {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    padding: 12px 16px;
    color: #2d3748 !important;
    font-size: 0.95rem;
    transition: all 0.2s ease-in-out !important;
  }

  .form-control:focus {
    border-color: #1f6b3e !important;
    box-shadow: 0 0 0 3px rgba(31, 107, 62, 0.15) !important;
    outline: none;
  }

  .btn-premium-solid {
    background: #1f6b3e;
    color: #ffffff !important;
    font-weight: 550;
    border-radius: 8px;
    border: none;
    padding: 12px 24px;
    transition: all 0.25s ease;
  }
  
  .btn-premium-solid:hover {
    background: #17522f;
    box-shadow: 0 4px 12px rgba(31, 107, 62, 0.2);
  }

  /* 4. SEAMLESS PROGRESS TIMELINE MARKERS */
  .timeline-container {
    position: relative;
    padding-left: 32px;
  }

  .timeline-container::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 5px;
    bottom: 5px;
    width: 2px;
    background: #e2e8f0;
  }

  .timeline-item {
    position: relative;
    margin-bottom: 24px;
  }

  .timeline-item:last-child {
    margin-bottom: 0;
  }

  .timeline-dot {
    position: absolute;
    left: -32px;
    top: 3px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #ffffff;
    border: 2px solid #cbd5e1;
    z-index: 2;
    transition: all 0.3s ease;
  }

  /* Active/Completed states lit up by system green */
  .timeline-item.completed .timeline-dot {
    background: #1f6b3e;
    border-color: #1f6b3e;
    box-shadow: 0 0 0 4px rgba(31, 107, 62, 0.15);
  }

  .timeline-item.completed::before {
    content: '';
    position: absolute;
    left: -25px;
    top: 18px;
    bottom: -24px;
    width: 2px;
    background: #1f6b3e;
    z-index: 1;
  }
  
  .timeline-item:last-child::before {
    display: none !important;
  }

  .badge-status {
    background: rgba(31, 107, 62, 0.1);
    color: #1f6b3e;
    font-weight: 600;
    font-size: 0.75rem;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid rgba(31, 107, 62, 0.15);
  }
</style>

<div class="container py-5">

    <div class="text-center mb-5 animate-header">
        <h1 class="fw-bold" style="color: #1a202c; font-size: 2.3rem; letter-spacing: -0.5px;">Status Tracking</h1>
        <p class="lead text-muted" style="font-size: 0.95rem; color: #718096 !important;">Monitor real-time progress and tracking updates for filed grievances.</p>
        <div class="mx-auto rounded mt-3" style="width: 30px; height: 3px; background-color: #1f6b3e;"></div>
    </div>

    <div class="row justify-content-center animate-body">
        <div class="col-lg-8">
            
            <div class="card track-glass-card p-4 mb-4">
                <div class="card-body p-1">
                    <h5 class="fw-bold mb-3" style="color: #1a202c; font-size: 1.1rem;">Enter Case Tracking Reference</h5>
                    <form action="" method="POST">
                        <div class="input-group">
                            <input type="text" name="tracking_id" class="form-control" placeholder="e.g. GRL-B453-6B33" value="<?= htmlspecialchars($search_query) ?>" required>
                            <button type="submit" class="btn btn-premium-solid">
                                <i class="fas fa-magnifying-glass me-2"></i> Track Case
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (isset($db_error)): ?>
                <div class="alert alert-danger shadow-sm rounded-3">
                    <i class="fas fa-triangle-exclamation me-2"></i> <strong>System Data Error:</strong> <?= htmlspecialchars($db_error) ?>
                </div>
            <?php endif; ?>

            <?php if ($case_found && $case_data): ?>
                <div class="card track-glass-card p-4 animate-header">
                    <div class="card-body p-1">
                        
                        <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-4 gap-2">
                            <div>
                                <span class="text-muted small d-block">Tracking ID</span>
                                <h5 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.3px;"><?= $case_data['id'] ?></h5>
                            </div>
                            <div>
                                <span class="text-muted small d-block text-md-end">Current Phase</span>
                                <span class="badge-status">
                                    <?php if (!$step4_done): ?>
                                        <i class="fas fa-spinner fa-spin me-1"></i>
                                    <?php else: ?>
                                        <i class="fas fa-circle-check me-1"></i>
                                    <?php endif; ?>
                                    <?= $case_data['current_status'] ?>
                                </span>
                            </div>
                        </div>

                        <div class="row g-3 bg-light p-3 rounded-3 mb-4 border border-light-subtle mx-0">
                            <div class="col-sm-6">
                                <span class="text-muted small d-block" style="font-size: 0.75rem;">Subject</span>
                                <span class="text-dark fw-medium small"><?= $case_data['subject'] ?></span>
                            </div>
                            <div class="col-sm-3 col-6">
                                <span class="text-muted small d-block" style="font-size: 0.75rem;">Category</span>
                                <span class="text-dark fw-medium small"><?= $case_data['category'] ?></span>
                            </div>
                            <div class="col-sm-3 col-6">
                                <span class="text-muted small d-block" style="font-size: 0.75rem;">Filing Date</span>
                                <span class="text-dark fw-medium small"><?= $case_data['date_submitted'] ?></span>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-4 text-dark" style="font-size: 1rem;">Lifecycle Progress Map</h6>

                        <div class="timeline-container">
                            <?php foreach ($case_data['steps'] as $step): ?>
                                <div class="timeline-item <?= $step['completed'] ? 'completed' : '' ?>">
                                    <div class="timeline-dot"></div>
                                    <div class="ms-2">
                                        <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2">
                                            <h6 class="fw-bold mb-1" style="font-size: 0.9rem; color: <?= $step['completed'] ? '#1f6b3e' : '#4a5568' ?>;">
                                                <?= $step['title'] ?>
                                            </h6>
                                            <small class="text-muted" style="font-size: 0.75rem;"><?= $step['date'] ?></small>
                                        </div>
                                        <p class="text-muted mb-0 small" style="font-size: 0.8rem; line-height: 1.4;"><?= $step['desc'] ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>
            <?php elseif (!empty($search_query)): ?>
                <div class="card track-glass-card text-center p-5 animate-header">
                    <div class="card-body">
                        <i class="fas fa-folder-open text-muted mb-3 fs-2"></i>
                        <h6 class="fw-bold text-dark">No Record Discovered</h6>
                        <p class="text-muted small mb-0">We could not match any logged incident mapping to "<strong><?= htmlspecialchars($search_query) ?></strong>". Please confirm the characters or contact system support.</p>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>