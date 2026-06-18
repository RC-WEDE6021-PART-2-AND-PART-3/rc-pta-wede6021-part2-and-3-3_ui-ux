<?php
/**
 * Order History Page - PASTIMES
 * Displays user's past orders
 */

session_start();
require_once 'includes/DBConn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=orders");
    exit();
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Get user's orders
$stmt = $conn->prepare("
    SELECT o.*, da.streetAddress, da.suburb, da.city, da.province, da.postalCode
    FROM tblOrder o
    LEFT JOIN tblDeliveryAddress da ON o.deliveryAddressID = da.addressID
    WHERE o.userID = ?
    ORDER BY o.orderDate DESC
");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get order items for each order
foreach ($orders as &$order) {
    $stmt = $conn->prepare("
        SELECT oi.*, c.brand, c.name, c.imagePath, c.size
        FROM tblOrderItem oi
        JOIN tblClothing c ON oi.clothingID = c.clothingID
        WHERE oi.orderID = ?
    ");
    $stmt->bind_param("s", $order['orderID']);
    $stmt->execute();
    $order['items'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - PASTIMES</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="orders-page">
        <div class="page-header">
            <h1>Order History</h1>
            <p>View your past purchases</p>
        </div>
        
        <div class="container">
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <h2>No orders yet</h2>
                    <p>When you make a purchase, your orders will appear here</p>
                    <a href="browse.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <span class="order-number">Order #<?php echo htmlspecialchars($order['orderID']); ?></span>
                                    <span class="order-date"><?php echo date('F j, Y', strtotime($order['orderDate'])); ?></span>
                                </div>
                                <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </div>
                            </div>
                            
                            <div class="order-items">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="order-item">
                                        <div class="item-image">
                                            <?php if ($item['imagePath'] && file_exists($item['imagePath'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['imagePath']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            <?php else: ?>
                                                <div class="placeholder-image">
                                                    <span><?php echo strtoupper(substr($item['brand'], 0, 1)); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="item-details">
                                            <span class="brand"><?php echo htmlspecialchars($item['brand']); ?></span>
                                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                            <span class="size">Size: <?php echo htmlspecialchars($item['size']); ?></span>
                                        </div>
                                        <div class="item-price">
                                            R<?php echo number_format($item['priceAtPurchase'], 2); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="order-footer">
                                <div class="delivery-address">
                                    <strong>Delivery Address:</strong>
                                    <?php if ($order['streetAddress']): ?>
                                        <?php echo htmlspecialchars($order['streetAddress'] . ', ' . $order['suburb'] . ', ' . $order['city'] . ', ' . $order['province'] . ' ' . $order['postalCode']); ?>
                                    <?php else: ?>
                                        <span>Address not available</span>
                                    <?php endif; ?>
                                </div>
                                <div class="order-total">
                                    <span>Total:</span>
                                    <strong>R<?php echo number_format($order['totalAmount'], 2); ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>