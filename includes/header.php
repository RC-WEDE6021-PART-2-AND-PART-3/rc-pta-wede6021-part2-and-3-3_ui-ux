<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: includes/header.php
 * Description: Shared navigation header component
 * ============================================================
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['userID']);
$username = $isLoggedIn ? $_SESSION['username'] : '';
$userRole = $isLoggedIn ? $_SESSION['role'] : '';
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Get current page for active nav highlighting
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pastimes - South Africa's premier marketplace for quality second-hand branded clothing.">
    <title>Pastimes | Premium Second-Hand Fashion</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Animated Falling Clothing Background -->
    <div class="clothes-bg">
        <span class="cloth-item">👔</span>
        <span class="cloth-item">👗</span>
        <span class="cloth-item">👠</span>
        <span class="cloth-item">👜</span>
        <span class="cloth-item">🧥</span>
        <span class="cloth-item">👒</span>
        <span class="cloth-item">👟</span>
        <span class="cloth-item">🧣</span>
        <span class="cloth-item">👔</span>
        <span class="cloth-item">👗</span>
        <span class="cloth-item">👠</span>
        <span class="cloth-item">👜</span>
        <span class="cloth-item">🧥</span>
        <span class="cloth-item">👒</span>
        <span class="cloth-item">👟</span>
        <span class="cloth-item">🧣</span>
        <span class="cloth-item">👔</span>
        <span class="cloth-item">👗</span>
        <span class="cloth-item">👠</span>
        <span class="cloth-item">👜</span>
        <span class="cloth-item">🧥</span>
        <span class="cloth-item">👒</span>
        <span class="cloth-item">👟</span>
        <span class="cloth-item">🧣</span>
        <span class="cloth-item">👔</span>
        <span class="cloth-item">👗</span>
        <span class="cloth-item">👠</span>
        <span class="cloth-item">👜</span>
        <span class="cloth-item">🧥</span>
        <span class="cloth-item">👒</span>
    </div>
    <div class="clothes-bg-overlay"></div>

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        
        <!-- Top Bar -->
        <div class="topbar">
            <div class="topbar-left">Premium Second-Hand Fashion Since 2026</div>
            <div class="topbar-right">
                <?php if ($isLoggedIn): ?>
                    <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="navbar">
            <a href="index.php" class="navbar-brand">
                <div class="brand-logo">P</div>
                <div class="brand-text">
                    <span class="brand-name">Pastimes</span>
                    <span class="brand-tagline">Vintage Fashion</span>
                </div>
            </a>

            <ul class="navbar-nav">
                <li><a href="index.php" class="nav-link <?php echo $currentPage == 'index' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="browse.php" class="nav-link <?php echo $currentPage == 'browse' ? 'active' : ''; ?>">Browse</a></li>
                <li><a href="how-it-works.php" class="nav-link <?php echo $currentPage == 'how-it-works' ? 'active' : ''; ?>">How It Works</a></li>
                <li><a href="about.php" class="nav-link <?php echo $currentPage == 'about' ? 'active' : ''; ?>">About</a></li>
            </ul>

            <form class="navbar-search" action="browse.php" method="GET">
                <i class="fas fa-search navbar-search-icon"></i>
                <input type="text" name="search" placeholder="Search brands, items...">
            </form>

            <div class="navbar-actions">
                <a href="cart.php" class="nav-icon-btn" title="Shopping Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="nav-badge"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>
                <a href="messages.php" class="nav-icon-btn" title="Messages">
                    <i class="fas fa-comment-dots"></i>
                </a>
                <a href="<?php echo $isLoggedIn ? 'profile.php' : 'login.php'; ?>" class="nav-icon-btn" title="Profile">
                    <i class="fas fa-user"></i>
                </a>
                <?php if ($isLoggedIn && ($userRole == 'seller' || $userRole == 'admin')): ?>
                    <a href="<?php echo $userRole == 'admin' ? 'admin/index.php' : 'profile.php'; ?>" class="btn-sell-now">
                        <?php echo $userRole == 'admin' ? 'Admin' : 'Sell Now'; ?>
                    </a>
                <?php elseif (!$isLoggedIn): ?>
                    <a href="register.php" class="btn-sell-now">Sell Now</a>
                <?php endif; ?>
            </div>

            <button class="navbar-toggle" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </nav>  