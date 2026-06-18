-- ============================================================
-- PASTIMES — myClothingStore.sql
-- Full DDL + Data Export | ClothingStore Database
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `ClothingStore`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ClothingStore`;

-- ── Drop tables ──────────────────────────────────────────
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `tblWishlist`;
DROP TABLE IF EXISTS `tblMessage`;
DROP TABLE IF EXISTS `tblOrderItem`;
DROP TABLE IF EXISTS `tblOrder`;
DROP TABLE IF EXISTS `tblClothing`;
DROP TABLE IF EXISTS `tblDeliveryAddress`;
DROP TABLE IF EXISTS `tblUser`;
SET FOREIGN_KEY_CHECKS = 1;

-- ── tblUser ──────────────────────────────────────────────
CREATE TABLE `tblUser` (
  `userID`        INT            NOT NULL AUTO_INCREMENT,
  `fullName`      VARCHAR(100)   NOT NULL,
  `username`      VARCHAR(50)    NOT NULL,
  `email`         VARCHAR(150)   NOT NULL,
  `password`      VARCHAR(255)   NOT NULL,
  `role`          ENUM('buyer','seller','admin') NOT NULL DEFAULT 'buyer',
  `seller_status` ENUM('none','pending','verified','rejected') NOT NULL DEFAULT 'none',
  `created_at`    DATETIME       DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userID`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblUser` (`fullName`, `username`, `email`, `password`, `role`, `seller_status`) VALUES
('John Doe',      'johndoe',   'j.doe@abc.co.za',           '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer',  'none'),
('Jane Smith',    'janesmith', 'jane.smith@email.co.za',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller', 'verified'),
('Admin User',    'admin',     'admin@pastimes.co.za',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',  'verified'),
('Thabo Mokoena', 'thabom',    'thabo@email.co.za',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller', 'verified'),
('Lerato Nkosi',  'leraton',   'lerato@email.co.za',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer',  'none'),
('Sipho Dlamini', 'siphod',    'sipho@email.co.za',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer',  'none');

-- ── tblDeliveryAddress ────────────────────────────────────
CREATE TABLE `tblDeliveryAddress` (
  `addressID`     INT          NOT NULL AUTO_INCREMENT,
  `userID`        INT          NOT NULL,
  `streetAddress` VARCHAR(200) NOT NULL,
  `suburb`        VARCHAR(100) DEFAULT NULL,
  `city`          VARCHAR(100) NOT NULL,
  `province`      VARCHAR(100) DEFAULT NULL,
  `postalCode`    VARCHAR(10)  DEFAULT NULL,
  `isDefault`     TINYINT(1)   DEFAULT 0,
  PRIMARY KEY (`addressID`),
  KEY `userID` (`userID`),
  CONSTRAINT `fk_addr_user` FOREIGN KEY (`userID`) REFERENCES `tblUser` (`userID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblDeliveryAddress` (`userID`,`streetAddress`,`suburb`,`city`,`province`,`postalCode`,`isDefault`) VALUES
(1,'123 Main Street','Sandton','Johannesburg','Gauteng','2196',1),
(2,'45 Beach Road','Sea Point','Cape Town','Western Cape','8005',1),
(3,'78 Church Street','Hatfield','Pretoria','Gauteng','0028',1),
(4,'22 Florida Road','Morningside','Durban','KwaZulu-Natal','4001',1),
(5,'15 Long Street','CBD','Cape Town','Western Cape','8001',1);

-- ── tblClothing ───────────────────────────────────────────
CREATE TABLE `tblClothing` (
  `clothingID`        INT            NOT NULL AUTO_INCREMENT,
  `sellerID`          INT            NOT NULL,
  `brand`             VARCHAR(100)   NOT NULL,
  `category`          VARCHAR(50)    NOT NULL,
  `size`              VARCHAR(20)    NOT NULL,
  `clothingCondition` VARCHAR(30)    NOT NULL,
  `description`       TEXT           DEFAULT NULL,
  `price`             DECIMAL(10,2)  NOT NULL,
  `imagePath`         VARCHAR(255)   DEFAULT 'images/default.png',
  `status`            ENUM('available','sold','pending') DEFAULT 'available',
  `dateAdded`         DATETIME       DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`clothingID`),
  KEY `sellerID` (`sellerID`),
  CONSTRAINT `fk_cloth_seller` FOREIGN KEY (`sellerID`) REFERENCES `tblUser` (`userID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblClothing` (`sellerID`,`brand`,`category`,`size`,`clothingCondition`,`description`,`price`,`imagePath`,`status`) VALUES
(2,'Gucci','Men','M','Excellent','Vintage Gucci Blazer. Perfect for formal occasions.',2500.00,'images/gucci_blazer.jpg','available'),
(2,'Levi\'s','Women','S','Good','Classic Levi\'s Denim Jacket. Timeless wardrobe essential.',850.00,'images/levis_jacket.jpg','available'),
(4,'Burberry','Women','L','Like New','Burberry Trench Coat. Iconic check lining.',4200.00,'images/burberry_coat.jpg','available'),
(4,'Ralph Lauren','Men','S','Good','Ralph Lauren Polo Shirt. Classic fit, navy.',450.00,'images/rl_polo.jpg','available'),
(2,'Nike','Men','L','Excellent','Nike Vintage Windbreaker. Retro 90s style.',680.00,'images/nike_windbreaker.jpg','available'),
(4,'Adidas','Men','M','Good','Adidas Track Jacket. Classic three stripes.',520.00,'images/adidas_track.jpg','available'),
(2,'Zara','Women','S','Like New','Zara Wool Coat. Elegant winter coat.',1200.00,'images/zara_coat.jpg','available'),
(4,'H&M','Women','M','Good','H&M Floral Dress. Perfect for summer.',380.00,'images/hm_dress.jpg','available'),
(2,'Tommy Hilfiger','Men','XL','Excellent','Tommy Hilfiger Cable Knit Sweater. Warm and stylish.',750.00,'images/tommy_sweater.jpg','available'),
(4,'Calvin Klein','Men','M','Like New','Calvin Klein Slim Fit Jeans. Dark wash.',890.00,'images/ck_jeans.jpg','available'),
(2,'Prada','Women','S','Excellent','Prada Silk Blouse. Luxurious fabric.',1850.00,'images/prada_blouse.jpg','available'),
(4,'Versace','Men','L','Good','Versace Print Shirt. Bold Baroque print.',2200.00,'images/versace_shirt.jpg','available'),
(2,'Chanel','Accessories','One Size','Excellent','Chanel Silk Scarf. Iconic design.',1500.00,'images/chanel_scarf.jpg','available'),
(4,'Louis Vuitton','Accessories','One Size','Good','Louis Vuitton Belt. Classic monogram.',950.00,'images/lv_belt.jpg','available'),
(2,'Converse','Footwear','42','Good','Converse Chuck Taylor High Tops. Classic white.',450.00,'images/converse_hi.jpg','available'),
(4,'Dr. Martens','Footwear','40','Excellent','Dr. Martens 1460 Boots. Cherry red.',1800.00,'images/docs_boots.jpg','available'),
(2,'Vans','Footwear','44','Like New','Vans Old Skool. Black and white.',650.00,'images/vans_oldskool.jpg','available'),
(4,'Diesel','Men','L','Good','Diesel Leather Jacket. Genuine leather.',2800.00,'images/diesel_leather.jpg','available'),
(2,'Armani','Men','M','Excellent','Armani Dress Shirt. Perfect for formal events.',780.00,'images/armani_shirt.jpg','available'),
(4,'Guess','Women','S','Good','Guess Denim Skirt. Vintage wash.',420.00,'images/guess_skirt.jpg','available'),
(2,'Michael Kors','Accessories','One Size','Like New','Michael Kors Handbag. Signature print.',1350.00,'images/mk_bag.jpg','available'),
(4,'Coach','Accessories','One Size','Excellent','Coach Leather Wallet. Classic brown.',580.00,'images/coach_wallet.jpg','available'),
(2,'Lacoste','Men','M','Good','Lacoste Polo Shirt. Classic green.',550.00,'images/lacoste_polo.jpg','available'),
(4,'Hugo Boss','Men','L','Excellent','Hugo Boss Suit Jacket. Navy tailored fit.',1950.00,'images/boss_jacket.jpg','available'),
(2,'Fendi','Women','M','Like New','Fendi Printed T-Shirt. Designer logo.',720.00,'images/fendi_tshirt.jpg','available'),
(4,'Balenciaga','Footwear','39','Excellent','Balenciaga Speed Trainers. Rare find.',3500.00,'images/balenciaga_speed.jpg','available'),
(2,'Off-White','Men','L','Good','Off-White Hoodie. Signature arrow design.',1680.00,'images/offwhite_hoodie.jpg','available'),
(4,'Givenchy','Women','S','Like New','Givenchy Evening Dress. Black, elegant.',2850.00,'images/givenchy_dress.jpg','available'),
(2,'Dolce & Gabbana','Men','M','Excellent','D&G Printed Blazer. Bold floral, Italian craft.',2400.00,'images/dg_blazer.jpg','available'),
(4,'Puma','Men','M','Good','Puma Tracksuit. Comfortable sportswear.',600.00,'images/puma_tracksuit.jpg','available');

-- ── tblOrder ──────────────────────────────────────────────
CREATE TABLE `tblOrder` (
  `orderID`     INT           NOT NULL AUTO_INCREMENT,
  `buyerID`     INT           NOT NULL,
  `addressID`   INT           DEFAULT NULL,
  `totalAmount` DECIMAL(10,2) NOT NULL,
  `orderStatus` ENUM('placed','processing','shipped','delivered','cancelled') DEFAULT 'placed',
  `orderDate`   DATETIME      DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`orderID`),
  KEY `buyerID` (`buyerID`),
  KEY `addressID` (`addressID`),
  CONSTRAINT `fk_order_buyer`   FOREIGN KEY (`buyerID`)   REFERENCES `tblUser`            (`userID`)   ON DELETE CASCADE,
  CONSTRAINT `fk_order_address` FOREIGN KEY (`addressID`) REFERENCES `tblDeliveryAddress` (`addressID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblOrder` (`buyerID`,`addressID`,`totalAmount`,`orderStatus`) VALUES
(1,1,3350.00,'delivered'),
(5,5,1800.00,'processing');

-- ── tblOrderItem ──────────────────────────────────────────
CREATE TABLE `tblOrderItem` (
  `orderItemID`     INT           NOT NULL AUTO_INCREMENT,
  `orderID`         INT           NOT NULL,
  `clothingID`      INT           NOT NULL,
  `priceAtPurchase` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`orderItemID`),
  KEY `orderID` (`orderID`),
  KEY `clothingID` (`clothingID`),
  CONSTRAINT `fk_oi_order`    FOREIGN KEY (`orderID`)    REFERENCES `tblOrder`   (`orderID`)    ON DELETE CASCADE,
  CONSTRAINT `fk_oi_clothing` FOREIGN KEY (`clothingID`) REFERENCES `tblClothing`(`clothingID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblOrderItem` (`orderID`,`clothingID`,`priceAtPurchase`) VALUES
(1,1,2500.00),
(1,2,850.00),
(2,17,1800.00);

-- ── tblMessage ────────────────────────────────────────────
CREATE TABLE `tblMessage` (
  `messageID`   INT          NOT NULL AUTO_INCREMENT,
  `senderID`    INT          NOT NULL,
  `receiverID`  INT          DEFAULT NULL,
  `clothingID`  INT          DEFAULT NULL,
  `messageText` TEXT         NOT NULL,
  `isRead`      TINYINT(1)   DEFAULT 0,
  `isBroadcast` TINYINT(1)   DEFAULT 0,
  `sentAt`      DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`messageID`),
  KEY `senderID`   (`senderID`),
  KEY `receiverID` (`receiverID`),
  KEY `clothingID` (`clothingID`),
  CONSTRAINT `fk_msg_sender`   FOREIGN KEY (`senderID`)   REFERENCES `tblUser`    (`userID`)     ON DELETE CASCADE,
  CONSTRAINT `fk_msg_receiver` FOREIGN KEY (`receiverID`) REFERENCES `tblUser`    (`userID`)     ON DELETE SET NULL,
  CONSTRAINT `fk_msg_clothing` FOREIGN KEY (`clothingID`) REFERENCES `tblClothing`(`clothingID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblMessage` (`senderID`,`receiverID`,`clothingID`,`messageText`,`isRead`,`isBroadcast`) VALUES
(1,2,1,'Hi! Is the Gucci blazer still available?',1,0),
(2,1,1,'Yes, it is! Are you interested?',1,0),
(1,2,1,'Definitely! Can you tell me more about the condition?',0,0),
(3,NULL,NULL,'Welcome to Pastimes! Check out our new arrivals.',0,1);

-- ── tblWishlist ───────────────────────────────────────────
CREATE TABLE `tblWishlist` (
  `wishlistID` INT      NOT NULL AUTO_INCREMENT,
  `userID`     INT      NOT NULL,
  `clothingID` INT      NOT NULL,
  `dateAdded`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`wishlistID`),
  UNIQUE KEY `unique_wish` (`userID`,`clothingID`),
  KEY `userID`     (`userID`),
  KEY `clothingID` (`clothingID`),
  CONSTRAINT `fk_wish_user`    FOREIGN KEY (`userID`)     REFERENCES `tblUser`    (`userID`)     ON DELETE CASCADE,
  CONSTRAINT `fk_wish_clothing`FOREIGN KEY (`clothingID`) REFERENCES `tblClothing`(`clothingID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblWishlist` (`userID`,`clothingID`) VALUES
(1,3),(1,11),(5,1),(5,19);

COMMIT;

-- ============================================================
-- END OF myClothingStore.sql
-- Default Passwords (hashed with password_hash BCRYPT):
-- Admin:  username=admin      password=AdminPass1
-- Buyer:  username=johndoe    password=Password123
-- Seller: username=janesmith  password=SecurePass1
-- ============================================================