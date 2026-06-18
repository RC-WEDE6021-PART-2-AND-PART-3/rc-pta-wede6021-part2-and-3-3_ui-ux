<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header('Location: /Pastimes/login.php'); exit;
}

$fullName = $_SESSION['fullName'];
$userID   = $_SESSION['userID'];

$conn = getConnection();

// Handle actions
$success = ''; $error = '';

// Verify seller
if (isset($_GET['verify'])) {
    $vid = intval($_GET['verify']);
    $conn->query("UPDATE tblUser SET seller_status='verified' WHERE userID=$vid");
    $success = 'Seller verified successfully.';
}
// Reject seller
if (isset($_GET['reject'])) {
    $rid = intval($_GET['reject']);
    $conn->query("UPDATE tblUser SET seller_status='rejected' WHERE userID=$rid");
    $success = 'Seller rejected.';
}
// Delete user
if (isset($_GET['delete_user'])) {
    $did = intval($_GET['delete_user']);
    if ($did !== $userID) {
        $conn->query("DELETE FROM tblUser WHERE userID=$did");
        $success = 'User deleted.';
    }
}
// Delete listing
if (isset($_GET['delete_listing'])) {
    $dlid = intval($_GET['delete_listing']);
    $conn->query("DELETE FROM tblClothing WHERE clothingID=$dlid");
    $success = 'Listing deleted.';
}
// Update order status
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_order'])) {
    $oid  = intval($_POST['orderID']);
    $ost  = $conn->real_escape_string($_POST['orderStatus']);
    $conn->query("UPDATE tblOrder SET orderStatus='$ost' WHERE orderID=$oid");
    $success = 'Order status updated.';
}
// Broadcast message
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['broadcast'])) {
    $msg = $conn->real_escape_string(trim($_POST['broadcastMsg']));
    if ($msg) {
        $conn->query("INSERT INTO tblMessage (senderID, messageText, isBroadcast) VALUES ($userID, '$msg', 1)");
        $success = 'Broadcast sent to all users.';
    }
}

// Stats
$totalUsers    = $conn->query("SELECT COUNT(*) AS c FROM tblUser")->fetch_assoc()['c'];
$totalBuyers   = $conn->query("SELECT COUNT(*) AS c FROM tblUser WHERE role='buyer'")->fetch_assoc()['c'];
$totalSellers  = $conn->query("SELECT COUNT(*) AS c FROM tblUser WHERE role='seller'")->fetch_assoc()['c'];
$totalListings = $conn->query("SELECT COUNT(*) AS c FROM tblClothing")->fetch_assoc()['c'];
$totalOrders   = $conn->query("SELECT COUNT(*) AS c FROM tblOrder")->fetch_assoc()['c'];
$pendingSellers= $conn->query("SELECT COUNT(*) AS c FROM tblUser WHERE seller_status='pending'")->fetch_assoc()['c'];

// Users
$users = [];
$ur = $conn->query("SELECT * FROM tblUser ORDER BY created_at DESC");
while ($r = $ur->fetch_assoc()) $users[] = $r;

// Listings
$listings = [];
$lr = $conn->query("SELECT c.*, u.fullName AS sellerName FROM tblClothing c JOIN tblUser u ON c.sellerID=u.userID ORDER BY c.dateAdded DESC");
while ($r = $lr->fetch_assoc()) $listings[] = $r;

// Orders
$orders = [];
$or2 = $conn->query("SELECT o.*, u.fullName AS buyerName FROM tblOrder o JOIN tblUser u ON o.buyerID=u.userID ORDER BY o.orderDate DESC");
while ($r = $or2->fetch_assoc()) $orders[] = $r;

$conn->close();

$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | Pastimes</title>
<link rel="stylesheet" href="/Pastimes/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="page-wrapper">

<!-- Admin Topbar -->
<div class="topbar">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="/Pastimes/index.php" style="color:var(--gold); font-size:0.85rem;"><i class="fas fa-arrow-left"></i> Back to Site</a>
        <span style="color:var(--border);">|</span>
        <span>Admin Panel</span>
    </div>
    <div class="topbar-right">
        <span><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($fullName); ?></span>
        <a href="/Pastimes/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div style="padding:1.5rem 1rem; border-bottom:1px solid var(--border);">
            <a href="/Pastimes/index.php" class="navbar-brand">
                <div class="brand-logo">P</div>
                <div class="brand-text">
                    <span class="brand-name">Pastimes</span>
                    <span class="brand-tagline">Admin Panel</span>
                </div>
            </a>
        </div>
        <p class="admin-sidebar-title" style="margin-top:1rem;">Main</p>
        <a href="?page=dashboard" class="admin-nav-link <?php echo $page==='dashboard'?'active':'';?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="?page=users"     class="admin-nav-link <?php echo $page==='users'    ?'active':'';?>"><i class="fas fa-users"></i> Users <?php if($pendingSellers>0):?><span class="badge badge-warning" style="margin-left:auto;"><?php echo $pendingSellers;?></span><?php endif;?></a>
        <a href="?page=listings"  class="admin-nav-link <?php echo $page==='listings' ?'active':'';?>"><i class="fas fa-tshirt"></i> Listings</a>
        <a href="?page=orders"    class="admin-nav-link <?php echo $page==='orders'   ?'active':'';?>"><i class="fas fa-box"></i> Orders</a>
        <a href="?page=broadcast" class="admin-nav-link <?php echo $page==='broadcast'?'active':'';?>"><i class="fas fa-bullhorn"></i> Broadcast</a>
        <p class="admin-sidebar-title" style="margin-top:1rem;">Account</p>
        <a href="/Pastimes/logout.php" class="admin-nav-link" style="color:#f87171;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </aside>

    <!-- Main -->
    <div class="admin-main">

        <?php if ($success): ?><div class="alert alert-success mb-lg"><i class="fas fa-check"></i> <?php echo $success; ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-danger  mb-lg"><i class="fas fa-times"></i> <?php echo $error;   ?></div><?php endif; ?>

        <?php if ($page === 'dashboard'): ?>
        <h1 style="font-family:var(--font-display); color:var(--gold); margin-bottom:var(--space-xl);">Admin Dashboard</h1>
        <div class="admin-stat-grid">
            <div class="admin-stat-card"><div class="admin-stat-number"><?php echo $totalUsers;?></div><div class="admin-stat-label">Total Users</div></div>
            <div class="admin-stat-card"><div class="admin-stat-number"><?php echo $totalBuyers;?></div><div class="admin-stat-label">Buyers</div></div>
            <div class="admin-stat-card"><div class="admin-stat-number"><?php echo $totalSellers;?></div><div class="admin-stat-label">Sellers</div></div>
            <div class="admin-stat-card"><div class="admin-stat-number"><?php echo $totalListings;?></div><div class="admin-stat-label">Listings</div></div>
            <div class="admin-stat-card"><div class="admin-stat-number"><?php echo $totalOrders;?></div><div class="admin-stat-label">Orders</div></div>
            <div class="admin-stat-card"><div class="admin-stat-number" style="color:#fbbf24;"><?php echo $pendingSellers;?></div><div class="admin-stat-label">Pending Sellers</div></div>
        </div>

        <?php if ($pendingSellers > 0): ?>
        <h2 style="color:var(--text-primary); font-size:1.1rem; margin-bottom:var(--space-md);">⚠️ Pending Seller Verifications</h2>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): if ($u['seller_status'] !== 'pending') continue; ?>
                <tr>
                    <td style="color:var(--text-primary);"><?php echo htmlspecialchars($u['fullName']); ?></td>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                        <a href="?verify=<?php echo $u['userID']; ?>" class="btn btn-success btn-sm">✓ Verify</a>
                        <a href="?reject=<?php echo $u['userID']; ?>" class="btn btn-danger btn-sm">✗ Reject</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php elseif ($page === 'users'): ?>
        <h1 style="font-family:var(--font-display); color:var(--gold); margin-bottom:var(--space-xl);">Manage Users</h1>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td style="color:var(--text-primary); font-weight:600;"><?php echo htmlspecialchars($u['fullName']); ?></td>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                    <td>
                        <?php if ($u['role']==='seller'): ?>
                        <span class="badge badge-<?php echo $u['seller_status']==='verified'?'success':($u['seller_status']==='pending'?'warning':'danger'); ?>">
                            <?php echo ucfirst($u['seller_status']); ?>
                        </span>
                        <?php else: ?><span class="badge badge-muted">N/A</span><?php endif; ?>
                    </td>
                    <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                    <td style="display:flex; gap:4px; flex-wrap:wrap;">
                        <?php if ($u['role']==='seller' && $u['seller_status']==='pending'): ?>
                        <a href="?page=users&verify=<?php echo $u['userID']; ?>" class="btn btn-success btn-sm">Verify</a>
                        <a href="?page=users&reject=<?php echo $u['userID']; ?>" class="btn btn-danger btn-sm">Reject</a>
                        <?php endif; ?>
                        <?php if ($u['userID'] !== $userID): ?>
                        <a href="?page=users&delete_user=<?php echo $u['userID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($page === 'listings'): ?>
        <h1 style="font-family:var(--font-display); color:var(--gold); margin-bottom:var(--space-xl);">Manage Listings</h1>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Image</th><th>Brand</th><th>Seller</th><th>Category</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($listings as $l): ?>
                <tr>
                    <td><img src="/Pastimes/<?php echo htmlspecialchars($l['imagePath']); ?>" style="width:50px;height:50px;object-fit:cover;border-radius:4px;" onerror="this.src='/Pastimes/images/placeholder.png'"></td>
                    <td style="color:var(--text-primary); font-weight:600;"><?php echo htmlspecialchars($l['brand']); ?></td>
                    <td><?php echo htmlspecialchars($l['sellerName']); ?></td>
                    <td><?php echo htmlspecialchars($l['category']); ?></td>
                    <td style="color:var(--gold);">R <?php echo number_format($l['price'],2); ?></td>
                    <td><span class="badge badge-<?php echo $l['status']==='available'?'success':($l['status']==='sold'?'muted':'warning'); ?>"><?php echo ucfirst($l['status']); ?></span></td>
                    <td>
                        <a href="/Pastimes/product.php?id=<?php echo $l['clothingID']; ?>" class="btn btn-outline btn-sm">View</a>
                        <a href="?page=listings&delete_listing=<?php echo $l['clothingID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete listing?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($page === 'orders'): ?>
        <h1 style="font-family:var(--font-display); color:var(--gold); margin-bottom:var(--space-xl);">Manage Orders</h1>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Order #</th><th>Buyer</th><th>Total</th><th>Status</th><th>Date</th><th>Update</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?php echo $o['orderID']; ?></td>
                    <td style="color:var(--text-primary);"><?php echo htmlspecialchars($o['buyerName']); ?></td>
                    <td style="color:var(--gold);">R <?php echo number_format($o['totalAmount'],2); ?></td>
                    <td><span class="badge badge-<?php echo $o['orderStatus']==='delivered'?'success':($o['orderStatus']==='cancelled'?'danger':'warning'); ?>"><?php echo ucfirst($o['orderStatus']); ?></span></td>
                    <td><?php echo date('d M Y', strtotime($o['orderDate'])); ?></td>
                    <td>
                        <form method="POST" style="display:flex; gap:4px;">
                            <input type="hidden" name="update_order" value="1">
                            <input type="hidden" name="orderID" value="<?php echo $o['orderID']; ?>">
                            <select name="orderStatus" class="form-control" style="padding:.3rem .5rem; font-size:0.8rem; width:130px;">
                                <?php foreach (['placed','processing','shipped','delivered','cancelled'] as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $o['orderStatus']===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($page === 'broadcast'): ?>
        <h1 style="font-family:var(--font-display); color:var(--gold); margin-bottom:var(--space-xl);">Broadcast Message</h1>
        <div class="form-card" style="max-width:600px;">
            <h2 style="color:var(--text-primary); margin-bottom:var(--space-md); font-size:1.1rem;">Send a message to all users</h2>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea name="broadcastMsg" class="form-control" rows="5" placeholder="Type your broadcast message..." required></textarea>
                </div>
                <button type="submit" name="broadcast" class="btn btn-primary btn-full">
                    <i class="fas fa-bullhorn"></i> Send Broadcast
                </button>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>
</div>
<script src="/Pastimes/js/main.js"></script>
</body>
</html>