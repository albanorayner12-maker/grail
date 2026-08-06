<?php
require_once 'includes/header.php';
?>

<style>
  :root { --cream: #fff8ef; --yellow: #ffd449; --mint: #a7f3d0; --green: #548c2f; --deep-green: #104911; }
  html, body { background: var(--cream) !important; color: var(--deep-green) !important; }
  body { background-image: radial-gradient(circle at 0 15%, rgba(167,243,208,.4), transparent 28%), radial-gradient(circle at 100% 72%, rgba(84,140,47,.14), transparent 30%) !important; }

  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .animate-header { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
  .animate-grid { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; opacity: 0; }

  .services-hero { position: relative; overflow: hidden; padding: clamp(2.25rem,6vw,4rem); border: 1px solid rgba(84,140,47,.22); border-radius: 26px; background: var(--cream); box-shadow: 0 18px 45px rgba(16,73,17,.12); }
  .services-hero::after { content: ""; position: absolute; right: -90px; top: -115px; width: 260px; height: 260px; border-radius: 50%; background: var(--mint); opacity: .48; }
  .services-hero-content { position: relative; z-index: 1; }
  .services-eyebrow { display: inline-flex; align-items: center; gap: .5rem; padding: .45rem .85rem; border-radius: 999px; color: var(--deep-green); background: var(--mint); font-size: .9rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
  .services-title { color: var(--deep-green); font-size: clamp(2.15rem,5vw,3.4rem); letter-spacing: -.035em; }
  .services-intro { max-width: 680px; margin-inline: auto; color: var(--deep-green); font-size: clamp(1.05rem,2vw,1.2rem); line-height: 1.7; opacity: .78; }
  .section-marker { width: 44px; height: 4px; background: var(--yellow); }

  .feature-card-link {
    text-decoration: none !important;
    display: block;
    height: 100%;
  }

  .glass-card {
    background: var(--cream) !important;
    border: 1px solid rgba(84,140,47,.2) !important;
    border-radius: 18px !important;
    box-shadow: 0 8px 22px rgba(16,73,17,.07) !important;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
  }

  .card-icon-frame {
    width: 68px;
    height: 68px;
    background: var(--mint);
    border: 1px solid rgba(84,140,47,.2);
    color: var(--deep-green);
    transition: all 0.3s ease !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .feature-card-link:hover .glass-card {
    transform: translateY(-4px);
    border-color: var(--green) !important;
    box-shadow: 0 14px 28px rgba(16,73,17,.12) !important;
  }

  .feature-card-link:hover .card-icon-frame {
    background: var(--green) !important;
    color: var(--cream) !important;
    border-color: var(--green) !important;
  }

  .service-card-title { color: var(--deep-green); font-size: 1.2rem; }
  .service-card-copy { color: var(--deep-green) !important; font-size: 1rem !important; line-height: 1.65 !important; opacity: .8; }
  .nav-glass-container { background: linear-gradient(135deg,var(--deep-green),var(--green)) !important; border-color: rgba(255,248,239,.18); }
  .nav-link { color: var(--cream) !important; }
  .nav-link.active { color: var(--deep-green) !important; background: var(--cream) !important; }
  .location-box { color: var(--deep-green); background: var(--cream); border-color: rgba(84,140,47,.2); }
  .location-box .text-success, .location-box .text-dark, .location-box .text-muted { color: var(--deep-green) !important; }
  footer { color: var(--cream) !important; background: var(--deep-green) !important; }
  .navbar-toggler { border-color: rgba(255,248,239,.5); }
  .navbar-toggler-icon { filter: brightness(0) invert(1); }
  @media (max-width: 575.98px) { .services-hero { border-radius: 20px; } .top-header .location-box { padding: 8px 10px; } .top-header .location-address { display: none; } }
  @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; } }
</style>

<div class="container py-4 py-md-5">

    <header class="services-hero text-center mb-4 mb-md-5 animate-header">
      <div class="services-hero-content">
        <span class="services-eyebrow mb-3"><i class="fas fa-layer-group"></i> What we offer</span>
        <h1 class="services-title fw-bold mb-3">GRAIL Services</h1>
        <p class="services-intro mb-3">Explore tools for grievance submission, status tracking, and support throughout the grievance process.</p>
        <div class="section-marker mx-auto rounded"></div>
      </div>
    </header>

    <div class="row g-3 justify-content-center animate-grid">

        <div class="col-lg-4 col-md-6">
            <a href="<?= $base_url ?>submit_report.php" class="feature-card-link">
                <div class="card glass-card h-100 text-center p-3">
                    <div class="card-body p-2">
                        <div class="card-icon-frame rounded-circle mb-3">
                            <i class="fas fa-file-alt fs-5"></i>
                        </div>
                        <h2 class="service-card-title fw-bold">Grievance Submission</h2>
                        <p class="service-card-copy mb-0">
                            Provides a guided process for users to submit grievances with necessary details and attachments.
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6">
            <a href="<?= $base_url ?>track_case.php" class="feature-card-link">
                <div class="card glass-card h-100 text-center p-3">
                    <div class="card-body p-2">
                        <div class="card-icon-frame rounded-circle mb-3">
                            <i class="fas fa-chart-bar fs-5"></i>
                        </div>
                        <h2 class="service-card-title fw-bold">Status Tracking</h2>
                        <p class="service-card-copy mb-0">
                            Allows users to monitor the real-time status of their submissions from initial review to final resolution.
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6">
            <a href="<?= $base_url ?>support.php" class="feature-card-link">
                <div class="card glass-card h-100 text-center p-3">
                    <div class="card-body p-2">
                        <div class="card-icon-frame rounded-circle mb-3">
                            <i class="fas fa-life-ring fs-5"></i>
                        </div>
                        <h2 class="service-card-title fw-bold">User Support</h2>
                        <p class="service-card-copy mb-0">
                            Lists dedicated support services to assist users with system-related questions or issues.
                        </p>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
