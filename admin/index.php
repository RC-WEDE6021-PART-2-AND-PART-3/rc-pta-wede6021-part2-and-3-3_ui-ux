<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: admin/index.php
 * Description: Admin dashboard
 * ============================================================
 */

session_start();
require_once '../includes/DBConn.php';

// Check admin access
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch dashboard statistics
try {
    $conn = getConnection();
    
    // Total users
    $totalUsers = $conn->query("SELECT COUNT(*) as count FROM tblUser")->fetch_assoc()['count'];
    
    // Active listings
    $activeListings = $conn->query("SELECT COUNT(*) as count FROM tblClothing WHERE status = 'available'")->fetch_assoc()['count'];
    
    // Total orders
    $totalOrders = $conn->query("SELECT COUNT(*) as count FROM tblOrder")->fetch_assoc()['count'];
    
    // Pending verifications
    $pendingVerifications = $conn->query("SELECT COUNT(*) as count FROM tblUser WHERE seller_status = 'pending' AND role = 'buyer'")->fetch_assoc()['count'];
    
    // Recent orders
    $recentOrders = $conn->query("SELECT o.*, u.username FROM tblOrder o JOIN tblUser u ON o.buyerID = u.userID ORDER BY o.orderDate DESC LIMIT 5");
    
    // Pending seller requests
    $pendingRequests = $conn->query("SELECT * FROM tblUser WHERE seller_status = 'pending' ORDER BY created_at DESC LIMIT 5");
    
    $conn->close();
} catch (Exception $e) {
    $totalUsers = 0;
    $activeListings = 0;
    $totalOrders = 0;
    $pendingVerifications = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Pastimes</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
        
        <!-- Admin Header -->
        <div class="topbar">
            <div class="topbar-left">PASTIMES ADMIN PANEL</div>
            <div class="topbar-right">
                <span>Logged in as: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../logout.php">Logout</a>
            </div>
        </div>

        <div class="admin-layout">
            <!-- Admin Sidebar -->
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
                <a href="index.php" class="admin-nav-link active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                
                <div class="admin-sidebar-title">Management</div>
                <a href="users.php" class="admin-nav-link">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="listings.php" class="admin-nav-link">
                    <i class="fas fa-tshirt"></i> Listings
                </a>
                <a href="orders.php" class="admin-nav-link">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                
                <div class="admin-sidebar-title">Communication</div>
                <a href="broadcast.php" class="admin-nav-link">
                    <i class="fas fa-bullhorn"></i> Broadcast
                </a>
                
                <div class="admin-sidebar-title">System</div>
                <a href="../includes/createTable.php" class="admin-nav-link" target="_blank">
                    <i class="fas fa-database"></i> Reset Database
                </a>
                <a href="../index.php" class="admin-nav-link">
                    <i class="fas fa-home"></i> View Site
                </a>
            </aside>

            <!-- Main Content -->
            <main class="admin-main">
                <h1 style="font-family: var(--font-display); font-size: 2rem; color: var(--text-primary); margin-bottom: var(--space-xl);">
                    Admin Dashboard
                </h1>

                <!-- Stats Grid -->
                <div class="admin-stat-grid">
                    <div class="admin-stat-card">
                        <div class="admin-stat-number"><?php echo $totalUsers; ?></div>
                        <div class="admin-stat-label">Total Users</div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-number"><?php echo $activeListings; ?></div>
                        <div class="admin-stat-label">Active Listings</div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-number"><?php echo $totalOrders; ?></div>
                        <div class="admin-stat-label">Total Orders</div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-number"><?php echo $pendingVerifications; ?></div>
                        <div class="admin-stat-label">Pending Verifications</div>
                    </div>
                </div>

                <div class="grid-2" style="gap: var(--space-xl);">
                    <!-- Recent Orders -->
                    <div class="settings-section">
                        <h2>Recent Orders</h2>
                        <p>Latest purchases on the platform</p>

                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $recentOrders->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $order['orderID']; ?></td>
                                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                                            <td>R<?php echo number_format($order['totalAmount'], 2); ?></td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo $order['orderStatus'] === 'delivered' ? 'badge-success' : 
                                                        ($order['orderStatus'] === 'processing' ? 'badge-warning' : 'badge-gold'); 
                                                ?>">
                                                    <?php echo ucfirst($order['orderStatus']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <a href="orders.php" class="btn btn-outline btn-sm mt-md">View All Orders</a>
                    </div>

                    <!-- Pending Seller Requests -->
                    <div class="settings-section">
                        <h2>Pending Seller Requests</h2>
                        <p>Users waiting for verification</p>

                        <?php if ($pendingRequests->num_rows > 0): ?>
                            <div class="table-wrapper">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Email</th>
                                            <th>Joined</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = $pendingRequests->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo date('M j', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <a href="users.php?verify=<?php echo $user['userID']; ?>" class="btn btn-sm btn-success">
                                                        Verify
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No pending requests</p>
                        <?php endif; ?>

                        <a href="users.php" class="btn btn-outline btn-sm mt-md">Manage Users</a>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="settings-section mt-xl">
                    <h2>Quick Actions</h2>
                    <p>Common administrative tasks</p>

                    <div class="flex gap-md">
                        <a href="listings.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Listing
                        </a>
                        <a href="broadcast.php" class="btn btn-outline">
                            <i class="fas fa-bullhorn"></i> Send Broadcast
                        </a>
                        <a href="users.php" class="btn btn-outline">
                            <i class="fas fa-user-check"></i> Verify Sellers
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../js/main.js"></script>
</body>
</html>