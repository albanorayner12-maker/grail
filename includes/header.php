<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$base_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_url = rtrim($base_path, '/') . '/';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GRAIL System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css?v=1.0">
    <style>
        /* 1. CLEAN WORKSPACE BACKDROP WITH SUBTLE ACCENT GLOWS */
        html, body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f8;
            background-image: 
                radial-gradient(at 0% 0%, rgba(31, 107, 62, 0.05) 0px, transparent 40%),
                radial-gradient(at 100% 100%, rgba(244, 246, 248, 1) 0px, #f4f6f8 100%);
            background-attachment: fixed;
            color: #2d3748;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
        }

        /* 2. SILKY WHITE MINT TRANSITION CURTAIN - RESOLVED CLICK WALL CONFLICTS */
        .page-curtain {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8); 
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none; /* CRITICAL: Allows clicks to pass through when hidden */
            visibility: hidden;   /* CRITICAL: Safely removes element layout from target axis */
            will-change: opacity;
        }

        .curtain-content {
            text-align: center;
            color: #1f6b3e;
            transform: scale(0.97);
            will-change: transform, opacity;
        }

        .curtain-logo-container {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 15px auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .curtain-logo {
            font-size: 2.2rem;
            color: #1f6b3e;
            z-index: 2;
        }

        .scanner-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 2px solid rgba(31, 107, 62, 0.2);
            border-radius: 50%;
            animation: pulseRing 1.4s infinite cubic-bezier(0.215, 0.610, 0.355, 1);
            z-index: 1;
        }

        .curtain-text {
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #4a5568;
        }

        /* Transition Animations with Structural Fixes */
        .curtain-entrance { animation: glassReveal 0.4s ease-out forwards; }
        .curtain-exit { animation: glassCover 0.35s ease-in forwards; }

        @keyframes glassReveal {
            from { opacity: 1; pointer-events: auto; visibility: visible; }
            to { opacity: 0; pointer-events: none; visibility: hidden; }
        }
        @keyframes glassCover {
            from { opacity: 0; pointer-events: none; visibility: hidden; }
            to { opacity: 1; pointer-events: auto; visibility: visible; }
        }
        @keyframes pulseRing {
            0% { transform: scale(0.8); opacity: 1; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        /* 3. YOUR EXACT SYSTEM GREEN NAVBAR */
        .navbar-blur-wrapper {
            width: 100%;
            max-width: none;
            padding: 12px 0 0;
        }
        
        .nav-glass-container {
            background: linear-gradient(135deg, #1f6b3e 0%, #17522f 100%) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-right: 0;
            border-left: 0;
            border-radius: 0;
            min-height: 76px;
            padding: 12px 24px;
            box-shadow: 0 10px 20px rgba(31, 107, 62, 0.15);
        }

        .transition-nav {
            column-gap: clamp(20px, 3vw, 48px) !important;
        }

        .nav-link { 
            color: rgba(255, 255, 255, 0.8) !important; 
            font-weight: 500;
            font-size: 1.05rem;
            transition: all 0.25s ease !important;
            padding: 11px 18px !important;
        }

        .nav-link i { font-size: 1.05em; }
        
        .nav-link:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .nav-link.active { 
            color: #1f6b3e !important;
            background: #ffffff !important;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .top-header { 
            background: transparent; 
            padding: 14px 0; 
        }
        .top-header-inner { padding-inline: clamp(18px, 3vw, 48px); }
        .grail-logo { width: auto; height: clamp(52px, 6vw, 68px); object-fit: contain; }
        .location-box { 
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 10px 16px;
            border-radius: 10px;
            display: flex; 
            align-items: center; 
            gap: 10px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .location-box > i { font-size: 1.25rem !important; }
        .location-title { font-size: .95rem; }
        .location-address { font-size: .85rem; }

        @media (max-width: 575.98px) {
            .top-header-inner { padding-inline: 16px; }
            .grail-logo { height: 54px; }
        }

        @media (max-width: 991.98px) {
            .nav-glass-container { min-height: 70px; }
            .transition-nav { column-gap: 0 !important; }
        }

        /* Canonical palette configuration mapping */
        :root {
            --cream: #fff8ef;
            --yellow: #ffd449;
            --mint: #a7f3d0;
            --green: #548c2f;
            --deep-green: #104911;
        }
        html, body {
            color: var(--deep-green) !important;
            background-color: var(--cream) !important;
        }
        body {
            background-image:
                radial-gradient(circle at 0 15%, rgba(167,243,208,.4), transparent 28%),
                radial-gradient(circle at 100% 72%, rgba(84,140,47,.14), transparent 30%) !important;
            background-attachment: fixed;
        }
        .nav-glass-container {
            background: linear-gradient(135deg, var(--deep-green), var(--green)) !important;
            border-color: rgba(255,248,239,.18) !important;
        }
        .nav-link { color: var(--cream) !important; }
        .nav-link.active { color: var(--deep-green) !important; background: var(--cream) !important; }
        .location-box { color: var(--deep-green); background: var(--cream); border-color: rgba(84,140,47,.2); }
        .location-box .text-success, .location-box .text-dark, .location-box .text-muted { color: var(--deep-green) !important; }
        .navbar-toggler { border-color: rgba(255,248,239,.5); }
        .navbar-toggler-icon { filter: brightness(0) invert(1); }
    </style>
</head>
<body>

<div id="transition-curtain" class="page-curtain curtain-entrance">
    <div class="curtain-content" id="curtain-content-wrapper">
        <div class="curtain-logo-container">
            <div class="scanner-ring"></div>
            <i class="fas fa-shield-halved curtain-logo"></i>
        </div>
        <div class="curtain-text">GRAIL Secure</div>
    </div>
</div>

<header class="top-header">
    <div class="container-fluid top-header-inner d-flex justify-content-between align-items-center">
        <img src="assets/css/img/wword-removebg.png" alt="GRAIL" class="grail-logo">
        <div class="location-box">
            <i class="fas fa-map-pin text-success"></i>
            <div>
                <span class="location-title d-block fw-bold text-dark">Our Location</span>
                <span class="location-address text-muted">Quezon St., Bayombong, Nueva Vizcaya</span>
            </div>
        </div>
    </div>
</header>

<div class="container-fluid navbar-blur-wrapper sticky-top px-0">
    <nav class="navbar navbar-expand-lg nav-glass-container">
        <div class="container-fluid">
            <span class="navbar-brand d-lg-none text-white fw-bold mb-0">Menu</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="mainNavigation">
            <ul class="navbar-nav gap-1 transition-nav">
                <li class="nav-item"><a class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="<?= $base_url ?>index.php"><i class="fas fa-home me-1"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link <?= ($current_page == 'service.php') ? 'active' : '' ?>" href="<?= $base_url ?>service.php"><i class="fas fa-layer-group me-1"></i> Services</a></li>
                <li class="nav-item"><a class="nav-link <?= ($current_page == 'aboutus.php') ? 'active' : '' ?>" href="<?= $base_url ?>aboutus.php"><i class="fas fa-circle-info me-1"></i> About</a></li>

                    <li class="nav-item"><a class="nav-link <?= ($current_page == 'contact.php') ? 'active' : '' ?>" href="<?= $base_url ?>contact.php"><i class="fas fa-paper-plane me-1"></i> Contact</a></li>
                    <li class="nav-item dropdown">
    <!-- Added "data-bs-auto-close="true" to force native overlay resolution -->
                    <a class="nav-link dropdown-toggle <?= in_array($current_page, ['support.php', 'privacypolicy.php']) ? 'active' : '' ?>" 
                        href="#" 
                        role="button" 
                        data-bs-toggle="dropdown" 
                        data-bs-auto-close="true" 
                        aria-expanded="false">
                        <i class="fas fa-circle-question me-1"></i> Help
                    </a>
    <!-- Ensure your parent ul tag contains class="navbar-nav gap-1 transition-nav" as written in your master header file -->
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item <?= ($current_page == 'support.php') ? 'active' : '' ?>" href="<?= $base_url ?>support.php"><i class="fas fa-headset me-2"></i>Support Hub</a></li>
                            <li><a class="dropdown-item <?= ($current_page == 'privacypolicy.php') ? 'active' : '' ?>" href="<?= $base_url ?>privacypolicy.php"><i class="fas fa-user-shield me-2"></i>Privacy Policy</a></li>
                         </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
const curtain = document.getElementById('transition-curtain');
const content = document.getElementById('curtain-content-wrapper');
// SAFE SELECTOR ENGINE: Collects nav items but skips dropdown event nodes
const interactiveLinks = document.querySelectorAll('.transition-nav .nav-link, .feature-card-link');
interactiveLinks.forEach(link => {
link.addEventListener('click', function(e) {
// CRITICAL VERIFICATION: Escapes handler if link handles submenus
if (this.classList.contains('dropdown-toggle') || this.getAttribute('href') === '#') {
return;
}
const destination = this.getAttribute('href');
if (destination && !destination.startsWith('javascript:')) {
e.preventDefault();
if (content) content.style.transform = "scale(1)";
if (curtain) {
curtain.classList.remove('curtain-entrance');
curtain.classList.add('curtain-exit');
}
setTimeout(() => { window.location.href = destination; }, 350);
}
});
});
});

window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        const curtain = document.getElementById('transition-curtain');
        if(curtain) {
            curtain.classList.remove('curtain-exit');
            curtain.classList.add('curtain-entrance');
        }
    }
});
</script>

<main class="py-3">
