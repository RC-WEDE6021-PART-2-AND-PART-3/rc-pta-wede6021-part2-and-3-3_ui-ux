<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: product.php
 * Description: Single product detail page
 * ============================================================
 */

session_start();
require_once 'includes/DBConn.php';

$productID = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$seller = null;
$error = '';
$success = '';

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_SESSION['userID'])) {
        header('Location: login.php');
        exit();
    }
    
    if ($_POST['action'] === 'add_to_cart') {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Check if item already in cart
        if (!in_array($productID, $_SESSION['cart'])) {
            $_SESSION['cart'][] = $productID;
            $success = 'Item added to cart successfully!';
        } else {
            $error = 'This item is already in your cart.';
        }
    }
    
    if ($_POST['action'] === 'add_to_wishlist') {
        try {
            $conn = getConnection();
            $stmt = $conn->prepare("INSERT IGNORE INTO tblWishlist (userID, clothingID) VALUES (?, ?)");
            $userID = $_SESSION['userID'];
            $stmt->bind_param('ss', $userID, $productID);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $success = 'Item added to wishlist!';
            } else {
                $error = 'Item is already in your wishlist.';
            }
            $conn->close();
        } catch (Exception $e) {
            $error = 'Could not add to wishlist.';
        }
    }
}

// Fetch product details
if ($productID > 0) {
    try {
        $conn = getConnection();
        
        $stmt = $conn->prepare("SELECT c.*, u.username, u.fullName, u.email, u.created_at as sellerSince 
                                FROM tblClothing c 
                                LEFT JOIN tblUser u ON c.sellerID = u.userID 
                                WHERE c.clothingID = ?");
        $stmt->bind_param('s', $productID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
        }
        
        $conn->close();
    } catch (Exception $e) {
        $error = 'Could not load product details.';
    }
}
?>
<?php include 'includes/header.php'; ?>

        <?php if ($product): ?>
            <!-- Page Header -->
            <div class="page-header">
                <p style="margin-bottom: var(--space-sm);">
                    <a href="browse.php" class="text-gold"><i class="fas fa-arrow-left"></i> Back to Browse</a>
                </p>
                <h1><?php echo htmlspecialchars($product['brand']); ?></h1>
            </div>

            <!-- Product Detail Section -->
            <section class="section">
                <div class="container">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <div class="grid-2" style="gap: var(--space-2xl);">
                        <!-- Product Image -->
                        <div>
                            <div class="card" style="overflow: hidden;">
                                <div class="card-img-placeholder" style="aspect-ratio: 1; font-size: 6rem;">
                                    <span style="color: var(--gold);"><?php echo strtoupper(substr($product['brand'], 0, 1)); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div>
                            <div class="flex gap-sm mb-md">
                                <span class="badge condition-<?php echo strtolower(str_replace(' ', '-', $product['clothingCondition'])); ?>">
                                    <?php echo htmlspecialchars($product['clothingCondition']); ?>
                                </span>
                                <span class="badge badge-muted"><?php echo htmlspecialchars($product['category']); ?></span>
                                <span class="badge badge-muted">Size: <?php echo htmlspecialchars($product['size']); ?></span>
                            </div>

                            <h1 style="font-family: var(--font-display); font-size: 2rem; color: var(--text-primary); margin-bottom: var(--space-sm);">
                                <?php echo htmlspecialchars($product['brand']); ?>
                            </h1>

                            <div style="font-size: 2.5rem; font-family: var(--font-display); color: var(--gold); margin-bottom: var(--space-lg);">
                                R<?php echo number_format($product['price'], 2); ?>
                            </div>

                            <div style="padding: var(--space-lg); background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); margin-bottom: var(--space-lg);">
                                <h3 style="font-size: 0.85rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); margin-bottom: var(--space-md);">Description</h3>
                                <p style="color: var(--text-secondary); line-height: 1.8;">
                                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                                </p>
                            </div>

                            <!-- Item Details -->
                            <div class="table-wrapper mb-lg">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td style="font-weight: 600; color: var(--text-muted);">Brand</td>
                                            <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: 600; color: var(--text-muted);">Category</td>
                                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: 600; color: var(--text-muted);">Size</td>
                                            <td><?php echo htmlspecialchars($product['size']); ?></td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: 600; color: var(--text-muted);">Condition</td>
                                            <td><?php echo htmlspecialchars($product['clothingCondition']); ?></td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: 600; color: var(--text-muted);">Listed</td>
                                            <td><?php echo date('F j, Y', strtotime($product['dateAdded'])); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Action Buttons -->
                            <?php if ($product['status'] === 'available'): ?>
                                <form method="POST" class="flex gap-md">
                                    <button type="submit" name="action" value="add_to_cart" class="btn btn-primary btn-lg" style="flex: 2;">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                    <button type="submit" name="action" value="add_to_wishlist" class="btn btn-outline btn-lg" style="flex: 1;">
                                        <i class="fas fa-heart"></i> Save
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle"></i>
                                    This item has been sold.
                                </div>
                            <?php endif; ?>

                            <!-- Seller Info -->
                            <div style="margin-top: var(--space-xl); padding: var(--space-lg); background: var(--bg-surface); border-radius: var(--radius-lg);">
                                <div class="flex gap-md" style="align-items: center;">
                                    <div class="profile-avatar" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                        <?php echo strtoupper(substr($product['username'], 0, 1)); ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <h4 style="color: var(--text-primary); margin-bottom: 2px;">
                                            <?php echo htmlspecialchars($product['username']); ?>
                                        </h4>
                                        <p class="text-muted" style="font-size: 0.8rem;">
                                            Seller since <?php echo date('F Y', strtotime($product['sellerSince'])); ?>
                                        </p>
                                    </div>
                                    <a href="messages.php?to=<?php echo $product['sellerID']; ?>&item=<?php echo $product['clothingID']; ?>" class="btn btn-outline btn-sm">
                                        <i class="fas fa-comment"></i> Message
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section class="section">
                <div class="container text-center">
                    <div style="font-size: 4rem; color: var(--text-faint); margin-bottom: var(--space-lg);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h2 style="color: var(--text-secondary); margin-bottom: var(--space-sm);">Product Not Found</h2>
                    <p class="text-muted">The item you're looking for doesn't exist or has been removed.</p>
                    <a href="browse.php" class="btn btn-primary mt-lg">Browse Collection</a>
                </div>
            </section>
        <?php endif; ?>

<?php include 'includes/footer.php'; ?>