<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$preview_mode = !isset($_GET['live']) || $_GET['live'] !== '1';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = trim((string) ($_POST['password'] ?? ''));

    if (!empty($username) && !empty($password)) {
        if ($preview_mode) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $username));
            $_SESSION['admin_preview'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            require_once 'db.php';
            try {
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
                $stmt->execute(['username' => $username]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($admin && password_verify($password, $admin['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_id'] = (int) $admin['id'];
                    $_SESSION['admin_role'] = (string) ($admin['role'] ?? 'case_manager');
                    $_SESSION['admin_department'] = (string) ($admin['department'] ?? '');
                    unset($_SESSION['admin_preview']);
                    header("Location: dashboard.php?live=1");
                    exit();
                }
                $error = "Invalid username or password.";
            } catch (PDOException $e) {
                $error = "The live database is not available. Use preview login instead.";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --cream: #fff8ef;
            --yellow: #ffd449;
            --mint: #a7f3d0;
            --green: #548c2f;
            --deep-green: #104911;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            min-height: 100dvh;
            margin: 0;
            padding: clamp(18px, 5vw, 64px);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--deep-green);
            background-color: var(--cream);
            background-image:
                radial-gradient(circle at 8% 12%, rgba(167, 243, 208, .58), transparent 30%),
                radial-gradient(circle at 92% 88%, rgba(84, 140, 47, .34), transparent 35%),
                linear-gradient(145deg, var(--cream) 35%, rgba(167, 243, 208, .36) 100%);
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            font-size: 17px;
        }

        .login-card {
            width: min(100%, 500px);
            position: relative;
            border: 1px solid rgba(84, 140, 47, .18) !important;
            border-radius: 24px;
            overflow: hidden;
            background: var(--cream);
            box-shadow: 0 24px 65px rgba(16, 73, 17, .18);
        }

        .login-header {
            position: relative;
            padding: clamp(34px, 6vw, 48px) clamp(24px, 7vw, 48px) 32px;
            text-align: center;
            color: var(--cream);
            background: linear-gradient(135deg, var(--deep-green), var(--green));
        }

        .login-header::after {
            content: "";
            position: absolute;
            right: -35px;
            bottom: -55px;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: rgba(255, 212, 73, .13);
        }

        .brand-icon { color: var(--yellow); }
        .login-header h1 { position: relative; z-index: 1; font-size: clamp(1.8rem, 5vw, 2.25rem); letter-spacing: .02em; }
        .login-header p { position: relative; z-index: 1; color: var(--cream); font-size: 1.05rem; line-height: 1.55; opacity: .9; }

        .card-body {
            padding: clamp(28px, 7vw, 48px) !important;
            background: var(--cream);
        }

        .card-title { color: var(--deep-green); font-size: 1.6rem; }
        .form-label { margin-bottom: .55rem; color: var(--deep-green); font-size: 1rem; }
        .input-group { border-radius: 12px; box-shadow: 0 0 0 1px rgba(16, 73, 17, .2); overflow: hidden; transition: box-shadow .2s ease; }
        .input-group:focus-within { box-shadow: 0 0 0 3px rgba(84, 140, 47, .3); }
        .input-group-text { min-width: 50px; justify-content: center; border: 0; background: var(--mint); color: var(--deep-green); }
        .form-control { min-height: 58px; border: 0; padding: .85rem 1rem; color: var(--deep-green); background: var(--cream); font-size: 1.05rem; }
        .form-control::placeholder { color: rgba(16, 73, 17, .55); }
        .form-control:focus { color: var(--deep-green); background: var(--cream); box-shadow: none; }

        .login-button {
            min-height: 58px;
            border: 2px solid var(--green);
            border-radius: 12px;
            color: var(--cream);
            background: var(--green);
            font-size: 1.08rem;
            transition: background-color .2s ease, border-color .2s ease, transform .2s ease;
        }
        .login-button:hover, .login-button:focus-visible { color: var(--cream); background: var(--deep-green); border-color: var(--deep-green); }
        .login-button:active { transform: translateY(1px); }
        .login-button:focus-visible { box-shadow: 0 0 0 3px var(--yellow); }

        .alert-login {
            border: 1px solid var(--green);
            border-radius: 10px;
            color: var(--deep-green);
            background: var(--mint);
            font-size: 1rem;
            line-height: 1.5;
        }

        @media (max-width: 420px) {
            body { align-items: flex-start; }
            .login-card { border-radius: 18px; }
            .login-header { padding-inline: 20px; }
            .card-body { padding-inline: 22px !important; }
        }

        @media (max-height: 650px) and (orientation: landscape) {
            body { align-items: flex-start; padding-block: 16px; }
            .login-header { padding-block: 22px 18px; }
            .card-body { padding-block: 22px !important; }
        }
    </style>
    <link rel="stylesheet" href="assets/css/admin-theme.css">
</head>
<body>

    <div class="card login-card border-0">
        <div class="login-header">
            <h1 class="fw-bold mb-2"><i class="fa-solid fa-shield-halved me-2 brand-icon" aria-hidden="true"></i>GRAIL SYSTEM</h1>
            <p class="mb-0">Faculty Grievance Management Portal</p>
        </div>
        <div class="card-body">
            <h2 class="card-title text-center mb-4 fw-semibold">Admin Sign In</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-login py-2" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user" aria-hidden="true"></i></span>
                        <input id="username" type="text" class="form-control" name="username" placeholder="Enter your username" autocomplete="username" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock" aria-hidden="true"></i></span>
                        <input id="password" type="password" class="form-control" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                    </div>
                </div>

                <button type="submit" class="btn login-button w-100 fw-bold">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Log In
                </button>
            </form>
        </div>
    </div>

</body>
</html>
