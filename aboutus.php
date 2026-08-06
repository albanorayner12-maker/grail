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
  .animate-body { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; opacity: 0; }

  .about-hero { position: relative; overflow: hidden; padding: clamp(2.25rem,6vw,4rem); border: 1px solid rgba(84,140,47,.22); border-radius: 26px; background: var(--cream); box-shadow: 0 18px 45px rgba(16,73,17,.12); }
  .about-hero::after { content: ""; position: absolute; right: -90px; top: -115px; width: 260px; height: 260px; border-radius: 50%; background: var(--mint); opacity: .48; }
  .about-hero-content { position: relative; z-index: 1; }
  .about-eyebrow { display: inline-flex; align-items: center; gap: .5rem; padding: .45rem .85rem; border-radius: 999px; color: var(--deep-green); background: var(--mint); font-size: .9rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
  .about-page-title { color: var(--deep-green); font-size: clamp(2.15rem,5vw,3.4rem); letter-spacing: -.035em; }
  .about-intro { max-width: 680px; margin-inline: auto; color: var(--deep-green); font-size: clamp(1.05rem,2vw,1.2rem); line-height: 1.7; opacity: .78; }
  .section-marker { width: 44px; height: 4px; background: var(--yellow); }

  .about-glass-card {
    background: var(--cream) !important;
    border: 1px solid rgba(84,140,47,.2) !important;
    border-radius: 18px !important;
    box-shadow: 0 8px 22px rgba(16,73,17,.07) !important;
    padding: clamp(1.5rem,4vw,2.25rem) !important;
    transition: transform .3s ease, border-color .3s ease, box-shadow .3s ease !important;
  }

  .about-glass-card:hover {
    transform: translateY(-3px);
    border-color: var(--green) !important;
    box-shadow: 0 14px 28px rgba(16,73,17,.12) !important;
  }

  .section-badge-icon {
    width: 54px; height: 54px; flex: 0 0 54px; display: inline-flex; align-items: center; justify-content: center;
    color: var(--deep-green); background: var(--mint); margin-right: 14px; border-radius: 50%; font-size: 1.3rem;
  }

  .section-title {
    color: var(--deep-green) !important;
    font-size: clamp(1.3rem,3vw,1.55rem);
    font-weight: 700;
    letter-spacing: -0.3px;
  }

  .about-glass-card p {
    font-size: 1.05rem;
    line-height: 1.75;
    color: var(--deep-green);
    opacity: .8;
  }

  .about-glass-card p strong { color: var(--deep-green); opacity: 1; }
  .nav-glass-container { background: linear-gradient(135deg,var(--deep-green),var(--green)) !important; border-color: rgba(255,248,239,.18); }
  .nav-link { color: var(--cream) !important; }
  .nav-link.active { color: var(--deep-green) !important; background: var(--cream) !important; }
  .location-box { color: var(--deep-green); background: var(--cream); border-color: rgba(84,140,47,.2); }
  .location-box .text-success, .location-box .text-dark, .location-box .text-muted { color: var(--deep-green) !important; }
  footer { color: var(--cream) !important; background: var(--deep-green) !important; }
  .navbar-toggler { border-color: rgba(255,248,239,.5); }
  .navbar-toggler-icon { filter: brightness(0) invert(1); }
  @media (max-width: 575.98px) { .about-hero { border-radius: 20px; } .top-header .location-box { padding: 8px 10px; } .top-header .location-address { display: none; } .section-title { align-items: flex-start !important; } }
  @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; } }
</style>

<div class="container py-4 py-md-5">

    <header class="about-hero text-center mb-4 mb-md-5 animate-header">
      <div class="about-hero-content">
        <span class="about-eyebrow mb-3"><i class="fas fa-people-group"></i> Who we are</span>
        <h1 class="about-page-title fw-bold mb-3">About GRAIL</h1>
        <p class="about-intro mb-3">Learn more about our mission, vision, and the people building a transparent and accountable reporting environment.</p>
        <div class="section-marker mx-auto rounded"></div>
      </div>
    </header>

    <div class="row justify-content-center animate-body">
        <div class="col-lg-10">

            <div class="card about-glass-card mb-4">
                <div class="card-body p-0">
                    <h2 class="section-title mb-3 d-flex align-items-center">
                        <i class="fas fa-info-circle section-badge-icon"></i> About Us Overview
                    </h2>
                    <p class="lead mb-3">
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
                        Behind GRAIL is a <strong>dedicated group of individuals</strong> working collaboratively toward a common goal: empowering safer communities.
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
