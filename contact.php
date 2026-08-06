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
  .animate-grid { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards; opacity: 0; }
  .animate-form { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.25s forwards; opacity: 0; }

  .contact-hero { position: relative; overflow: hidden; padding: clamp(2.25rem,6vw,4rem); border: 1px solid rgba(84,140,47,.22); border-radius: 26px; background: var(--cream); box-shadow: 0 18px 45px rgba(16,73,17,.12); }
  .contact-hero::after { content: ""; position: absolute; right: -90px; top: -115px; width: 260px; height: 260px; border-radius: 50%; background: var(--mint); opacity: .48; }
  .contact-hero-content { position: relative; z-index: 1; }
  .contact-eyebrow { display: inline-flex; align-items: center; gap: .5rem; padding: .45rem .85rem; border-radius: 999px; color: var(--deep-green); background: var(--mint); font-size: .9rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
  .contact-title { color: var(--deep-green); font-size: clamp(2.15rem,5vw,3.4rem); letter-spacing: -.035em; }
  .contact-intro { max-width: 680px; margin-inline: auto; color: var(--deep-green); font-size: clamp(1.05rem,2vw,1.2rem); line-height: 1.7; opacity: .78; }
  .section-marker { width: 44px; height: 4px; background: var(--yellow); }

  .contact-glass-card {
    background: var(--cream) !important;
    border: 1px solid rgba(84,140,47,.2) !important;
    border-radius: 18px !important;
    box-shadow: 0 8px 22px rgba(16,73,17,.07) !important;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
  }

  .contact-glass-card a {
    color: var(--deep-green) !important;
    font-weight: 600;
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

  .contact-glass-card:hover {
    transform: translateY(-4px);
    border-color: var(--green) !important;
    box-shadow: 0 14px 28px rgba(16,73,17,.12) !important;
  }

  .contact-glass-card:hover .card-icon-frame {
    background: var(--green) !important;
    color: var(--cream) !important;
    border-color: var(--green) !important;
  }

  .form-control {
    background-color: #fffdf8 !important;
    border: 1px solid rgba(84,140,47,.35) !important;
    border-radius: 11px !important;
    padding: 12px 14px;
    color: var(--deep-green) !important;
    font-size: 1rem;
    transition: all 0.2s ease-in-out !important;
  }

  .form-control:focus {
    border-color: var(--green) !important;
    box-shadow: 0 0 0 3px rgba(84,140,47,.15) !important;
    outline: none;
  }

  .form-label {
    font-weight: 700;
    font-size: .92rem;
    color: var(--deep-green);
    margin-bottom: 6px;
  }

  .btn-premium-solid {
    background: var(--green);
    color: var(--cream) !important;
    font-weight: 700;
    font-size: 1rem;
    border-radius: 12px;
    border: 2px solid var(--green);
    transition: all 0.25s ease;
  }
  
  .btn-premium-solid:hover {
    background: var(--deep-green);
    border-color: var(--deep-green);
    transform: translateY(-1px);
    box-shadow: 0 6px 15px rgba(16,73,17,.2);
  }
  .contact-card-title, .form-title { color: var(--deep-green); }
  .contact-copy, .contact-copy.text-muted, .contact-copy .text-muted { color: var(--deep-green) !important; opacity: .8; }
  .nav-glass-container { background: linear-gradient(135deg,var(--deep-green),var(--green)) !important; border-color: rgba(255,248,239,.18); }
  .nav-link { color: var(--cream) !important; }
  .nav-link.active { color: var(--deep-green) !important; background: var(--cream) !important; }
  .location-box { color: var(--deep-green); background: var(--cream); border-color: rgba(84,140,47,.2); }
  .location-box .text-success, .location-box .text-dark, .location-box .text-muted { color: var(--deep-green) !important; }
  footer { color: var(--cream) !important; background: var(--deep-green) !important; }
  .navbar-toggler { border-color: rgba(255,248,239,.5); }
  .navbar-toggler-icon { filter: brightness(0) invert(1); }
  @media (max-width: 575.98px) { .contact-hero { border-radius: 20px; } .top-header .location-box { padding: 8px 10px; } .top-header .location-address { display: none; } }
  @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; } }
</style>

<div class="container py-4 py-md-5">

    <header class="contact-hero text-center mb-4 mb-md-5 animate-header">
      <div class="contact-hero-content">
        <span class="contact-eyebrow mb-3"><i class="fas fa-paper-plane"></i> Get in touch</span>
        <h1 class="contact-title fw-bold mb-3">Contact GRAIL</h1>
        <p class="contact-intro mb-3">Reach out to our team for support, inquiries, or feedback. We are ready to help you use GRAIL confidently.</p>
        <div class="section-marker mx-auto rounded"></div>
      </div>
    </header>

    <div class="row g-3 justify-content-center animate-grid">

        <div class="col-md-4">
            <div class="card contact-glass-card h-100 text-center p-3">
                <div class="card-body p-2">
                    <div class="card-icon-frame rounded-circle mb-3">
                        <i class="fas fa-envelope fs-5"></i>
                    </div>
                    <h2 class="contact-card-title h5 fw-bold mb-3">Email Support</h2>
                    <p class="small mb-0">
                        <a href="mailto:support@grailsystem.com" class="text-decoration-none">support@grailsystem.com</a>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card contact-glass-card h-100 text-center p-3">
                <div class="card-body p-2">
                    <div class="card-icon-frame rounded-circle mb-3">
                        <i class="fas fa-phone fs-5"></i>
                    </div>
                    <h2 class="contact-card-title h5 fw-bold mb-3">Phone Support</h2>
                    <p class="contact-copy mb-0">
                        <a href="tel:+63936723444" class="text-decoration-none">+63 936 723 444</a><br>
                        <span class="text-muted" style="font-size: 0.75rem; display: block; margin-top: 4px;">Monday - Friday, 9 AM - 5 PM</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card contact-glass-card h-100 text-center p-3">
                <div class="card-body p-2">
                    <div class="card-icon-frame rounded-circle mb-3">
                        <i class="fas fa-map-marker-alt fs-5"></i>
                    </div>
                    <h2 class="contact-card-title h5 fw-bold mb-3">Mailing Address</h2>
                    <p class="contact-copy mb-0">
                        Quezon St., Bayombong,<br>
                        Nueva Vizcaya, 3700,<br>
                        Philippines
                    </p>
                </div>
            </div>
        </div>

    </div>

    <div class="row mt-5 justify-content-center animate-form">
        <div class="col-lg-8">
            <div class="card contact-glass-card p-4">
                <div class="card-body p-2">
                    <h2 class="form-title h4 fw-bold text-center mb-4">Send Us a Quick Message</h2>
                    <form action="<?= $base_url ?>contact.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="johndoe@gmail.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="4" placeholder="How can we assist you?" required></textarea>
                            </div>
                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-premium-solid px-5 py-2">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
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
