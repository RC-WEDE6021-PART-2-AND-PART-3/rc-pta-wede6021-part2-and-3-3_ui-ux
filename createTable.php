<?php
/**
 * PASTIMES — includes/createTable.php
 * Drops and recreates all tables, loads seed data from userData.txt
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'DBConn.php';

echo "<h1 style='font-family:sans-serif;'>PASTIMES — Database Setup</h1><pre>";

try {
    $conn = getConnectionWithoutDB();
    echo "✓ Connected to MySQL.\n";

    $conn->query("CREATE DATABASE IF NOT EXISTS ClothingStore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db('ClothingStore');
    echo "✓ Database 'ClothingStore' ready.\n\n";

    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // Drop tables
    foreach (['tblWishlist','tblMessage','tblOrderItem','tblOrder','tblClothing','tblDeliveryAddress','tblUser'] as $t) {
        $conn->query("DROP TABLE IF EXISTS $t");
        echo "✓ Dropped $t\n";
    }

    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    // ── tblUser ──────────────────────────────────────────────
    $conn->query("CREATE TABLE tblUser (
        userID      INT AUTO_INCREMENT PRIMARY KEY,
        fullName    VARCHAR(100) NOT NULL,
        username    VARCHAR(50)  NOT NULL UNIQUE,
        email       VARCHAR(150) NOT NULL UNIQUE,
        password    VARCHAR(255) NOT NULL,
        role        ENUM('buyer','seller','admin') NOT NULL DEFAULT 'buyer',
        seller_status ENUM('none','pending','verified','rejected') NOT NULL DEFAULT 'none',
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "\n✓ Created tblUser\n";

    // ── tblDeliveryAddress ────────────────────────────────────
    $conn->query("CREATE TABLE tblDeliveryAddress (
        addressID     INT AUTO_INCREMENT PRIMARY KEY,
        userID        INT NOT NULL,
        streetAddress VARCHAR(200) NOT NULL,
        suburb        VARCHAR(100),
        city          VARCHAR(100) NOT NULL,
        province      VARCHAR(100),
        postalCode    VARCHAR(10),
        isDefault     TINYINT(1) DEFAULT 0,
        FOREIGN KEY (userID) REFERENCES tblUser(userID) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Created tblDeliveryAddress\n";

    // ── tblClothing ───────────────────────────────────────────
    $conn->query("CREATE TABLE tblClothing (
        clothingID       INT AUTO_INCREMENT PRIMARY KEY,
        sellerID         INT NOT NULL,
        brand            VARCHAR(100) NOT NULL,
        category         VARCHAR(50)  NOT NULL,
        size             VARCHAR(20)  NOT NULL,
        clothingCondition VARCHAR(30) NOT NULL,
        description      TEXT,
        price            DECIMAL(10,2) NOT NULL,
        imagePath        VARCHAR(255) DEFAULT 'images/default.png',
        status           ENUM('available','sold','pending') DEFAULT 'available',
        dateAdded        DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sellerID) REFERENCES tblUser(userID) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Created tblClothing\n";

    // ── tblOrder ──────────────────────────────────────────────
    $conn->query("CREATE TABLE tblOrder (
        orderID     INT AUTO_INCREMENT PRIMARY KEY,
        buyerID     INT NOT NULL,
        addressID   INT,
        totalAmount DECIMAL(10,2) NOT NULL,
        orderStatus ENUM('placed','processing','shipped','delivered','cancelled') DEFAULT 'placed',
        orderDate   DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (buyerID) REFERENCES tblUser(userID) ON DELETE CASCADE,
        FOREIGN KEY (addressID) REFERENCES tblDeliveryAddress(addressID) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Created tblOrder\n";

    // ── tblOrderItem ──────────────────────────────────────────
    $conn->query("CREATE TABLE tblOrderItem (
        orderItemID     INT AUTO_INCREMENT PRIMARY KEY,
        orderID         INT NOT NULL,
        clothingID      INT NOT NULL,
        priceAtPurchase DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (orderID)    REFERENCES tblOrder(orderID)   ON DELETE CASCADE,
        FOREIGN KEY (clothingID) REFERENCES tblClothing(clothingID) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Created tblOrderItem\n";

    // ── tblMessage ────────────────────────────────────────────
    $conn->query("CREATE TABLE tblMessage (
        messageID   INT AUTO_INCREMENT PRIMARY KEY,
        senderID    INT NOT NULL,
        receiverID  INT,
        clothingID  INT,
        messageText TEXT NOT NULL,
        isRead      TINYINT(1) DEFAULT 0,
        isBroadcast TINYINT(1) DEFAULT 0,
        sentAt      DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (senderID)   REFERENCES tblUser(userID)    ON DELETE CASCADE,
        FOREIGN KEY (receiverID) REFERENCES tblUser(userID)    ON DELETE SET NULL,
        FOREIGN KEY (clothingID) REFERENCES tblClothing(clothingID) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Created tblMessage\n";

    // ── tblWishlist ───────────────────────────────────────────
    $conn->query("CREATE TABLE tblWishlist (
        wishlistID  INT AUTO_INCREMENT PRIMARY KEY,
        userID      INT NOT NULL,
        clothingID  INT NOT NULL,
        dateAdded   DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY  unique_wish (userID, clothingID),
        FOREIGN KEY (userID)     REFERENCES tblUser(userID)    ON DELETE CASCADE,
        FOREIGN KEY (clothingID) REFERENCES tblClothing(clothingID) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Created tblWishlist\n";

    // ── Seed users from userData.txt ──────────────────────────
    echo "\n--- Loading userData.txt ---\n";
    $file = __DIR__ . '/../data/userData.txt';
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $stmtU = $conn->prepare("INSERT INTO tblUser (fullName, username, email, password, role, seller_status) VALUES (?,?,?,?,?,?)");
        foreach ($lines as $line) {
            $parts = array_map('trim', explode(',', $line));
            if (count($parts) >= 6) {
                list($fullName, $email, $username, $rawPass, $role, $status) = $parts;
                $hashed = password_hash($rawPass, PASSWORD_BCRYPT);
                $stmtU->bind_param('ssssss', $fullName, $username, $email, $hashed, $role, $status);
                $stmtU->execute();
                echo "  ✓ Inserted user: $username ($role)\n";
            }
        }
    } else {
        // Fallback: insert hardcoded users
        $users = [
            ['John Doe',    'johndoe',  'j.doe@abc.co.za',         'Password123',  'buyer',  'none'],
            ['Jane Smith',  'janesmith','jane.smith@email.co.za',  'SecurePass1',  'seller', 'verified'],
            ['Admin User',  'admin',    'admin@pastimes.co.za',    'AdminPass1',   'admin',  'verified'],
            ['Thabo Mokoena','thabom',  'thabo@email.co.za',       'ThaboPass1',   'seller', 'verified'],
            ['Lerato Nkosi', 'leraton', 'lerato@email.co.za',      'LeratoPass1',  'buyer',  'none'],
            ['Sipho Dlamini','siphod',  'sipho@email.co.za',       'SiphoPass1',   'buyer',  'none'],
        ];
        $stmtU = $conn->prepare("INSERT INTO tblUser (fullName, username, email, password, role, seller_status) VALUES (?,?,?,?,?,?)");
        foreach ($users as $u) {
            $hashed = password_hash($u[3], PASSWORD_BCRYPT);
            $stmtU->bind_param('ssssss', $u[0], $u[1], $u[2], $hashed, $u[4], $u[5]);
            $stmtU->execute();
            echo "  ✓ Inserted user: {$u[1]} ({$u[4]})\n";
        }
    }

    // ── Seed delivery addresses ───────────────────────────────
    $addresses = [
        [1,'123 Main Street','Sandton','Johannesburg','Gauteng','2196',1],
        [2,'45 Beach Road','Sea Point','Cape Town','Western Cape','8005',1],
        [3,'78 Church Street','Hatfield','Pretoria','Gauteng','0028',1],
        [4,'22 Florida Road','Morningside','Durban','KwaZulu-Natal','4001',1],
        [5,'15 Long Street','CBD','Cape Town','Western Cape','8001',1],
    ];
    $stmtA = $conn->prepare("INSERT INTO tblDeliveryAddress (userID,streetAddress,suburb,city,province,postalCode,isDefault) VALUES (?,?,?,?,?,?,?)");
    foreach ($addresses as $a) {
        $stmtA->bind_param('sssssss',$a[0],$a[1],$a[2],$a[3],$a[4],$a[5],$a[6]);
        $stmtA->execute();
    }
    echo "\n✓ Inserted delivery addresses\n";

    // ── Seed clothing items (30 items) ────────────────────────
    $clothing = [
        [2,'Gucci',       'Men',         'M',  'Excellent','Vintage Gucci Blazer. Perfect for formal occasions.','2500.00','images/gucci_blazer.jpg'],
        [2,"Levi's",      'Women',       'S',  'Good',     "Classic Levi's Denim Jacket. Timeless wardrobe essential.",'850.00','images/levis_jacket.jpg'],
        [4,'Burberry',    'Women',       'L',  'Like New', 'Burberry Trench Coat. Iconic check lining.','4200.00','images/burberry_coat.jpg'],
        [4,'Ralph Lauren','Men',         'S',  'Good',     'Ralph Lauren Polo Shirt. Classic fit, navy.','450.00','images/rl_polo.jpg'],
        [2,'Nike',        'Men',         'L',  'Excellent','Nike Vintage Windbreaker. Retro 90s style.','680.00','images/nike_windbreaker.jpg'],
        [4,'Adidas',      'Men',         'M',  'Good',     'Adidas Track Jacket. Classic three stripes.','520.00','images/adidas_track.jpg'],
        [2,'Zara',        'Women',       'S',  'Like New', 'Zara Wool Coat. Elegant winter coat.','1200.00','images/zara_coat.jpg'],
        [4,'H&M',         'Women',       'M',  'Good',     'H&M Floral Dress. Perfect for summer.','380.00','images/hm_dress.jpg'],
        [2,'Tommy Hilfiger','Men',       'XL', 'Excellent','Tommy Hilfiger Cable Knit Sweater. Warm and stylish.','750.00','images/tommy_sweater.jpg'],
        [4,'Calvin Klein','Men',         'M',  'Like New', 'Calvin Klein Slim Fit Jeans. Dark wash.','890.00','images/ck_jeans.jpg'],
        [2,'Prada',       'Women',       'S',  'Excellent','Prada Silk Blouse. Luxurious fabric.','1850.00','images/prada_blouse.jpg'],
        [4,'Versace',     'Men',         'L',  'Good',     'Versace Print Shirt. Bold Baroque print.','2200.00','images/versace_shirt.jpg'],
        [2,'Chanel',      'Accessories', 'One Size','Excellent','Chanel Silk Scarf. Iconic design.','1500.00','images/chanel_scarf.jpg'],
        [4,'Louis Vuitton','Accessories','One Size','Good',  'Louis Vuitton Belt. Classic monogram.','950.00','images/lv_belt.jpg'],
        [2,'Converse',    'Footwear',    '42', 'Good',     'Converse Chuck Taylor High Tops. Classic white.','450.00','images/converse_hi.jpg'],
        [4,'Dr. Martens', 'Footwear',    '40', 'Excellent','Dr. Martens 1460 Boots. Cherry red.','1800.00','images/docs_boots.jpg'],
        [2,'Vans',        'Footwear',    '44', 'Like New', 'Vans Old Skool. Black and white.','650.00','images/vans_oldskool.jpg'],
        [4,'Diesel',      'Men',         'L',  'Good',     'Diesel Leather Jacket. Genuine leather.','2800.00','images/diesel_leather.jpg'],
        [2,'Armani',      'Men',         'M',  'Excellent','Armani Dress Shirt. Perfect for formal events.','780.00','images/armani_shirt.jpg'],
        [4,'Guess',       'Women',       'S',  'Good',     'Guess Denim Skirt. Vintage wash.','420.00','images/guess_skirt.jpg'],
        [2,'Michael Kors','Accessories', 'One Size','Like New','Michael Kors Handbag. Signature print.','1350.00','images/mk_bag.jpg'],
        [4,'Coach',       'Accessories', 'One Size','Excellent','Coach Leather Wallet. Classic brown.','580.00','images/coach_wallet.jpg'],
        [2,'Lacoste',     'Men',         'M',  'Good',     'Lacoste Polo Shirt. Classic red.','550.00','images/lacoste_polo.jpg'],
        [4,'Hugo Boss',   'Men',         'L',  'Excellent','Hugo Boss Suit Jacket. Navy tailored fit.','1950.00','images/boss_jacket.jpg'],
        [2,'Fendi',       'Women',       'M',  'Like New', 'Fendi Printed T-Shirt. Designer logo.','720.00','images/fendi_tshirt.jpg'],
        [4,'Balenciaga',  'Footwear',    '39', 'Excellent','Balenciaga Speed Trainers. RedRunners Rare find.','3500.00','images/balenciaga_speed.jpg'],
        [2,'Off-White',   'Men',         'L',  'Good',     'Off-White Hoodie. Signature arrow design.','1680.00','images/offwhite_hoodie.jpg'],
        [4,'Givenchy',    'Women',       'S',  'Like New', 'Givenchy Evening Dress. Black, elegant.','2850.00','images/givenchy_dress.jpg'],
        [2,'Dolce & Gabbana','Men',      'M',  'Excellent','D&G Printed Blazer. Bold floral, Italian craft.','2400.00','images/dg_blazer.jpg'],
        [4,'Puma',        'Men',         'M',  'Good',     'Puma Tracksuit. Comfortable sportswear.','600.00','images/puma_tracksuit.jpg'],
    ];
    $stmtC = $conn->prepare("INSERT INTO tblClothing (sellerID,brand,category,size,clothingCondition,description,price,imagePath,status) VALUES (?,?,?,?,?,?,?,?,?)");
    foreach ($clothing as $c) {
        $status = 'available';
        $stmtC->bind_param('sssssssss',$c[0],$c[1],$c[2],$c[3],$c[4],$c[5],$c[6],$c[7],$status);
        $stmtC->execute();
    }
    echo "✓ Inserted " . count($clothing) . " clothing items\n";

    // ── Seed orders ───────────────────────────────────────────
    $orders = [[1,1,'3350.00','delivered'],[5,5,'1800.00','processing']];
    $stmtO = $conn->prepare("INSERT INTO tblOrder (buyerID,addressID,totalAmount,orderStatus) VALUES (?,?,?,?)");
    foreach ($orders as $o) {
        $stmtO->bind_param('ssss',$o[0],$o[1],$o[2],$o[3]);
        $stmtO->execute();
    }
    $oiData = [[1,1,'2500.00'],[1,2,'850.00'],[2,17,'1800.00']];
    $stmtOI = $conn->prepare("INSERT INTO tblOrderItem (orderID,clothingID,priceAtPurchase) VALUES (?,?,?)");
    foreach ($oiData as $oi) {
        $stmtOI->bind_param('sss',$oi[0],$oi[1],$oi[2]);
        $stmtOI->execute();
    }
    echo "✓ Inserted orders and order items\n";

    // ── Seed messages ─────────────────────────────────────────
    $msgs = [
        [1,2,1,'Hi! Is the Gucci blazer still available?',0,0],
        [2,1,1,'Yes, it is! Are you interested?',0,0],
        [1,2,1,'Definitely! Can you tell me more about the condition?',0,0],
        [3,null,null,'Welcome to Pastimes! Check out new arrivals.',0,1],
    ];
    $stmtM = $conn->prepare("INSERT INTO tblMessage (senderID,receiverID,clothingID,messageText,isRead,isBroadcast) VALUES (?,?,?,?,?,?)");
    foreach ($msgs as $m) {
        $stmtM->bind_param('ssssss',$m[0],$m[1],$m[2],$m[3],$m[4],$m[5]);
        $stmtM->execute();
    }
    echo "✓ Inserted messages\n";

    // ── Seed wishlist ─────────────────────────────────────────
    $wishes = [[1,3],[1,11],[5,1],[5,19]];
    $stmtW = $conn->prepare("INSERT INTO tblWishlist (userID,clothingID) VALUES (?,?)");
    foreach ($wishes as $w) {
        $stmtW->bind_param('ss',$w[0],$w[1]);
        $stmtW->execute();
    }
    echo "✓ Inserted wishlist items\n";

    $conn->close();

    echo "\n========================================\n";
    echo "✓ DATABASE SETUP COMPLETE!\n";
    echo "========================================\n";
    echo "\nDefault Login Credentials:\n";
    echo "  Admin:  username='admin'     password='AdminPass1'\n";
    echo "  Buyer:  username='johndoe'   password='Password123'\n";
    echo "  Seller: username='janesmith' password='SecurePass1'\n";

} catch (mysqli_sql_exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . " | File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
echo "</pre>";
?>