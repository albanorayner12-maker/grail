<?php
require_once 'includes/header.php';
?>

<style>
  /* 1. STRUCTURAL ENTRANCE ANIMATIONS (Matching index.php) */
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .animate-header { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
  .animate-grid { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; opacity: 0; }

  /* 2. PREMIUM STRIP-BACK GLASS TILES */
  .feature-card-link {
    text-decoration: none !important;
    display: block;
    height: 100%;
  }

  .glass-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 14px rgba(148, 163, 184, 0.05) !important;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
  }

  /* 3. BRAND ICON FRAMES WITH ACCENT GREEN BORDER */
  .card-icon-frame {
    width: 55px;
    height: 55px;
    background: rgba(31, 107, 62, 0.06);
    border: 1px solid rgba(31, 107, 62, 0.15);
    color: #1f6b3e; /* Signature Brand Green from Screenshot */
    transition: all 0.3s ease !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  /* 4. MICRO-INTERACTION HOVER MECHANICS */
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
</style>

<div class="container py-5">

    <div class="text-center mb-5 animate-header">
        <h1 class="fw-bold" style="color: #1a202c; font-size: 2.3rem; letter-spacing: -0.5px;">Services Offered</h1>
        <p class="lead text-muted" style="font-size: 0.95rem; color: #718096 !important;">Explore the core features of the GRAIL System</p>
        <div class="mx-auto rounded mt-3" style="width: 30px; height: 3px; background-color: #1f6b3e;"></div>
    </div>

    <div class="row g-3 justify-content-center animate-grid">

        <div class="col-lg-4 col-md-6">
            <a href="submit_report.php" class="feature-card-link">
                <div class="card glass-card h-100 text-center p-3">
                    <div class="card-body p-2">
                        <div class="card-icon-frame rounded-circle mb-3">
                            <i class="fas fa-file-alt fs-5"></i>
                        </div>
                        <h6 class="fw-bold" style="color: #1a202c;">Grievance Submission</h6>
                        <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Provides a guided process for users to submit grievances with necessary details and attachments.
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6">
            <a href="track_case.php" class="feature-card-link">
                <div class="card glass-card h-100 text-center p-3">
                    <div class="card-body p-2">
                        <div class="card-icon-frame rounded-circle mb-3">
                            <i class="fas fa-chart-bar fs-5"></i>
                        </div>
                        <h6 class="fw-bold" style="color: #1a202c;">Status Tracking</h6>
                        <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Allows users to monitor the real-time status of their submissions from initial review to final resolution.
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6">
            <a href="analytics.php" class="feature-card-link">
                <div class="card glass-card h-100 text-center p-3">
                    <div class="card-body p-2">
                        <div class="card-icon-frame rounded-circle mb-3">
                            <i class="fas fa-line-chart fs-5"></i>
                        </div>
                        <h6 class="fw-bold" style="color: #1a202c;">Insights and Analytics</h6>
                        <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Offers data-driven insights regarding grievance trends and resolution times to help improve organizational processes.
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6">
            <a href="messages.php" class="feature-card-link">
                <div class="card glass-card h-100 text-center p-3">
                    <div class="card-body p-2">
                        <div class="card-icon-frame rounded-circle mb-3">
                            <i class="fas fa-lock fs-5"></i>
                        </div>
                        <h6 class="fw-bold" style="color: #1a202c;">Secure Communication</h6>
                        <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Facilitates secure messaging between users, administrators, and relevant parties concerning specific grievances.
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6">
            <a href="generate_reports.php" class="feature-card-link">
                <div class="card glass-card h-100 text-center p-3">
                    <div class="card-body p-2">
                        <div class="card-icon-frame rounded-circle mb-3">
                            <i class="fas fa-file-text fs-5"></i>
                        </div>
                        <h6 class="fw-bold" style="color: #1a202c;">Reporting Tools</h6>
                        <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Enables the generation of comprehensive reports on grievance data for the purposes of internal review and compliance.
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6">
            <a href="support.php" class="feature-card-link">
                <div class="card glass-card h-100 text-center p-3">
                    <div class="card-body p-2">
                        <div class="card-icon-frame rounded-circle mb-3">
                            <i class="fas fa-life-ring fs-5"></i>
                        </div>
                        <h6 class="fw-bold" style="color: #1a202c;">User Support</h6>
                        <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                            Lists dedicated support services to assist users with system-related questions or issues.
                        </p>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>