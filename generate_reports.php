<?php
require_once 'includes/header.php';
?>

<style>
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .animate-header { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
  .animate-body { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards; opacity: 0; }

  .report-glass-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 14px rgba(148, 163, 184, 0.04) !important;
  }

  .form-select {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    padding: 10px 14px;
    font-size: 0.9rem;
    transition: all 0.2s ease-in-out !important;
  }
  .form-select:focus {
    border-color: #1f6b3e !important;
    box-shadow: 0 0 0 3px rgba(31, 107, 62, 0.15) !important;
    outline: none;
  }

  .btn-premium-solid {
    background: #1f6b3e;
    color: #ffffff !important;
    font-weight: 550;
    font-size: 0.9rem;
    border-radius: 8px;
    border: none;
    transition: all 0.25s ease;
  }
  .btn-premium-solid:hover {
    background: #17522f;
    box-shadow: 0 4px 12px rgba(31, 107, 62, 0.2);
  }
</style>

<div class="container py-5">
    <div class="text-center mb-5 animate-header">
        <h1 class="fw-bold" style="color: #1a202c; font-size: 2.3rem; letter-spacing: -0.5px;">Reporting Tools</h1>
        <p class="lead text-muted" style="font-size: 0.95rem; color: #718096 !important;">Compile and export structured data logs regarding incident lifecycle resolutions for audit reporting.</p>
        <div class="mx-auto rounded mt-3" style="width: 30px; height: 3px; background-color: #1f6b3e;"></div>
    </div>

    <div class="row justify-content-center animate-body">
        <div class="col-lg-8">
            <div class="card report-glass-card p-4">
                <div class="card-body p-1">
                    <h5 class="fw-bold text-dark mb-4" style="font-size: 1.1rem;">Configure Report Export Parameters</h5>
                    
                    <form action="#" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-2 d-block">Report Scope / Timeframe</label>
                                <select class="form-select" required>
                                    <option value="current_month">Current Billing / Month Period</option>
                                    <option value="last_quarter">Previous Financial Quarter</option>
                                    <option value="annual">Full System Year Summary</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-2 d-block">Output File Architecture Type</label>
                                <select class="form-select" required>
                                    <option value="pdf">Structured PDF Document Print</option>
                                    <option value="csv">Raw Microsoft Excel CSV Matrix</option>
                                </select>
                            </div>
                            <div class="col-12 mt-4 text-center">
                                <button type="submit" class="btn btn-premium-solid px-4 py-2">
                                    <i class="fas fa-file-export me-2"></i> Compile and Export File
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>