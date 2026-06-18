<?php
/**
 * PASTIMES — settings.php
 * Redirects to the correct dashboard settings page based on role
 */
require_once 'includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['userID'])) {
    header('Location: /Pastimes/login.php'); exit;
}

$role = $_SESSION['role'];

if ($role === 'admin')       { header('Location: /Pastimes/admin/index.php?page=dashboard');      exit; }
if ($role === 'seller')      { header('Location: /Pastimes/dashboards/seller.php?page=settings'); exit; }
header('Location: /Pastimes/dashboards/buyer.php?page=settings'); exit;
?>