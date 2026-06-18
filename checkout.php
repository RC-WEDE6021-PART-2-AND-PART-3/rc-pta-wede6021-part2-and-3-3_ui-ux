<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'buyer') {
    header('Location: /Pastimes/login.php'); exit;
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: /Pastimes/cart.php'); exit;
}

$userID = $_SESSION['userID'];
$total  = array_sum(array_column($cart, 'price'));
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $street   = trim($_POST['street']   ?? '');
    $suburb   = trim($_POST['suburb']   ?? '');
    $city     = trim($_POST['city']     ?? '');
    $province = trim($_POST['province'] ?? '');
    $postal   = trim($_POST['postal']   ?? '');

    if (!$street || !$city) {
        $error = 'Street address and city are required.';
    } else {
        try {
            $conn = getConnection();

            // Save address
            $as = $conn->prepare("INSERT INTO tblDeliveryAddress (userID,streetAddress,suburb,city,province,postalCode,isDefault) VALUES (?,?,?,?,?,?,0)");
            $as->bind_param('isssss', $userID, $street, $suburb, $city, $province, $postal);
            $as->execute();
            $addressID = $conn->insert_id;

            // Create order
            $os = $conn->prepare("INSERT INTO tblOrder (buyerID,addressID,totalAmount,orderStatus) VALUES (?,?,?,'placed')");
            $os->bind_param('iid', $userID, $addressID, $total);
            $os->execute();
            $orderID = $conn->insert_id;

            // Order items + mark as sold
            foreach ($cart as $ci) {
                $oi = $conn->prepare("INSERT INTO tblOrderItem (orderID,clothingID,priceAtPurchase) VALUES (?,?,?)");
                $oi->bind_param('iid', $orderID, $ci['clothingID'], $ci['price']);
                $oi->execute();
                $conn->query("UPDATE tblClothing SET status='sold' WHERE clothingID={$ci['clothingID']}");
            }

            // Clear cart
            $_SESSION['cart'] = [];
            $conn->close();

            $success = "Order #$orderID placed successfully! Thank you for shopping with Pastimes.";
        } catch (Exception $e) {
            $error = 'Checkout failed. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Check<span style="color:var(--gold);">out</span></h1>
    <p>Complete your order</p>
</div>

<div class="container section">

    <?php if ($success): ?>
    <div style="text-align:center; padding:var(--space-3xl);">
        <div style="font-size:4rem; color:var(--gold); margin-bottom:1rem;">✅</div>
        <h2 style="font-family:var(--font-display); color:var(--gold); font-size:2rem; margin-bottom:var(--space-md);">Order Placed!</h2>
        <p style="color:var(--text-secondary); font-size:1rem; margin-bottom:var(--space-xl);"><?php echo htmlspecialchars($success); ?></p>
        <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
            <a href="/Pastimes/dashboards/buyer.php?page=orders" class="btn btn-primary btn-lg">View My Orders</a>
            <a href="/Pastimes/browse.php" class="btn btn-outline btn-lg">Continue Shopping</a>
        </div>
    </div>

    <?php else: ?>

    <?php if ($error): ?>
    <div class="alert alert-danger mb-lg"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="cart-layout">

        <!-- Delivery Form -->
        <div>
            <h2 style="color:var(--text-primary); font-size:1.2rem; margin-bottom:var(--space-lg);">Delivery Address</h2>
            <div class="form-card">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Street Address <span class="required">*</span></label>
                        <input type="text" name="street" class="form-control" placeholder="e.g. 123 Main Street" required>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Suburb</label>
                            <input type="text" name="suburb" class="form-control" placeholder="e.g. Sandton">
                        </div>
                        <div class="form-group">
                            <label class="form-label">City <span class="required">*</span></label>
                            <input type="text" name="city" class="form-control" placeholder="e.g. Johannesburg" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Province</label>
                            <select name="province" class="form-control">
                                <option value="">Select province</option>
                                <?php foreach (['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape','Limpopo','Mpumalanga','North West','Free State','Northern Cape'] as $p): ?>
                                <option><?php echo $p; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="postal" class="form-control" placeholder="e.g. 2196">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full btn-lg">
                        <i class="fas fa-check"></i> Place Order
                    </button>
                </form>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="cart-summary">
            <h3>Order Summary</h3>
            <?php foreach ($cart as $ci): ?>
            <div class="summary-row">
                <span style="font-size:0.85rem;"><?php echo htmlspecialchars($ci['brand']); ?></span>
                <span>R <?php echo number_format($ci['price'],2); ?></span>
            </div>
            <?php endforeach; ?>
            <div class="summary-row">
                <span>Delivery</span>
                <span style="color:#4ade80;">Free</span>
            </div>
            <div class="summary-row" style="border-top:1px solid var(--border-gold); padding-top:var(--space-sm); margin-top:var(--space-sm);">
                <span class="summary-total">Total</span>
                <span class="summary-total">R <?php echo number_format($total,2); ?></span>
            </div>
            <a href="/Pastimes/cart.php" class="btn btn-ghost btn-full" style="margin-top:var(--space-md);">
                <i class="fas fa-arrow-left"></i> Back to Cart
            </a>
        </div>

    </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>