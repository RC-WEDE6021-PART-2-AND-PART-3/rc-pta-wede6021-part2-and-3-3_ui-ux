<?php
/**
 * Sell Item Page - PASTIMES
 * Allows verified sellers to list items for sale
 */

session_start();
require_once 'includes/DBConn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=sell");
    exit();
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

// Check if user is a verified seller
$stmt = $conn->prepare("SELECT userType, isVerified FROM tblUser WHERE userID = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$canSell = ($user['userType'] === 'Seller' || $user['userType'] === 'Admin') && $user['isVerified'] == 1;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canSell) {
    $brand = trim($_POST['brand'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $condition = trim($_POST['condition'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    
    // Validation
    if (empty($brand)) $errors[] = "Brand is required";
    if (empty($name)) $errors[] = "Item name is required";
    if (empty($description)) $errors[] = "Description is required";
    if (empty($category)) $errors[] = "Category is required";
    if (empty($size)) $errors[] = "Size is required";
    if (empty($condition)) $errors[] = "Condition is required";
    if (empty($price) || !is_numeric($price) || $price <= 0) $errors[] = "Valid price is required";
    if (empty($gender)) $errors[] = "Gender category is required";
    
    // Handle image upload
    $imagePath = "images/products/default.jpg";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = $_FILES['image']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid('product_') . '.' . $extension;
            $uploadPath = 'images/products/' . $newFileName;
            
            if (!is_dir('images/products')) {
                mkdir('images/products', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $imagePath = $uploadPath;
            } else {
                $errors[] = "Failed to upload image";
            }
        } else {
            $errors[] = "Invalid image type. Please use JPG, PNG, or WebP";
        }
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO tblClothing (sellerID, brand, name, description, category, size, color, clothingCondition, price, gender, imagePath, dateAdded, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Available')");
        $stmt->bind_param("sssssssssss", $user_id, $brand, $name, $description, $category, $size, $color, $condition, $price, $gender, $imagePath);
        
        if ($stmt->execute()) {
            $success = "Your item has been listed successfully!";
            // Clear form
            $brand = $name = $description = $category = $size = $color = $condition = $price = $gender = "";
        } else {
            $errors[] = "Failed to create listing. Please try again.";
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Your Items - PASTIMES</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="sell-page">
        <div class="page-header">
            <h1>Sell Your Items</h1>
            <p>List your pre-loved fashion items on PASTIMES</p>
        </div>
        
        <div class="container">
            <?php if (!$canSell): ?>
                <div class="seller-notice">
                    <div class="notice-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <h2>Become a Verified Seller</h2>
                    <p>To sell items on PASTIMES, you need to be a verified seller. All new accounts start as Buyers.</p>
                    <p>To become a seller:</p>
                    <ol>
                        <li>Go to your <a href="profile.php">Profile Settings</a></li>
                        <li>Request seller status</li>
                        <li>Wait for admin verification</li>
                    </ol>
                    <a href="profile.php" class="btn btn-primary">Go to Profile</a>
                </div>
            <?php else: ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="sell.php" method="POST" enctype="multipart/form-data" class="sell-form">
                    <div class="form-card">
                        <h2>Item Details</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="brand">Brand *</label>
                                <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($brand ?? ''); ?>" placeholder="e.g., Gucci, Levi's, Nike" required>
                            </div>
                            <div class="form-group">
                                <label for="name">Item Name *</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" placeholder="e.g., Vintage Denim Jacket" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" rows="4" placeholder="Describe your item in detail - condition, measurements, any flaws, etc." required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Tops" <?php echo ($category ?? '') === 'Tops' ? 'selected' : ''; ?>>Tops</option>
                                    <option value="Bottoms" <?php echo ($category ?? '') === 'Bottoms' ? 'selected' : ''; ?>>Bottoms</option>
                                    <option value="Dresses" <?php echo ($category ?? '') === 'Dresses' ? 'selected' : ''; ?>>Dresses</option>
                                    <option value="Outerwear" <?php echo ($category ?? '') === 'Outerwear' ? 'selected' : ''; ?>>Outerwear</option>
                                    <option value="Footwear" <?php echo ($category ?? '') === 'Footwear' ? 'selected' : ''; ?>>Footwear</option>
                                    <option value="Accessories" <?php echo ($category ?? '') === 'Accessories' ? 'selected' : ''; ?>>Accessories</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender *</label>
                                <select id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Women" <?php echo ($gender ?? '') === 'Women' ? 'selected' : ''; ?>>Women</option>
                                    <option value="Men" <?php echo ($gender ?? '') === 'Men' ? 'selected' : ''; ?>>Men</option>
                                    <option value="Unisex" <?php echo ($gender ?? '') === 'Unisex' ? 'selected' : ''; ?>>Unisex</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="size">Size *</label>
                                <select id="size" name="size" required>
                                    <option value="">Select Size</option>
                                    <option value="XS" <?php echo ($size ?? '') === 'XS' ? 'selected' : ''; ?>>XS</option>
                                    <option value="S" <?php echo ($size ?? '') === 'S' ? 'selected' : ''; ?>>S</option>
                                    <option value="M" <?php echo ($size ?? '') === 'M' ? 'selected' : ''; ?>>M</option>
                                    <option value="L" <?php echo ($size ?? '') === 'L' ? 'selected' : ''; ?>>L</option>
                                    <option value="XL" <?php echo ($size ?? '') === 'XL' ? 'selected' : ''; ?>>XL</option>
                                    <option value="XXL" <?php echo ($size ?? '') === 'XXL' ? 'selected' : ''; ?>>XXL</option>
                                    <option value="One Size" <?php echo ($size ?? '') === 'One Size' ? 'selected' : ''; ?>>One Size</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="color">Color</label>
                                <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($color ?? ''); ?>" placeholder="e.g., Navy Blue">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="condition">Condition *</label>
                                <select id="condition" name="condition" required>
                                    <option value="">Select Condition</option>
                                    <option value="Like New" <?php echo ($condition ?? '') === 'Like New' ? 'selected' : ''; ?>>Like New</option>
                                    <option value="Excellent" <?php echo ($condition ?? '') === 'Excellent' ? 'selected' : ''; ?>>Excellent</option>
                                    <option value="Good" <?php echo ($condition ?? '') === 'Good' ? 'selected' : ''; ?>>Good</option>
                                    <option value="Fair" <?php echo ($condition ?? '') === 'Fair' ? 'selected' : ''; ?>>Fair</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="price">Price (ZAR) *</label>
                                <div class="input-with-icon">
                                    <span class="input-prefix">R</span>
                                    <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($price ?? ''); ?>" placeholder="0.00" min="1" step="0.01" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-card">
                        <h2>Item Photo</h2>
                        <div class="image-upload-area">
                            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                            <label for="image" class="upload-label">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <span>Click to upload image</span>
                                <span class="upload-hint">JPG, PNG or WebP (max 5MB)</span>
                            </label>
                            <div id="imagePreview" class="image-preview"></div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">List Item for Sale</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>