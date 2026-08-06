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

  .animate-header { animation: fadeInUp .7s cubic-bezier(.16,1,.3,1) forwards; }
  .animate-body { animation: fadeInUp .7s cubic-bezier(.16,1,.3,1) .2s forwards; opacity: 0; }
  .policy-hero { position: relative; overflow: hidden; padding: clamp(2.25rem,6vw,4rem); border: 1px solid rgba(84,140,47,.22); border-radius: 26px; background: var(--cream); box-shadow: 0 18px 45px rgba(16,73,17,.12); }
  .policy-hero::after { content: ""; position: absolute; right: -90px; top: -115px; width: 260px; height: 260px; border-radius: 50%; background: var(--mint); opacity: .48; }
  .policy-hero-content { position: relative; z-index: 1; }
  .policy-eyebrow { display: inline-flex; align-items: center; gap: .5rem; padding: .45rem .85rem; border-radius: 999px; color: var(--deep-green); background: var(--mint); font-size: .9rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
  .policy-page-title { color: var(--deep-green); font-size: clamp(2.15rem,5vw,3.4rem); letter-spacing: -.035em; }
  .policy-intro { max-width: 680px; margin-inline: auto; color: var(--deep-green); font-size: clamp(1.05rem,2vw,1.2rem); line-height: 1.7; opacity: .78; }
  .section-marker { width: 44px; height: 4px; background: var(--yellow); }

  .policy-glass-card { background: var(--cream) !important; border: 1px solid rgba(84,140,47,.2) !important; border-radius: 18px !important; box-shadow: 0 8px 22px rgba(16,73,17,.07) !important; padding: clamp(1.5rem,4vw,2.5rem) !important; transition: transform .3s ease, border-color .3s ease, box-shadow .3s ease !important; }
  .policy-glass-card:hover { transform: translateY(-3px); border-color: var(--green) !important; box-shadow: 0 14px 28px rgba(16,73,17,.12) !important; }
  .section-badge-icon { width: 54px; height: 54px; flex: 0 0 54px; display: inline-flex; align-items: center; justify-content: center; color: var(--deep-green); background: var(--mint); margin-right: 14px; border-radius: 50%; font-size: 1.3rem; }
  .section-title { color: var(--deep-green) !important; font-size: clamp(1.3rem,3vw,1.55rem); font-weight: 700; letter-spacing: -.3px; }
  .policy-glass-card h3 { color: var(--deep-green); font-size: 1.1rem; }
  .policy-glass-card p { color: var(--deep-green); font-size: 1.05rem; line-height: 1.75; opacity: .8; }
  .definitions-list { list-style: none; padding-left: 0; margin-bottom: 0; }
  .definitions-list li { position: relative; padding-left: 22px; margin-bottom: 12px; color: var(--deep-green); font-size: 1.05rem; line-height: 1.65; opacity: .82; }
  .definitions-list li::before { content: "\2022"; position: absolute; left: 0; top: -2px; color: var(--green); font-size: 1.35rem; font-weight: 700; }
  .definitions-list strong { color: var(--deep-green); }
  .policy-divider { border: 0; height: 1px; background: rgba(84,140,47,.2); margin: 2rem 0; }
  .policy-contact { background: var(--deep-green); color: var(--cream); border-radius: 18px; }
  .policy-contact p { color: var(--mint); }
  .policy-contact-link { color: var(--deep-green) !important; background: var(--yellow); border: 2px solid var(--yellow); border-radius: 11px; font-weight: 700; text-decoration: none; }
  .policy-contact-link:hover { background: var(--cream); border-color: var(--cream); }

  .nav-glass-container { background: linear-gradient(135deg,var(--deep-green),var(--green)) !important; border-color: rgba(255,248,239,.18); }
  .nav-link { color: var(--cream) !important; }
  .nav-link.active { color: var(--deep-green) !important; background: var(--cream) !important; }
  .location-box { color: var(--deep-green); background: var(--cream); border-color: rgba(84,140,47,.2); }
  .location-box .text-success, .location-box .text-dark, .location-box .text-muted { color: var(--deep-green) !important; }
  footer { color: var(--cream) !important; background: var(--deep-green) !important; }
  .navbar-toggler { border-color: rgba(255,248,239,.5); }
  .navbar-toggler-icon { filter: brightness(0) invert(1); }

  @media (max-width: 575.98px) {
    .policy-hero { border-radius: 20px; }
    .top-header .location-box { padding: 8px 10px; }
    .top-header .location-address { display: none; }
    .section-title { align-items: flex-start !important; }
  }
  @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; } }
</style>

<div class="container py-4 py-md-5">
  <header class="policy-hero text-center mb-4 mb-md-5 animate-header">
    <div class="policy-hero-content">
      <span class="policy-eyebrow mb-3"><i class="fas fa-user-shield"></i> Your privacy matters</span>
      <h1 class="policy-page-title fw-bold mb-3">Privacy Policy</h1>
      <p class="policy-intro mb-2">Learn how GRAIL handles information and protects your privacy while you use the system.</p>
      <p class="mb-3"><strong>Last updated:</strong> <?= date('F j, Y'); ?></p>
      <div class="section-marker mx-auto rounded"></div>
    </div>
  </header>

  <div class="row justify-content-center animate-body">
    <div class="col-lg-10">
      <article class="card policy-glass-card">
        <div class="card-body p-0">
          <section aria-labelledby="policy-overview-title">
            <h2 id="policy-overview-title" class="section-title mb-3 d-flex align-items-center">
              <i class="fas fa-shield-halved section-badge-icon"></i> General Policy Overview
            </h2>
            <p>This section describes the policies and procedures on the collection, use, and disclosure of your information when you use the Service.</p>
            <p class="mb-0">We use your personal data to provide and improve the Service. By using the Service, you agree to the collection and use of information in accordance with this Privacy Policy.</p>
          </section>

          <div class="policy-divider"></div>

          <section aria-labelledby="definitions-title">
            <h2 id="definitions-title" class="section-title mb-3 d-flex align-items-center">
              <i class="fas fa-book section-badge-icon"></i> Interpretation and Definitions
            </h2>
            <h3 class="mt-4 fw-bold mb-2">Interpretation</h3>
            <p>The words whose initial letters are capitalized have meanings defined under the following conditions.</p>
            <h3 class="mt-4 fw-bold mb-2">Definitions</h3>
            <p class="mb-3">For the purposes of this Privacy Policy:</p>
            <ul class="definitions-list">
              <li><strong>Account</strong> means a unique account created for you to access our Service.</li>
              <li><strong>Company</strong> refers to <strong>GRAIL System</strong>.</li>
            </ul>
          </section>
        </div>
      </article>

      <section class="policy-contact mt-4 p-4 p-md-5 d-md-flex align-items-center justify-content-between gap-4" aria-labelledby="privacy-help-title">
        <div>
          <h2 id="privacy-help-title" class="h4 fw-bold mb-2">Questions about this policy?</h2>
          <p class="mb-3 mb-md-0">Contact the GRAIL team for privacy-related questions or assistance.</p>
        </div>
        <a href="<?= $base_url ?>contact.php" class="policy-contact-link px-4 py-3 flex-shrink-0"><i class="fas fa-paper-plane me-2"></i> Contact Us</a>
      </section>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
