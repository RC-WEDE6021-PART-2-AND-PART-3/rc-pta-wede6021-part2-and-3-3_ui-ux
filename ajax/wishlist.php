<?php
/**
 * AJAX Wishlist Handler - PASTIMES
 * Handles wishlist operations via AJAX
 */

session_start();
header('Content-Type: application/json');

require_once '../includes/DBConn.php';

// Initialize response
$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to manage wishlist';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$clothingId = $_POST['clothing_id'] ?? '';

$conn = getConnection();

switch ($action) {
    case 'add':
        if (!empty($clothingId)) {
            // Check if already in wishlist
            $stmt = $conn->prepare("SELECT wishlistID FROM tblWishlist WHERE userID = ? AND clothingID = ?");
            $stmt->bind_param("ss", $user_id, $clothingId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                // Add to wishlist
                $stmt = $conn->prepare("INSERT INTO tblWishlist (userID, clothingID, dateAdded) VALUES (?, ?, NOW())");
                $stmt->bind_param("ss", $user_id, $clothingId);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Item added to wishlist';
                } else {
                    $response['message'] = 'Failed to add item';
                }
            } else {
                $response['message'] = 'Item already in wishlist';
            }
            $stmt->close();
        }
        break;
        
    case 'remove':
        if (!empty($clothingId)) {
            $stmt = $conn->prepare("DELETE FROM tblWishlist WHERE userID = ? AND clothingID = ?");
            $stmt->bind_param("ss", $user_id, $clothingId);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Item removed from wishlist';
            } else {
                $response['message'] = 'Item not found in wishlist';
            }
            $stmt->close();
        }
        break;
        
    case 'check':
        if (!empty($clothingId)) {
            $stmt = $conn->prepare("SELECT wishlistID FROM tblWishlist WHERE userID = ? AND clothingID = ?");
            $stmt->bind_param("ss", $user_id, $clothingId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $response['success'] = true;
            $response['inWishlist'] = $result->num_rows > 0;
            $stmt->close();
        }
        break;
        
    default:
        $response['message'] = 'Invalid action';
}

$conn->close();
echo json_encode($response);
?>