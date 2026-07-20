<?php
session_start();
$_SESSION = array();
session_destroy();

// Redirect back to your login page (admin.php)
header("Location: index.php");
exit();
?>