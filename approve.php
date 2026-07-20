<?php
session_start();
require_once 'db.php';

// Check admin session
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Validate ID
if (!isset($_GET['id']) || empty($_GET['id'])) {

    $_SESSION['error'] = "Invalid grievance ID.";
    header("Location: dashboard.php");
    exit();
}

$id = intval($_GET['id']);

try {

    // Check if record exists
    $check = $pdo->prepare("SELECT id, status FROM grievances WHERE id = ?");
    $check->execute([$id]);

    $record = $check->fetch(PDO::FETCH_ASSOC);

    if (!$record) {

        $_SESSION['error'] = "Record not found.";

    } else {

        // Update status
        $update = $pdo->prepare("
            UPDATE grievances
            SET status = 'resolved'
            WHERE id = ?
        ");

        $update->execute([$id]);

        $_SESSION['success'] = "Record #{$id} marked as resolved.";
    }

} catch (PDOException $e) {

    $_SESSION['error'] = "Database Error: " . $e->getMessage();
}

// Redirect back
header("Location: dashboard.php");
exit();
?>