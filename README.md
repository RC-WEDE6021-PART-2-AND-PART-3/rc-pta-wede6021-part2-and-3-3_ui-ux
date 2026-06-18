# PASTIMES — Online Second-Hand Clothing Store

> **"Buy. Sell. Discover. Sustainably."**  
> A fully functional PHP/MySQL web application for buying and selling pre-loved clothing in South Africa.

---

## 👥 Project Team

| Name | Role |
|---|---|
| **Tshiamo Mosetlha** | Lead Developer & UI Designer |
| **Zandile Selao** | Developer |
| **Muhluri Nkuna** | Developer |

**Institution:** IIE Rosebank College — Pretoria, South Africa  
**Module:** WEDE5020/6021 — Web Development  
**Year:** 2026

---

## 📋 Table of Contents

1. [Project Overview](#project-overview)
2. [Features](#features)
3. [User Roles](#user-roles)
4. [Project Structure](#project-structure)
5. [Database Schema](#database-schema)
6. [Setup & Installation](#setup--installation)
7. [How to Use](#how-to-use)
8. [Technology Stack](#technology-stack)
9. [Security](#security)
10. [Pages Reference](#pages-reference)

---

## Project Overview

**Pastimes** is a second-hand clothing marketplace built for South African users. It allows customers to browse and purchase pre-loved fashion items, enables verified sellers to list their clothing for sale, and provides administrators with full control over the platform — including user verification, listing management, order tracking, and broadcast messaging.

The platform is built entirely with **PHP and MySQL**, with no external frameworks. It uses a dark, gold-accented design system inspired by premium fashion retail.

---

## Features

### For Everyone (Public)
- Splash screen welcome page on entry
- Browse all available clothing listings with filters (category, size, gender, price, condition)
- View individual product detail pages
- Register an account (as Buyer or Seller)
- Login with role-based redirection

### For Buyers
- Dedicated Buyer Dashboard
- Add items to cart and checkout
- Edit and manage shopping cart
- Save items to wishlist
- View order history
- Message sellers directly
- Request upgrade to Seller status

### For Sellers
- Dedicated Seller Dashboard
- List new clothing items for sale (brand, name, description, category, size, color, condition, price, gender, image)
- Manage and update their own listings
- Mark items as sold or remove listings
- View messages from buyers
- View earnings and listing stats
- Must be verified by Administrator before listing is active

### For Administrators
- Dedicated Admin Dashboard with full stats overview
- Verify and approve new Seller registrations
- Add, update, and delete any user
- Add, update, and delete any clothing listing
- View and manage all orders
- Broadcast messages to all users on the platform
- Full platform oversight

---

## User Roles

Pastimes has **3 user roles**. The role is chosen at registration and controls which dashboard the user is sent to after login.

| Role | Dashboard | Key Permissions |
|---|---|---|
| **Buyer** | `buyer/dashboard.php` | Browse, cart, checkout, wishlist, messages |
| **Seller** | `seller/dashboard.php` | List items, manage listings, messages, earnings |
| **Administrator** | `admin/index.php` | Full control — users, listings, orders, broadcasts |

> **Note:** Administrators cannot self-register. Admin accounts are created directly in the database. New Seller accounts must be verified by an Admin before they can list items.

---

## Project Structure

```
Pastimes/
│
├── splash.php                  ← Welcome splash screen (entry point)
├── index.php                   ← Public homepage
├── login.php                   ← Login page (role-based redirect)
├── register.php                ← Registration page (Buyer or Seller)
├── logout.php                  ← Session destroy & redirect
├── browse.php                  ← Browse all listings with filters
├── product.php                 ← Single product detail page
├── cart.php                    ← Shopping cart
├── checkout.php                ← Checkout / order placement
├── messages.php                ← User messaging
├── wishlist.php                ← Saved/wishlisted items
├── about.php                   ← About the platform & team
├── sell.php                    ← Sell item form (verified sellers only)
│
├── buyer/
│   └── dashboard.php           ← Buyer dashboard
│
├── seller/
│   └── dashboard.php           ← Seller dashboard
│
├── admin/
│   ├── index.php               ← Admin dashboard (stats overview)
│   ├── users.php               ← Manage all users
│   ├── listings.php            ← Manage all clothing listings
│   ├── orders.php              ← Manage all orders
│   └── broadcast.php          ← Send broadcast messages to all users
│
├── includes/
│   ├── DBConn.php              ← Database connection (getConnection())
│   ├── createTable.php         ← Drop & recreate tblUser, load from userData.txt
│   ├── loadClothingStore.php   ← Full DB setup script (all tables)
│   ├── header.php              ← Global site header / navbar
│   └── footer.php              ← Global site footer
│
├── ajax/
│   ├── cart.php                ← AJAX cart add/remove/update handler
│   └── wishlist.php            ← AJAX wishlist add/remove/check handler
│
├── css/
│   └── style.css               ← Full Pastimes design system (CSS variables, components)
│
├── js/
│   └── main.js                 ← Client-side interactivity
│
├── images/
│   ├── Nike_Tshirt.png         ← Sample product image
│   └── products/               ← Uploaded product images (auto-created)
│
├── data/
│   ├── userData.txt            ← Seed data: 5+ fictitious users
│   ├── clothingData.txt        ← Seed data: clothing items
│   └── myClothingStore.sql     ← Full exported database (DDL + 30 rows per table)
│
└── README.md                   ← This file
```

---

## Database Schema

**Database name:** `ClothingStore`

### tblUser
| Column | Type | Description |
|---|---|---|
| userID | INT (PK, AUTO_INCREMENT) | Unique user ID |
| fullName | VARCHAR(100) | User's full name |
| username | VARCHAR(50) | Unique alphanumeric username |
| email | VARCHAR(150) | Unique email address |
| password | VARCHAR(255) | Bcrypt hashed password |
| role | ENUM('buyer','seller','admin') | User role |
| seller_status | ENUM('pending','verified','rejected') | Seller verification status |
| isVerified | TINYINT(1) | 1 = verified, 0 = pending |
| createdAt | DATETIME | Account creation timestamp |

### tblClothing
| Column | Type | Description |
|---|---|---|
| clothingID | INT (PK, AUTO_INCREMENT) | Unique listing ID |
| sellerID | INT (FK → tblUser) | Seller who posted the listing |
| brand | VARCHAR(100) | Brand name (e.g. Nike, Levi's) |
| name | VARCHAR(150) | Item name |
| description | TEXT | Full item description |
| category | ENUM | Tops, Bottoms, Dresses, Outerwear, Footwear, Accessories |
| size | ENUM | XS, S, M, L, XL, XXL, One Size |
| color | VARCHAR(50) | Item color |
| clothingCondition | ENUM | Like New, Excellent, Good, Fair |
| price | DECIMAL(10,2) | Price in ZAR |
| gender | ENUM | Women, Men, Unisex |
| imagePath | VARCHAR(255) | Path to product image |
| status | ENUM | Available, Sold, Removed |
| dateAdded | DATETIME | Date listed |

### tblOrder
| Column | Type | Description |
|---|---|---|
| orderID | INT (PK, AUTO_INCREMENT) | Unique order ID |
| buyerID | INT (FK → tblUser) | Buyer who placed the order |
| clothingID | INT (FK → tblClothing) | Item ordered |
| quantity | INT | Quantity ordered |
| totalPrice | DECIMAL(10,2) | Total price at time of order |
| orderStatus | ENUM | Pending, Confirmed, Shipped, Delivered, Cancelled |
| orderDate | DATETIME | Date order was placed |

### tblCart
| Column | Type | Description |
|---|---|---|
| cartID | INT (PK, AUTO_INCREMENT) | Unique cart entry ID |
| userID | INT (FK → tblUser) | User who owns the cart |
| clothingID | INT (FK → tblClothing) | Item in cart |
| quantity | INT | Quantity |
| addedAt | DATETIME | Time added to cart |

### tblWishlist
| Column | Type | Description |
|---|---|---|
| wishlistID | INT (PK, AUTO_INCREMENT) | Unique wishlist entry ID |
| userID | INT (FK → tblUser) | User who saved the item |
| clothingID | INT (FK → tblClothing) | Saved item |
| dateAdded | DATETIME | Date saved |

### tblMessage
| Column | Type | Description |
|---|---|---|
| messageID | INT (PK, AUTO_INCREMENT) | Unique message ID |
| senderID | INT (FK → tblUser) | Message sender |
| receiverID | INT (FK → tblUser, nullable) | Message receiver (NULL = broadcast) |
| clothingID | INT (FK → tblClothing, nullable) | Related listing (if any) |
| messageText | TEXT | Message content |
| isRead | TINYINT(1) | 0 = unread, 1 = read |
| isBroadcast | TINYINT(1) | 1 = sent to all users by admin |
| sentAt | DATETIME | Timestamp sent |

---

## Setup & Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with `mod_rewrite` enabled (XAMPP / WAMP / LAMP)
- phpMyAdmin (recommended for database setup)

### Step 1 — Set up the database

1. Open **phpMyAdmin**
2. Create a new database called `ClothingStore`
3. Import the file `data/myClothingStore.sql` — this creates all tables and loads seed data

**OR** navigate to `includes/loadClothingStore.php` in your browser once the project is running. This script will drop and recreate all tables automatically.

### Step 2 — Configure the database connection

Open `includes/DBConn.php` and confirm the credentials match your local setup:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // Your MySQL password
define('DB_NAME', 'ClothingStore');
```

### Step 3 — Place the project in your server root

Copy the `Pastimes/` folder to:
- **XAMPP:** `C:/xampp/htdocs/Pastimes/`
- **WAMP:** `C:/wamp64/www/Pastimes/`
- **LAMP:** `/var/www/html/Pastimes/`

### Step 4 — Run the application

Open your browser and go to:
```
http://localhost/Pastimes/splash.php
```

The splash screen is the entry point. From there users can login, register, or browse.

### Step 5 — Create an Admin account

Admin accounts cannot be registered through the UI. Create one directly in phpMyAdmin:

```sql
INSERT INTO tblUser (fullName, username, email, password, role, isVerified, seller_status)
VALUES (
  'Admin User',
  'admin',
  'admin@pastimes.co.za',
  '$2y$10$YourBcryptHashHere',   -- use password_hash() to generate
  'admin',
  1,
  'verified'
);
```

Or run `includes/createTable.php` in the browser — it loads seed users from `data/userData.txt` which includes a default admin entry.

---

## How to Use

### Registering

1. Go to `splash.php` → click **Create Account**
2. Fill in Full Name, Username, Email, Password
3. Choose your role: **Buyer** or **Seller**
4. Accept Terms of Service → Submit
5. If registering as a Seller, your account will be **pending** until an Administrator verifies you

### Logging In

1. Go to `login.php`
2. Enter username and password
3. You are redirected to your role-specific dashboard:
   - Buyer → `buyer/dashboard.php`
   - Seller → `seller/dashboard.php`
   - Admin → `admin/index.php`

### As a Buyer

- Use **Browse** to filter and find items
- Click a product → view details → **Add to Cart**
- Go to **Cart** → adjust quantities → **Checkout**
- Save items to **Wishlist** for later
- Message sellers about items directly

### As a Seller

- Go to your **Seller Dashboard**
- Click **List New Item** → fill in all item details, upload photo
- Manage your active listings — edit, mark sold, or remove
- Check messages from buyers
- Track your earnings and listing stats

> Seller accounts must be verified by an Admin before listings go live.

### As an Administrator

- Full **Admin Dashboard** with platform stats
- **Users tab** — add, update, delete users; verify pending Sellers
- **Listings tab** — manage all clothing listings across the platform
- **Orders tab** — view and update order statuses
- **Broadcast tab** — send a message to all users on the platform

---

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP 7.4+ |
| Database | MySQL via MySQLi (prepared statements) |
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Icons | Font Awesome 6.5.1 (CDN) |
| Password Hashing | PHP `password_hash()` / `password_verify()` — Bcrypt |
| Session Management | PHP native sessions |
| AJAX | Vanilla JS `fetch()` for cart and wishlist |
| Server | Apache (XAMPP / WAMP / LAMP) |

**No external PHP frameworks. No React. No Tailwind. Pure PHP.**

---

## Security

- All passwords are hashed using **PHP Bcrypt** (`PASSWORD_DEFAULT`) — never stored as plain text
- All database queries use **MySQLi prepared statements** with `bind_param()` — protected against SQL injection
- All user-supplied output is escaped with `htmlspecialchars()` — protected against XSS
- Session-based authentication — role is stored in `$_SESSION['role']` and validated on every protected page
- Role-based access control enforced at the top of every dashboard and admin page — unauthorized users are redirected immediately
- File uploads validated by MIME type and extension before being saved
- Admin pages are completely inaccessible to non-admin sessions

---

## Pages Reference

| File | Access | Description |
|---|---|---|
| `splash.php` | Public | Welcome entry screen |
| `index.php` | Public | Homepage with featured listings |
| `browse.php` | Public | Browse & filter all listings |
| `product.php` | Public | Single product detail view |
| `login.php` | Public | Login form |
| `register.php` | Public | Registration with role selector |
| `logout.php` | Logged in | Destroys session, redirects to splash |
| `about.php` | Public | About Pastimes & team |
| `sell.php` | Verified Seller | List a new item for sale |
| `cart.php` | Buyer | Shopping cart management |
| `checkout.php` | Buyer | Place order |
| `wishlist.php` | Logged in | Saved items |
| `messages.php` | Logged in | Inbox & conversations |
| `buyer/dashboard.php` | Buyer | Buyer home dashboard |
| `seller/dashboard.php` | Seller | Seller home dashboard |
| `admin/index.php` | Admin | Admin dashboard |
| `admin/users.php` | Admin | Manage users |
| `admin/listings.php` | Admin | Manage listings |
| `admin/orders.php` | Admin | Manage orders |
| `admin/broadcast.php` | Admin | Broadcast messaging |
| `includes/DBConn.php` | System | Database connection helper |
| `includes/createTable.php` | System | Drops & recreates tblUser from userData.txt |
| `includes/loadClothingStore.php` | System | Full DB setup — all tables + seed data |
| `includes/header.php` | System | Shared site header |
| `includes/footer.php` | System | Shared site footer |
| `ajax/cart.php` | System | AJAX cart handler |
| `ajax/wishlist.php` | System | AJAX wishlist handler |
| `data/myClothingStore.sql` | System | Full database export for submission |

---

## Data Files (Seed Data)

`data/userData.txt` contains at least 5 fictitious users in the format:

```
John Doe    j.doe@abc.co.za    29ef52e7563626a96cea7f4b4085c124
Jane Smith  j.smith@abc.co.za  29ef52e7563626a96cea7f4b4085c124
...
```

`data/clothingData.txt` contains seed clothing listings loaded into `tblClothing`.

---

## Product Images

Place product images inside the `images/` folder. The system references them as:

```
images/Nike_Tshirt.png
images/products/product_xyz.jpg   ← uploaded via sell form
images/products/default.jpg       ← fallback if no image uploaded
```

Supported formats: **JPG, PNG, WebP**

---

## POE Submission Checklist

- [x] Splash screen welcoming the user on entry
- [x] 3 user roles: Buyer, Seller, Administrator
- [x] Role selected at registration
- [x] Role-based login redirect to 3 separate dashboards
- [x] Login uses hashed password comparison (Bcrypt)
- [x] Sticky form — displays entered data on validation failure
- [x] Registration information stored in MySQL database
- [x] All registration fields are required (HTML5 + PHP validation)
- [x] Admin can verify new Seller registrations
- [x] Admin can add, update, delete users
- [x] Admin can add, update, delete listings
- [x] Buyer can select items and add to cart
- [x] Buyer can edit items in cart and continue shopping
- [x] Seller can send a request to sell clothing (description, image, brand)
- [x] Admin communicates with sellers and buyers via broadcast
- [x] `DBConn.php` — connection file included in all scripts
- [x] `createTable.php` — drops and recreates tblUser, loads userData.txt
- [x] `loadClothingStore.php` — creates all tables if they do not exist
- [x] `myClothingStore.sql` — full database export with DDL and 30+ rows per table
- [x] Visually appealing, easy-to-navigate design
- [x] PHP only — no frameworks

---

*Pastimes — IIE Rosebank College | Web Development 2026*  
*Tshiamo Mosetlha · Zandile Selao · Muhluri Nkuna*
