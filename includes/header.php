<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$base_url = "http://grail.local";
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GRAIL System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* 1. CLEAN WORKSPACE BACKDROP WITH SUBTLE ACCENT GLOWS */
        html, body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f8; /* Soft, clean, low-glare off-white */
            background-image: 
                radial-gradient(at 0% 0%, rgba(31, 107, 62, 0.05) 0px, transparent 40%), /* Subtle hint of your green top-left */
                radial-gradient(at 100% 100%, rgba(244, 246, 248, 1) 0px, #f4f6f8 100%);
            background-attachment: fixed;
            color: #2d3748; /* Smooth charcoal for crisp, highly readable text */
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
        }

        /* 2. SILKY WHITE MINT TRANSITION CURTAIN */
        .page-curtain {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8); 
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            z-index: 99999;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            will-change: opacity, backdrop-filter;
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
            color: #1f6b3e; /* Your system green */
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

        /* Entrance / Exit Animations */
        .curtain-entrance { animation: glassReveal 0.4s ease-out forwards; }
        .curtain-exit { animation: glassCover 0.35s ease-in forwards; }

        @keyframes glassReveal {
            from { opacity: 1; backdrop-filter: blur(20px); }
            to { opacity: 0; backdrop-filter: blur(0px); visibility: hidden; }
        }
        @keyframes glassCover {
            from { opacity: 0; backdrop-filter: blur(0px); }
            to { opacity: 1; backdrop-filter: blur(20px); }
        }
        @keyframes pulseRing {
            0% { transform: scale(0.8); opacity: 1; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        /* 3. YOUR EXACT SYSTEM GREEN NAVBAR */
        .navbar-blur-wrapper {
            padding: 12px 0;
        }
        
        .nav-glass-container {
            background: linear-gradient(135deg, #1f6b3e 0%, #17522f 100%) !important; /* Your signature rich green */
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 6px 24px;
            box-shadow: 0 10px 20px rgba(31, 107, 62, 0.15);
        }

        .nav-link { 
            color: rgba(255, 255, 255, 0.8) !important; 
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.25s ease !important;
            padding: 8px 16px !important;
        }
        
        .nav-link:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .nav-link.active { 
            color: #1f6b3e !important; /* High contrast text on the white active pill */
            background: #ffffff !important;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .top-header { 
            background: transparent; 
            padding: 15px 0 5px 0; 
        }
        .location-box { 
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 6px 14px;
            border-radius: 10px;
            display: flex; 
            align-items: center; 
            gap: 10px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
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
    <div class="container d-flex justify-content-between align-items-center">
        <img src="assets/css/img/wword-removebg.png" alt="GRAIL" height="45">
        <div class="location-box">
            <i class="fas fa-map-pin text-success fs-6"></i>
            <div>
                <span class="d-block small fw-bold text-dark" style="font-size: 0.8rem;">Our Location</span>
                <small class="text-muted" style="font-size: 0.7rem;">Quezon St., Bayombong, Nueva Vizcaya</small>
            </div>
        </div>
    </div>
</header>

<div class="container navbar-blur-wrapper sticky-top">
    <nav class="navbar navbar-expand-lg nav-glass-container">
        <div class="container-fluid justify-content-center">
            <ul class="navbar-nav gap-1 transition-nav">
                <li class="nav-item"><a class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="<?= $base_url ?>index.php"><i class="fas fa-home me-1"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link <?= ($current_page == 'service.php') ? 'active' : '' ?>" href="<?= $base_url ?>service.php"><i class="fas fa-layer-group me-1"></i> Services</a></li>
                <li class="nav-item"><a class="nav-link <?= ($current_page == 'aboutus.php') ? 'active' : '' ?>" href="<?= $base_url ?>aboutus.php"><i class="fas fa-circle-info me-1"></i> About</a></li>
                <li class="nav-item"><a class="nav-link <?= ($current_page == 'contact.php') ? 'active' : '' ?>" href="<?= $base_url ?>contact.php"><i class="fas fa-paper-plane me-1"></i> Contact</a></li>
                <li class="nav-item"><a class="nav-link <?= ($current_page == 'privacypolicy.php') ? 'active' : '' ?>" href="<?= $base_url ?>privacypolicy.php"><i class="fas fa-user-shield me-1"></i> Privacy</a></li>
            </ul>
        </div>
    </nav>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const curtain = document.getElementById('transition-curtain');
    const content = document.getElementById('curtain-content-wrapper');
    const interactiveLinks = document.querySelectorAll('.transition-nav .nav-link, .feature-card-link');
    
    interactiveLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const destination = this.getAttribute('href');
            if (destination && destination !== '#' && !destination.startsWith('javascript:')) {
                e.preventDefault(); 
                content.style.transform = "scale(1)";
                curtain.classList.remove('curtain-entrance');
                curtain.classList.add('curtain-exit'); 
                setTimeout(() => { window.location.href = destination; }, 350); 
            }
        });
    });
});
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        const curtain = document.getElementById('transition-curtain');
        curtain.classList.remove('curtain-exit');
        curtain.classList.add('curtain-entrance');
    }
});
</script>

<main class="py-3">
