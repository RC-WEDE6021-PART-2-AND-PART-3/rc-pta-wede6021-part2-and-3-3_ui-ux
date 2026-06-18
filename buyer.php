<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'buyer') {
    header('Location: /Pastimes/login.php'); exit;
}

$userID   = $_SESSION['userID'];
$fullName = $_SESSION['fullName'];
$username = $_SESSION['username'];

$conn = getConnection();

// Stats
$orders    = $conn->query("SELECT COUNT(*) AS c FROM tblOrder WHERE buyerID=$userID")->fetch_assoc()['c'];
$wishlist  = $conn->query("SELECT COUNT(*) AS c FROM tblWishlist WHERE userID=$userID")->fetch_assoc()['c'];
$messages  = $conn->query("SELECT COUNT(*) AS c FROM tblMessage WHERE receiverID=$userID AND isRead=0")->fetch_assoc()['c'];

// Recent orders
$recentOrders = [];
$ro = $conn->query("SELECT o.orderID, o.totalAmount, o.orderStatus, o.orderDate,
                    COUNT(oi.orderItemID) AS itemCount
                    FROM tblOrder o
                    LEFT JOIN tblOrderItem oi ON o.orderID = oi.orderID
                    WHERE o.buyerID = $userID
                    GROUP BY o.orderID ORDER BY o.orderDate DESC LIMIT 5");
while ($r = $ro->fetch_assoc()) $recentOrders[] = $r;

// Wishlist items
$wishItems = [];
$wi = $conn->query("SELECT c.clothingID, c.brand, c.description, c.price, c.imagePath, c.status
                    FROM tblWishlist w JOIN tblClothing c ON w.clothingID = c.clothingID
                    WHERE w.userID = $userID LIMIT 4");
while ($r = $wi->fetch_assoc()) $wishItems[] = $r;

$conn->close();

$page = $_GET['page'] ?? 'dashboard';
include '../includes/header.php';
?>

<div class="dashboard-layout">

    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?php echo strtoupper(substr($fullName,0,1)); ?></div>
            <div class="sidebar-username"><?php echo htmlspecialchars($fullName); ?></div>
            <div class="sidebar-role"><span class="badge badge-buyer">Buyer</span></div>
        </div>
        <p class="sidebar-title">Menu</p>
        <a href="?page=dashboard" class="sidebar-nav-link <?php echo $page==='dashboard'?'active':''; ?>"><i class="fas fa-home"></i> Dashboard</a>
        <a href="?page=orders"    class="sidebar-nav-link <?php echo $page==='orders'   ?'active':''; ?>"><i class="fas fa-box"></i> My Orders</a>
        <a href="?page=wishlist"  class="sidebar-nav-link <?php echo $page==='wishlist' ?'active':''; ?>"><i class="fas fa-heart"></i> Wishlist</a>
        <a href="/Pastimes/browse.php"   class="sidebar-nav-link"><i class="fas fa-search"></i> Browse</a>
        <a href="/Pastimes/cart.php"     class="sidebar-nav-link"><i class="fas fa-shopping-bag"></i> Cart</a>
        <a href="/Pastimes/messages.php" class="sidebar-nav-link"><i class="fas fa-envelope"></i> Messages <?php if($messages>0): ?><span class="badge badge-danger" style="margin-left:auto;"><?php echo $messages;?></span><?php endif;?></a>
        <a href="?page=settings"  class="sidebar-nav-link <?php echo $page==='settings' ?'active':''; ?>"><i class="fas fa-cog"></i> Settings</a>
        <p class="sidebar-title" style="margin-top:1rem;">Account</p>
        <a href="/Pastimes/logout.php" class="sidebar-nav-link" style="color:#f87171;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </aside>

    <!-- Main -->
    <div class="dashboard-main">

        <?php if ($page === 'dashboard'): ?>
        <h1 style="font-family:var(--font-display); color:var(--gold); margin-bottom:var(--space-sm);">
            Welcome back, <?php echo htmlspecialchars($fullName); ?>!
        </h1>
        <p style="color:var(--text-muted); margin-bottom:var(--space-xl);">Here's what's happening with your account.</p>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-number"><?php echo $orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-heart"></i></div>
                <div class="stat-number"><?php echo $wishlist; ?></div>
                <div class="stat-label">Wishlist Items</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                <div class="stat-number"><?php echo $messages; ?></div>
                <div class="stat-label">Unread Messages</div>
            </div>
        </div>

        <!-- Recent Orders -->
        <h2 style="color:var(--text-primary); font-size:1.2rem; margin-bottom:var(--space-md);">Recent Orders</h2>
        <?php if ($recentOrders): ?>
        <div class="table-wrapper mb-xl">
            <table>
                <thead><tr><th>Order #</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td>#<?php echo $o['orderID']; ?></td>
                    <td><?php echo $o['itemCount']; ?> item(s)</td>
                    <td>R <?php echo number_format($o['totalAmount'],2); ?></td>
                    <td><span class="badge badge-<?php echo $o['orderStatus']==='delivered'?'success':($o['orderStatus']==='cancelled'?'danger':'warning'); ?>"><?php echo ucfirst($o['orderStatus']); ?></span></td>
                    <td><?php echo date('d M Y', strtotime($o['orderDate'])); ?></td>
                    <td><a href="/Pastimes/browse.php" class="btn btn-outline btn-sm">Browse More</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state mb-xl">
            <div style="font-size:2.5rem;"><i class="fas fa-shopping-bag" style="color:var(--gold);font-size:2.5rem;"></i></div>
            <h2>No orders yet</h2>
            <p>Start shopping to see your orders here.</p>
            <a href="/Pastimes/browse.php" class="btn btn-primary">Browse Now</a>
        </div>
        <?php endif; ?>

        <!-- Wishlist Preview -->
        <?php if ($wishItems): ?>
        <h2 style="color:var(--text-primary); font-size:1.2rem; margin-bottom:var(--space-md);">Wishlist</h2>
        <div class="products-grid">
            <?php foreach ($wishItems as $item): ?>
            <a href="/Pastimes/product.php?id=<?php echo $item['clothingID']; ?>" class="card">
                <div class="card-img-placeholder">
                    <img src="/Pastimes/<?php echo htmlspecialchars($item['imagePath']); ?>" alt="<?php echo htmlspecialchars($item['brand']); ?>" onerror="this.src='/Pastimes/images/placeholder.png'">
                </div>
                <div class="card-body">
                    <div class="card-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                    <div class="card-title"><?php echo htmlspecialchars(mb_strimwidth($item['description'],0,50,'…')); ?></div>
                    <div class="card-price">R <?php echo number_format($item['price'],2); ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php elseif ($page === 'orders'): ?>
        <h1 style="font-family:var(--font-display); color:var(--gold); margin-bottom:var(--space-xl);">My Orders</h1>
        <?php if ($recentOrders): ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Order #</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td>#<?php echo $o['orderID']; ?></td>
                    <td><?php echo $o['itemCount']; ?> item(s)</td>
                    <td>R <?php echo number_format($o['totalAmount'],2); ?></td>
                    <td><span class="badge badge-<?php echo $o['orderStatus']==='delivered'?'success':($o['orderStatus']==='cancelled'?'danger':'warning'); ?>"><?php echo ucfirst($o['orderStatus']); ?></span></td>
                    <td><?php echo date('d M Y', strtotime($o['orderDate'])); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div style="font-size:2.5rem;"><i class="fas fa-box" style="color:var(--gold);font-size:2.5rem;"></i></div>
            <h2>No orders yet</h2>
            <a href="/Pastimes/browse.php" class="btn btn-primary">Start Shopping</a>
        </div>
        <?php endif; ?>

        <?php elseif ($page === 'wishlist'): ?>
        <h1 style="font-family:var(--font-display); color:var(--gold); margin-bottom:var(--space-xl);">My Wishlist</h1>
        <?php if ($wishItems): ?>
        <div class="products-grid">
            <?php foreach ($wishItems as $item): ?>
            <a href="/Pastimes/product.php?id=<?php echo $item['clothingID']; ?>" class="card">
                <div class="card-img-placeholder">
                    <img src="/Pastimes/<?php echo htmlspecialchars($item['imagePath']); ?>" alt="" onerror="this.src='/Pastimes/images/placeholder.png'">
                </div>
                <div class="card-body">
                    <div class="card-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                    <div class="card-price">R <?php echo number_format($item['price'],2); ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div style="font-size:2.5rem;"><i class="fas fa-heart" style="color:var(--gold);font-size:2.5rem;"></i></div>
            <h2>Wishlist is empty</h2>
            <a href="/Pastimes/browse.php" class="btn btn-primary">Browse Items</a>
        </div>
        <?php endif; ?>

        <?php elseif ($page === 'settings'): ?>
        <h1 style="font-family:var(--font-display); color:var(--gold); margin-bottom:var(--space-xl);">Account Settings</h1>
        <div class="settings-section">
            <h2>Profile Information</h2>
            <p>Your account details</p>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($fullName); ?>" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($username); ?>" readonly>
                </div>
            </div>
            <p style="color:var(--text-muted); font-size:0.85rem;">To update your profile details, please contact admin.</p>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php include '../includes/footer.php'; ?>