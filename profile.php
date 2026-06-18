<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['userID'])) {
    header('Location: /Pastimes/login.php'); exit;
}

$userID   = $_SESSION['userID'];
$role     = $_SESSION['role'];

// Redirect to correct dashboard
if ($role === 'admin')       { header('Location: /Pastimes/admin/index.php');       exit; }
if ($role === 'seller')      { header('Location: /Pastimes/dashboards/seller.php'); exit; }
header('Location: /Pastimes/dashboards/buyer.php'); exit;