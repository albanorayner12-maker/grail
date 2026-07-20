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

  /* 2. PREMIUM WHITE GLASS PANEL */
  .policy-glass-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 14px rgba(148, 163, 184, 0.04) !important;
    padding: 2.5rem !important;
    transition: border-color 0.3s ease !important;
  }

  .policy-glass-card:hover {
    border-color: rgba(31, 107, 62, 0.2) !important;
  }

  /* 3. SCANNABLE TYPOGRAPHY TREATMENT */
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

  .policy-glass-card h4 {
    color: #2d3748 !important;
    font-size: 1rem;
    letter-spacing: -0.2px;
  }

  .policy-glass-card p {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #4a5568;
  }

  /* 4. REFINED ACCENT LISTS */
  .definitions-list {
    list-style: none;
    padding-left: 0;
    margin-bottom: 0;
  }

  .definitions-list li {
    font-size: 0.9rem;
    color: #4a5568;
    position: relative;
    padding-left: 20px;
    margin-bottom: 12px;
    line-height: 1.5;
  }

  .definitions-list li::before {
    content: "•";
    color: #1f6b3e; /* Precise green alignment points */
    font-weight: bold;
    font-size: 1.2rem;
    position: absolute;
    left: 0;
    top: -2px;
  }

  .definitions-list strong {
    color: #1a202c;
  }
  
  /* Minimalist Section Separator */
  .policy-divider {
    border: 0;
    height: 1px;
    background: #e2e8f0;
    margin: 2rem 0;
  }
</style>

<div class="container py-5">

    <div class="text-center mb-5 animate-header">
        <h1 class="fw-bold" style="color: #1a202c; font-size: 2.3rem; letter-spacing: -0.5px;">Privacy Policy</h1>
        <p class="lead text-muted" style="font-size: 0.95rem; color: #718096 !important;">Last updated: <?= date('F j, Y'); ?></p>
        <div class="mx-auto rounded mt-3" style="width: 30px; height: 3px; background-color: #1f6b3e;"></div>
    </div>

    <div class="row justify-content-center animate-body">
        <div class="col-lg-10">
            <div class="card policy-glass-card">
                <div class="card-body p-0">

                    <section>
                        <h2 class="section-title mb-3 d-flex align-items-center">
                            <i class="fas fa-shield-halved section-badge-icon"></i> General Policy Overview
                        </h2>
                        <p>This section describes the policies and procedures on the collection, use, and disclosure of your information when you use the Service.</p>
                        <p class="mb-0">We use your Personal data to provide and improve the Service. By using the Service, you agree to the collection and use of information in accordance with this Privacy Policy.</p>
                    </section>

                    <div class="policy-divider"></div>

                    <section>
                        <h2 class="section-title mb-3 d-flex align-items-center">
                            <i class="fas fa-book section-badge-icon"></i> Interpretation and Definitions
                        </h2>
                        
                        <h4 class="mt-4 fw-bold mb-2">Interpretation</h4>
                        <p>The words of which the initial letter is capitalized have meanings defined under the following conditions.</p>

                        <h4 class="mt-4 fw-bold mb-2">Definitions</h4>
                        <p class="mb-3">For the purposes of this Privacy Policy:</p>
                        
                        <ul class="definitions-list">
                            <li><strong>Account</strong> means a unique account created for you to access our Service.</li>
                            <li><strong>Company</strong> refers to <span style="color: #1f6b3e; font-weight: 600;">Grail System</span>.</li>
                        </ul>
                    </section>

                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>