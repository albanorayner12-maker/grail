<?php
require_once 'includes/header.php';
?>

<style>
  :root {
    --cream: #fff8ef;
    --yellow: #ffd449;
    --mint: #a7f3d0;
    --green: #548c2f;
    --deep-green: #104911;
  }

  html, body {
    background: var(--cream) !important;
    color: var(--deep-green) !important;
  }

  body {
    background-image:
      radial-gradient(circle at 0 15%, rgba(167, 243, 208, .4), transparent 28%),
      radial-gradient(circle at 100% 70%, rgba(84, 140, 47, .14), transparent 30%) !important;
  }

  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .animate-hero { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
  .animate-header { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards; opacity: 0; }
  .animate-grid { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; opacity: 0; }

  .hero-glass-panel {
    position: relative;
    overflow: hidden;
    background: var(--cream);
    border: 1px solid rgba(84, 140, 47, .22);
    border-radius: 26px;
    box-shadow: 0 18px 45px rgba(16, 73, 17, .12);
  }

  .hero-glass-panel::before {
    content: "";
    position: absolute;
    width: 340px;
    height: 340px;
    right: -180px;
    top: -190px;
    border-radius: 50%;
    background: var(--mint);
    opacity: .42;
    pointer-events: none;
  }

  .hero-content, .hero-logos { position: relative; z-index: 1; }
  .hero-title { color: var(--deep-green); font-size: clamp(2rem, 5vw, 3.35rem); letter-spacing: -.035em; line-height: 1.08; }
  .hero-copy { max-width: 650px; color: var(--deep-green); font-size: clamp(1rem, 2vw, 1.15rem); line-height: 1.7; opacity: .78; }
  .hero-logo { width: clamp(90px, 12vw, 130px); max-height: 130px; object-fit: contain; filter: drop-shadow(0 7px 12px rgba(16, 73, 17, .12)); }

  .eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .45rem .8rem;
    border-radius: 999px;
    color: var(--deep-green);
    background: var(--mint);
    font-size: .85rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
  }

  .feature-card-link {
    text-decoration: none !important;
    display: block;
    height: 100%;
  }

  .glass-card {
    background: var(--cream) !important;
    border: 1px solid rgba(84, 140, 47, .2) !important;
    border-radius: 18px !important;
    box-shadow: 0 8px 22px rgba(16, 73, 17, .07) !important;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
  }

  .card-icon-frame {
    width: 68px;
    height: 68px;
    background: var(--mint);
    border: 1px solid rgba(84, 140, 47, .2);
    color: var(--deep-green);
    transition: all 0.3s ease !important;
    font-size: 1.45rem;
  }

  .feature-card-link:hover .glass-card {
    transform: translateY(-4px);
    border-color: var(--green) !important;
    box-shadow: 0 14px 28px rgba(16, 73, 17, .12) !important;
  }

  .feature-card-link:hover .card-icon-frame {
    background: var(--green) !important;
    color: var(--cream) !important;
    border-color: var(--green) !important;
  }

  .btn-premium-solid {
    background: var(--green);
    color: var(--cream) !important;
    font-weight: 700;
    border-radius: 12px;
    border: 2px solid var(--green);
    transition: all 0.25s ease;
  }
  .btn-premium-solid:hover {
    background: var(--deep-green);
    border-color: var(--deep-green);
    transform: translateY(-1px);
    box-shadow: 0 6px 15px rgba(16, 73, 17, .2);
  }

  .btn-premium-outline {
    background: transparent;
    color: var(--deep-green) !important;
    border: 2px solid var(--green);
    font-weight: 700;
    border-radius: 12px;
    transition: all 0.25s ease;
  }
  .btn-premium-outline:hover {
    background: var(--mint);
    border-color: var(--green);
    transform: translateY(-1px);
  }

  .btn-premium-solid, .btn-premium-outline
   { min-height: 50px; display: inline-flex; align-items: center; justify-content: center;}
  .feature-title, .section-title { color: var(--deep-green); }
  .feature-title { font-size: 1.25rem; }
  .feature-copy { color: var(--deep-green); font-size: 1.05rem; line-height: 1.65; opacity: .82; }
  .section-marker { width: 42px; height: 4px; background: var(--yellow); }

  .quick-track {
    color: var(--cream);
    background: var(--deep-green);
    border-radius: 18px;
  }
  .quick-track p { color: var(--mint); }
  .quick-track h2 { font-size: clamp(1.35rem, 3vw, 1.7rem); }
  .quick-track p { font-size: 1.05rem; }
  .track-link { color: var(--deep-green); background: var(--yellow); border: 2px solid var(--yellow); border-radius: 11px; font-weight: 700; }
  .track-link:hover { color: var(--deep-green); background: var(--cream); border-color: var(--cream); }

  /* Keep the shared header and footer on this page within the page palette. */
  .nav-glass-container { background: linear-gradient(135deg, var(--deep-green), var(--green)) !important; border-color: rgba(255, 248, 239, .18); }
  .nav-link { color: var(--cream) !important; }
  .nav-link.active { color: var(--deep-green) !important; background: var(--cream) !important; }
  .location-box { color: var(--deep-green); background: var(--cream); border-color: rgba(84, 140, 47, .2); }
  .location-box .text-success, .location-box .text-dark, .location-box .text-muted { color: var(--deep-green) !important; }
  footer { color: var(--cream) !important; background: var(--deep-green) !important; }
  .navbar-toggler { border-color: rgba(255, 248, 239, .5); }
  .navbar-toggler-icon { filter: brightness(0) invert(1); }

  @media (max-width: 991.98px) {
    .navbar-nav { padding-top: .75rem; }
    .nav-link { text-align: left; }
    .hero-logos { margin-top: .75rem; }
  }

  @media (max-width: 575.98px) {
    .top-header .location-box { padding: 8px 10px; }
    .top-header .location-address { display: none; }
    .hero-actions { flex-direction: column; }
    .hero-actions .btn { width: 100%; }
    .hero-glass-panel { border-radius: 20px; }
  }

  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
  }
</style>

<div class="container my-3 animate-hero">
  <div class="p-4 p-md-5 hero-glass-panel">
    <div class="row align-items-center g-4">
      <div class="col-lg-7 hero-content">
        <span class="eyebrow mb-3"><i class="fas fa-shield-halved"></i> Secure reporting</span>
        <h1 class="hero-title fw-bold mb-3">
          GRAIL: Incident Logging &amp; Accountability
        </h1>
        <p class="hero-copy mb-4">
          A secure, streamlined workspace tailored for the CITE community. Track, investigate, and audit system issues transparently with cryptographic integrity.
        </p>
        <div class="d-flex flex-wrap gap-3 hero-actions">
          <a href="submit_report.php" class="btn btn-premium-solid px-4">
            <i class="fas fa-user-edit me-2"></i> File Report
          </a>
          <a href="login.php" class="btn btn-premium-outline px-4">
            <i class="fas fa-shield-alt me-2"></i> Admin Portal
          </a>
        </div>
      </div>
      <div class="col-lg-5 text-center hero-logos">
        <div class="d-flex justify-content-center align-items-center gap-3">
          <img src="assets/css/img/nvsu-logo.png" alt="NVSU Logo" class="hero-logo" onerror="this.style.display='none'">
          <img src="assets/css/img/cite-logo.png" alt="CITE Logo" class="hero-logo" onerror="this.style.display='none'">
        </div>
      </div>
    </div>
  </div>
</div>

<section class="container my-5" aria-labelledby="features-title">
  <div class="text-center mb-4 animate-header">
    <h2 id="features-title" class="section-title fw-bold fs-3">System Features</h2>
    <div class="section-marker mx-auto rounded"></div>
  </div>

  <div class="row g-3 justify-content-center animate-grid">
    <div class="col-md-6 col-lg-3">
      <a href="submit_report.php" class="feature-card-link">
        <div class="card glass-card h-100 text-center p-3 p-xl-4">
          <div class="card-body p-2 p-xl-3">
            <div class="card-icon-frame d-inline-flex align-items-center justify-content-center rounded-circle mb-3">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h3 class="feature-title h5 fw-bold">Secure Submission</h3>
            <p class="feature-copy mb-0">Submit grievances safely with options for protected information encryption.</p>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-6 col-lg-3">
      <a href="service.php" class="feature-card-link">
        <div class="card glass-card h-100 text-center p-3 p-xl-4">
          <div class="card-body p-2 p-xl-3">
            <div class="card-icon-frame d-inline-flex align-items-center justify-content-center rounded-circle mb-3">
              <i class="fas fa-route"></i>
            </div>
            <h3 class="feature-title h5 fw-bold">Case Tracking</h3>
            <p class="feature-copy mb-0">Monitor case lifecycle transparently from submission to closure status.</p>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-6 col-lg-3">
      <a class="feature-card-link">
        <div class="card glass-card h-100 text-center p-3 p-xl-4">
          <div class="card-body p-2 p-xl-3">
            <div class="card-icon-frame d-inline-flex align-items-center justify-content-center rounded-circle mb-3">
              <i class="fas fa-users-cog"></i>
            </div>
            <h3 class="feature-title h5 fw-bold">Role Access</h3>
            <p class="feature-copy mb-0">Distinct access structures optimized for users, investigators, and admins.</p>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-6 col-lg-3">
      <a href="aboutus.php" class="feature-card-link">
        <div class="card glass-card h-100 text-center p-3 p-xl-4">
          <div class="card-body p-2 p-xl-3">
            <div class="card-icon-frame d-inline-flex align-items-center justify-content-center rounded-circle mb-3">
              <i class="fas fa-history"></i>
            </div>
            <h3 class="feature-title h5 fw-bold">Audit Trails</h3>
            <p class="feature-copy mb-0">Secure logging architecture and evidence storage for total accountability.</p>
          </div>
        </div>
      </a>
    </div>
  </div>

  <section class="quick-track mt-4 mt-md-5 p-4 p-md-5 d-md-flex align-items-center justify-content-between gap-4 animate-grid" aria-labelledby="track-title">
    <div>
      <h2 id="track-title" class="h4 fw-bold mb-2">Already submitted a report?</h2>
      <p class="mb-3 mb-md-0">Check its latest status securely using your case reference.</p>
    </div>
    <a href="track_case.php" class="btn track-link px-4 py-3 flex-shrink-0"><i class="fas fa-magnifying-glass me-2"></i> Track a Case</a>
  </section>
</section>

<?php require_once 'includes/footer.php'; ?>
