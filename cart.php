<?php
/**
 * PASTIMES — ajax/cart.php
 * Handles AJAX cart add/remove requests
 */
require_once '../includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit;
}

if ($_SESSION['role'] !== 'buyer') {
    echo json_encode(['success' => false, 'message' => 'Only buyers can use the cart.']);
    exit;
}

$action     = $_POST['action']     ?? '';
$clothingID = intval($_POST['clothingID'] ?? 0);

if (!$clothingID) {
    echo json_encode(['success' => false, 'message' => 'Invalid item.']);
    exit;
}

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($action === 'add') {
    // Check already in cart
    foreach ($_SESSION['cart'] as $ci) {
        if ($ci['clothingID'] == $clothingID) {
            echo json_encode(['success' => false, 'message' => 'Already in cart.', 'cartCount' => count($_SESSION['cart'])]);
            exit;
        }
    }
    // Get item from DB
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT clothingID, brand, description, price, imagePath FROM tblClothing WHERE clothingID = ? AND status = 'available'");
    $stmt->bind_param('i', $clothingID);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $conn->close();

    if ($item) {
        $_SESSION['cart'][] = $item;
        echo json_encode(['success' => true, 'message' => 'Added to cart!', 'cartCount' => count($_SESSION['cart'])]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not available.']);
    }

} elseif ($action === 'remove') {
    foreach ($_SESSION['cart'] as $k => $ci) {
        if ($ci['clothingID'] == $clothingID) {
            unset($_SESSION['cart'][$k]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            break;
        }
    }
    echo json_encode(['success' => true, 'message' => 'Removed.', 'cartCount' => count($_SESSION['cart'])]);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>