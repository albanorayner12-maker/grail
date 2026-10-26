<?php
// auth_guard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If the session variables aren't established, block entry and bounce back to login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php?error=authentication_required");
    exit();
}

// Enforce strict administrative user role check conditions
if (!in_array($_SESSION['admin_role'], ['admin', 'investigator'])) {
    header("Location: login.php?error=unauthorized_privileges");
    exit();
}
?>
