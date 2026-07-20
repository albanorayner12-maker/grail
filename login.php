<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

session_start();
require_once 'db.php'; 

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); 
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            // PLAIN TEXT CHECK: Compares the password directly
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username AND password = :password LIMIT 1");
            $stmt->execute([
                'username' => $username,
                'password' => $password
            ]); 
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                session_regenerate_id(true);

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['name'];

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password!";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
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
        body { background-color: #2c3e50; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 420px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; }
        .login-header { background-color: #1a252f; padding: 30px 20px; text-align: center; color: white; }
    </style>
</head>
<body>

    <div class="card login-card border-0">
        <div class="login-header">
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-shield-halved me-2 text-primary"></i>GRAIL SYSTEM</h3>
            <p class="text-secondary mb-0 small">Faculty Grievance Management Portal</p>
        </div>
        <div class="card-body p-4 bg-white">
            <h5 class="card-title text-center mb-4 text-dark fw-semibold">Admin Sign In</h5>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-medium text-secondary small">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-user text-muted"></i></span>
                        <input type="text" class="form-control" name="username" placeholder="Enter username" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium text-secondary small">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Log In
                </button>
            </form>
        </div>
    </div>

</body>
</html>