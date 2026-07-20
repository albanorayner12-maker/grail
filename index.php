<?php
require_once 'includes/header.php';
?>

<style>
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .animate-hero { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
  .animate-header { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards; opacity: 0; }
  .animate-grid { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; opacity: 0; }

  /* Premium Crisp White Panel sitting elegantly on the soft background */
  .hero-glass-panel {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(148, 163, 184, 0.12);
  }

  .feature-card-link {
    text-decoration: none !important;
    display: block;
    height: 100%;
  }

  /* Soft Clean Interactive White Cards */
  .glass-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 14px rgba(148, 163, 184, 0.05) !important;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
  }

  /* Icons use your signature green as a crisp accent border */
  .card-icon-frame {
    width: 50px;
    height: 50px;
    background: rgba(31, 107, 62, 0.06);
    border: 1px solid rgba(31, 107, 62, 0.15);
    color: #1f6b3e;
    transition: all 0.3s ease !important;
  }

  /* Micro-interactions: Cards pop with a green accent border on hover */
  .feature-card-link:hover .glass-card {
    transform: translateY(-4px);
    border-color: rgba(31, 107, 62, 0.4) !important;
    box-shadow: 0 12px 24px rgba(31, 107, 62, 0.08) !important;
  }

  .feature-card-link:hover .card-icon-frame {
    background: #1f6b3e !important;
    color: #ffffff !important;
    border-color: #1f6b3e !important;
  }

  /* Core Buttons utilizing the brand green */
  .btn-premium-solid {
    background: #1f6b3e;
    color: #ffffff !important;
    font-weight: 550;
    border-radius: 10px;
    border: none;
    transition: all 0.25s ease;
  }
  .btn-premium-solid:hover {
    background: #17522f;
    transform: translateY(-1px);
    box-shadow: 0 6px 15px rgba(31, 107, 62, 0.2);
  }

  .btn-premium-outline {
    background: transparent;
    color: #4a5568 !important;
    border: 1px solid #cbd5e1;
    font-weight: 550;
    border-radius: 10px;
    transition: all 0.25s ease;
  }
  .btn-premium-outline:hover {
    background: rgba(0, 0, 0, 0.02);
    border-color: #94a3b8;
    transform: translateY(-1px);
  }
</style>

<div class="container my-3 animate-hero">
  <div class="p-5 hero-glass-panel text-dark">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <h1 class="fw-bold mb-3" style="letter-spacing: -0.5px; line-height: 1.2; font-size: 2.3rem; color: #1a202c;">
          GRAIL: Incident Logging &amp; Accountability
        </h1>
        <p class="lead mb-4 text-muted" style="font-size: 0.95rem; line-height: 1.6; color: #718096 !important;">
          A secure, streamlined workspace tailored for the CITE community. Track, investigate, and audit system issues transparently with cryptographic integrity.
        </p>
        <div class="d-flex gap-2">
          <a href="submit_report.php" class="btn btn-premium-solid px-4 py-2 small">
            <i class="fas fa-user-edit me-2"></i> File Report
          </a>
          <a href="login.php" class="btn btn-premium-outline px-4 py-2 small">
            <i class="fas fa-shield-alt me-2"></i> Admin Portal
          </a>
        </div>
      </div>
      <div class="col-lg-5 text-center">
        <div class="d-flex justify-content-center align-items-center gap-3">
          <img src="assets/css/img/nvsu-logo.png" alt="NVSU Logo" class="img-fluid" style="max-height: 110px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.05));" onerror="this.style.display='none'">
          <img src="assets/css/img/cite-logo.png" alt="CITE Logo" class="img-fluid" style="max-height: 110px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.05));" onerror="this.style.display='none'">
        </div>
      </div>
    </div>
  </div>
</div>

<main class="container my-5">
  <div class="text-center mb-4 animate-header">
    <h4 class="fw-bold" style="color: #1a202c; font-size: 1.2rem;">System Features</h4>
    <div class="mx-auto rounded" style="width: 30px; height: 3px; background-color: #1f6b3e;"></div>
  </div>

  <div class="row g-3 justify-content-center animate-grid">
    <div class="col-md-6 col-lg-3">
      <a href="submit_report.php" class="feature-card-link">
        <div class="card glass-card h-100 text-center p-3">
          <div class="card-body p-2">
            <div class="card-icon-frame d-inline-flex align-items-center justify-content-center rounded-circle mb-3">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h6 class="fw-bold" style="color: #1a202c;">Secure Submission</h6>
            <p class="small text-muted mb-0" style="font-size: 0.8rem;">Submit grievances safely with options for protected information encryption.</p>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-6 col-lg-3">
      <a href="service.php" class="feature-card-link">
        <div class="card glass-card h-100 text-center p-3">
          <div class="card-body p-2">
            <div class="card-icon-frame d-inline-flex align-items-center justify-content-center rounded-circle mb-3">
              <i class="fas fa-route"></i>
            </div>
            <h6 class="fw-bold" style="color: #1a202c;">Case Tracking</h6>
            <p class="small text-muted mb-0" style="font-size: 0.8rem;">Monitor case lifecycle transparently from submission to closure status.</p>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-6 col-lg-3">
      <a href="login.php" class="feature-card-link">
        <div class="card glass-card h-100 text-center p-3">
          <div class="card-body p-2">
            <div class="card-icon-frame d-inline-flex align-items-center justify-content-center rounded-circle mb-3">
              <i class="fas fa-users-cog"></i>
            </div>
            <h6 class="fw-bold" style="color: #1a202c;">Role Access</h6>
            <p class="small text-muted mb-0" style="font-size: 0.8rem;">Distinct access structures optimized for users, investigators, and admins.</p>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-6 col-lg-3">
      <a href="aboutus.php" class="feature-card-link">
        <div class="card glass-card h-100 text-center p-3">
          <div class="card-body p-2">
            <div class="card-icon-frame d-inline-flex align-items-center justify-content-center rounded-circle mb-3">
              <i class="fas fa-history"></i>
            </div>
            <h6 class="fw-bold" style="color: #1a202c;">Audit Trails</h6>
            <p class="small text-muted mb-0" style="font-size: 0.8rem;">Secure logging architecture and evidence storage for total accountability.</p>
          </div>
        </div>
      </a>
    </div>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>