<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: admin/orders.php
 * Description: Admin orders management page
 * ============================================================
 */

session_start();
require_once '../includes/DBConn.php';

// Check admin access
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';

// Handle status update
if (isset($_GET['status']) && isset($_GET['id'])) {
    try {
        $conn = getConnection();
        $status = $_GET['status'];
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("UPDATE tblOrder SET orderStatus = ? WHERE orderID = ?");
        $stmt->bind_param('ss', $status, $id);
        $stmt->execute();
        $success = 'Order status updated to ' . ucfirst($status) . '.';
        $conn->close();
    } catch (Exception $e) {
        $error = 'Could not update order status.';
    }
}

// Fetch orders
try {
    $conn = getConnection();
    $orders = $conn->query("SELECT o.*, u.username, u.fullName, u.email,
                            a.streetAddress, a.suburb, a.city, a.postalCode,
                            (SELECT COUNT(*) FROM tblOrderItem WHERE orderID = o.orderID) as itemCount
                            FROM tblOrder o
                            JOIN tblUser u ON o.buyerID = u.userID
                            JOIN tblDeliveryAddress a ON o.addressID = a.addressID
                            ORDER BY o.orderDate DESC");
} catch (Exception $e) {
    $orders = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders | Pastimes Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
        
        <div class="topbar">
            <div class="topbar-left">PASTIMES ADMIN PANEL</div>
            <div class="topbar-right">
                <span>Logged in as: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../logout.php">Logout</a>
            </div>
        </div>

        <div class="admin-layout">
            <aside class="admin-sidebar">
                <div style="padding: var(--space-lg);">
                    <a href="../index.php" class="navbar-brand">
                        <div class="brand-logo">P</div>
                        <div class="brand-text">
                            <span class="brand-name">Pastimes</span>
                            <span class="brand-tagline">Admin Panel</span>
                        </div>
                    </a>
                </div>

                <div class="admin-sidebar-title">Main</div>
                <a href="index.php" class="admin-nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                
                <div class="admin-sidebar-title">Management</div>
                <a href="users.php" class="admin-nav-link"><i class="fas fa-users"></i> Users</a>
                <a href="listings.php" class="admin-nav-link"><i class="fas fa-tshirt"></i> Listings</a>
                <a href="orders.php" class="admin-nav-link active"><i class="fas fa-shopping-cart"></i> Orders</a>
                
                <div class="admin-sidebar-title">Communication</div>
                <a href="broadcast.php" class="admin-nav-link"><i class="fas fa-bullhorn"></i> Broadcast</a>
                
                <div class="admin-sidebar-title">System</div>
                <a href="../includes/createTable.php" class="admin-nav-link" target="_blank"><i class="fas fa-database"></i> Reset Database</a>
                <a href="../index.php" class="admin-nav-link"><i class="fas fa-home"></i> View Site</a>
            </aside>

            <main class="admin-main">
                <h1 style="font-family: var(--font-display); font-size: 2rem; color: var(--text-primary); margin-bottom: var(--space-xl);">
                    Manage Orders
                </h1>

                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="settings-section">
                    <h2>All Orders</h2>
                    <p>View and manage customer orders</p>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Delivery Address</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($orders && $orders->num_rows > 0): ?>
                                    <?php while ($order = $orders->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $order['orderID']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($order['username']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                            </td>
                                            <td><?php echo $order['itemCount']; ?> item(s)</td>
                                            <td><strong class="text-gold">R<?php echo number_format($order['totalAmount'], 2); ?></strong></td>
                                            <td>
                                                <?php echo htmlspecialchars($order['streetAddress']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($order['suburb'] . ', ' . $order['city'] . ' ' . $order['postalCode']); ?></small>
                                            </td>
                                            <td><?php echo date('M j, Y H:i', strtotime($order['orderDate'])); ?></td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo $order['orderStatus'] === 'delivered' ? 'badge-success' : 
                                                        ($order['orderStatus'] === 'processing' ? 'badge-warning' : 
                                                        ($order['orderStatus'] === 'shipped' ? 'badge-gold' : 
                                                        ($order['orderStatus'] === 'cancelled' ? 'badge-danger' : 'badge-muted'))); 
                                                ?>">
                                                    <?php echo ucfirst($order['orderStatus']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <select onchange="updateOrderStatus(<?php echo $order['orderID']; ?>, this.value)" class="form-control" style="padding: 0.3rem 0.5rem; font-size: 0.75rem;">
                                                    <option value="placed" <?php echo $order['orderStatus'] === 'placed' ? 'selected' : ''; ?>>Placed</option>
                                                    <option value="processing" <?php echo $order['orderStatus'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                    <option value="shipped" <?php echo $order['orderStatus'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                    <option value="delivered" <?php echo $order['orderStatus'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?php echo $order['orderStatus'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center text-muted">No orders found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function updateOrderStatus(orderID, status) {
            if (confirm('Update order status to ' + status + '?')) {
                window.location.href = '?status=' + status + '&id=' + orderID;
            }
        }
    </script>
</body>
</html>
<?php if (isset($conn)) $conn->close(); ?>