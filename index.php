<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$featured = [];
try {
    $conn = getConnection();
    $result = $conn->query(
        "SELECT c.clothingID, c.brand, c.category, c.size, c.clothingCondition,
                c.description, c.price, c.imagePath, c.status,
                u.fullName AS sellerName
         FROM tblClothing c
         JOIN tblUser u ON c.sellerID = u.userID
         WHERE c.status = 'available'
         ORDER BY c.dateAdded DESC
         LIMIT 8"
    );
    while ($row = $result->fetch_assoc()) $featured[] = $row;
    $conn->close();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pastimes | Premium Second-Hand Fashion</title>
<link rel="stylesheet" href="/Pastimes/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<!-- SPLASH SCREEN — only on index -->
<div class="splash-screen" id="splashScreen">
  <div class="splash-inner">
    <div class="splash-logo">P</div>
    <h1 class="splash-title">Pastimes</h1>
    <p class="splash-tagline">Premium Second-Hand Fashion</p>
    <div class="splash-spinner"></div>
    <p class="splash-sub">EST. 2026 &bull; SOUTH AFRICA</p>
  </div>
</div>

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
      <li><a href="/Pastimes/index.php"  class="nav-link active">Home</a></li>
      <li><a href="/Pastimes/browse.php" class="nav-link">Browse</a></li>
      <li><a href="/Pastimes/about.php"  class="nav-link">About</a></li>
      <?php if (isset($_SESSION['userID'])): ?>
        <?php if ($_SESSION['role']==='admin'): ?>
          <li><a href="/Pastimes/admin/index.php" class="nav-link"><i class="fas fa-shield-alt"></i> Admin</a></li>
        <?php elseif ($_SESSION['role']==='seller'): ?>
          <li><a href="/Pastimes/dashboards/seller.php" class="nav-link"><i class="fas fa-store"></i> Seller Hub</a></li>
        <?php else: ?>
          <li><a href="/Pastimes/dashboards/buyer.php" class="nav-link"><i class="fas fa-user"></i> My Account</a></li>
        <?php endif; ?>
        <li>
          <a href="/Pastimes/cart.php" class="nav-link nav-cart">
            <i class="fas fa-shopping-bag"></i>
            <?php $cc = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; if ($cc>0): ?><span class="cart-badge"><?php echo $cc;?></span><?php endif;?>
          </a>
        </li>
        <li><a href="/Pastimes/messages.php" class="nav-link"><i class="fas fa-envelope"></i></a></li>
        <li><a href="/Pastimes/logout.php" class="btn btn-outline btn-sm">Logout</a></li>
      <?php else: ?>
        <li><a href="/Pastimes/login.php"    class="nav-link">Login</a></li>
        <li><a href="/Pastimes/register.php" class="btn btn-primary btn-sm">Register</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<main class="main-content">

<!-- Hero -->
<section class="hero">
    <div class="hero-content">
        <p class="hero-est">Est. 2026 &bull; South Africa</p>
        <h1>Find Your Next<br><span class="highlight">Signature Piece</span></h1>
        <p class="hero-desc">Discover premium second-hand branded clothing from South Africa's most passionate fashion community. Sustainable, stylish and affordable.</p>
        <div class="hero-cta">
            <a href="/Pastimes/browse.php" class="btn btn-primary btn-lg"><i class="fas fa-search"></i> Browse Collection</a>
            <?php if (!isset($_SESSION['userID'])): ?>
            <a href="/Pastimes/register.php" class="btn btn-outline btn-lg"><i class="fas fa-user-plus"></i> Join Pastimes</a>
            <?php elseif ($_SESSION['role']==='seller'): ?>
            <a href="/Pastimes/dashboards/seller.php" class="btn btn-outline btn-lg"><i class="fas fa-store"></i> My Seller Hub</a>
            <?php else: ?>
            <a href="/Pastimes/dashboards/buyer.php" class="btn btn-outline btn-lg"><i class="fas fa-user"></i> My Account</a>
            <?php endif; ?>
        </div>
        <div class="hero-stats">
            <div><div class="hero-stat-number">500+</div><div class="hero-stat-label">Listings</div></div>
            <div><div class="hero-stat-number">200+</div><div class="hero-stat-label">Sellers</div></div>
            <div><div class="hero-stat-number">50+</div><div class="hero-stat-label">Brands</div></div>
            <div><div class="hero-stat-number">100%</div><div class="hero-stat-label">Authentic</div></div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section" style="background:var(--bg-deep);">
    <div class="container">
        <h2 class="section-title">How <span>Pastimes</span> Works</h2>
        <div class="gold-divider"><div class="gold-divider-dot"></div></div>
        <p class="section-subtitle">Three simple steps to your next favourite outfit</p>
        <div class="steps-grid">
            <div class="step-card"><div class="step-number">1</div><div class="step-icon"><i class="fas fa-user-plus"></i></div><h3>Create Account</h3><p>Register as a Buyer, Seller or Admin.</p></div>
            <div class="step-card"><div class="step-number">2</div><div class="step-icon"><i class="fas fa-search"></i></div><h3>Browse &amp; Discover</h3><p>Filter by brand, size, category and condition.</p></div>
            <div class="step-card"><div class="step-number">3</div><div class="step-icon"><i class="fas fa-shopping-bag"></i></div><h3>Buy or Sell</h3><p>Add to cart and checkout, or list your items.</p></div>
            <div class="step-card"><div class="step-number">4</div><div class="step-icon"><i class="fas fa-truck"></i></div><h3>Fast Delivery</h3><p>Items delivered safely and on time.</p></div>
        </div>
    </div>
</section>

<!-- Featured Listings -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Featured <span>Listings</span></h2>
        <div class="gold-divider"><div class="gold-divider-dot"></div></div>
        <p class="section-subtitle">Fresh arrivals from verified sellers across South Africa</p>
        <?php if (!empty($featured)): ?>
        <div class="products-grid">
            <?php foreach ($featured as $item): ?>
            <a href="/Pastimes/product.php?id=<?php echo $item['clothingID']; ?>" class="card">
                <div class="card-img-placeholder card-position-relative">
                    <img src="/Pastimes/<?php echo htmlspecialchars($item['imagePath']); ?>" alt="<?php echo htmlspecialchars($item['brand']); ?>" onerror="this.src='/Pastimes/images/placeholder.png'" loading="lazy">
                    <span class="card-badge"><?php echo htmlspecialchars($item['clothingCondition']); ?></span>
                    <span class="card-badge-category"><?php echo htmlspecialchars($item['category']); ?></span>
                </div>
                <div class="card-body">
                    <div class="card-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                    <div class="card-title"><?php echo htmlspecialchars(mb_strimwidth($item['description'],0,55,'…')); ?></div>
                    <div class="card-meta">Size: <?php echo htmlspecialchars($item['size']); ?> &bull; <?php echo htmlspecialchars($item['sellerName']); ?></div>
                    <div class="card-price">R <?php echo number_format($item['price'],2); ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-xl">
            <a href="/Pastimes/browse.php" class="btn btn-outline btn-lg">View All Listings <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div style="font-size:3rem;">👗</div>
            <h2>No listings yet</h2>
            <a href="/Pastimes/register.php" class="btn btn-primary">Start Selling</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Categories — Role Based -->
<section class="section" style="background:var(--bg-deep);">
    <div class="container">

        <?php
        $role = $_SESSION['role'] ?? 'guest';

        if ($role === 'seller'):
        // ── SELLER: Sell by Category ──────────────────────────
        ?>
        <h2 class="section-title">Sell by <span>Category</span></h2>
        <div class="gold-divider"><div class="gold-divider-dot"></div></div>
        <p class="section-subtitle">Choose a category to list your item quickly</p>
        <div class="grid-4" style="margin-top:var(--space-2xl);">
            <?php
            $cats = [
                ['Men',         'fas fa-male',        'dashboards/seller.php?page=add&cat=Men'],
                ['Women',       'fas fa-female',      'dashboards/seller.php?page=add&cat=Women'],
                ['Footwear',    'fas fa-shoe-prints', 'dashboards/seller.php?page=add&cat=Footwear'],
                ['Accessories', 'fas fa-glasses',     'dashboards/seller.php?page=add&cat=Accessories'],
            ];
            foreach ($cats as $c):
            ?>
            <a href="/Pastimes/<?php echo $c[2]; ?>" class="card" style="text-align:center;padding:var(--space-xl);">
                <div style="font-size:2.5rem;color:var(--gold);margin-bottom:var(--space-md);"><i class="<?php echo $c[1]; ?>"></i></div>
                <div style="font-size:1rem;font-weight:700;color:var(--text-primary);"><?php echo $c[0]; ?></div>
                <div style="font-size:0.78rem;color:var(--text-muted);margin-top:var(--space-xs);">List an item</div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php elseif ($role === 'admin'): ?>
        <!-- ── ADMIN: Manage by Category ───────────────────── -->
        <h2 class="section-title">Manage by <span>Category</span></h2>
        <div class="gold-divider"><div class="gold-divider-dot"></div></div>
        <p class="section-subtitle">Oversee listings by category from the admin panel</p>
        <div class="grid-4" style="margin-top:var(--space-2xl);">
            <?php
            $cats = [
                ['Men',         'fas fa-male',        'admin/index.php?page=listings&cat=Men'],
                ['Women',       'fas fa-female',      'admin/index.php?page=listings&cat=Women'],
                ['Footwear',    'fas fa-shoe-prints', 'admin/index.php?page=listings&cat=Footwear'],
                ['Accessories', 'fas fa-glasses',     'admin/index.php?page=listings&cat=Accessories'],
            ];
            foreach ($cats as $c):
            ?>
            <a href="/Pastimes/<?php echo $c[2]; ?>" class="card" style="text-align:center;padding:var(--space-xl);">
                <div style="font-size:2.5rem;color:var(--gold);margin-bottom:var(--space-md);"><i class="<?php echo $c[1]; ?>"></i></div>
                <div style="font-size:1rem;font-weight:700;color:var(--text-primary);"><?php echo $c[0]; ?></div>
                <div style="font-size:0.78rem;color:var(--text-muted);margin-top:var(--space-xs);">Manage listings</div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <!-- ── BUYER / GUEST: Shop by Category ─────────────── -->
        <h2 class="section-title">Shop by <span>Category</span></h2>
        <div class="gold-divider"><div class="gold-divider-dot"></div></div>
        <p class="section-subtitle">Browse your favourite style</p>
        <div class="grid-4" style="margin-top:var(--space-2xl);">
            <?php
            $cats = [
                ['Men',         'fas fa-male',        'browse.php?category=Men'],
                ['Women',       'fas fa-female',      'browse.php?category=Women'],
                ['Footwear',    'fas fa-shoe-prints', 'browse.php?category=Footwear'],
                ['Accessories', 'fas fa-glasses',     'browse.php?category=Accessories'],
            ];
            foreach ($cats as $c):
            ?>
            <a href="/Pastimes/<?php echo $c[2]; ?>" class="card" style="text-align:center;padding:var(--space-xl);">
                <div style="font-size:2.5rem;color:var(--gold);margin-bottom:var(--space-md);"><i class="<?php echo $c[1]; ?>"></i></div>
                <div style="font-size:1rem;font-weight:700;color:var(--text-primary);"><?php echo $c[0]; ?></div>
                <div style="font-size:0.78rem;color:var(--text-muted);margin-top:var(--space-xs);">Browse items</div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php if (!isset($_SESSION['userID'])): ?>
<section class="section">
    <div class="container text-center">
        <h2 class="section-title">Ready to Start?</h2>
        <div class="gold-divider"><div class="gold-divider-dot"></div></div>
        <p class="section-subtitle">Join thousands of South Africans buying and selling quality fashion.</p>
        <div style="display:flex;gap:var(--space-md);justify-content:center;flex-wrap:wrap;margin-top:var(--space-xl);">
            <a href="/Pastimes/register.php" class="btn btn-primary btn-lg"><i class="fas fa-user-plus"></i> Create Free Account</a>
            <a href="/Pastimes/browse.php"   class="btn btn-outline btn-lg"><i class="fas fa-eye"></i> Browse First</a>
        </div>
    </div>
</section>
<?php endif; ?>

</main>

<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="/Pastimes/index.php" class="navbar-brand">
          <div class="brand-logo">P</div>
          <div class="brand-text"><span class="brand-name">Pastimes</span><span class="brand-tagline">Vintage Fashion</span></div>
        </a>
        <p>South Africa's premier marketplace for quality second-hand branded clothing.</p>
        <div class="footer-social">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
        </div>
      </div>
      <div class="footer-col"><h4>Shop</h4><ul><li><a href="/Pastimes/browse.php">Browse All</a></li><li><a href="/Pastimes/browse.php?category=Women">Women</a></li><li><a href="/Pastimes/browse.php?category=Men">Men</a></li><li><a href="/Pastimes/browse.php?category=Accessories">Accessories</a></li></ul></div>
      <div class="footer-col"><h4>Account</h4><ul><li><a href="/Pastimes/login.php">Login</a></li><li><a href="/Pastimes/register.php">Register</a></li><li><a href="/Pastimes/messages.php">Messages</a></li></ul></div>
      <div class="footer-col"><h4>Contact</h4><address><div class="footer-contact-item"><i class="fas fa-envelope"></i> support@pastimes.co.za</div><div class="footer-contact-item"><i class="fas fa-phone"></i> +27 11 123 4567</div><div class="footer-contact-item"><i class="fas fa-map-marker-alt"></i> Johannesburg, SA</div></address></div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?php echo date('Y'); ?> Pastimes. All rights reserved.</p>
      <div class="footer-bottom-links"><a href="#">Privacy Policy</a><a href="#">Terms of Service</a></div>
    </div>
  </div>
</footer>

</div>

<script src="/Pastimes/js/main.js"></script>
</body>
</html>