-- ========================================
-- PASTIMES Database Export
-- myClothingStore.sql
-- Database: ClothingStore
-- ========================================

-- Disable foreign key checks for safe table creation
SET FOREIGN_KEY_CHECKS = 0;

-- ========================================
-- Drop existing tables (in reverse dependency order)
-- ========================================
DROP TABLE IF EXISTS tblWishlist;
DROP TABLE IF EXISTS tblMessage;
DROP TABLE IF EXISTS tblOrderItem;
DROP TABLE IF EXISTS tblOrder;
DROP TABLE IF EXISTS tblClothing;
DROP TABLE IF EXISTS tblDeliveryAddress;
DROP TABLE IF EXISTS tblUser;

-- ========================================
-- Create Database
-- ========================================
CREATE DATABASE IF NOT EXISTS ClothingStore;
USE ClothingStore;

-- ========================================
-- Table: tblUser (Parent Table - No Dependencies)
-- Stores user account information
-- ========================================
CREATE TABLE IF NOT EXISTS tblUser (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    fullName VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    userType ENUM('Buyer', 'Seller', 'Admin') DEFAULT 'Buyer',
    isVerified TINYINT(1) DEFAULT 0,
    dateRegistered DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastLogin DATETIME NULL,
    profileImage VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    bio TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- Table: tblDeliveryAddress (Depends on tblUser)
-- Stores delivery addresses for users
-- ========================================
CREATE TABLE IF NOT EXISTS tblDeliveryAddress (
    addressID INT AUTO_INCREMENT PRIMARY KEY,
    streetAddress VARCHAR(255) NOT NULL,
    suburb VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    postalCode VARCHAR(10) NOT NULL,
    userID INT NOT NULL,
    isDefault TINYINT(1) DEFAULT 0,
    FOREIGN KEY (userID) REFERENCES tblUser(userID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- Table: tblClothing (Depends on tblUser)
-- Stores clothing item listings
-- ========================================
CREATE TABLE IF NOT EXISTS tblClothing (
    clothingID INT AUTO_INCREMENT PRIMARY KEY,
    sellerID INT NOT NULL,
    brand VARCHAR(100) NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('Tops', 'Bottoms', 'Dresses', 'Outerwear', 'Footwear', 'Accessories') NOT NULL,
    size VARCHAR(20) NOT NULL,
    color VARCHAR(50) DEFAULT NULL,
    clothingCondition ENUM('Like New', 'Excellent', 'Good', 'Fair') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    gender ENUM('Men', 'Women', 'Unisex') NOT NULL,
    imagePath VARCHAR(255) DEFAULT 'images/products/default.jpg',
    dateAdded DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Available', 'Sold', 'Reserved') DEFAULT 'Available',
    views INT DEFAULT 0,
    FOREIGN KEY (sellerID) REFERENCES tblUser(userID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- Table: tblOrder (Depends on tblUser, tblDeliveryAddress)
-- Stores order information
-- ========================================
CREATE TABLE IF NOT EXISTS tblOrder (
    orderID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    deliveryAddressID INT NOT NULL,
    totalAmount DECIMAL(10, 2) NOT NULL,
    orderDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    paymentMethod VARCHAR(50) NOT NULL,
    trackingNumber VARCHAR(100) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    FOREIGN KEY (userID) REFERENCES tblUser(userID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (deliveryAddressID) REFERENCES tblDeliveryAddress(addressID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- Table: tblOrderItem (Depends on tblOrder, tblClothing)
-- Stores individual items within an order
-- ========================================
CREATE TABLE IF NOT EXISTS tblOrderItem (
    orderItemID INT AUTO_INCREMENT PRIMARY KEY,
    orderID INT NOT NULL,
    clothingID INT NOT NULL,
    priceAtPurchase DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (orderID) REFERENCES tblOrder(orderID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (clothingID) REFERENCES tblClothing(clothingID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- Table: tblMessage (Depends on tblUser)
-- Stores messages between buyers and sellers
-- ========================================
CREATE TABLE IF NOT EXISTS tblMessage (
    messageID INT AUTO_INCREMENT PRIMARY KEY,
    senderID INT NOT NULL,
    receiverID INT NOT NULL,
    messageContent TEXT NOT NULL,
    dateSent DATETIME DEFAULT CURRENT_TIMESTAMP,
    isRead TINYINT(1) DEFAULT 0,
    relatedClothingID INT DEFAULT NULL,
    FOREIGN KEY (senderID) REFERENCES tblUser(userID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (receiverID) REFERENCES tblUser(userID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (relatedClothingID) REFERENCES tblClothing(clothingID) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- Table: tblWishlist (Depends on tblUser, tblClothing)
-- Stores user wishlists
-- ========================================
CREATE TABLE IF NOT EXISTS tblWishlist (
    wishlistID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    clothingID INT NOT NULL,
    dateAdded DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES tblUser(userID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (clothingID) REFERENCES tblClothing(clothingID) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_wishlist (userID, clothingID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- Insert Test Data
-- ========================================

-- Insert Users (5 fictitious entries)
-- Passwords are hashed versions of: Password123
INSERT INTO tblUser (fullName, username, email, password, userType, isVerified, dateRegistered) VALUES
('John Doe', 'johndoe', 'j.doe@abc.co.za', '$2y$10$29ef52e7563626a96cea7f4b4085c124', 'Buyer', 1, '2026-01-01 10:00:00'),
('Jane Smith', 'janesmith', 'jane.smith@email.co.za', '$2y$10$29ef52e7563626a96cea7f4b4085c124', 'Seller', 1, '2026-01-02 11:00:00'),
('Admin User', 'admin', 'admin@pastimes.co.za', '$2y$10$29ef52e7563626a96cea7f4b4085c124', 'Admin', 1, '2026-01-01 09:00:00'),
('Mike Johnson', 'mikej', 'mike.j@email.co.za', '$2y$10$29ef52e7563626a96cea7f4b4085c124', 'Seller', 1, '2026-01-03 12:00:00'),
('Sarah Williams', 'sarahw', 'sarah.w@email.co.za', '$2y$10$29ef52e7563626a96cea7f4b4085c124', 'Buyer', 0, '2026-01-04 13:00:00');

-- Insert Delivery Addresses (5 fictitious entries)
INSERT INTO tblDeliveryAddress (streetAddress, suburb, city, province, postalCode, userID, isDefault) VALUES
('123 Main Street', 'Sandton', 'Johannesburg', 'Gauteng', '2196', 1, 1),
('45 Beach Road', 'Sea Point', 'Cape Town', 'Western Cape', '8005', 2, 1),
('78 Church Street', 'Hatfield', 'Pretoria', 'Gauteng', '0028', 3, 1),
('22 Florida Road', 'Morningside', 'Durban', 'KwaZulu-Natal', '4001', 4, 1),
('15 Long Street', 'CBD', 'Cape Town', 'Western Cape', '8001', 5, 1);

-- Insert Clothing Items (5 fictitious entries)
INSERT INTO tblClothing (sellerID, brand, name, description, category, size, color, clothingCondition, price, gender, imagePath, status) VALUES
(2, 'Gucci', 'Vintage Gucci Blazer', 'Classic vintage Gucci blazer in excellent condition. Perfect for formal occasions.', 'Outerwear', 'M', 'Navy', 'Excellent', '2500.00', 'Men', 'images/products/default.jpg', 'Available'),
(2, 'Levis', 'Classic Levis Denim Jacket', 'Timeless denim jacket with authentic wear patterns. A wardrobe essential.', 'Outerwear', 'S', 'Blue', 'Good', '850.00', 'Women', 'images/products/default.jpg', 'Available'),
(4, 'Burberry', 'Burberry Trench Coat', 'Iconic Burberry trench coat in pristine condition. Features signature check lining.', 'Outerwear', 'L', 'Beige', 'Like New', '4200.00', 'Women', 'images/products/default.jpg', 'Available'),
(4, 'Ralph Lauren', 'Ralph Lauren Polo Shirt', 'Classic fit polo shirt in excellent condition. Perfect for casual wear.', 'Tops', 'S', 'White', 'Good', '450.00', 'Men', 'images/products/default.jpg', 'Available'),
(2, 'Prada', 'Prada Leather Handbag', 'Authentic Prada leather handbag with dust bag. Minor wear on handles.', 'Accessories', 'One Size', 'Black', 'Excellent', '3800.00', 'Women', 'images/products/default.jpg', 'Available');

-- Insert Orders (5 fictitious entries)
INSERT INTO tblOrder (userID, deliveryAddressID, totalAmount, orderDate, status, paymentMethod) VALUES
(1, 1, '2650.00', '2026-01-15 14:00:00', 'Delivered', 'EFT'),
(1, 1, '850.00', '2026-01-18 15:00:00', 'Shipped', 'Credit Card'),
(5, 5, '4350.00', '2026-01-20 16:00:00', 'Processing', 'EFT'),
(1, 1, '450.00', '2026-01-22 17:00:00', 'Pending', 'Credit Card'),
(5, 5, '3950.00', '2026-01-25 18:00:00', 'Delivered', 'EFT');

-- Insert Order Items (5 fictitious entries)
INSERT INTO tblOrderItem (orderID, clothingID, priceAtPurchase) VALUES
(1, 1, '2500.00'),
(2, 2, '850.00'),
(3, 3, '4200.00'),
(4, 4, '450.00'),
(5, 5, '3800.00');

-- Insert Messages (5 fictitious entries)
INSERT INTO tblMessage (senderID, receiverID, messageContent, dateSent, isRead, relatedClothingID) VALUES
(1, 2, 'Hi! Is the Gucci Blazer still available?', '2026-01-15 10:30:00', 1, 1),
(2, 1, 'Yes it is! Are you interested?', '2026-01-15 11:00:00', 1, 1),
(1, 2, 'Can you tell me more about the condition? Any stains or repairs?', '2026-01-15 11:30:00', 1, 1),
(2, 1, 'The blazer is in excellent condition. No stains, repairs, or damage. It has been dry cleaned and is ready to wear.', '2026-01-15 12:00:00', 1, 1),
(1, 2, 'That would be great! Also, is the price negotiable?', '2026-01-15 12:30:00', 0, 1);

-- Insert Wishlist Items (5 fictitious entries)
INSERT INTO tblWishlist (userID, clothingID, dateAdded) VALUES
(1, 1, '2026-01-10 09:00:00'),
(1, 3, '2026-01-12 10:00:00'),
(5, 2, '2026-01-14 11:00:00'),
(5, 4, '2026-01-16 12:00:00'),
(1, 5, '2026-01-18 13:00:00');

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- End of SQL Export
-- ========================================