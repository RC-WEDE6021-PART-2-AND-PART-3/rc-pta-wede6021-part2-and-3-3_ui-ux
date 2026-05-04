<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: cart.php
 * Description: Shopping cart page
 * ============================================================
 */

session_start();
require_once 'includes/DBConn.php';

$error = '';
$success = '';
$cartItems = [];
$subtotal = 0;
$deliveryFee = 150; // Fixed delivery fee in ZAR

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'remove':
                $itemID = intval($_POST['itemID']);
                if (isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = array_diff($_SESSION['cart'], [$itemID]);
                    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
                    $success = 'Item removed from cart.';
                }
                break;
            
            case 'clear':
                $_SESSION['cart'] = [];
                $success = 'Cart cleared.';
                break;
            
            case 'save_for_later':
                if (isset($_SESSION['userID'])) {
                    $itemID = intval($_POST['itemID']);
                    try {
                        $conn = getConnection();
                        $stmt = $conn->prepare("INSERT IGNORE INTO tblWishlist (userID, clothingID) VALUES (?, ?)");
                        $userID = $_SESSION['userID'];
                        $stmt->bind_param('ss', $userID, $itemID);
                        $stmt->execute();
                        
                        // Remove from cart
                        $_SESSION['cart'] = array_diff($_SESSION['cart'], [$itemID]);
                        $_SESSION['cart'] = array_values($_SESSION['cart']);
                        
                        $success = 'Item saved for later.';
                        $conn->close();
                    } catch (Exception $e) {
                        $error = 'Could not save item.';
                    }
                }
                break;
        }
    }
}

// Fetch cart items from database
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    try {
        $conn = getConnection();
        $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
        $types = str_repeat('s', count($_SESSION['cart']));
        
        $stmt = $conn->prepare("SELECT c.*, u.username as sellerName 
                                FROM tblClothing c 
                                LEFT JOIN tblUser u ON c.sellerID = u.userID 
                                WHERE c.clothingID IN ($placeholders) AND c.status = 'available'");
        $stmt->bind_param($types, ...$_SESSION['cart']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $cartItems[] = $row;
            $subtotal += $row['price'];
        }
        
        // Clean up cart - remove sold items
        $validIDs = array_column($cartItems, 'clothingID');
        $_SESSION['cart'] = array_intersect($_SESSION['cart'], $validIDs);
        
        $conn->close();
    } catch (Exception $e) {
        $error = 'Could not load cart items.';
    }
}

$total = $subtotal + $deliveryFee;
?>
<?php include 'includes/header.php'; ?>

        <!-- Page Header -->
        <div class="page-header">
            <h1>Shopping <span>Cart</span></h1>
            <p>Review your items before checkout</p>
        </div>

        <!-- Cart Section -->
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

                <?php if (!empty($cartItems)): ?>
                    <div class="grid-2" style="grid-template-columns: 2fr 1fr; gap: var(--space-xl); align-items: start;">
                        <!-- Cart Items -->
                        <div>
                            <div class="flex-between mb-lg">
                                <h2 style="font-family: var(--font-display); font-size: 1.3rem; color: var(--text-primary);">
                                    <?php echo count($cartItems); ?> Items in Cart
                                </h2>
                                <form method="POST" style="display: inline;">
                                    <button type="submit" name="action" value="clear" class="text-gold" style="background: none; border: none; cursor: pointer; font-size: 0.85rem;">
                                        Clear Cart
                                    </button>
                                </form>
                            </div>

                            <?php foreach ($cartItems as $item): ?>
                                <div class="cart-item">
                                    <div class="cart-item-img">
                                        <span style="color: var(--gold);"><?php echo strtoupper(substr($item['brand'], 0, 1)); ?></span>
                                    </div>
                                    <div class="cart-item-info">
                                        <div class="cart-item-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                                        <h3 class="cart-item-name"><?php echo htmlspecialchars(substr($item['description'], 0, 40)); ?>...</h3>
                                        <p class="cart-item-meta">
                                            Size: <?php echo htmlspecialchars($item['size']); ?> | 
                                            Condition: <?php echo htmlspecialchars($item['clothingCondition']); ?>
                                        </p>
                                        <p class="cart-item-meta">Seller: <?php echo htmlspecialchars($item['sellerName']); ?></p>
                                        <div class="cart-item-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="itemID" value="<?php echo $item['clothingID']; ?>">
                                                <button type="submit" name="action" value="remove" class="cart-action-btn">
                                                    <i class="fas fa-trash"></i> Remove
                                                </button>
                                            </form>
                                            <?php if (isset($_SESSION['userID'])): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="itemID" value="<?php echo $item['clothingID']; ?>">
                                                    <button type="submit" name="action" value="save_for_later" class="cart-action-btn save">
                                                        <i class="fas fa-heart"></i> Save for Later
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="cart-item-price">R<?php echo number_format($item['price'], 2); ?></div>
                                </div>
                            <?php endforeach; ?>

                            <a href="browse.php" class="text-gold" style="display: inline-flex; align-items: center; gap: var(--space-sm); margin-top: var(--space-lg);">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                        </div>

                        <!-- Order Summary -->
                        <div class="order-summary">
                            <h3>Order Summary</h3>

                            <div class="order-line">
                                <span>Subtotal (<?php echo count($cartItems); ?> items)</span>
                                <span>R<?php echo number_format($subtotal, 2); ?></span>
                            </div>

                            <div class="order-line">
                                <span>Delivery Fee</span>
                                <span>R<?php echo number_format($deliveryFee, 2); ?></span>
                            </div>

                            <div class="order-line total">
                                <span>Total</span>
                                <span class="price">R<?php echo number_format($total, 2); ?></span>
                            </div>

                            <!-- Promo Code -->
                            <div class="form-group mt-lg">
                                <label class="form-label">Promo Code</label>
                                <div class="promo-input-row">
                                    <input type="text" class="form-control" placeholder="Enter code">
                                    <button class="btn btn-ghost">Apply</button>
                                </div>
                            </div>

                            <a href="checkout.php" class="btn btn-primary btn-full btn-lg mt-lg">
                                Proceed to Checkout <i class="fas fa-arrow-right"></i>
                            </a>

                            <div class="mt-lg text-center">
                                <p class="text-muted" style="font-size: 0.8rem; display: flex; align-items: center; gap: var(--space-sm); justify-content: center;">
                                    <i class="fas fa-lock"></i> Secure checkout
                                </p>
                                <p class="text-muted" style="font-size: 0.8rem; display: flex; align-items: center; gap: var(--space-sm); justify-content: center;">
                                    <i class="fas fa-truck"></i> Nationwide delivery
                                </p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center" style="padding: var(--space-3xl);">
                        <div style="font-size: 5rem; color: var(--text-faint); margin-bottom: var(--space-lg);">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h2 style="color: var(--text-secondary); margin-bottom: var(--space-sm);">Your cart is empty</h2>
                        <p class="text-muted" style="max-width: 400px; margin: 0 auto var(--space-xl);">
                            Looks like you haven't added anything to your cart yet. Start browsing our collection to find something you love!
                        </p>
                        <a href="browse.php" class="btn btn-primary btn-lg">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

<?php include 'includes/footer.php'; ?>