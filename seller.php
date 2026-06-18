<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'seller') {
    header('Location: /Pastimes/login.php'); exit;
}

$userID   = $_SESSION['userID'];
$fullName = $_SESSION['fullName'];
$username = $_SESSION['username'];
$status   = $_SESSION['seller_status'];

$conn = getConnection();

// Handle new listing
$success = ''; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_listing') {
    $brand      = trim($_POST['brand'] ?? '');
    $category   = trim($_POST['category'] ?? '');
    $size       = trim($_POST['size'] ?? '');
    $condition  = trim($_POST['condition'] ?? '');
    $desc       = trim($_POST['description'] ?? '');
    $price      = floatval($_POST['price'] ?? 0);
    $imagePath  = 'images/placeholder.png';

    if ($brand && $category && $size && $condition && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO tblClothing (sellerID,brand,category,size,clothingCondition,description,price,imagePath) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssssssds', $userID, $brand, $category, $size, $condition, $desc, $price, $imagePath);
        $stmt->execute();
        $success = 'Listing added successfully!';
    } else {
        $error = 'Please fill in all required fields.';
    }
}

// Handle delete listing
if (isset($_GET['delete'])) {
    $delID = intval($_GET['delete']);
    $conn->query("DELETE FROM tblClothing WHERE clothingID=$delID AND sellerID=$userID");
    header('Location: /Pastimes/dashboards/seller.php?page=listings'); exit;
}

// Stats
$totalListings  = $conn->query("SELECT COUNT(*) AS c FROM tblClothing WHERE sellerID=$userID")->fetch_assoc()['c'];
$activeListings = $conn->query("SELECT COUNT(*) AS c FROM tblClothing WHERE sellerID=$userID AND status='available'")->fetch_assoc()['c'];
$soldListings   = $conn->query("SELECT COUNT(*) AS c FROM tblClothing WHERE sellerID=$userID AND status='sold'")->fetch_assoc()['c'];
$messages       = $conn->query("SELECT COUNT(*) AS c FROM tblMessage WHERE receiverID=$userID AND isRead=0")->fetch_assoc()['c'];

// My listings
$listings = [];
$lr = $conn->query("SELECT * FROM tblClothing WHERE sellerID=$userID ORDER BY dateAdded DESC");
while ($r = $lr->fetch_assoc()) $listings[] = $r;

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
            <div class="sidebar-role">
                <span class="badge badge-seller">Seller</span>
                <?php if ($status === 'pending'): ?>
                <span class="badge badge-warning" style="margin-left:4px;">Pending</span>
                <?php elseif ($status === 'verified'): ?>
                <span class="badge badge-success" style="margin-left:4px;">Verified</span>
                <?php endif; ?>
            </div>
        </div>
        <p class="sidebar-title">Menu</p>
        <a href="?page=dashboard" class="sidebar-nav-link <?php echo $page==='dashboard'?'active':'';?>"><i class="fas fa-home"></i> Dashboard</a>
        <a href="?page=listings"  class="sidebar-nav-link <?php echo $page==='listings' ?'active':'';?>"><i class="fas fa-tshirt"></i> My Listings</a>
        <a href="?page=add"       class="sidebar-nav-link <?php echo $page==='add'      ?'active':'';?>"><i class="fas fa-plus-circle"></i> Add Listing</a>
        <a href="/Pastimes/messages.php" class="sidebar-nav-link"><i class="fas fa-envelope"></i> Messages <?php if($messages>0):?><span class="badge badge-danger" style="margin-left:auto;"><?php echo $messages;?></span><?php endif;?></a>
        <a href="?page=settings"  class="sidebar-nav-link <?php echo $page==='settings' ?'active':'';?>"><i class="fas fa-cog"></i> Settings</a>
        <p class="sidebar-title" style="margin-top:1rem;">Account</p>
        <a href="/Pastimes/logout.php" class="sidebar-nav-link" style="color:#f87171;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </aside>

    <!-- Main -->
    <div class="dashboard-main">

        <?php if ($status === 'pending'): ?>
        <div class="alert alert-warning mb-lg">
            <i class="fas fa-clock"></i> Your seller account is <strong>pending verification</strong> by an admin. You can add listings but they will be visible once verified.
        </div>
        <?php endif; ?>

        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check"></i> <?php echo $success; ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-danger"><i class="fas fa-times"></i> <?php echo $error; ?></div><?php endif; ?>

        <?php if ($page === 'dashboard'): ?>
        <h1 style="font-family:var(--font-display); color:var(--gold); margin-bottom:var(--space-sm);">Seller Hub</h1>
        <p style="color:var(--text-muted); margin-bottom:var(--space-xl);">Manage your listings and track your sales.</p>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tshirt"></i></div>
                <div class="stat-number"><?php echo $totalListings; ?></div>
                <div class="stat-label">Total Listings</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-number"><?php echo $activeListings; ?></div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-number"><?php echo $soldListings; ?></div>
                <div class="stat-label">Sold</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                <div class="stat-number"><?php echo $messages; ?></div>
                <div class="stat-label">Messages</div>
            </div>
        </div>

        <div style="display:flex; gap:var(--space-md); margin-top:var(--space-lg);">
            <a href="?page=add"      class="btn btn-primary"><i class="fas fa-plus"></i> Add New Listing</a>
            <a href="?page=listings" class="btn btn-outline"><i class="fas fa-list"></i> View All Listings</a>
        </div>

        <?php elseif ($page === 'listings'): ?>
        <div class="flex-between mb-lg">
            <h1 style="font-family:var(--font-display); color:var(--gold);">My Listings</h1>
            <a href="?page=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add New</a>
        </div>
        <?php if ($listings): ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Image</th><th>Brand</th><th>Category</th><th>Size</th><th>Condition</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($listings as $l): ?>
                <tr>
                    <td><img src="/Pastimes/<?php echo htmlspecialchars($l['imagePath']); ?>" style="width:50px;height:50px;object-fit:cover;border-radius:4px;" onerror="this.src='/Pastimes/images/placeholder.png'"></td>
                    <td style="color:var(--text-primary); font-weight:600;"><?php echo htmlspecialchars($l['brand']); ?></td>
                    <td><?php echo htmlspecialchars($l['category']); ?></td>
                    <td><?php echo htmlspecialchars($l['size']); ?></td>
                    <td><?php echo htmlspecialchars($l['clothingCondition']); ?></td>
                    <td style="color:var(--gold);">R <?php echo number_format($l['price'],2); ?></td>
                    <td><span class="badge badge-<?php echo $l['status']==='available'?'success':($l['status']==='sold'?'muted':'warning'); ?>"><?php echo ucfirst($l['status']); ?></span></td>
                    <td>
                        <a href="/Pastimes/product.php?id=<?php echo $l['clothingID']; ?>" class="btn btn-outline btn-sm">View</a>
                        <a href="?page=listings&delete=<?php echo $l['clothingID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this listing?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div style="font-size:2.5rem;">👗</div>
            <h2>No listings yet</h2>
            <a href="?page=add" class="btn btn-primary">Add Your First Listing</a>
        </div>
        <?php endif; ?>

        <?php elseif ($page === 'add'): ?>
        <h1 style="font-family:var(--font-display); color:var(--gold); margin-bottom:var(--space-xl);">Add New Listing</h1>
        <div class="form-card" style="max-width:640px;">
            <form method="POST">
                <input type="hidden" name="action" value="add_listing">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Brand <span class="required">*</span></label>
                        <input type="text" name="brand" class="form-control" placeholder="e.g. Nike, Gucci" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select name="category" class="form-control" required>
                            <option value="">Select category</option>
                            <option>Men</option>
                            <option>Women</option>
                            <option>Footwear</option>
                            <option>Accessories</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Size <span class="required">*</span></label>
                        <input type="text" name="size" class="form-control" placeholder="e.g. M, L, 42" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Condition <span class="required">*</span></label>
                        <select name="condition" class="form-control" required>
                            <option value="">Select condition</option>
                            <option>Like New</option>
                            <option>Excellent</option>
                            <option>Good</option>
                            <option>Fair</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price (R) <span class="required">*</span></label>
                        <input type="number" name="price" class="form-control" placeholder="e.g. 350.00" step="0.01" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Image Filename</label>
                        <input type="text" name="image" class="form-control" placeholder="e.g. images/nike_shirt.jpg">
                        <div class="form-hint">Place image in the images/ folder first.</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" placeholder="Describe the item..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-full"><i class="fas fa-plus"></i> Add Listing</button>
            </form>
        </div>

        <?php elseif ($page === 'settings'): ?>
        <h1 style="font-family:var(--font-display); color:var(--gold); margin-bottom:var(--space-xl);">Settings</h1>
        <div class="settings-section">
            <h2>Profile Information</h2>
            <p>Your seller account details</p>
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
        </div>
        <?php endif; ?>

    </div>
</div>

<?php include '../includes/footer.php'; ?>