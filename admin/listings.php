<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: admin/listings.php
 * Description: Admin clothing listings management page
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
$showAddForm = isset($_GET['action']) && $_GET['action'] === 'add';

// Handle add listing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_listing') {
    $sellerID = intval($_POST['sellerID']);
    $brand = trim($_POST['brand']);
    $category = $_POST['category'];
    $size = trim($_POST['size']);
    $condition = $_POST['condition'];
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $imagePath = 'default.jpg';
    
    if (empty($brand) || empty($description) || $price <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $conn = getConnection();
            $stmt = $conn->prepare("INSERT INTO tblClothing (sellerID, brand, category, size, clothingCondition, description, price, imagePath, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available')");
            $stmt->bind_param('ssssssss', $sellerID, $brand, $category, $size, $condition, $description, $price, $imagePath);
            $stmt->execute();
            $success = 'Listing added successfully!';
            $showAddForm = false;
            $conn->close();
        } catch (Exception $e) {
            $error = 'Could not add listing.';
        }
    }
}

// Handle status update
if (isset($_GET['status']) && isset($_GET['id'])) {
    try {
        $conn = getConnection();
        $status = $_GET['status'];
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("UPDATE tblClothing SET status = ? WHERE clothingID = ?");
        $stmt->bind_param('ss', $status, $id);
        $stmt->execute();
        $success = 'Listing status updated.';
        $conn->close();
    } catch (Exception $e) {
        $error = 'Could not update status.';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    try {
        $conn = getConnection();
        $id = intval($_GET['delete']);
        $stmt = $conn->prepare("DELETE FROM tblClothing WHERE clothingID = ?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $success = 'Listing deleted.';
        $conn->close();
    } catch (Exception $e) {
        $error = 'Could not delete listing.';
    }
}

// Fetch listings and verified sellers
try {
    $conn = getConnection();
    $listings = $conn->query("SELECT c.*, u.username as sellerName FROM tblClothing c LEFT JOIN tblUser u ON c.sellerID = u.userID ORDER BY c.dateAdded DESC");
    $sellers = $conn->query("SELECT userID, username, fullName FROM tblUser WHERE seller_status = 'verified' OR role = 'admin'");
} catch (Exception $e) {
    $listings = null;
    $sellers = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Listings | Pastimes Admin</title>
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
                <a href="listings.php" class="admin-nav-link active"><i class="fas fa-tshirt"></i> Listings</a>
                <a href="orders.php" class="admin-nav-link"><i class="fas fa-shopping-cart"></i> Orders</a>
                
                <div class="admin-sidebar-title">Communication</div>
                <a href="broadcast.php" class="admin-nav-link"><i class="fas fa-bullhorn"></i> Broadcast</a>
                
                <div class="admin-sidebar-title">System</div>
                <a href="../includes/createTable.php" class="admin-nav-link" target="_blank"><i class="fas fa-database"></i> Reset Database</a>
                <a href="../index.php" class="admin-nav-link"><i class="fas fa-home"></i> View Site</a>
            </aside>

            <main class="admin-main">
                <div class="flex-between mb-xl">
                    <h1 style="font-family: var(--font-display); font-size: 2rem; color: var(--text-primary);">
                        Manage Listings
                    </h1>
                    <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus"></i> Add Listing</a>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($showAddForm): ?>
                    <!-- Add Listing Form -->
                    <div class="settings-section">
                        <h2>Add New Listing</h2>
                        <p>Enter clothing details for a verified seller</p>

                        <form method="POST">
                            <input type="hidden" name="action" value="add_listing">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Seller <span class="required">*</span></label>
                                    <select name="sellerID" class="form-control" required>
                                        <option value="">Select a verified seller</option>
                                        <?php while ($seller = $sellers->fetch_assoc()): ?>
                                            <option value="<?php echo $seller['userID']; ?>">
                                                <?php echo htmlspecialchars($seller['username'] . ' (' . $seller['fullName'] . ')'); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Brand <span class="required">*</span></label>
                                    <input type="text" name="brand" class="form-control" placeholder="e.g. Gucci, Nike" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Category <span class="required">*</span></label>
                                    <select name="category" class="form-control" required>
                                        <option value="Women">Women</option>
                                        <option value="Men">Men</option>
                                        <option value="Accessories">Accessories</option>
                                        <option value="Footwear">Footwear</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Size <span class="required">*</span></label>
                                    <input type="text" name="size" class="form-control" placeholder="e.g. M, L, XL, 42" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Condition <span class="required">*</span></label>
                                    <select name="condition" class="form-control" required>
                                        <option value="Like New">Like New</option>
                                        <option value="Excellent">Excellent</option>
                                        <option value="Good" selected>Good</option>
                                        <option value="Fair">Fair</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Price (ZAR) <span class="required">*</span></label>
                                    <input type="number" name="price" class="form-control" step="0.01" min="1" placeholder="0.00" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Description <span class="required">*</span></label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Detailed item description..." required minlength="20"></textarea>
                            </div>

                            <div class="flex gap-md">
                                <button type="submit" class="btn btn-primary">Add Listing</button>
                                <a href="listings.php" class="btn btn-ghost">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Listings Table -->
                <div class="settings-section">
                    <h2>All Listings</h2>
                    <p>View and manage clothing listings</p>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Brand</th>
                                    <th>Category</th>
                                    <th>Size</th>
                                    <th>Condition</th>
                                    <th>Price</th>
                                    <th>Seller</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($listings && $listings->num_rows > 0): ?>
                                    <?php while ($item = $listings->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $item['clothingID']; ?></td>
                                            <td><?php echo htmlspecialchars($item['brand']); ?></td>
                                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                                            <td><?php echo htmlspecialchars($item['size']); ?></td>
                                            <td><?php echo htmlspecialchars($item['clothingCondition']); ?></td>
                                            <td>R<?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($item['sellerName']); ?></td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo $item['status'] === 'available' ? 'badge-success' : 
                                                        ($item['status'] === 'sold' ? 'badge-muted' : 'badge-warning'); 
                                                ?>">
                                                    <?php echo ucfirst($item['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($item['status'] === 'available'): ?>
                                                    <a href="?status=sold&id=<?php echo $item['clothingID']; ?>" class="btn btn-sm btn-ghost" onclick="return confirm('Mark as sold?')">Mark Sold</a>
                                                <?php endif; ?>
                                                <a href="?delete=<?php echo $item['clothingID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this listing?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="9" class="text-center text-muted">No listings found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
<?php if (isset($conn)) $conn->close(); ?>