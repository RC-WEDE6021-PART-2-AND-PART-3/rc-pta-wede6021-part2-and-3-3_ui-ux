<?php
/**
 * AJAX Cart Handler - PASTIMES
 * Handles cart operations via AJAX
 */

session_start();
header('Content-Type: application/json');

require_once '../includes/DBConn.php';

// Initialize response
$response = ['success' => false, 'message' => '', 'cartCount' => 0];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to add items to cart';
    echo json_encode($response);
    exit();
}

// Initialize cart if needed
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? '';
$clothingId = $_POST['clothing_id'] ?? '';

switch ($action) {
    case 'add':
        if (!empty($clothingId)) {
            // Check if item exists and is available
            $conn = getConnection();
            $stmt = $conn->prepare("SELECT clothingID, status FROM tblClothing WHERE clothingID = ?");
            $stmt->bind_param("s", $clothingId);
            $stmt->execute();
            $result = $stmt->get_result();
            $item = $result->fetch_assoc();
            $stmt->close();
            $conn->close();
            
            if ($item && $item['status'] === 'Available') {
                if (!in_array($clothingId, $_SESSION['cart'])) {
                    $_SESSION['cart'][] = $clothingId;
                    $response['success'] = true;
                    $response['message'] = 'Item added to cart';
                } else {
                    $response['message'] = 'Item already in cart';
                }
            } else {
                $response['message'] = 'Item is not available';
            }
        }
        break;
        
    case 'remove':
        if (!empty($clothingId)) {
            $key = array_search($clothingId, $_SESSION['cart']);
            if ($key !== false) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex
                $response['success'] = true;
                $response['message'] = 'Item removed from cart';
            }
        }
        break;
        
    case 'clear':
        $_SESSION['cart'] = [];
        $response['success'] = true;
        $response['message'] = 'Cart cleared';
        break;
        
    default:
        $response['message'] = 'Invalid action';
}

$response['cartCount'] = count($_SESSION['cart']);
echo json_encode($response);
?>