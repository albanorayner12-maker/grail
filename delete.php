<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM grievances WHERE id = :id");
        $result = $stmt->execute(['id' => $id]);

        if ($result) {
            $_SESSION['success'] = "Record #" . $id . " has been deleted permanently.";
        } else {
            $_SESSION['error'] = "Failed to delete the record.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid record ID.";
}

header("Location: dashboard.php");
exit();