<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: checkout.php
 * Description: Checkout page for completing purchases
 * ============================================================
 */

session_start();
require_once 'includes/DBConn.php';

// Redirect if not logged in
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

$error = '';
$success = '';
$cartItems = [];
$addresses = [];
$subtotal = 0;
$deliveryFee = 150;

// Fetch cart items and user addresses
try {
    $conn = getConnection();
    
    // Get cart items
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $types = str_repeat('s', count($_SESSION['cart']));
    
    $stmt = $conn->prepare("SELECT * FROM tblClothing WHERE clothingID IN ($placeholders) AND status = 'available'");
    $stmt->bind_param($types, ...$_SESSION['cart']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $subtotal += $row['price'];
    }
    
    // Get user addresses
    $stmt = $conn->prepare("SELECT * FROM tblDeliveryAddress WHERE userID = ?");
    $userID = $_SESSION['userID'];
    $stmt->bind_param('s', $userID);
    $stmt->execute();
    $addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    $error = 'Could not load checkout data.';
}

$total = $subtotal + $deliveryFee;

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    $addressID = isset($_POST['addressID']) ? intval($_POST['addressID']) : 0;
    
    if ($addressID === 0) {
        $error = 'Please select a delivery address.';
    } elseif (empty($cartItems)) {
        $error = 'Your cart is empty or items are no longer available.';
    } else {
        try {
            $conn = getConnection();
            $conn->begin_transaction();
            
            // Create order
            $stmt = $conn->prepare("INSERT INTO tblOrder (buyerID, addressID, totalAmount, orderStatus) VALUES (?, ?, ?, 'placed')");
            $totalStr = strval($total);
            $stmt->bind_param('sss', $userID, $addressID, $totalStr);
            $stmt->execute();
            $orderID = $conn->insert_id;
            
            // Create order items and mark clothing as sold
            $stmtItem = $conn->prepare("INSERT INTO tblOrderItem (orderID, clothingID, priceAtPurchase) VALUES (?, ?, ?)");
            $stmtUpdate = $conn->prepare("UPDATE tblClothing SET status = 'sold' WHERE clothingID = ?");
            
            foreach ($cartItems as $item) {
                $orderIDStr = strval($orderID);
                $clothingIDStr = strval($item['clothingID']);
                $priceStr = strval($item['price']);
                
                $stmtItem->bind_param('sss', $orderIDStr, $clothingIDStr, $priceStr);
                $stmtItem->execute();
                
                $stmtUpdate->bind_param('s', $clothingIDStr);
                $stmtUpdate->execute();
            }
            
            $conn->commit();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Redirect to success page
            header('Location: profile.php?tab=orders&success=1');
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Could not process order. Please try again.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

        <!-- Page Header -->
        <div class="page-header">
            <h1>Checkout</h1>
            <p>Complete your purchase</p>
        </div>

        <!-- Checkout Section -->
        <section class="section">
            <div class="container">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="place_order">
                    
                    <div class="grid-2" style="grid-template-columns: 2fr 1fr; gap: var(--space-xl); align-items: start;">
                        <!-- Checkout Form -->
                        <div>
                            <!-- Delivery Address -->
                            <div class="settings-section">
                                <h2>Delivery Address</h2>
                                <p>Select where you'd like your items delivered</p>

                                <?php if (!empty($addresses)): ?>
                                    <div class="grid-2" style="gap: var(--space-md);">
                                        <?php foreach ($addresses as $addr): ?>
                                            <label class="card" style="cursor: pointer; padding: var(--space-lg);">
                                                <div class="flex gap-md" style="align-items: flex-start;">
                                                    <input type="radio" name="addressID" value="<?php echo $addr['addressID']; ?>" <?php echo $addr['isDefault'] ? 'checked' : ''; ?> style="margin-top: 4px;">
                                                    <div>
                                                        <span class="badge badge-gold mb-sm"><?php echo ucfirst($addr['addressType']); ?></span>
                                                        <p style="color: var(--text-primary); margin-bottom: var(--space-xs);">
                                                            <?php echo htmlspecialchars($addr['streetAddress']); ?>
                                                        </p>
                                                        <p class="text-muted" style="font-size: 0.85rem;">
                                                            <?php echo htmlspecialchars($addr['suburb']); ?>, <?php echo htmlspecialchars($addr['city']); ?>, <?php echo htmlspecialchars($addr['postalCode']); ?>
                                                        </p>
                                                        <?php if ($addr['isDefault']): ?>
                                                            <span class="badge badge-success mt-sm">Default</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <a href="profile.php?tab=addresses" class="btn btn-outline mt-lg">
                                        <i class="fas fa-plus"></i> Add New Address
                                    </a>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-info-circle"></i>
                                        You don't have any delivery addresses saved. Please add one to continue.
                                    </div>
                                    <a href="profile.php?tab=addresses" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Delivery Address
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Order Items -->
                            <div class="settings-section">
                                <h2>Order Items</h2>
                                <p><?php echo count($cartItems); ?> items in your order</p>

                                <?php foreach ($cartItems as $item): ?>
                                    <div class="cart-item">
                                        <div class="cart-item-img">
                                            <span style="color: var(--gold);"><?php echo strtoupper(substr($item['brand'], 0, 1)); ?></span>
                                        </div>
                                        <div class="cart-item-info">
                                            <div class="cart-item-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                                            <h3 class="cart-item-name"><?php echo htmlspecialchars(substr($item['description'], 0, 40)); ?>...</h3>
                                            <p class="cart-item-meta">Size: <?php echo htmlspecialchars($item['size']); ?></p>
                                        </div>
                                        <div class="cart-item-price">R<?php echo number_format($item['price'], 2); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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

                            <button type="submit" class="btn btn-primary btn-full btn-lg mt-lg" <?php echo empty($addresses) ? 'disabled' : ''; ?>>
                                Place Order <i class="fas fa-check"></i>
                            </button>

                            <a href="cart.php" class="btn btn-ghost btn-full mt-md">
                                <i class="fas fa-arrow-left"></i> Back to Cart
                            </a>

                            <div class="mt-lg text-center">
                                <p class="text-muted" style="font-size: 0.8rem;">
                                    <i class="fas fa-lock"></i> Your payment information is secure
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>

<?php 
if (isset($conn)) $conn->close();
include 'includes/footer.php'; 
?>