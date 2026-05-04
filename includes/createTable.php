<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: includes/createTable.php
 * Authors: Tshiamo Mosetlha (ST10451437)
 *          Zandile Selao (ST10436981)
 *          Muhluri Nkuna (ST10437226)
 * Description: BULLETPROOF table creation script.
 *              - Uses FOREIGN_KEY_CHECKS = 0 to disable FK constraints
 *              - Drops and recreates all 7 tables in correct order
 *              - Inserts test data using 's' (string) type for all bind_param
 *              - Full error reporting enabled
 * ============================================================
 */

// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'DBConn.php';

echo "<h1>PASTIMES - Database Setup Script</h1>";
echo "<pre>";

try {
    // Get connection without database first to create database if needed
    $conn = getConnectionWithoutDB();
    echo "✓ Connected to MySQL server successfully.\n";

    // Create database if it doesn't exist
    $conn->query("CREATE DATABASE IF NOT EXISTS ClothingStore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database 'ClothingStore' created or already exists.\n";

    // Select the database
    $conn->select_db('ClothingStore');
    echo "✓ Selected database 'ClothingStore'.\n";

    // ================================================================
    // STEP 1: DISABLE FOREIGN KEY CHECKS (KILL SWITCH)
    // ================================================================
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    echo "\n✓ FOREIGN_KEY_CHECKS disabled - Safe to drop tables.\n\n";

    // ================================================================
    // STEP 2: DROP ALL TABLES IN REVERSE DEPENDENCY ORDER
    // ================================================================
    echo "--- DROPPING EXISTING TABLES ---\n";
    
    $tablesToDrop = [
        'tblWishlist',      // Child - depends on tblUser and tblClothing
        'tblOrderItem',     // Child - depends on tblOrder and tblClothing
        'tblMessage',       // Child - depends on tblUser and tblClothing
        'tblOrder',         // Child - depends on tblUser and tblDeliveryAddress
        'tblClothing',      // Child - depends on tblUser
        'tblDeliveryAddress', // Child - depends on tblUser
        'tblUser'           // Parent - no dependencies
    ];

    foreach ($tablesToDrop as $table) {
        $conn->query("DROP TABLE IF EXISTS $table");
        echo "✓ Dropped table: $table\n";
    }

    // ================================================================
    // STEP 3: CREATE TABLES IN CORRECT DEPENDENCY ORDER
    // ================================================================
    echo "\n--- CREATING TABLES ---\n";

    // ----------------------------------------------------------------
    // TABLE 1: tblUser (PARENT - No foreign keys)
    // ----------------------------------------------------------------
    $sql_tblUser = "CREATE TABLE IF NOT EXISTS tblUser (
        userID INT(11) AUTO_INCREMENT PRIMARY KEY,
        fullName VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('buyer', 'seller', 'admin') NOT NULL DEFAULT 'buyer',
        seller_status ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->query($sql_tblUser);
    echo "✓ Created table: tblUser\n";

    // ----------------------------------------------------------------
    // TABLE 2: tblDeliveryAddress (Depends on tblUser)
    // ----------------------------------------------------------------
    $sql_tblDeliveryAddress = "CREATE TABLE IF NOT EXISTS tblDeliveryAddress (
        addressID INT(11) AUTO_INCREMENT PRIMARY KEY,
        userID INT(11) NOT NULL,
        addressType ENUM('residential', 'work') NOT NULL DEFAULT 'residential',
        streetAddress VARCHAR(200) NOT NULL,
        suburb VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        postalCode VARCHAR(10) NOT NULL,
        isDefault TINYINT(1) NOT NULL DEFAULT 0,
        FOREIGN KEY (userID) REFERENCES tblUser(userID) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->query($sql_tblDeliveryAddress);
    echo "✓ Created table: tblDeliveryAddress\n";

    // ----------------------------------------------------------------
    // TABLE 3: tblClothing (Depends on tblUser for sellerID)
    // ----------------------------------------------------------------
    $sql_tblClothing = "CREATE TABLE IF NOT EXISTS tblClothing (
        clothingID INT(11) AUTO_INCREMENT PRIMARY KEY,
        sellerID INT(11) NOT NULL,
        brand VARCHAR(100) NOT NULL,
        category VARCHAR(100) NOT NULL,
        size VARCHAR(20) NOT NULL,
        clothingCondition ENUM('Like New', 'Excellent', 'Good', 'Fair') NOT NULL DEFAULT 'Good',
        description TEXT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        imagePath VARCHAR(255) DEFAULT 'default.jpg',
        status ENUM('available', 'sold', 'pending') NOT NULL DEFAULT 'available',
        dateAdded DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sellerID) REFERENCES tblUser(userID) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->query($sql_tblClothing);
    echo "✓ Created table: tblClothing\n";

    // ----------------------------------------------------------------
    // TABLE 4: tblOrder (Depends on tblUser and tblDeliveryAddress)
    // ----------------------------------------------------------------
    $sql_tblOrder = "CREATE TABLE IF NOT EXISTS tblOrder (
        orderID INT(11) AUTO_INCREMENT PRIMARY KEY,
        buyerID INT(11) NOT NULL,
        addressID INT(11) NOT NULL,
        orderDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        totalAmount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        orderStatus ENUM('placed', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'placed',
        FOREIGN KEY (buyerID) REFERENCES tblUser(userID) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (addressID) REFERENCES tblDeliveryAddress(addressID) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->query($sql_tblOrder);
    echo "✓ Created table: tblOrder\n";

    // ----------------------------------------------------------------
    // TABLE 5: tblOrderItem (Junction table - Depends on tblOrder and tblClothing)
    // ----------------------------------------------------------------
    $sql_tblOrderItem = "CREATE TABLE IF NOT EXISTS tblOrderItem (
        orderItemID INT(11) AUTO_INCREMENT PRIMARY KEY,
        orderID INT(11) NOT NULL,
        clothingID INT(11) NOT NULL,
        priceAtPurchase DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (orderID) REFERENCES tblOrder(orderID) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (clothingID) REFERENCES tblClothing(clothingID) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->query($sql_tblOrderItem);
    echo "✓ Created table: tblOrderItem\n";

    // ----------------------------------------------------------------
    // TABLE 6: tblMessage (Depends on tblUser and optionally tblClothing)
    // ----------------------------------------------------------------
    $sql_tblMessage = "CREATE TABLE IF NOT EXISTS tblMessage (
        messageID INT(11) AUTO_INCREMENT PRIMARY KEY,
        senderID INT(11) NOT NULL,
        receiverID INT(11) NULL,
        clothingID INT(11) NULL,
        messageText TEXT NOT NULL,
        sentAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        isRead TINYINT(1) NOT NULL DEFAULT 0,
        isBroadcast TINYINT(1) NOT NULL DEFAULT 0,
        FOREIGN KEY (senderID) REFERENCES tblUser(userID) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (receiverID) REFERENCES tblUser(userID) ON DELETE SET NULL ON UPDATE CASCADE,
        FOREIGN KEY (clothingID) REFERENCES tblClothing(clothingID) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->query($sql_tblMessage);
    echo "✓ Created table: tblMessage\n";

    // ----------------------------------------------------------------
    // TABLE 7: tblWishlist (Depends on tblUser and tblClothing)
    // ----------------------------------------------------------------
    $sql_tblWishlist = "CREATE TABLE IF NOT EXISTS tblWishlist (
        wishlistID INT(11) AUTO_INCREMENT PRIMARY KEY,
        userID INT(11) NOT NULL,
        clothingID INT(11) NOT NULL,
        addedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (userID) REFERENCES tblUser(userID) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (clothingID) REFERENCES tblClothing(clothingID) ON DELETE CASCADE ON UPDATE CASCADE,
        UNIQUE KEY unique_wishlist (userID, clothingID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->query($sql_tblWishlist);
    echo "✓ Created table: tblWishlist\n";

    // ================================================================
    // STEP 4: RE-ENABLE FOREIGN KEY CHECKS
    // ================================================================
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    echo "\n✓ FOREIGN_KEY_CHECKS re-enabled.\n";

    // ================================================================
    // STEP 5: INSERT TEST DATA (Using 's' string type for ALL bind_param)
    // ================================================================
    echo "\n--- INSERTING TEST DATA ---\n";

    // Load users from userData.txt if it exists, otherwise use defaults
    $userData = [];
    $userDataFile = __DIR__ . '/../data/userData.txt';
    
    if (file_exists($userDataFile)) {
        $lines = file($userDataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode(',', $line);
            if (count($parts) >= 4) {
                $userData[] = [
                    'fullName' => trim($parts[0]),
                    'email' => trim($parts[1]),
                    'username' => trim($parts[2]),
                    'password' => trim($parts[3]),
                    'role' => isset($parts[4]) ? trim($parts[4]) : 'buyer',
                    'seller_status' => isset($parts[5]) ? trim($parts[5]) : 'pending'
                ];
            }
        }
        echo "✓ Loaded users from userData.txt\n";
    }

    // If no user data file, use default test users
    if (empty($userData)) {
        $userData = [
            ['fullName' => 'John Doe', 'email' => 'j.doe@abc.co.za', 'username' => 'johndoe', 'password' => 'Password123', 'role' => 'buyer', 'seller_status' => 'pending'],
            ['fullName' => 'Jane Smith', 'email' => 'jane.smith@email.co.za', 'username' => 'janesmith', 'password' => 'SecurePass1', 'role' => 'seller', 'seller_status' => 'verified'],
            ['fullName' => 'Admin User', 'email' => 'admin@pastimes.co.za', 'username' => 'admin', 'password' => 'AdminPass1', 'role' => 'admin', 'seller_status' => 'verified'],
            ['fullName' => 'Thabo Mokoena', 'email' => 'thabo@email.co.za', 'username' => 'thabom', 'password' => 'ThaboPass1', 'role' => 'seller', 'seller_status' => 'verified'],
            ['fullName' => 'Lerato Nkosi', 'email' => 'lerato@email.co.za', 'username' => 'leraton', 'password' => 'LeratoPass1', 'role' => 'buyer', 'seller_status' => 'pending']
        ];
    }

    // Insert users - ALL 's' STRING TYPE FOR SAFETY
    $stmtUser = $conn->prepare("INSERT INTO tblUser (fullName, email, username, password, role, seller_status) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($userData as $user) {
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
        $fullName = $user['fullName'];
        $email = $user['email'];
        $username = $user['username'];
        $role = $user['role'];
        $seller_status = $user['seller_status'];
        
        // Using 'ssssss' - ALL STRINGS for safety
        $stmtUser->bind_param('ssssss', $fullName, $email, $username, $hashedPassword, $role, $seller_status);
        $stmtUser->execute();
    }
    echo "✓ Inserted " . count($userData) . " users into tblUser\n";

    // Insert delivery addresses - ALL 's' STRING TYPE
    $addresses = [
        ['1', 'residential', '123 Main Street', 'Sandton', 'Johannesburg', '2196', '1'],
        ['1', 'work', '456 Office Park', 'Rosebank', 'Johannesburg', '2196', '0'],
        ['2', 'residential', '789 Oak Avenue', 'Durban North', 'Durban', '4051', '1'],
        ['4', 'residential', '321 Pine Road', 'Centurion', 'Pretoria', '0157', '1'],
        ['5', 'residential', '654 Beach Road', 'Sea Point', 'Cape Town', '8005', '1']
    ];

    $stmtAddress = $conn->prepare("INSERT INTO tblDeliveryAddress (userID, addressType, streetAddress, suburb, city, postalCode, isDefault) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($addresses as $addr) {
        // Using 'sssssss' - ALL STRINGS for safety
        $stmtAddress->bind_param('sssssss', $addr[0], $addr[1], $addr[2], $addr[3], $addr[4], $addr[5], $addr[6]);
        $stmtAddress->execute();
    }
    echo "✓ Inserted " . count($addresses) . " addresses into tblDeliveryAddress\n";

    // Insert clothing items (30 entries as required) - ALL 's' STRING TYPE
    $clothingItems = [
        ['2', 'Gucci', 'Women', 'M', 'Excellent', 'Vintage Gucci Blazer in excellent condition. Classic design with gold buttons.', '2500.00', 'gucci_blazer.jpg', 'available'],
        ['2', 'Levis', 'Women', 'L', 'Good', 'Classic Levis Denim Jacket. Timeless style, slight fading for vintage look.', '850.00', 'levis_jacket.jpg', 'available'],
        ['4', 'Burberry', 'Women', 'L', 'Like New', 'Burberry Trench Coat in pristine condition. Iconic check lining.', '4200.00', 'burberry_coat.jpg', 'available'],
        ['4', 'Ralph Lauren', 'Men', 'S', 'Good', 'Ralph Lauren Polo Shirt. Classic fit, navy blue with signature logo.', '450.00', 'rl_polo.jpg', 'available'],
        ['2', 'Nike', 'Men', 'L', 'Excellent', 'Nike Vintage Windbreaker. Retro 90s style, perfect condition.', '680.00', 'nike_windbreaker.jpg', 'available'],
        ['4', 'Adidas', 'Men', 'M', 'Good', 'Adidas Track Jacket. Classic three stripes, comfortable fit.', '520.00', 'adidas_track.jpg', 'available'],
        ['2', 'Zara', 'Women', 'S', 'Like New', 'Zara Wool Coat. Elegant winter coat, barely worn.', '1200.00', 'zara_coat.jpg', 'available'],
        ['4', 'H&M', 'Women', 'M', 'Good', 'H&M Floral Dress. Perfect for summer, vibrant prints.', '380.00', 'hm_dress.jpg', 'available'],
        ['2', 'Tommy Hilfiger', 'Men', 'XL', 'Excellent', 'Tommy Hilfiger Cable Knit Sweater. Premium quality, warm and stylish.', '750.00', 'tommy_sweater.jpg', 'available'],
        ['4', 'Calvin Klein', 'Men', 'M', 'Like New', 'Calvin Klein Slim Fit Jeans. Dark wash, modern cut.', '890.00', 'ck_jeans.jpg', 'available'],
        ['2', 'Prada', 'Women', 'S', 'Excellent', 'Prada Silk Blouse. Luxurious fabric, elegant design.', '1850.00', 'prada_blouse.jpg', 'available'],
        ['4', 'Versace', 'Men', 'L', 'Good', 'Versace Print Shirt. Bold Baroque print, statement piece.', '2200.00', 'versace_shirt.jpg', 'available'],
        ['2', 'Chanel', 'Accessories', 'One Size', 'Excellent', 'Chanel Silk Scarf. Iconic design, perfect accessory.', '1500.00', 'chanel_scarf.jpg', 'available'],
        ['4', 'Rolex', 'Accessories', 'One Size', 'Like New', 'Vintage Rolex Watch Box. Collector item, pristine condition.', '800.00', 'rolex_box.jpg', 'available'],
        ['2', 'Louis Vuitton', 'Accessories', 'One Size', 'Good', 'Louis Vuitton Belt. Classic monogram, genuine leather.', '950.00', 'lv_belt.jpg', 'available'],
        ['4', 'Converse', 'Footwear', '42', 'Good', 'Converse Chuck Taylor High Tops. Classic white, slight wear.', '450.00', 'converse_hi.jpg', 'available'],
        ['2', 'Dr. Martens', 'Footwear', '40', 'Excellent', 'Dr. Martens 1460 Boots. Cherry red, well maintained.', '1800.00', 'docs_boots.jpg', 'available'],
        ['4', 'Vans', 'Footwear', '44', 'Like New', 'Vans Old Skool. Black and white, barely worn.', '650.00', 'vans_oldskool.jpg', 'available'],
        ['2', 'Diesel', 'Men', 'L', 'Good', 'Diesel Leather Jacket. Genuine leather, distressed look.', '2800.00', 'diesel_leather.jpg', 'available'],
        ['4', 'Armani', 'Men', 'M', 'Excellent', 'Armani Dress Shirt. Premium cotton, perfect for formal occasions.', '780.00', 'armani_shirt.jpg', 'available'],
        ['2', 'Guess', 'Women', 'S', 'Good', 'Guess Denim Skirt. Vintage wash, stylish cut.', '420.00', 'guess_skirt.jpg', 'available'],
        ['4', 'Michael Kors', 'Accessories', 'One Size', 'Like New', 'Michael Kors Handbag. Signature print, excellent condition.', '1350.00', 'mk_bag.jpg', 'available'],
        ['2', 'Coach', 'Accessories', 'One Size', 'Excellent', 'Coach Leather Wallet. Classic brown, multiple compartments.', '580.00', 'coach_wallet.jpg', 'available'],
        ['4', 'Lacoste', 'Men', 'M', 'Good', 'Lacoste Polo Shirt. Classic green, iconic crocodile logo.', '550.00', 'lacoste_polo.jpg', 'available'],
        ['2', 'Hugo Boss', 'Men', 'L', 'Excellent', 'Hugo Boss Suit Jacket. Navy blue, tailored fit.', '1950.00', 'boss_jacket.jpg', 'available'],
        ['4', 'Fendi', 'Women', 'M', 'Like New', 'Fendi Printed T-Shirt. Designer logo, premium quality.', '720.00', 'fendi_tshirt.jpg', 'available'],
        ['2', 'Balenciaga', 'Footwear', '39', 'Excellent', 'Balenciaga Speed Trainers. Iconic sock design, rare find.', '3500.00', 'balenciaga_speed.jpg', 'available'],
        ['4', 'Off-White', 'Men', 'L', 'Good', 'Off-White Hoodie. Signature arrow design, streetwear essential.', '1680.00', 'offwhite_hoodie.jpg', 'available'],
        ['2', 'Givenchy', 'Women', 'S', 'Like New', 'Givenchy Evening Dress. Black, elegant, perfect for events.', '2850.00', 'givenchy_dress.jpg', 'available'],
        ['4', 'Dolce & Gabbana', 'Men', 'M', 'Excellent', 'D&G Printed Blazer. Bold floral print, Italian craftsmanship.', '2400.00', 'dg_blazer.jpg', 'available']
    ];

    $stmtClothing = $conn->prepare("INSERT INTO tblClothing (sellerID, brand, category, size, clothingCondition, description, price, imagePath, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($clothingItems as $item) {
        // Using 'sssssssss' - ALL STRINGS for safety (9 parameters)
        $stmtClothing->bind_param('sssssssss', $item[0], $item[1], $item[2], $item[3], $item[4], $item[5], $item[6], $item[7], $item[8]);
        $stmtClothing->execute();
    }
    echo "✓ Inserted " . count($clothingItems) . " clothing items into tblClothing\n";

    // Insert sample orders - ALL 's' STRING TYPE
    $orders = [
        ['1', '1', '3350.00', 'delivered'],
        ['5', '5', '1800.00', 'processing']
    ];

    $stmtOrder = $conn->prepare("INSERT INTO tblOrder (buyerID, addressID, totalAmount, orderStatus) VALUES (?, ?, ?, ?)");
    
    foreach ($orders as $order) {
        // Using 'ssss' - ALL STRINGS for safety
        $stmtOrder->bind_param('ssss', $order[0], $order[1], $order[2], $order[3]);
        $stmtOrder->execute();
    }
    echo "✓ Inserted " . count($orders) . " orders into tblOrder\n";

    // Insert sample order items - ALL 's' STRING TYPE
    $orderItems = [
        ['1', '1', '2500.00'],
        ['1', '2', '850.00'],
        ['2', '17', '1800.00']
    ];

    $stmtOrderItem = $conn->prepare("INSERT INTO tblOrderItem (orderID, clothingID, priceAtPurchase) VALUES (?, ?, ?)");
    
    foreach ($orderItems as $orderItem) {
        // Using 'sss' - ALL STRINGS for safety
        $stmtOrderItem->bind_param('sss', $orderItem[0], $orderItem[1], $orderItem[2]);
        $stmtOrderItem->execute();
    }
    echo "✓ Inserted " . count($orderItems) . " order items into tblOrderItem\n";

    // Insert sample messages - ALL 's' STRING TYPE
    $messages = [
        ['1', '2', '1', 'Hi! Is the Gucci blazer still available?', '0', '0'],
        ['2', '1', '1', 'Yes, it is! Are you interested?', '0', '0'],
        ['1', '2', '1', 'Definitely! Can you tell me more about the condition?', '0', '0'],
        ['3', NULL, NULL, 'Welcome to Pastimes! Check out our new arrivals this week.', '0', '1'],
        ['5', '4', '4', 'Is the Ralph Lauren polo true to size?', '0', '0']
    ];

    $stmtMessage = $conn->prepare("INSERT INTO tblMessage (senderID, receiverID, clothingID, messageText, isRead, isBroadcast) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($messages as $msg) {
        // Using 'ssssss' - ALL STRINGS for safety
        $stmtMessage->bind_param('ssssss', $msg[0], $msg[1], $msg[2], $msg[3], $msg[4], $msg[5]);
        $stmtMessage->execute();
    }
    echo "✓ Inserted " . count($messages) . " messages into tblMessage\n";

    // Insert sample wishlist items - ALL 's' STRING TYPE
    $wishlistItems = [
        ['1', '3'],
        ['1', '11'],
        ['5', '1'],
        ['5', '19']
    ];

    $stmtWishlist = $conn->prepare("INSERT INTO tblWishlist (userID, clothingID) VALUES (?, ?)");
    
    foreach ($wishlistItems as $wish) {
        // Using 'ss' - ALL STRINGS for safety
        $stmtWishlist->bind_param('ss', $wish[0], $wish[1]);
        $stmtWishlist->execute();
    }
    echo "✓ Inserted " . count($wishlistItems) . " wishlist items into tblWishlist\n";

    // ================================================================
    // STEP 6: VERIFY TABLE CREATION
    // ================================================================
    echo "\n--- VERIFICATION ---\n";
    
    $result = $conn->query("SHOW TABLES");
    echo "Tables in database 'ClothingStore':\n";
    while ($row = $result->fetch_array()) {
        $countResult = $conn->query("SELECT COUNT(*) as count FROM " . $row[0]);
        $count = $countResult->fetch_assoc()['count'];
        echo "  • " . $row[0] . " (" . $count . " records)\n";
    }

    // Close connection
    $conn->close();

    echo "\n========================================\n";
    echo "✓ DATABASE SETUP COMPLETED SUCCESSFULLY!\n";
    echo "========================================\n";
    echo "\nDefault Login Credentials:\n";
    echo "  Admin: username='admin', password='AdminPass1'\n";
    echo "  Buyer: username='johndoe', password='Password123'\n";
    echo "  Seller: username='janesmith', password='SecurePass1'\n";

} catch (mysqli_sql_exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . " (Line " . $e->getLine() . ")\n";
}

echo "</pre>";
?>