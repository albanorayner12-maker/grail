<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="incidents.php" class="text-decoration-none">
            <div class="card bg-white border-0 shadow-sm p-3 border-start border-primary border-4 card-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small text-uppercase fw-bold">Total Grievances</h6>
                        <h2 class="fw-bold mb-0 text-dark"><?= $total_incidents ?></h2>
                    </div>
                    <div class="bg-light p-3 rounded-circle text-primary"><i class="fa-solid fa-folder-open fa-xl"></i></div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="incidents.php?status=Pending" class="text-decoration-none">
            <div class="card bg-white border-0 shadow-sm p-3 border-start border-warning border-4 card-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small text-uppercase fw-bold">Pending Actions</h6>
                        <h2 class="fw-bold mb-0 text-warning"><?= $pending_incidents ?></h2>
                    </div>
                    <div class="bg-warning-subtle p-3 rounded-circle text-warning"><i class="fa-solid fa-clock fa-xl"></i></div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="incidents.php?status=Resolved" class="text-decoration-none">
            <div class="card bg-white border-0 shadow-sm p-3 border-start border-success border-4 card-hover">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small text-uppercase fw-bold">Resolved Cases</h6>
                        <h2 class="fw-bold mb-0 text-success"><?= $resolved_incidents ?></h2>
                    </div>
                    <div class="bg-success-subtle p-3 rounded-circle text-success"><i class="fa-solid fa-square-check fa-xl"></i></div>
                </div>
            </div>
        </a>
    </div>
</div>