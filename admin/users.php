<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: admin/users.php
 * Description: Admin user management page
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

// Handle actions
if (isset($_GET['verify'])) {
    try {
        $conn = getConnection();
        $userID = intval($_GET['verify']);
        $stmt = $conn->prepare("UPDATE tblUser SET seller_status = 'verified', role = 'seller' WHERE userID = ?");
        $stmt->bind_param('s', $userID);
        $stmt->execute();
        $success = 'User verified as seller successfully!';
        $conn->close();
    } catch (Exception $e) {
        $error = 'Could not verify user.';
    }
}

if (isset($_GET['reject'])) {
    try {
        $conn = getConnection();
        $userID = intval($_GET['reject']);
        $stmt = $conn->prepare("UPDATE tblUser SET seller_status = 'rejected' WHERE userID = ?");
        $stmt->bind_param('s', $userID);
        $stmt->execute();
        $success = 'Seller request rejected.';
        $conn->close();
    } catch (Exception $e) {
        $error = 'Could not reject user.';
    }
}

if (isset($_GET['delete'])) {
    try {
        $conn = getConnection();
        $userID = intval($_GET['delete']);
        $stmt = $conn->prepare("DELETE FROM tblUser WHERE userID = ? AND role != 'admin'");
        $stmt->bind_param('s', $userID);
        $stmt->execute();
        $success = 'User deleted.';
        $conn->close();
    } catch (Exception $e) {
        $error = 'Could not delete user.';
    }
}

// Fetch users
try {
    $conn = getConnection();
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    if (!empty($search)) {
        $stmt = $conn->prepare("SELECT * FROM tblUser WHERE fullName LIKE ? OR email LIKE ? OR username LIKE ? ORDER BY created_at DESC");
        $searchTerm = "%$search%";
        $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $users = $stmt->get_result();
    } else {
        $users = $conn->query("SELECT * FROM tblUser ORDER BY created_at DESC");
    }
} catch (Exception $e) {
    $users = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Pastimes Admin</title>
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
                <a href="index.php" class="admin-nav-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                
                <div class="admin-sidebar-title">Management</div>
                <a href="users.php" class="admin-nav-link active">
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
                    Manage Users
                </h1>

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

                <!-- Search -->
                <div class="settings-section">
                    <form method="GET" class="flex gap-md">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, or username..." value="<?php echo htmlspecialchars($search); ?>" style="max-width: 400px;">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if (!empty($search)): ?>
                            <a href="users.php" class="btn btn-ghost">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="settings-section">
                    <h2>All Users</h2>
                    <p>View and manage registered users</p>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Seller Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($users && $users->num_rows > 0): ?>
                                    <?php while ($user = $users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $user['userID']; ?></td>
                                            <td><?php echo htmlspecialchars($user['fullName']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge badge-gold"><?php echo ucfirst($user['role']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo $user['seller_status'] === 'verified' ? 'badge-success' : 
                                                        ($user['seller_status'] === 'rejected' ? 'badge-danger' : 'badge-warning'); 
                                                ?>">
                                                    <?php echo ucfirst($user['seller_status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <?php if ($user['seller_status'] === 'pending' && $user['role'] !== 'admin'): ?>
                                                    <a href="?verify=<?php echo $user['userID']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Verify this user as a seller?')">Verify</a>
                                                    <a href="?reject=<?php echo $user['userID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Reject this seller request?')">Reject</a>
                                                <?php endif; ?>
                                                <?php if ($user['role'] !== 'admin'): ?>
                                                    <a href="?delete=<?php echo $user['userID']; ?>" class="btn btn-sm btn-ghost" onclick="return confirm('Delete this user? This cannot be undone.')">Delete</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No users found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../js/main.js"></script>
</body>
</html>
<?php if (isset($conn)) $conn->close(); ?>