<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$isLoggedIn  = isset($_SESSION['userID']);
$username    = $isLoggedIn ? $_SESSION['username'] : '';
$userRole    = $isLoggedIn ? $_SESSION['role'] : '';
$cartCount   = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Always correct base URL
$base = '/Pastimes/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pastimes | Premium Second-Hand Fashion</title>
<link rel="stylesheet" href="/Pastimes/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
</head>
<body>

<div class="page-wrapper">

<div class="clothes-bg">
  <span class="cloth-item">👔</span><span class="cloth-item">👗</span>
  <span class="cloth-item">👠</span><span class="cloth-item">👜</span>
  <span class="cloth-item">🧥</span><span class="cloth-item">👒</span>
  <span class="cloth-item">👟</span><span class="cloth-item">🧣</span>
  <span class="cloth-item">👔</span><span class="cloth-item">👗</span>
  <span class="cloth-item">👜</span><span class="cloth-item">🧤</span>
</div>

<nav class="navbar">
  <div class="nav-container">
    <a href="/Pastimes/index.php" class="navbar-brand">
      <div class="brand-logo">P</div>
      <div class="brand-text">
        <span class="brand-name">Pastimes</span>
        <span class="brand-tagline">Vintage Fashion</span>
      </div>
    </a>
    <button class="mobile-menu-toggle" id="menuToggle"><span></span><span></span><span></span></button>
    <ul class="nav-menu" id="navMenu">
      <li><a href="/Pastimes/index.php"  class="nav-link <?php echo $currentPage==='index'  ?'active':'';?>">Home</a></li>
      <li><a href="/Pastimes/browse.php" class="nav-link <?php echo $currentPage==='browse' ?'active':'';?>">Browse</a></li>
      <li><a href="/Pastimes/about.php"  class="nav-link <?php echo $currentPage==='about'  ?'active':'';?>">About</a></li>
      <?php if ($isLoggedIn): ?>
        <?php if ($userRole==='admin'): ?>
          <li><a href="/Pastimes/admin/index.php" class="nav-link"><i class="fas fa-shield-alt"></i> Admin</a></li>
        <?php elseif ($userRole==='seller'): ?>
          <li><a href="/Pastimes/dashboards/seller.php" class="nav-link"><i class="fas fa-store"></i> Seller Hub</a></li>
        <?php else: ?>
          <li><a href="/Pastimes/dashboards/buyer.php" class="nav-link"><i class="fas fa-user"></i> My Account</a></li>
        <?php endif; ?>
        <li>
          <a href="/Pastimes/cart.php" class="nav-link nav-cart">
            <i class="fas fa-shopping-bag"></i>
            <?php if ($cartCount>0): ?><span class="cart-badge"><?php echo $cartCount;?></span><?php endif;?>
          </a>
        </li>
        <li><a href="/Pastimes/messages.php" class="nav-link"><i class="fas fa-envelope"></i></a></li>
        <li><a href="/Pastimes/logout.php" class="btn btn-outline btn-sm">Logout</a></li>
      <?php else: ?>
        <li><a href="/Pastimes/login.php"    class="nav-link <?php echo $currentPage==='login'   ?'active':'';?>">Login</a></li>
        <li><a href="/Pastimes/register.php" class="btn btn-primary btn-sm">Register</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<main class="main-content">