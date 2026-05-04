<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: index.php
 * Description: Homepage / Landing page
 * ============================================================
 */

session_start();
require_once 'includes/DBConn.php';

// Get statistics for the hero section
try {
    $conn = getConnection();
    
    // Count active listings
    $result = $conn->query("SELECT COUNT(*) as count FROM tblClothing WHERE status = 'available'");
    $activeListings = $result->fetch_assoc()['count'];
    
    // Count verified sellers
    $result = $conn->query("SELECT COUNT(*) as count FROM tblUser WHERE seller_status = 'verified'");
    $verifiedSellers = $result->fetch_assoc()['count'];
    
    // Count total customers
    $result = $conn->query("SELECT COUNT(*) as count FROM tblUser WHERE role = 'buyer' OR role = 'seller'");
    $totalCustomers = $result->fetch_assoc()['count'];
    
    // Get recent items for category section
    $recentItems = $conn->query("SELECT category, COUNT(*) as count FROM tblClothing WHERE status = 'available' GROUP BY category");
    $categories = [];
    while ($row = $recentItems->fetch_assoc()) {
        $categories[$row['category']] = $row['count'];
    }
    
    $conn->close();
} catch (Exception $e) {
    $activeListings = 0;
    $verifiedSellers = 0;
    $totalCustomers = 0;
    $categories = [];
}
?>
<?php include 'includes/header.php'; ?>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <div class="hero-est">EST. 2026</div>
                <h1>Discover <span class="highlight">Timeless Fashion</span><br>at Exceptional Prices</h1>
                <p class="hero-desc">South Africa's premier marketplace for quality second-hand branded clothing. Buy and sell pre-loved fashion with confidence and style.</p>
                <div class="hero-cta">
                    <a href="browse.php" class="btn btn-primary btn-lg">Start Shopping <i class="fas fa-arrow-right"></i></a>
                    <a href="register.php" class="btn btn-outline btn-lg">Become a Seller</a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="hero-stat-number"><?php echo number_format($activeListings); ?>+</div>
                        <div class="hero-stat-label">Active Listings</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-number"><?php echo number_format($totalCustomers); ?>+</div>
                        <div class="hero-stat-label">Happy Customers</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-number"><?php echo number_format($verifiedSellers); ?>+</div>
                        <div class="hero-stat-label">Verified Sellers</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Shop by Category Section -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">Shop by <span>Category</span></h2>
                <div class="gold-divider">
                    <span class="gold-divider-dot"></span>
                </div>
                <p class="section-subtitle">Explore our curated collection of premium second-hand branded clothing</p>
                
                <div class="grid-4">
                    <a href="browse.php?category=Women" class="card">
                        <div class="card-img-placeholder">
                            <span style="font-size: 3rem; color: var(--gold);">W</span>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="card-title">Women's Fashion</h3>
                            <p class="card-meta"><?php echo isset($categories['Women']) ? $categories['Women'] : 0; ?> Items</p>
                        </div>
                    </a>
                    
                    <a href="browse.php?category=Men" class="card">
                        <div class="card-img-placeholder">
                            <span style="font-size: 3rem; color: var(--gold);">M</span>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="card-title">Men's Fashion</h3>
                            <p class="card-meta"><?php echo isset($categories['Men']) ? $categories['Men'] : 0; ?> Items</p>
                        </div>
                    </a>
                    
                    <a href="browse.php?category=Accessories" class="card">
                        <div class="card-img-placeholder">
                            <span style="font-size: 3rem; color: var(--gold);">A</span>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="card-title">Accessories</h3>
                            <p class="card-meta"><?php echo isset($categories['Accessories']) ? $categories['Accessories'] : 0; ?> Items</p>
                        </div>
                    </a>
                    
                    <a href="browse.php?category=Footwear" class="card">
                        <div class="card-img-placeholder">
                            <span style="font-size: 3rem; color: var(--gold);">F</span>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="card-title">Footwear</h3>
                            <p class="card-meta"><?php echo isset($categories['Footwear']) ? $categories['Footwear'] : 0; ?> Items</p>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- Featured Items Section -->
        <section class="section" style="background: var(--bg-deep);">
            <div class="container">
                <h2 class="section-title">Featured <span>Items</span></h2>
                <div class="gold-divider">
                    <span class="gold-divider-dot"></span>
                </div>
                <p class="section-subtitle">Handpicked premium pieces from our collection</p>
                
                <div class="grid-4">
                    <?php
                    try {
                        $conn = getConnection();
                        $featured = $conn->query("SELECT * FROM tblClothing WHERE status = 'available' ORDER BY dateAdded DESC LIMIT 4");
                        
                        while ($item = $featured->fetch_assoc()):
                    ?>
                    <a href="product.php?id=<?php echo $item['clothingID']; ?>" class="card card-position-relative">
                        <span class="card-badge"><?php echo htmlspecialchars($item['clothingCondition']); ?></span>
                        <span class="card-badge-category"><?php echo htmlspecialchars($item['category']); ?></span>
                        <div class="card-img-placeholder">
                            <span style="font-size: 2.5rem; color: var(--gold);"><?php echo strtoupper(substr($item['brand'], 0, 1)); ?></span>
                        </div>
                        <div class="card-body">
                            <div class="card-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                            <h3 class="card-title"><?php echo htmlspecialchars(substr($item['description'], 0, 40)); ?>...</h3>
                            <p class="card-meta">Size: <?php echo htmlspecialchars($item['size']); ?></p>
                            <div class="card-price">R<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                    </a>
                    <?php 
                        endwhile;
                        $conn->close();
                    } catch (Exception $e) {
                        echo '<p class="text-muted">Unable to load featured items.</p>';
                    }
                    ?>
                </div>
                
                <div class="text-center mt-xl">
                    <a href="browse.php" class="btn btn-outline btn-lg">View All Items <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">How It <span>Works</span></h2>
                <div class="gold-divider">
                    <span class="gold-divider-dot"></span>
                </div>
                <p class="section-subtitle">Start buying and selling in three simple steps</p>
                
                <div class="steps-grid">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon"><i class="fas fa-user-plus"></i></div>
                        <h3>Create Account</h3>
                        <p>Sign up for free and join our community of fashion-conscious buyers and sellers.</p>
                    </div>
                    
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon"><i class="fas fa-search"></i></div>
                        <h3>Browse & Buy</h3>
                        <p>Explore our curated collection of premium branded clothing at exceptional prices.</p>
                    </div>
                    
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon"><i class="fas fa-truck"></i></div>
                        <h3>Get Delivered</h3>
                        <p>Enjoy secure checkout and nationwide delivery right to your doorstep.</p>
                    </div>
                </div>
            </div>
        </section>

<?php include 'includes/footer.php'; ?>