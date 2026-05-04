<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: admin/broadcast.php
 * Description: Admin broadcast messaging page
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
$adminID = $_SESSION['userID'];

// Handle broadcast
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_broadcast') {
    $messageText = trim($_POST['messageText']);
    
    if (empty($messageText)) {
        $error = 'Please enter a message.';
    } elseif (strlen($messageText) < 5) {
        $error = 'Message must be at least 5 characters.';
    } else {
        try {
            $conn = getConnection();
            $stmt = $conn->prepare("INSERT INTO tblMessage (senderID, receiverID, clothingID, messageText, isRead, isBroadcast) VALUES (?, NULL, NULL, ?, 0, 1)");
            $stmt->bind_param('ss', $adminID, $messageText);
            $stmt->execute();
            $success = 'Broadcast message sent to all users!';
            $conn->close();
        } catch (Exception $e) {
            $error = 'Could not send broadcast.';
        }
    }
}

// Fetch past broadcasts
try {
    $conn = getConnection();
    $broadcasts = $conn->query("SELECT m.*, u.username FROM tblMessage m JOIN tblUser u ON m.senderID = u.userID WHERE m.isBroadcast = 1 ORDER BY m.sentAt DESC");
} catch (Exception $e) {
    $broadcasts = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broadcast Messages | Pastimes Admin</title>
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
                <a href="orders.php" class="admin-nav-link"><i class="fas fa-shopping-cart"></i> Orders</a>
                
                <div class="admin-sidebar-title">Communication</div>
                <a href="broadcast.php" class="admin-nav-link active"><i class="fas fa-bullhorn"></i> Broadcast</a>
                
                <div class="admin-sidebar-title">System</div>
                <a href="../includes/createTable.php" class="admin-nav-link" target="_blank"><i class="fas fa-database"></i> Reset Database</a>
                <a href="../index.php" class="admin-nav-link"><i class="fas fa-home"></i> View Site</a>
            </aside>

            <main class="admin-main">
                <h1 style="font-family: var(--font-display); font-size: 2rem; color: var(--text-primary); margin-bottom: var(--space-xl);">
                    Broadcast Messages
                </h1>

                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Send Broadcast Form -->
                <div class="settings-section">
                    <h2>Send Platform Announcement</h2>
                    <p>Send a message to all registered users about new stock, promotions, or updates</p>

                    <form method="POST">
                        <input type="hidden" name="action" value="send_broadcast">
                        
                        <div class="form-group">
                            <label class="form-label">Message <span class="required">*</span></label>
                            <textarea name="messageText" class="form-control" rows="4" placeholder="e.g. New arrivals just dropped! Check out our latest designer pieces." required minlength="5" maxlength="2000"></textarea>
                            <span class="form-hint">This message will appear in all users' inbox</span>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Broadcast
                        </button>
                    </form>
                </div>

                <!-- Past Broadcasts -->
                <div class="settings-section">
                    <h2>Past Broadcasts</h2>
                    <p>History of platform announcements</p>

                    <?php if ($broadcasts && $broadcasts->num_rows > 0): ?>
                        <?php while ($msg = $broadcasts->fetch_assoc()): ?>
                            <div class="alert alert-info" style="margin-bottom: var(--space-md);">
                                <div class="flex-between mb-sm">
                                    <span class="badge badge-gold">
                                        <i class="fas fa-bullhorn"></i> <?php echo htmlspecialchars($msg['username']); ?>
                                    </span>
                                    <small class="text-muted"><?php echo date('M j, Y H:i', strtotime($msg['sentAt'])); ?></small>
                                </div>
                                <p><?php echo nl2br(htmlspecialchars($msg['messageText'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted">No broadcasts sent yet.</p>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
<?php if (isset($conn)) $conn->close(); ?>