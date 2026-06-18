<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['userID'])) {
    header('Location: /Pastimes/login.php'); exit;
}

if ($_SESSION['role'] !== 'buyer') {
    header('Location: /Pastimes/index.php'); exit;
}

// Remove item
if (isset($_GET['remove'])) {
    $removeID = intval($_GET['remove']);
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $k => $ci) {
            if ($ci['clothingID'] == $removeID) {
                unset($_SESSION['cart'][$k]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                break;
            }
        }
    }
    header('Location: /Pastimes/cart.php'); exit;
}

// Clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header('Location: /Pastimes/cart.php'); exit;
}

$cart  = $_SESSION['cart'] ?? [];
$total = array_sum(array_column($cart, 'price'));

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Shopping <span style="color:var(--gold);">Cart</span></h1>
    <p>Review your selected items before checkout</p>
</div>

<div class="container section">

    <?php if (!empty($cart)): ?>
    <div class="cart-layout">

        <!-- Cart Items -->
        <div>
            <div class="flex-between mb-md">
                <h2 style="color:var(--text-primary); font-size:1.1rem;"><?php echo count($cart); ?> Item<?php echo count($cart)!==1?'s':''; ?></h2>
                <a href="cart.php?clear=1" class="btn btn-ghost btn-sm" onclick="return confirm('Clear entire cart?')">
                    <i class="fas fa-trash"></i> Clear Cart
                </a>
            </div>

            <?php foreach ($cart as $item): ?>
            <div class="cart-item">
                <!-- Image -->
                <div class="cart-item-img">
                    <img
                        src="/Pastimes/<?php echo htmlspecialchars($item['imagePath']); ?>"
                        alt="<?php echo htmlspecialchars($item['brand']); ?>"
                        onerror="this.src='/Pastimes/images/placeholder.png'"
                    >
                </div>
                <!-- Info -->
                <div style="flex:1;">
                    <div style="font-weight:700; color:var(--gold); font-size:0.9rem; text-transform:uppercase; letter-spacing:.06em;">
                        <?php echo htmlspecialchars($item['brand']); ?>
                    </div>
                    <div style="color:var(--text-primary); font-size:0.9rem; margin:.25rem 0;">
                        <?php echo htmlspecialchars(mb_strimwidth($item['description'],0,60,'…')); ?>
                    </div>
                    <div style="color:var(--gold); font-family:var(--font-display); font-size:1.1rem; font-weight:700;" class="cart-item-price" data-price="<?php echo $item['price']; ?>">
                        R <?php echo number_format($item['price'],2); ?>
                    </div>
                </div>
                <!-- Remove -->
                <a href="cart.php?remove=<?php echo $item['clothingID']; ?>" class="btn btn-danger btn-sm" style="align-self:flex-start;">
                    <i class="fas fa-times"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Summary -->
        <div class="cart-summary">
            <h3>Order Summary</h3>
            <div class="summary-row">
                <span>Subtotal (<?php echo count($cart); ?> items)</span>
                <span>R <?php echo number_format($total,2); ?></span>
            </div>
            <div class="summary-row">
                <span>Delivery</span>
                <span style="color:#4ade80;">Free</span>
            </div>
            <div class="summary-row" style="border-top:1px solid var(--border-gold); padding-top:var(--space-sm); margin-top:var(--space-sm);">
                <span class="summary-total">Total</span>
                <span class="summary-total" id="cartTotal">R <?php echo number_format($total,2); ?></span>
            </div>

            <a href="/Pastimes/checkout.php" class="btn btn-primary btn-full btn-lg" style="margin-top:var(--space-lg);">
                <i class="fas fa-lock"></i> Proceed to Checkout
            </a>
            <a href="/Pastimes/browse.php" class="btn btn-ghost btn-full" style="margin-top:var(--space-sm);">
                <i class="fas fa-arrow-left"></i> Continue Shopping
            </a>
        </div>

    </div>

    <?php else: ?>
    <div class="empty-state">
        <div style="font-size:4rem; margin-bottom:1rem;"><i class="fas fa-shopping-bag" style="color:var(--gold);font-size:3rem;"></i></div>
        <h2>Your cart is empty</h2>
        <p>Looks like you haven't added anything yet.</p>
        <a href="/Pastimes/browse.php" class="btn btn-primary btn-lg">
            <i class="fas fa-search"></i> Browse Collection
        </a>
    </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>