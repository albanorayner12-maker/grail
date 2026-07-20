<?php
session_start();
require_once 'db.php'; // Gagamitin ang iyong $pdo connection mula db.php
require_once 'includes/header.php';

try {
    // 1. Kuhanin ang Total Submissions Count
    $total_stmt = $pdo->query("SELECT COUNT(*) FROM grievances");
    $total_cases = $total_stmt->fetchColumn();

    // 2. Kuhanin ang Resolved Cases Count (Kumpunihin kung 'Resolved' o 'Closed' ang string sa DB mo)
    $resolved_stmt = $pdo->prepare("SELECT COUNT(*) FROM grievances WHERE status = :status_resolved OR status = 'Closed'");
    $resolved_stmt->execute(['status_resolved' => 'Resolved']);
    $resolved_cases = $resolved_stmt->fetchColumn();

    // 3. I-compute ang Completion Rate Percentage
    $completion_rate = 0;
    if ($total_cases > 0) {
        $completion_rate = round(($resolved_cases / $total_cases) * 100, 1);
    }

    // 4. Kuhanin ang bilang ng bawat Grievance Type para sa Category Breakdown Chart
    // Pinapantayan nito ang mga select choices mo: harassment, discrimination, safety, academic, administrative, financial, other
    $cat_stmt = $pdo->query("SELECT category, COUNT(*) as count FROM grievances GROUP BY category");
    $category_data = $cat_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Siguraduhing may default values na 0 kung wala pang laman ang db sa partikular na kategorya
    $categories = [
        'academic'       => $category_data['academic'] ?? 0,
        'safety'         => $category_data['safety'] ?? 0,
        'harassment'     => $category_data['harassment'] ?? 0,
        'discrimination' => $category_data['discrimination'] ?? 0,
        'administrative' => $category_data['administrative'] ?? 0,
        'financial'      => $category_data['financial'] ?? 0,
        'other'          => $category_data['other'] ?? 0,
    ];

} catch (PDOException $e) {
    // Fail-safe handler kapag nagka-error ang database connection string
    $total_cases = 0;
    $resolved_cases = 0;
    $completion_rate = 0;
    $categories = ['academic' => 0, 'safety' => 0, 'harassment' => 0, 'discrimination' => 0, 'administrative' => 0, 'financial' => 0, 'other' => 0];
    $db_error = $e->getMessage();
}
?>

<style>
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .animate-header { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
  .animate-body { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards; opacity: 0; }

  .analytics-glass-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 14px rgba(148, 163, 184, 0.04) !important;
  }
  
  .metric-number {
    font-size: 2.3rem;
    font-weight: 700;
    color: #1f6b3e;
    line-height: 1.1;
  }

  .progress-bar-custom {
    background-color: #1f6b3e;
    transition: width 1s ease-in-out;
  }
</style>

<div class="container py-5">
    <div class="text-center mb-5 animate-header">
        <h1 class="fw-bold" style="color: #1a202c; font-size: 2.3rem; letter-spacing: -0.5px;">Insights &amp; Analytics</h1>
        <p class="lead text-muted" style="font-size: 0.95rem; color: #718096 !important;">Real-time database pipeline monitoring grievance lifecycles and category counts.</p>
        <div class="mx-auto rounded mt-3" style="width: 30px; height: 3px; background-color: #1f6b3e;"></div>
    </div>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger mx-auto max-width-lg" style="max-width: 800px;">
            <i class="fas fa-exclamation-triangle me-2"></i> <strong>Database Connection Issue:</strong> <?= htmlspecialchars($db_error) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4 justify-content-center animate-body">
        <div class="col-lg-10">
            
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card analytics-glass-card p-3 text-center">
                        <div class="card-body">
                            <span class="text-muted small d-block mb-2 fw-medium">Total Grievances Filed</span>
                            <div class="metric-number"><?= $total_cases; ?></div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.75rem;"><i class="fas fa-database me-1"></i> Live count from system</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card analytics-glass-card p-3 text-center">
                        <div class="card-body">
                            <span class="text-muted small d-block mb-2 fw-medium">Resolved Cases</span>
                            <div class="metric-number"><?= $resolved_cases; ?></div>
                            <small class="text-success d-block mt-2" style="font-size: 0.75rem; font-weight: 600;"><i class="fas fa-circle-check me-1"></i> Actioned records</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card analytics-glass-card p-3 text-center">
                        <div class="card-body">
                            <span class="text-muted small d-block mb-2 fw-medium">Resolution Success Rate</span>
                            <div class="metric-number"><?= $completion_rate; ?><span style="font-size: 1.2rem; font-weight: 600;">%</span></div>
                            <small class="text-dark-emphasis d-block mt-2" style="font-size: 0.75rem;"><i class="fas fa-chart-line me-1"></i> Pipeline efficiency target</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card analytics-glass-card p-4 mt-4">
                <div class="card-body p-1">
                    <h5 class="fw-bold text-dark mb-4" style="font-size: 1.1rem; letter-spacing: -0.3px;">Grievance Frequency Breakdown by Category Type</h5>
                    
                    <?php 
                    // Render dynamically scalable bar elements helper
                    foreach ($categories as $key => $count) {
                        $percentage = $total_cases > 0 ? round(($count / $total_cases) * 100, 1) : 0;
                        $display_title = ucfirst($key);
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-dark fw-medium"><?= $display_title ?> Concerns</span>
                                <span class="text-muted fw-semibold"><?= $percentage ?>% (<?= $count ?> <?= $count == 1 ? 'case' : 'cases' ?>)</span>
                            </div>
                            <div class="progress" style="height: 8px; background-color: #e2e8f0; border-radius: 4px;">
                                <div class="progress-bar progress-bar-custom" style="width: <?= $percentage ?>%"></div>
                            </div>
                        </div>
                    <?php } ?>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>