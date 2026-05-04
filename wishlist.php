<?php
/**
 * Wishlist Page - PASTIMES
 * Displays user's saved items
 */

session_start();
require_once 'includes/DBConn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=wishlist");
    exit();
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Handle remove from wishlist
if (isset($_POST['remove_item']) && isset($_POST['clothing_id'])) {
    $clothing_id = $_POST['clothing_id'];
    $stmt = $conn->prepare("DELETE FROM tblWishlist WHERE userID = ? AND clothingID = ?");
    $stmt->bind_param("ss", $user_id, $clothing_id);
    $stmt->execute();
    $stmt->close();
    header("Location: wishlist.php");
    exit();
}

// Handle add to cart from wishlist
if (isset($_POST['add_to_cart']) && isset($_POST['clothing_id'])) {
    $clothing_id = $_POST['clothing_id'];
    
    // Initialize cart if needed
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add to cart if not already there
    if (!in_array($clothing_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $clothing_id;
    }
    
    // Remove from wishlist
    $stmt = $conn->prepare("DELETE FROM tblWishlist WHERE userID = ? AND clothingID = ?");
    $stmt->bind_param("ss", $user_id, $clothing_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: cart.php");
    exit();
}

// Get wishlist items
$stmt = $conn->prepare("
    SELECT c.*, u.username as sellerName, w.dateAdded as wishlistDate
    FROM tblWishlist w
    JOIN tblClothing c ON w.clothingID = c.clothingID
    JOIN tblUser u ON c.sellerID = u.userID
    WHERE w.userID = ?
    ORDER BY w.dateAdded DESC
");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$wishlistItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - PASTIMES</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="wishlist-page">
        <div class="page-header">
            <h1>My Wishlist</h1>
            <p>Items you've saved for later</p>
        </div>
        
        <div class="container">
            <?php if (empty($wishlistItems)): ?>
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    <h2>Your wishlist is empty</h2>
                    <p>Start adding items you love to keep track of them</p>
                    <a href="browse.php" class="btn btn-primary">Browse Collection</a>
                </div>
            <?php else: ?>
                <div class="wishlist-count">
                    <span><?php echo count($wishlistItems); ?> item<?php echo count($wishlistItems) !== 1 ? 's' : ''; ?> saved</span>
                </div>
                
                <div class="wishlist-grid">
                    <?php foreach ($wishlistItems as $item): ?>
                        <div class="wishlist-card">
                            <div class="wishlist-image">
                                <a href="product.php?id=<?php echo $item['clothingID']; ?>">
                                    <?php if ($item['imagePath'] && file_exists($item['imagePath'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['imagePath']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image">
                                            <span><?php echo strtoupper(substr($item['brand'], 0, 1)); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <?php if ($item['status'] !== 'Available'): ?>
                                    <div class="sold-overlay">
                                        <span>Sold</span>
                                    </div>
                                <?php endif; ?>
                                <span class="condition-badge badge-<?php echo strtolower(str_replace(' ', '-', $item['clothingCondition'])); ?>">
                                    <?php echo htmlspecialchars($item['clothingCondition']); ?>
                                </span>
                            </div>
                            
                            <div class="wishlist-info">
                                <span class="brand"><?php echo htmlspecialchars($item['brand']); ?></span>
                                <h3><a href="product.php?id=<?php echo $item['clothingID']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></h3>
                                <p class="seller">Seller: <?php echo htmlspecialchars($item['sellerName']); ?></p>
                                <div class="item-meta">
                                    <span>Size: <?php echo htmlspecialchars($item['size']); ?></span>
                                    <span><?php echo htmlspecialchars($item['gender']); ?></span>
                                </div>
                                <p class="price">R<?php echo number_format($item['price'], 2); ?></p>
                                <p class="saved-date">Saved <?php echo date('M j, Y', strtotime($item['wishlistDate'])); ?></p>
                            </div>
                            
                            <div class="wishlist-actions">
                                <?php if ($item['status'] === 'Available'): ?>
                                    <form action="wishlist.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="clothing_id" value="<?php echo $item['clothingID']; ?>">
                                        <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                                    </form>
                                <?php endif; ?>
                                <form action="wishlist.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="clothing_id" value="<?php echo $item['clothingID']; ?>">
                                    <button type="submit" name="remove_item" class="btn btn-outline">Remove</button>
                                </form>
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