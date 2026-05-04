<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: profile.php
 * Description: User profile/dashboard page
 * ============================================================
 */

session_start();
require_once 'includes/DBConn.php';

// Redirect if not logged in
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$userID = $_SESSION['userID'];
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';
$error = '';
$success = '';

// Check for success message from checkout
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success = 'Order placed successfully! Thank you for your purchase.';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getConnection();
        
        // Add address
        if (isset($_POST['action']) && $_POST['action'] === 'add_address') {
            $addressType = $_POST['addressType'];
            $streetAddress = trim($_POST['streetAddress']);
            $suburb = trim($_POST['suburb']);
            $city = trim($_POST['city']);
            $postalCode = trim($_POST['postalCode']);
            $isDefault = isset($_POST['isDefault']) ? 1 : 0;
            
            // If setting as default, unset other defaults first
            if ($isDefault) {
                $stmt = $conn->prepare("UPDATE tblDeliveryAddress SET isDefault = 0 WHERE userID = ?");
                $stmt->bind_param('s', $userID);
                $stmt->execute();
            }
            
            $stmt = $conn->prepare("INSERT INTO tblDeliveryAddress (userID, addressType, streetAddress, suburb, city, postalCode, isDefault) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssss', $userID, $addressType, $streetAddress, $suburb, $city, $postalCode, $isDefault);
            $stmt->execute();
            
            $success = 'Address added successfully!';
        }
        
        // Delete address
        if (isset($_POST['action']) && $_POST['action'] === 'delete_address') {
            $addressID = intval($_POST['addressID']);
            $stmt = $conn->prepare("DELETE FROM tblDeliveryAddress WHERE addressID = ? AND userID = ?");
            $stmt->bind_param('ss', $addressID, $userID);
            $stmt->execute();
            $success = 'Address deleted.';
        }
        
        // Update profile
        if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
            $fullName = trim($_POST['fullName']);
            $email = trim($_POST['email']);
            
            $stmt = $conn->prepare("UPDATE tblUser SET fullName = ?, email = ? WHERE userID = ?");
            $stmt->bind_param('sss', $fullName, $email, $userID);
            $stmt->execute();
            
            $_SESSION['fullName'] = $fullName;
            $_SESSION['email'] = $email;
            
            $success = 'Profile updated successfully!';
        }
        
        // Request seller status
        if (isset($_POST['action']) && $_POST['action'] === 'request_seller') {
            $stmt = $conn->prepare("UPDATE tblUser SET seller_status = 'pending' WHERE userID = ?");
            $stmt->bind_param('s', $userID);
            $stmt->execute();
            $_SESSION['seller_status'] = 'pending';
            $success = 'Seller status requested! Please wait for admin verification.';
        }
        
        $conn->close();
    } catch (Exception $e) {
        $error = 'An error occurred. Please try again.';
    }
}

// Fetch user data
try {
    $conn = getConnection();
    
    // Get user info
    $stmt = $conn->prepare("SELECT * FROM tblUser WHERE userID = ?");
    $stmt->bind_param('s', $userID);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    // Get addresses
    $stmt = $conn->prepare("SELECT * FROM tblDeliveryAddress WHERE userID = ? ORDER BY isDefault DESC");
    $stmt->bind_param('s', $userID);
    $stmt->execute();
    $addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get orders
    $stmt = $conn->prepare("SELECT o.*, 
                            (SELECT COUNT(*) FROM tblOrderItem WHERE orderID = o.orderID) as itemCount
                            FROM tblOrder o WHERE o.buyerID = ? ORDER BY o.orderDate DESC");
    $stmt->bind_param('s', $userID);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get wishlist
    $stmt = $conn->prepare("SELECT c.*, w.addedAt FROM tblWishlist w 
                            JOIN tblClothing c ON w.clothingID = c.clothingID 
                            WHERE w.userID = ? ORDER BY w.addedAt DESC");
    $stmt->bind_param('s', $userID);
    $stmt->execute();
    $wishlist = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    $user = null;
    $addresses = [];
    $orders = [];
    $wishlist = [];
}
?>
<?php include 'includes/header.php'; ?>

        <!-- Page Header -->
        <div class="page-header">
            <h1>My <span>Account</span></h1>
            <p>Manage your profile, orders, and settings</p>
        </div>

        <!-- Profile Section -->
        <section class="section">
            <div class="container">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Profile Header Card -->
                <div class="profile-header-card">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user['fullName'], 0, 1)); ?>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user['fullName']); ?></h2>
                        <p>@<?php echo htmlspecialchars($user['username']); ?> | <?php echo htmlspecialchars($user['email']); ?></p>
                        <div class="flex gap-sm">
                            <span class="badge badge-gold"><?php echo ucfirst($user['role']); ?></span>
                            <span class="badge <?php echo $user['seller_status'] === 'verified' ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo ucfirst($user['seller_status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="profile-stats">
                        <div class="profile-stat-item">
                            <div class="profile-stat-number"><?php echo count($orders); ?></div>
                            <div class="profile-stat-label">Orders</div>
                        </div>
                        <div class="profile-stat-item">
                            <div class="profile-stat-number"><?php echo count($wishlist); ?></div>
                            <div class="profile-stat-label">Wishlist</div>
                        </div>
                        <div class="profile-stat-item">
                            <div class="profile-stat-number"><?php echo count($addresses); ?></div>
                            <div class="profile-stat-label">Addresses</div>
                        </div>
                    </div>
                </div>

                <div class="settings-layout">
                    <!-- Sidebar Navigation -->
                    <nav class="settings-nav">
                        <a href="?tab=profile" class="settings-nav-item <?php echo $tab === 'profile' ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="?tab=orders" class="settings-nav-item <?php echo $tab === 'orders' ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-bag"></i> Orders
                        </a>
                        <a href="?tab=addresses" class="settings-nav-item <?php echo $tab === 'addresses' ? 'active' : ''; ?>">
                            <i class="fas fa-map-marker-alt"></i> Addresses
                        </a>
                        <a href="?tab=wishlist" class="settings-nav-item <?php echo $tab === 'wishlist' ? 'active' : ''; ?>">
                            <i class="fas fa-heart"></i> Wishlist
                        </a>
                        <a href="?tab=seller" class="settings-nav-item <?php echo $tab === 'seller' ? 'active' : ''; ?>">
                            <i class="fas fa-store"></i> Seller Status
                        </a>
                        <a href="logout.php" class="settings-nav-item">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>

                    <!-- Content Area -->
                    <div>
                        <?php if ($tab === 'profile'): ?>
                            <!-- Profile Tab -->
                            <div class="settings-section">
                                <h2>Edit Profile</h2>
                                <p>Update your personal information</p>

                                <form method="POST">
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" name="fullName" class="form-control" value="<?php echo htmlspecialchars($user['fullName']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                            <span class="form-hint">Username cannot be changed</span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Member Since</label>
                                        <input type="text" class="form-control" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" disabled>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </form>
                            </div>

                        <?php elseif ($tab === 'orders'): ?>
                            <!-- Orders Tab -->
                            <div class="settings-section">
                                <h2>Order History</h2>
                                <p>View your past purchases</p>

                                <?php if (!empty($orders)): ?>
                                    <div class="table-wrapper">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Order #</th>
                                                    <th>Date</th>
                                                    <th>Items</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($orders as $order): ?>
                                                    <tr>
                                                        <td>#<?php echo $order['orderID']; ?></td>
                                                        <td><?php echo date('M j, Y', strtotime($order['orderDate'])); ?></td>
                                                        <td><?php echo $order['itemCount']; ?> item(s)</td>
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
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center" style="padding: var(--space-2xl);">
                                        <div style="font-size: 3rem; color: var(--text-faint); margin-bottom: var(--space-md);">
                                            <i class="fas fa-shopping-bag"></i>
                                        </div>
                                        <p class="text-muted">No orders yet</p>
                                        <a href="browse.php" class="btn btn-primary mt-md">Start Shopping</a>
                                    </div>
                                <?php endif; ?>
                            </div>

                        <?php elseif ($tab === 'addresses'): ?>
                            <!-- Addresses Tab -->
                            <div class="settings-section">
                                <h2>Delivery Addresses</h2>
                                <p>Manage your saved addresses</p>

                                <?php if (!empty($addresses)): ?>
                                    <div class="grid-2" style="gap: var(--space-md); margin-bottom: var(--space-xl);">
                                        <?php foreach ($addresses as $addr): ?>
                                            <div class="card" style="padding: var(--space-lg);">
                                                <div class="flex-between mb-sm">
                                                    <span class="badge badge-gold"><?php echo ucfirst($addr['addressType']); ?></span>
                                                    <?php if ($addr['isDefault']): ?>
                                                        <span class="badge badge-success">Default</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p style="color: var(--text-primary); margin-bottom: var(--space-xs);">
                                                    <?php echo htmlspecialchars($addr['streetAddress']); ?>
                                                </p>
                                                <p class="text-muted" style="font-size: 0.85rem;">
                                                    <?php echo htmlspecialchars($addr['suburb']); ?>, <?php echo htmlspecialchars($addr['city']); ?><br>
                                                    <?php echo htmlspecialchars($addr['postalCode']); ?>
                                                </p>
                                                <form method="POST" style="margin-top: var(--space-md);">
                                                    <input type="hidden" name="action" value="delete_address">
                                                    <input type="hidden" name="addressID" value="<?php echo $addr['addressID']; ?>">
                                                    <button type="submit" class="btn btn-ghost btn-sm">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Add Address Form -->
                                <h3 style="font-family: var(--font-display); color: var(--text-primary); margin-bottom: var(--space-md);">Add New Address</h3>
                                <form method="POST">
                                    <input type="hidden" name="action" value="add_address">
                                    
                                    <div class="form-group">
                                        <label class="form-label">Address Type</label>
                                        <select name="addressType" class="form-control" required>
                                            <option value="residential">Residential</option>
                                            <option value="work">Work</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Street Address</label>
                                        <input type="text" name="streetAddress" class="form-control" placeholder="123 Main Street" required>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Suburb</label>
                                            <input type="text" name="suburb" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">City</label>
                                            <input type="text" name="city" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Postal Code</label>
                                            <input type="text" name="postalCode" class="form-control" pattern="[0-9]{4}" maxlength="4" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">&nbsp;</label>
                                            <div class="form-check" style="padding-top: 0.5rem;">
                                                <input type="checkbox" name="isDefault" id="isDefault">
                                                <label for="isDefault">Set as default address</label>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Add Address</button>
                                </form>
                            </div>

                        <?php elseif ($tab === 'wishlist'): ?>
                            <!-- Wishlist Tab -->
                            <div class="settings-section">
                                <h2>My Wishlist</h2>
                                <p>Items you've saved for later</p>

                                <?php if (!empty($wishlist)): ?>
                                    <div class="grid-3" style="gap: var(--space-md);">
                                        <?php foreach ($wishlist as $item): ?>
                                            <a href="product.php?id=<?php echo $item['clothingID']; ?>" class="card card-position-relative">
                                                <span class="card-badge"><?php echo htmlspecialchars($item['clothingCondition']); ?></span>
                                                <div class="card-img-placeholder">
                                                    <span style="font-size: 2rem; color: var(--gold);"><?php echo strtoupper(substr($item['brand'], 0, 1)); ?></span>
                                                </div>
                                                <div class="card-body">
                                                    <div class="card-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                                                    <h3 class="card-title"><?php echo htmlspecialchars(substr($item['description'], 0, 30)); ?>...</h3>
                                                    <div class="card-price">R<?php echo number_format($item['price'], 2); ?></div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center" style="padding: var(--space-2xl);">
                                        <div style="font-size: 3rem; color: var(--text-faint); margin-bottom: var(--space-md);">
                                            <i class="fas fa-heart"></i>
                                        </div>
                                        <p class="text-muted">Your wishlist is empty</p>
                                        <a href="browse.php" class="btn btn-primary mt-md">Browse Collection</a>
                                    </div>
                                <?php endif; ?>
                            </div>

                        <?php elseif ($tab === 'seller'): ?>
                            <!-- Seller Status Tab -->
                            <div class="settings-section">
                                <h2>Seller Status</h2>
                                <p>Manage your seller account</p>

                                <div class="card" style="padding: var(--space-xl); text-align: center;">
                                    <?php if ($user['seller_status'] === 'verified'): ?>
                                        <div style="font-size: 4rem; color: var(--success); margin-bottom: var(--space-md);">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <h3 style="color: var(--success); margin-bottom: var(--space-sm);">Verified Seller</h3>
                                        <p class="text-muted">You are approved to list items for sale. Contact admin to submit new listings.</p>
                                    <?php elseif ($user['seller_status'] === 'pending'): ?>
                                        <div style="font-size: 4rem; color: var(--warning); margin-bottom: var(--space-md);">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <h3 style="color: var(--warning); margin-bottom: var(--space-sm);">Verification Pending</h3>
                                        <p class="text-muted">Your seller application is being reviewed by our admin team.</p>
                                    <?php else: ?>
                                        <div style="font-size: 4rem; color: var(--gold); margin-bottom: var(--space-md);">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <h3 style="color: var(--text-primary); margin-bottom: var(--space-sm);">Become a Seller</h3>
                                        <p class="text-muted mb-lg">Request seller status to start listing your items on Pastimes.</p>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="request_seller">
                                            <button type="submit" class="btn btn-primary">Request Seller Status</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

<?php 
if (isset($conn)) $conn->close();
include 'includes/footer.php'; 
?>