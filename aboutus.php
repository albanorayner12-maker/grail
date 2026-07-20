<?php
require_once 'includes/header.php';
?>

<style>
  /* 1. STRUCTURAL ENTRANCE ANIMATIONS */
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .animate-header { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
  .animate-body { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; opacity: 0; }

  /* 2. WHITE GLASS CARDS WITH FINE DETAILS */
  .about-glass-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 14px rgba(148, 163, 184, 0.04) !important;
    padding: 2rem !important;
    transition: transform 0.3s ease, border-color 0.3s ease !important;
  }

  .about-glass-card:hover {
    border-color: rgba(31, 107, 62, 0.2) !important;
  }

  /* 3. ICON AND TYPOGRAPHY TREATMENT */
  .section-badge-icon {
    color: #1f6b3e; /* Brand Green */
    margin-right: 10px;
    font-size: 1.25rem;
  }

  .section-title {
    color: #1a202c !important;
    font-size: 1.3rem;
    font-weight: 700;
    letter-spacing: -0.3px;
  }

  p {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #4a5568;
  }

  p strong {
    color: #1f6b3e; /* Highlights key phrases in brand green subtly */
  }
</style>

<div class="container py-5">

    <div class="text-center mb-5 animate-header">
        <h1 class="fw-bold" style="color: #1a202c; font-size: 2.3rem; letter-spacing: -0.5px;">About Us</h1>
        <p class="lead text-muted" style="font-size: 0.95rem; color: #718096 !important;">Learn more about our mission, vision, and the team behind GRAIL.</p>
        <div class="mx-auto rounded mt-3" style="width: 30px; height: 3px; background-color: #1f6b3e;"></div>
    </div>

    <div class="row justify-content-center animate-body">
        <div class="col-lg-10">

            <div class="card about-glass-card mb-4">
                <div class="card-body p-0">
                    <h2 class="section-title mb-3 d-flex align-items-center">
                        <i class="fas fa-info-circle section-badge-icon"></i> About Us Overview
                    </h2>
                    <p class="lead fs-6 mb-3" style="color: #2d3748;">
                        Welcome to <strong>GRAIL System</strong> — your dedicated partner in building a transparent, accountable, and responsive environment.
                    </p>
                    <p class="mb-0">
                        We are committed to providing innovative and reliable solutions for individuals and organizations. Our platform is designed to bridge the gap between concern and resolution, ensuring every voice is heard and every incident is tracked with integrity.
                    </p>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card about-glass-card h-100">
                        <div class="card-body p-0">
                            <h2 class="section-title mb-3 d-flex align-items-center">
                                <i class="fas fa-bullseye section-badge-icon"></i> Our Mission
                            </h2>
                            <p class="mb-0">
                                To deliver <strong>cutting-edge technology</strong> and <strong>exceptional service</strong> that simplifies the complex process of grievance reporting and incident logging. We strive to enhance user productivity and organizational accountability through a seamless, secure, and user-centric platform.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card about-glass-card h-100">
                        <div class="card-body p-0">
                            <h2 class="section-title mb-3 d-flex align-items-center">
                                <i class="fas fa-eye section-badge-icon"></i> Our Vision
                            </h2>
                            <p class="mb-0">
                                To become a <strong>leading platform for intelligent system solutions</strong>, recognized globally for excellence, user satisfaction, and continuous innovation. We aspire to set the standard for digital accountability, fostering communities where transparency is the norm, not the exception.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card about-glass-card">
                <div class="card-body p-0">
                    <h2 class="section-title mb-3 d-flex align-items-center">
                        <i class="fas fa-users section-badge-icon"></i> Our Team
                    </h2>
                    <p class="mb-3">
                        Behind GRAIL is a <strong>dedicated group of professionals</strong> working collaboratively toward a common goal: empowering safer communities.
                    </p>
                    <p class="mb-0">
                        Our multidisciplinary team comprises <strong>developers, designers, system analysts, and support specialists</strong>. United by a passion for public service and technical excellence, we continuously refine GRAIL to meet the evolving needs of our users and administrators.
                    </p>
                </div>
            </div>

        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>