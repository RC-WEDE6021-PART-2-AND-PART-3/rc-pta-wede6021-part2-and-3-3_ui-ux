<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: browse.php
 * Description: Browse collection page with filters
 * ============================================================
 */

session_start();
require_once 'includes/DBConn.php';

// Get filter parameters
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$size = isset($_GET['size']) ? trim($_GET['size']) : '';
$condition = isset($_GET['condition']) ? trim($_GET['condition']) : '';
$minPrice = isset($_GET['minPrice']) && is_numeric($_GET['minPrice']) ? $_GET['minPrice'] : '';
$maxPrice = isset($_GET['maxPrice']) && is_numeric($_GET['maxPrice']) ? $_GET['maxPrice'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query
$where = ["status = 'available'"];
$params = [];
$types = '';

if (!empty($category)) {
    $where[] = "category = ?";
    $params[] = $category;
    $types .= 's';
}

if (!empty($brand)) {
    $where[] = "brand LIKE ?";
    $params[] = "%$brand%";
    $types .= 's';
}

if (!empty($size)) {
    $where[] = "size = ?";
    $params[] = $size;
    $types .= 's';
}

if (!empty($condition)) {
    $where[] = "clothingCondition = ?";
    $params[] = $condition;
    $types .= 's';
}

if (!empty($minPrice)) {
    $where[] = "price >= ?";
    $params[] = $minPrice;
    $types .= 's';
}

if (!empty($maxPrice)) {
    $where[] = "price <= ?";
    $params[] = $maxPrice;
    $types .= 's';
}

if (!empty($search)) {
    $where[] = "(brand LIKE ? OR description LIKE ? OR category LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

// Order by
$orderBy = "dateAdded DESC"; // Default: newest
switch ($sort) {
    case 'oldest':
        $orderBy = "dateAdded ASC";
        break;
    case 'price_low':
        $orderBy = "price ASC";
        break;
    case 'price_high':
        $orderBy = "price DESC";
        break;
    case 'brand':
        $orderBy = "brand ASC";
        break;
}

$sql = "SELECT c.*, u.username as sellerName FROM tblClothing c 
        LEFT JOIN tblUser u ON c.sellerID = u.userID 
        WHERE " . implode(' AND ', $where) . " 
        ORDER BY " . $orderBy;

try {
    $conn = getConnection();
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM tblClothing WHERE " . implode(' AND ', $where);
    if (!empty($params)) {
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $totalItems = $countStmt->get_result()->fetch_assoc()['total'];
    } else {
        $totalItems = $conn->query($countSql)->fetch_assoc()['total'];
    }
    
    // Get items
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $items = $stmt->get_result();
    } else {
        $items = $conn->query($sql);
    }
    
    // Get unique brands for filter
    $brandsResult = $conn->query("SELECT DISTINCT brand FROM tblClothing WHERE status = 'available' ORDER BY brand");
    $brands = [];
    while ($row = $brandsResult->fetch_assoc()) {
        $brands[] = $row['brand'];
    }
    
} catch (Exception $e) {
    $items = null;
    $totalItems = 0;
    $brands = [];
}
?>
<?php include 'includes/header.php'; ?>

        <!-- Page Header -->
        <div class="page-header">
            <h1>Browse <span>Collection</span></h1>
            <p>Explore our curated selection of premium second-hand branded clothing. Use filters to find exactly what you're looking for.</p>
        </div>

        <!-- Browse Section -->
        <section class="section">
            <div class="container">
                <div class="browse-layout">
                    
                    <!-- Filter Panel -->
                    <aside class="filter-panel">
                        <div class="filter-header">
                            <h3><i class="fas fa-sliders-h"></i> Filters</h3>
                            <a href="browse.php" class="filter-clear">Clear All</a>
                        </div>

                        <form method="GET" action="browse.php" id="filterForm">
                            <!-- Category Filter -->
                            <div class="filter-section">
                                <div class="filter-label">Category</div>
                                <div class="radio-option">
                                    <input type="radio" name="category" value="" <?php echo empty($category) ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    <label>All</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="category" value="Women" <?php echo $category === 'Women' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    <label>Women</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="category" value="Men" <?php echo $category === 'Men' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    <label>Men</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="category" value="Accessories" <?php echo $category === 'Accessories' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    <label>Accessories</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="category" value="Footwear" <?php echo $category === 'Footwear' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    <label>Footwear</label>
                                </div>
                            </div>

                            <!-- Brand Filter -->
                            <div class="filter-section">
                                <div class="filter-label">Brand</div>
                                <select name="brand" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Brands</option>
                                    <?php foreach ($brands as $b): ?>
                                        <option value="<?php echo htmlspecialchars($b); ?>" <?php echo $brand === $b ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($b); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Size Filter -->
                            <div class="filter-section">
                                <div class="filter-label">Size</div>
                                <div class="size-options">
                                    <button type="submit" name="size" value="" class="size-btn <?php echo empty($size) ? 'active' : ''; ?>">All</button>
                                    <button type="submit" name="size" value="XS" class="size-btn <?php echo $size === 'XS' ? 'active' : ''; ?>">XS</button>
                                    <button type="submit" name="size" value="S" class="size-btn <?php echo $size === 'S' ? 'active' : ''; ?>">S</button>
                                    <button type="submit" name="size" value="M" class="size-btn <?php echo $size === 'M' ? 'active' : ''; ?>">M</button>
                                    <button type="submit" name="size" value="L" class="size-btn <?php echo $size === 'L' ? 'active' : ''; ?>">L</button>
                                    <button type="submit" name="size" value="XL" class="size-btn <?php echo $size === 'XL' ? 'active' : ''; ?>">XL</button>
                                </div>
                            </div>

                            <!-- Condition Filter -->
                            <div class="filter-section">
                                <div class="filter-label">Condition</div>
                                <div class="form-check">
                                    <input type="checkbox" name="condition" value="" <?php echo empty($condition) ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    <label>All Conditions</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="condition" value="Like New" <?php echo $condition === 'Like New' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    <label>Like New</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="condition" value="Excellent" <?php echo $condition === 'Excellent' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    <label>Excellent</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="condition" value="Good" <?php echo $condition === 'Good' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    <label>Good</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="condition" value="Fair" <?php echo $condition === 'Fair' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    <label>Fair</label>
                                </div>
                            </div>

                            <!-- Price Range Filter -->
                            <div class="filter-section">
                                <div class="filter-label">Price Range</div>
                                <div class="price-range">
                                    <div class="price-range-inputs">
                                        <input type="number" name="minPrice" placeholder="Min" value="<?php echo htmlspecialchars($minPrice); ?>">
                                        <input type="number" name="maxPrice" placeholder="Max" value="<?php echo htmlspecialchars($maxPrice); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-outline btn-full mt-sm">Apply</button>
                                </div>
                            </div>
                            
                            <!-- Hidden sort parameter -->
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        </form>
                    </aside>

                    <!-- Products Grid -->
                    <main>
                        <div class="products-header">
                            <div class="products-count">
                                Showing <span><?php echo $totalItems; ?></span> of <span><?php echo $totalItems; ?></span> items
                            </div>
                            <div class="flex gap-md">
                                <span class="text-muted" style="font-size: 0.82rem;">Sort by:</span>
                                <select class="form-control" style="width: auto; padding: 0.4rem 2rem 0.4rem 0.8rem;" onchange="window.location.href='browse.php?sort='+this.value+'&category=<?php echo urlencode($category); ?>&brand=<?php echo urlencode($brand); ?>&size=<?php echo urlencode($size); ?>&condition=<?php echo urlencode($condition); ?>&search=<?php echo urlencode($search); ?>'">
                                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="brand" <?php echo $sort === 'brand' ? 'selected' : ''; ?>>Brand A-Z</option>
                                </select>
                            </div>
                        </div>

                        <?php if ($items && $items->num_rows > 0): ?>
                            <div class="products-grid">
                                <?php while ($item = $items->fetch_assoc()): ?>
                                    <a href="product.php?id=<?php echo $item['clothingID']; ?>" class="card card-position-relative">
                                        <span class="card-badge condition-<?php echo strtolower(str_replace(' ', '-', $item['clothingCondition'])); ?>">
                                            <?php echo htmlspecialchars($item['clothingCondition']); ?>
                                        </span>
                                        <span class="card-badge-category"><?php echo htmlspecialchars($item['category']); ?></span>
                                        <div class="card-img-placeholder">
                                            <span style="font-size: 2.5rem; color: var(--gold);">
                                                <?php echo strtoupper(substr($item['brand'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <div class="card-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                                            <h3 class="card-title"><?php echo htmlspecialchars(substr($item['description'], 0, 35)); ?>...</h3>
                                            <p class="card-meta">Size: <?php echo htmlspecialchars($item['size']); ?> | Seller: <?php echo htmlspecialchars($item['sellerName']); ?></p>
                                            <div class="card-price">R<?php echo number_format($item['price'], 2); ?></div>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center" style="padding: var(--space-3xl);">
                                <div style="font-size: 4rem; color: var(--text-faint); margin-bottom: var(--space-lg);">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h3 style="color: var(--text-secondary); margin-bottom: var(--space-sm);">No items found</h3>
                                <p class="text-muted">Try adjusting your filters or search terms</p>
                                <a href="browse.php" class="btn btn-outline mt-lg">Clear All Filters</a>
                            </div>
                        <?php endif; ?>
                    </main>
                </div>
            </div>
        </section>

<?php 
if (isset($conn)) $conn->close();
include 'includes/footer.php'; 
?>