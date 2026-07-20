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
  .animate-grid { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards; opacity: 0; }
  .animate-form { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.25s forwards; opacity: 0; }

  /* 2. PREMIUM STRIP-BACK GLASS CARDS */
  .contact-glass-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 14px rgba(148, 163, 184, 0.05) !important;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
  }

  .contact-glass-card a {
    color: #1f6b3e !important;
    font-weight: 500;
  }

  /* 3. BRAND ICON FRAMES WITH ACCENT GREEN BORDER */
  .card-icon-frame {
    width: 55px;
    height: 55px;
    background: rgba(31, 107, 62, 0.06);
    border: 1px solid rgba(31, 107, 62, 0.15);
    color: #1f6b3e; /* Your Exact System Green */
    transition: all 0.3s ease !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  /* 4. MICRO-INTERACTION HOVER MECHANICS */
  .contact-glass-card:hover {
    transform: translateY(-4px);
    border-color: rgba(31, 107, 62, 0.4) !important;
    box-shadow: 0 12px 24px rgba(31, 107, 62, 0.08) !important;
  }

  .contact-glass-card:hover .card-icon-frame {
    background: #1f6b3e !important;
    color: #ffffff !important;
    border-color: #1f6b3e !important;
  }

  /* 5. MINIMALIST DESIGN INPUT FIELDS */
  .form-control {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    padding: 10px 14px;
    color: #2d3748 !important;
    font-size: 0.9rem;
    transition: all 0.2s ease-in-out !important;
  }

  .form-control:focus {
    border-color: #1f6b3e !important;
    box-shadow: 0 0 0 3px rgba(31, 107, 62, 0.15) !important;
    outline: none;
  }

  .form-label {
    font-weight: 550;
    font-size: 0.85rem;
    color: #4a5568;
    margin-bottom: 6px;
  }

  /* Branded Form Action Button */
  .btn-premium-solid {
    background: #1f6b3e;
    color: #ffffff !important;
    font-weight: 550;
    font-size: 0.9rem;
    border-radius: 10px;
    border: none;
    transition: all 0.25s ease;
  }
  
  .btn-premium-solid:hover {
    background: #17522f;
    transform: translateY(-1px);
    box-shadow: 0 6px 15px rgba(31, 107, 62, 0.2);
  }
</style>

<div class="container py-5">

    <div class="text-center mb-5 animate-header">
        <h1 class="fw-bold" style="color: #1a202c; font-size: 2.3rem; letter-spacing: -0.5px;">Contact Us</h1>
        <p class="lead text-muted" style="font-size: 0.95rem; color: #718096 !important;">Reach out to us for support, inquiries, or feedback.</p>
        <div class="mx-auto rounded mt-3" style="width: 30px; height: 3px; background-color: #1f6b3e;"></div>
    </div>

    <div class="row g-3 justify-content-center animate-grid">

        <div class="col-md-4">
            <div class="card contact-glass-card h-100 text-center p-3">
                <div class="card-body p-2">
                    <div class="card-icon-frame rounded-circle mb-3">
                        <i class="fas fa-envelope fs-5"></i>
                    </div>
                    <h6 class="fw-bold" style="color: #1a202c; margin-bottom: 12px;">Email Support</h6>
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
                    <h6 class="fw-bold" style="color: #1a202c; margin-bottom: 8px;">Phone Support</h6>
                    <p class="small mb-0 text-dark">
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
                    <h6 class="fw-bold" style="color: #1a202c; margin-bottom: 12px;">Mailing Address</h6>
                    <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.5;">
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
                    <h5 class="fw-bold text-center mb-4" style="color: #1a202c; letter-spacing: -0.3px;">Send Us a Quick Message</h5>
                    
                    <form action="#" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" placeholder="you@example.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message</label>
                                <textarea class="form-control" rows="4" placeholder="How can we assist you?" required></textarea>
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