<?php
require_once 'includes/header.php';
?>

<style>
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .animate-header { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
  .animate-body { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards; opacity: 0; }

  .support-glass-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 14px rgba(148, 163, 184, 0.04) !important;
  }

  .faq-q {
    font-weight: 600;
    color: #1a202c;
    font-size: 0.95rem;
  }
  .faq-a {
    font-size: 0.85rem;
    color: #4a5568;
    line-height: 1.5;
  }
  
  .dot-pointer {
    color: #1f6b3e;
    font-weight: 700;
    margin-right: 6px;
  }
</style>

<div class="container py-5">
    <div class="text-center mb-5 animate-header">
        <h1 class="fw-bold" style="color: #1a202c; font-size: 2.3rem; letter-spacing: -0.5px;">User Support Hub</h1>
        <p class="lead text-muted" style="font-size: 0.95rem; color: #718096 !important;">Frequently Asked Questions and technical documentation reference rules for optimal workspace use.</p>
        <div class="mx-auto rounded mt-3" style="width: 30px; height: 3px; background-color: #1f6b3e;"></div>
    </div>

    <div class="row justify-content-center animate-body">
        <div class="col-lg-9">
            <div class="card support-glass-card p-4">
                <div class="card-body p-1">
                    
                    <div class="mb-4">
                        <div class="faq-q mb-1"><span class="dot-pointer">?</span> How do I safely monitor an anonymous case?</div>
                        <div class="faq-a">When submitting a report without credentials, you will be issued a private cryptographic Reference Code block. You must store this token safely as it is the only identifier used to reference your log status on the tracker interface.</div>
                    </div>

                    <div class="border-top pt-3 mb-4">
                        <div class="faq-q mb-1"><span class="dot-pointer">?</span> What is the average timeline before processing starts?</div>
                        <div class="faq-a">Our routing desk acts on all logged cases within a standard window of 24 operating hours, moving the status promptly into initial diagnostic investigation phases.</div>
                    </div>

                    <div class="border-top pt-3 mb-0">
                        <div class="faq-q mb-1"><span class="dot-pointer">?</span> Who manages the assigned tracking parameters?</div>
                        <div class="faq-a">Your concerns are automatically routed to the designated administrators of the College of Information Technology Education (CITE) and relevant technical engineering desks for optimal response accuracy.</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>