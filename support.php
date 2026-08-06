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
      radial-gradient(circle at 100% 72%, rgba(84, 140, 47, .14), transparent 30%) !important;
  }

  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .animate-header { animation: fadeInUp .7s cubic-bezier(.16, 1, .3, 1) forwards; }
  .animate-body { animation: fadeInUp .7s cubic-bezier(.16, 1, .3, 1) .18s forwards; opacity: 0; }

  .support-hero {
    position: relative;
    overflow: hidden;
    padding: clamp(2.25rem, 6vw, 4rem);
    background: var(--cream);
    border: 1px solid rgba(84, 140, 47, .22);
    border-radius: 26px;
    box-shadow: 0 18px 45px rgba(16, 73, 17, .12);
  }

  .support-hero::after {
    content: "";
    position: absolute;
    width: 280px;
    height: 280px;
    right: -110px;
    top: -130px;
    border-radius: 50%;
    background: var(--mint);
    opacity: .48;
    pointer-events: none;
  }

  .support-hero-content { position: relative; z-index: 1; }

  .support-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .45rem .85rem;
    color: var(--deep-green);
    background: var(--mint);
    border-radius: 999px;
    font-size: .9rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
  }

  .support-title {
    color: var(--deep-green);
    font-size: clamp(2.15rem, 5vw, 3.4rem);
    letter-spacing: -.035em;
    line-height: 1.08;
  }

  .support-intro {
    max-width: 720px;
    margin-inline: auto;
    color: var(--deep-green);
    font-size: clamp(1.05rem, 2vw, 1.2rem);
    line-height: 1.7;
    opacity: .78;
  }

  .section-marker { width: 44px; height: 4px; background: var(--yellow); }

  .support-card {
    height: 100%;
    padding: clamp(1.5rem, 4vw, 2.25rem);
    background: var(--cream) !important;
    border: 1px solid rgba(84, 140, 47, .2) !important;
    border-radius: 18px !important;
    box-shadow: 0 8px 22px rgba(16, 73, 17, .07) !important;
    transition: transform .3s ease, border-color .3s ease, box-shadow .3s ease;
  }

  .support-card:hover {
    transform: translateY(-3px);
    border-color: var(--green) !important;
    box-shadow: 0 14px 28px rgba(16, 73, 17, .12) !important;
  }

  .faq-icon {
    width: 58px;
    height: 58px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 58px;
    color: var(--deep-green);
    background: var(--mint);
    border: 1px solid rgba(84, 140, 47, .18);
    border-radius: 50%;
    font-size: 1.25rem;
  }

  .faq-question {
    color: var(--deep-green);
    font-size: 1.18rem;
    line-height: 1.4;
  }

  .faq-answer {
    color: var(--deep-green);
    font-size: 1.02rem;
    line-height: 1.7;
    opacity: .8;
  }

  .support-cta {
    color: var(--cream);
    background: var(--deep-green);
    border-radius: 18px;
  }

  .support-cta p { color: var(--mint); font-size: 1.05rem; }

  .support-cta-link {
    min-height: 50px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--deep-green) !important;
    background: var(--yellow);
    border: 2px solid var(--yellow);
    border-radius: 11px;
    font-weight: 700;
    transition: all .25s ease;
  }

  .support-cta-link:hover {
    background: var(--cream);
    border-color: var(--cream);
    transform: translateY(-1px);
  }

  .nav-glass-container { background: linear-gradient(135deg, var(--deep-green), var(--green)) !important; border-color: rgba(255, 248, 239, .18); }
  .nav-link { color: var(--cream) !important; }
  .nav-link.active { color: var(--deep-green) !important; background: var(--cream) !important; }
  .location-box { color: var(--deep-green); background: var(--cream); border-color: rgba(84, 140, 47, .2); }
  .location-box .text-success, .location-box .text-dark, .location-box .text-muted { color: var(--deep-green) !important; }
  footer { color: var(--cream) !important; background: var(--deep-green) !important; }
  .navbar-toggler { border-color: rgba(255, 248, 239, .5); }
  .navbar-toggler-icon { filter: brightness(0) invert(1); }

  @media (max-width: 575.98px) {
    .support-hero { border-radius: 20px; }
    .top-header .location-box { padding: 8px 10px; }
    .top-header .location-address { display: none; }
    .faq-heading { align-items: flex-start !important; }
  }

  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
  }
</style>

<div class="container py-4 py-md-5">
  <header class="support-hero text-center mb-4 mb-md-5 animate-header">
    <div class="support-hero-content">
      <span class="support-eyebrow mb-3"><i class="fas fa-headset"></i> Help center</span>
      <h1 class="support-title fw-bold mb-3">User Support Hub</h1>
      <p class="support-intro mb-3">Find clear answers to common questions and learn how to use the GRAIL workspace securely and effectively.</p>
      <div class="section-marker mx-auto rounded"></div>
    </div>
  </header>

  <section class="animate-body" aria-labelledby="faq-title">
    <div class="text-center mb-4">
      <h2 id="faq-title" class="h3 fw-bold mb-2" style="color: var(--deep-green);">Frequently Asked Questions</h2>
      <p class="mb-0" style="color: var(--deep-green); opacity: .72;">Quick guidance for reporting, tracking, and case management.</p>
    </div>

    <div class="row g-4 justify-content-center">
      <div class="col-lg-10">
        <article class="card support-card">
          <div class="card-body p-0">
            <div class="d-flex gap-3 align-items-center faq-heading mb-3">
              <span class="faq-icon" aria-hidden="true"><i class="fas fa-user-secret"></i></span>
              <h3 class="faq-question fw-bold mb-0">How do I safely monitor an anonymous case?</h3>
            </div>
            <p class="faq-answer mb-0">When submitting a report without credentials, you will receive a private cryptographic reference code. Store this code safely, as it is the only identifier you can use to check your case status through the tracking interface.</p>
          </div>
        </article>
      </div>

      <div class="col-lg-10">
        <article class="card support-card">
          <div class="card-body p-0">
            <div class="d-flex gap-3 align-items-center faq-heading mb-3">
              <span class="faq-icon" aria-hidden="true"><i class="fas fa-clock"></i></span>
              <h3 class="faq-question fw-bold mb-0">What is the average timeline before processing starts?</h3>
            </div>
            <p class="faq-answer mb-0">The routing desk reviews logged cases within a standard window of 24 operating hours, then moves each report into the appropriate initial investigation phase.</p>
          </div>
        </article>
      </div>

      <div class="col-lg-10">
        <article class="card support-card">
          <div class="card-body p-0">
            <div class="d-flex gap-3 align-items-center faq-heading mb-3">
              <span class="faq-icon" aria-hidden="true"><i class="fas fa-users-gear"></i></span>
              <h3 class="faq-question fw-bold mb-0">Who manages the assigned tracking parameters?</h3>
            </div>
            <p class="faq-answer mb-0">Concerns are routed to the designated administrators of the College of Information Technology Education (CITE) and the relevant technical teams to support an accurate response.</p>
          </div>
        </article>
      </div>
    </div>

    <section class="support-cta mt-4 mt-md-5 p-4 p-md-5 d-md-flex align-items-center justify-content-between gap-4" aria-labelledby="support-cta-title">
      <div>
        <h2 id="support-cta-title" class="h4 fw-bold mb-2">Still need assistance?</h2>
        <p class="mb-3 mb-md-0">Send your question to the GRAIL support team and we’ll point you in the right direction.</p>
      </div>
      <a href="contact.php" class="btn support-cta-link px-4 flex-shrink-0"><i class="fas fa-paper-plane me-2"></i> Contact Support</a>
    </section>
  </section>
</div>

<?php require_once 'includes/footer.php'; ?>
