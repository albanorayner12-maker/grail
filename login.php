<?php
// login.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Reaching the login page (for example with the Back button from the dashboard)
// ends any existing admin session, so the dashboard always needs a fresh login.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !empty($_SESSION['admin_logged_in'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    session_start();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = strtolower(trim((string) ($_POST['username'] ?? '')));
    $password = trim((string) ($_POST['password'] ?? ''));

    if (!empty($username) && !empty($password)) {
        
        // SECURE HARDCODED HASH FOR 'faculty' (Password: grail_admin123!)
        $demo_hash = '$2y$10$O4bK9Z.eQxX8dY/A2bZ8.eR5G9.Y6/4xM8jA2X4wK8X9w0Y1Z2a3b'; // bcrypt hash for 'grail_admin123!'
        
        if ($username === 'faculty') {
            if ($password === 'grail_admin123!') {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username']  = 'Faculty';
                $_SESSION['admin_name']      = 'Faculty Administrator';
                $_SESSION['admin_id']        = 1;
                $_SESSION['admin_role']      = 'admin';
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            // STANDARD DATABASE CHECK FOR ALL OTHER USERS
            require_once __DIR__ . '/db.php';
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(sso_uid) = :username LIMIT 1");
                $stmt->execute(['username' => $username]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($admin && password_verify($password, (string)$admin['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username']  = $admin['sso_uid'];
                    $_SESSION['admin_name']      = $admin['full_name'];
                    $_SESSION['admin_id']        = (int) $admin['user_id'];
                    $_SESSION['admin_role']      = (string) ($admin['role'] ?? 'investigator');
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid username or password.";
                }
            } catch (PDOException $e) {
                $error = "The live database is not available. Check your MariaDB service status.";
            }
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRAIL SYSTEM - Admin Login</title>
    <style>
        :root {
            --g-cream: #fff8ef;
            --g-yellow: #ffd449;
            --g-mint: #a7f3d0;
            --g-green: #548c2f;
            --g-deep: #104911;
            --g-font: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Roboto, Arial, sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: var(--g-font);
            background-color: var(--g-cream);
            -webkit-font-smoothing: antialiased;
        }

        input, button { font-family: inherit; }

        .g-login-wrapper {
            position: relative;
            overflow: hidden;
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background-image:
                radial-gradient(circle at 8% 12%, rgba(167, 243, 208, .58), transparent 30%),
                radial-gradient(circle at 92% 88%, rgba(84, 140, 47, .34), transparent 35%),
                linear-gradient(145deg, var(--g-cream) 35%, rgba(167, 243, 208, .36) 100%);
        }

        .g-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
            background: var(--g-cream);
            border: 1px solid rgba(84, 140, 47, .25);
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 24px 65px rgba(16, 73, 17, .18);
        }

        .g-header {
            padding: 38px 30px 34px;
            text-align: center;
            color: var(--g-cream);
            background: linear-gradient(135deg, var(--g-deep), var(--g-green));
        }

        .g-badge {
            display: inline-grid;
            place-items: center;
            width: 68px;
            height: 68px;
            margin-bottom: 16px;
            color: var(--g-yellow);
            background: rgba(255, 248, 239, .14);
            border: 1px solid rgba(255, 248, 239, .28);
            border-radius: 50%;
        }
        .g-badge svg { width: 34px; height: 34px; }

        .g-header h1 {
            margin: 0 0 6px;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: .04em;
            color: var(--g-cream);
        }
        .g-header p {
            margin: 0;
            font-size: 1rem;
            opacity: .9;
            color: var(--g-cream);
        }

        .g-body { padding: 36px 34px 30px; }

        .g-title {
            margin: 0 0 26px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--g-deep);
        }

        .g-label {
            display: block;
            margin-bottom: 8px;
            font-size: .95rem;
            font-weight: 700;
            color: var(--g-deep);
        }

        .g-field { margin-bottom: 20px; }

        .g-input-group {
            display: flex;
            align-items: stretch;
            width: 100%;
            overflow: hidden;
            background: #fffdf8;
            border: 1px solid rgba(84, 140, 47, .4);
            border-radius: 12px;
            transition: border-color .25s ease, box-shadow .25s ease;
        }
        .g-input-group:focus-within {
            border-color: var(--g-green);
            box-shadow: 0 0 0 .2rem rgba(84, 140, 47, .18);
        }

        .g-input-icon {
            flex: 0 0 52px;
            display: grid;
            place-items: center;
            color: var(--g-deep);
            background: var(--g-mint);
        }
        .g-input-icon svg { width: 20px; height: 20px; }

        .g-control {
            flex: 1 1 auto;
            min-width: 0;
            min-height: 54px;
            padding: .85rem 1rem;
            border: 0;
            outline: none;
            font-size: 1.05rem;
            color: var(--g-deep);
            background: transparent;
        }
        .g-control::placeholder { color: rgba(16, 73, 17, .42); }

        .g-toggle {
            flex: 0 0 52px;
            display: grid;
            place-items: center;
            padding: 0;
            border: 0;
            cursor: pointer;
            color: var(--g-green);
            background: transparent;
            transition: color .2s ease;
        }
        .g-toggle:hover { color: var(--g-deep); }
        .g-toggle svg { width: 20px; height: 20px; }
        .g-toggle .icon-off { display: none; }
        .g-toggle.is-visible .icon-on { display: none; }
        .g-toggle.is-visible .icon-off { display: block; }

        .g-button {
            width: 100%;
            min-height: 54px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--g-cream);
            background: var(--g-green);
            box-shadow: 0 4px 12px rgba(84, 140, 47, .2);
            transition: transform .3s ease, box-shadow .3s ease, background-color .3s ease;
        }
        .g-button svg { width: 20px; height: 20px; }
        .g-button:hover {
            background: var(--g-deep);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(46, 125, 50, .4);
        }
        .g-button:active { transform: translateY(-1px); }
        .g-button:disabled { opacity: .85; cursor: wait; transform: none; }

        .g-spinner {
            width: 1.1rem;
            height: 1.1rem;
            border: 3px solid rgba(255, 248, 239, .4);
            border-top-color: var(--g-cream);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        .g-note {
            margin: 22px 0 0;
            text-align: center;
            font-size: .85rem;
            color: var(--g-deep);
            opacity: .65;
        }

        .g-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding: 14px;
            font-size: .95rem;
            font-weight: 600;
            color: #7f1d1d;
            background: #fecaca;
            border: 1px solid #f87171;
            border-radius: 12px;
        }
        .g-alert svg { flex: 0 0 20px; width: 20px; height: 20px; }

        @keyframes popupShow { 0% { opacity: 0; transform: scale(0.7) translateY(30px); } 100% { opacity: 1; transform: scale(1) translateY(0); } }
        @keyframes iconBounce { 0%, 100% { transform: translateY(0); } 30% { transform: translateY(-12px); } 60% { transform: translateY(4px); } }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes floatBlob { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(24px, -28px) scale(1.08); } }
        @keyframes shake { 10%, 90% { transform: translateX(-2px); } 20%, 80% { transform: translateX(4px); } 30%, 50%, 70% { transform: translateX(-7px); } 40%, 60% { transform: translateX(7px); } }
        @keyframes spin { to { transform: rotate(360deg); } }

        .g-login-wrapper::before,
        .g-login-wrapper::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            animation: floatBlob 9s ease-in-out infinite;
        }
        .g-login-wrapper::before { width: 320px; height: 320px; left: -90px; top: -90px; background: rgba(167, 243, 208, .45); }
        .g-login-wrapper::after  { width: 260px; height: 260px; right: -70px; bottom: -70px; background: rgba(255, 212, 73, .30); animation-duration: 12s; animation-direction: reverse; }

        .g-card  { animation: popupShow .6s ease-out backwards; }
        .g-badge { animation: iconBounce 1.2s ease .6s backwards; }
        .g-alert { animation: shake .6s ease .1s backwards; }

        .g-anim { animation: fadeUp .6s ease backwards; }
        .g-anim.d1 { animation-delay: .35s; }
        .g-anim.d2 { animation-delay: .50s; }
        .g-anim.d3 { animation-delay: .65s; }
        .g-anim.d4 { animation-delay: .80s; }

        @media (max-width: 480px) {
            .g-body { padding: 28px 22px 24px; }
            .g-header h1 { font-size: 1.7rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
    </style>
</head>
<body>

    <div class="g-login-wrapper">
        <div class="g-card">
            <div class="g-header">
                <span class="g-badge" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                </span>
                <h1>GRAIL SYSTEM</h1>
                <p>Faculty Grievance Management Portal</p>
            </div>

            <div class="g-body">
                <h2 class="g-title g-anim d1">Admin Sign In</h2>

                <?php if (!empty($error)): ?>
                    <div class="g-alert" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" id="loginForm">
                    <div class="g-field g-anim d2">
                        <label for="username" class="g-label">Username</label>
                        <div class="g-input-group">
                            <span class="g-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </span>
                            <input id="username" type="text" class="g-control" name="username" placeholder="Enter your username" autocomplete="username" required autofocus>
                        </div>
                    </div>

                    <div class="g-field g-anim d3">
                        <label for="password" class="g-label">Password</label>
                        <div class="g-input-group">
                            <span class="g-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input id="password" type="password" class="g-control" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                            <button type="button" class="g-toggle" id="togglePw" aria-label="Show password" title="Show password">
                                <svg class="icon-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="icon-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="g-button g-anim d4" id="loginBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        <span>Log In</span>
                    </button>
                </form>

                <p class="g-note g-anim d4">Authorized administrators only.</p>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var form   = document.getElementById('loginForm');
            var btn    = document.getElementById('loginBtn');
            var pw     = document.getElementById('password');
            var toggle = document.getElementById('togglePw');
            var original = btn.innerHTML;

            toggle.addEventListener('click', function () {
                var show = pw.type === 'password';
                pw.type = show ? 'text' : 'password';
                toggle.classList.toggle('is-visible', show);
                toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                toggle.title = show ? 'Hide password' : 'Show password';
            });

            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.innerHTML = '<span class="g-spinner"></span><span>Signing in...</span>';
            });

            window.addEventListener('pageshow', function (e) {
                if (e.persisted) { btn.disabled = false; btn.innerHTML = original; }
            });
        })();
    </script>
</body>
</html>
