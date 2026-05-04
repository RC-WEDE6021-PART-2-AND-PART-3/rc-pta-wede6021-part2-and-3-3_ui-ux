# PASTIMES — Online Second-Hand Clothing Store
> *Timeless Fashion at Exceptional Prices*

![Pastimes](https://img.shields.io/badge/Pastimes-v1.0-c9a84c?style=for-the-badge&labelColor=0a0e1a)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-ClothingStore-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![XAMPP](https://img.shields.io/badge/Server-XAMPP-FB7A24?style=for-the-badge&logo=apache&logoColor=white)

---

## 📋 Project Overview

**Pastimes** is a fully functional, peer-to-peer (P2P) second-hand branded clothing e-commerce web application built for the **WEDE6021 Portfolio of Evidence (POE)** at **The IIE Rosebank College**.

The platform enables South African buyers and sellers to trade quality pre-owned branded clothing in a secure, admin-verified environment. Pastimes was designed with a luxury navy-and-gold aesthetic — inspired by high-end fashion brands — to differentiate itself from generic classifieds and thrift platforms.

The application responds directly to the dual market opportunity identified in the Part 1 research:
- The global e-commerce market reached **$6.09 trillion in 2024**
- The global secondhand apparel market is projected to reach **$367 billion by 2029**

---

## 👥 Development Team

| Name | Student Number | Role |
|------|---------------|------|
| Zandile Selao| ST10451437 | Lead Developer & Project Lead |
| Muhluri Nkuna | ST10436981 | Frontend & UI Design |
| Tshiamo Mosetlha | ST10437226 | Backend & Database Design |

**Institution:** The Independent Institute of Education (IIE) — Rosebank College  
**Module:** WEDE6021 — Web Development  
**Submission:** Part 2 — Prototype  

---

## 🎯 Core Features

### User Features
- ✅ User registration with full validation (name, email, username, 8-char min password)
- ✅ Secure login using username and bcrypt-hashed password comparison
- ✅ PHP session management with role-based access control
- ✅ Sticky form on failed login (fields retain values, no re-typing needed)
- ✅ "User [Name] is logged in" display banner on successful login
- ✅ Delivery address management (residential and work addresses)
- ✅ User profile dashboard with order history and listings
- ✅ Settings page (notification preferences, account management)
- ✅ Secure logout with goodbye splash screen animation

### Buyer Features
- ✅ Browse full clothing catalogue with product images, prices, and condition badges
- ✅ Advanced filtering: brand, category, size, condition, price range
- ✅ Product detail page with full description and seller information
- ✅ Shopping cart (add, remove, view items — session-based)
- ✅ Checkout flow with delivery address selection and order confirmation
- ✅ Order history tracking (placed → processing → dispatched → delivered)
- ✅ Wishlist / Save for Later functionality
- ✅ Direct messaging to sellers about specific listings

### Seller Features
- ✅ Seller registration and admin verification workflow
- ✅ Listings visible only after admin approval (seller_status = 'approved')
- ✅ View own listings and their status (available / sold / removed)
- ✅ Receive and reply to buyer messages
- ✅ Automated sold item removal from active catalogue on purchase

### Administrator Features
- ✅ Restricted admin dashboard (role-based session check)
- ✅ Verify or reject new seller registrations
- ✅ Add, edit, and remove clothing listings on behalf of verified sellers
- ✅ Manage all orders and update delivery status
- ✅ Broadcast notifications to all registered users
- ✅ Full CRUD operations on all database tables
- ✅ View and manage all registered users

### Design & UX
- ✅ Animated falling clothing items background (beanies, shirts, shoes, heels — slow rain effect)
- ✅ Page loading splash screen with spinner
- ✅ Goodbye/logout splash screen with wave animation
- ✅ Mobile-first fully responsive design (desktop, tablet, mobile)
- ✅ Gold shimmer text effects and smooth hover transitions
- ✅ Luxury navy (#0a0e1a) and gold (#c9a84c) colour scheme throughout

---

## 🛠️ Technology Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| Frontend | HTML5 | Page structure and semantic markup |
| Frontend | CSS3 | Custom design system, animations, responsive layout |
| Frontend | JavaScript (Vanilla) | Interactivity, form validation, cart logic, splash screens |
| Backend | PHP (7.4+) | Server-side scripting, session management, business logic |
| Database | MySQL (MySQLi improved) | Relational data storage with FK constraints |
| Server | Apache via XAMPP | Local development environment |
| Fonts | Google Fonts | Cormorant Garamond (display) + Montserrat (body) |

> ⚠️ **No frameworks used.** No React, no Laravel, no Bootstrap. Pure PHP, HTML, CSS, and JavaScript as required by the IIE brief.

---

## 🗄️ Database Schema

**Database name:** `ClothingStore`

### Tables

#### `tblUser`
| Field | Type | Description |
|-------|------|-------------|
| userID | INT PK AUTO_INCREMENT | Unique user identifier |
| fullName | VARCHAR(100) | User's full name |
| email | VARCHAR(150) UNIQUE | Email address |
| username | VARCHAR(50) UNIQUE | Login username |
| password | VARCHAR(255) | BCRYPT hashed password |
| role | ENUM(buyer, seller, admin) | Access level |
| seller_status | ENUM(pending, approved, rejected) | Seller verification state |
| delivery_address | VARCHAR(255) | Default delivery address |
| created_at | DATETIME | Registration timestamp |

#### `tblDeliveryAddress`
| Field | Type | Description |
|-------|------|-------------|
| addressID | INT PK | Unique address identifier |
| userID | INT FK → tblUser | Linked user |
| addressType | ENUM(residential, work) | Address category |
| streetAddress | VARCHAR(200) | Street number and name |
| suburb | VARCHAR(100) | Suburb |
| city | VARCHAR(100) | City or town |
| postalCode | VARCHAR(10) | SA postal code |
| isDefault | TINYINT(1) | 1 = default address |

#### `tblClothing`
| Field | Type | Description |
|-------|------|-------------|
| clothingID | INT PK | Unique listing identifier |
| sellerID | INT FK → tblUser | Linked seller |
| brand | VARCHAR(100) | Clothing brand |
| category | VARCHAR(100) | Women / Men / Footwear / Accessories |
| size | VARCHAR(20) | Size (S/M/L/XL or numeric) |
| condition | ENUM(Like New, Excellent, Good, Fair) | Item condition |
| description | TEXT | Full item description |
| price | DECIMAL(10,2) | Listed price in ZAR |
| imagePath | VARCHAR(255) | Path to product image |
| status | ENUM(available, sold, removed) | Listing state |
| dateAdded | DATETIME | Listing creation timestamp |

#### `tblOrder`
| Field | Type | Description |
|-------|------|-------------|
| orderID | INT PK | Unique order identifier |
| buyerID | INT FK → tblUser | Linked buyer |
| addressID | INT FK → tblDeliveryAddress | Delivery address |
| orderDate | DATETIME | Order placement timestamp |
| totalAmount | DECIMAL(10,2) | Total order value in ZAR |
| orderStatus | ENUM(placed, processing, dispatched, delivered, cancelled) | Fulfilment state |

#### `tblOrderItem`
| Field | Type | Description |
|-------|------|-------------|
| orderItemID | INT PK | Unique line item identifier |
| orderID | INT FK → tblOrder | Linked order |
| clothingID | INT FK → tblClothing | Linked clothing item |
| priceAtPurchase | DECIMAL(10,2) | Price captured at time of purchase |

#### `tblMessage`
| Field | Type | Description |
|-------|------|-------------|
| messageID | INT PK | Unique message identifier |
| senderID | INT FK → tblUser | Message sender |
| receiverID | INT FK → tblUser | Message recipient (NULL for broadcasts) |
| clothingID | INT FK → tblClothing | Related listing (NULL for general messages) |
| messageText | TEXT | Message content |
| sentAt | DATETIME | Send timestamp |
| isRead | TINYINT(1) | 0 = unread, 1 = read |
| isBroadcast | TINYINT(1) | 1 = admin broadcast to all users |

#### `tblWishlist`
| Field | Type | Description |
|-------|------|-------------|
| wishlistID | INT PK | Unique wishlist entry |
| userID | INT FK → tblUser | User who saved the item |
| clothingID | INT FK → tblClothing | Saved clothing item |
| addedAt | DATETIME | Timestamp item was saved |

---

## 📁 Project Structure

```
Pastimes/
│
├── index.php                  ← Home / Landing page
├── register.php               ← User registration
├── login.php                  ← User login
├── logout.php                 ← Session destroy + goodbye splash
├── browse.php                 ← Browse clothing catalogue
├── product.php                ← Single product detail page
├── cart.php                   ← Shopping cart
├── checkout.php               ← Checkout and order confirmation
├── profile.php                ← User profile and dashboard
├── messages.php               ← Buyer-seller messaging inbox
├── about.php                  ← About Pastimes and team
├── settings.php               ← Account settings
├── how-it-works.php           ← Platform guide page
│
├── admin/
│   ├── index.php              ← Admin dashboard (stats overview)
│   ├── users.php              ← Manage all registered users
│   ├── listings.php           ← Manage clothing listings
│   ├── orders.php             ← Manage and update orders
│   └── broadcast.php         ← Send broadcast messages
│
├── includes/
│   ├── DBConn.php             ← MySQLi database connection + helper functions
│   ├── createTable.php        ← Drop/recreate all tables + load userData.txt
│   ├── loadClothingStore.php  ← Load 30 clothing items + sample data
│   ├── header.php             ← Shared navigation header
│   ├── footer.php             ← Shared footer
│   └── splash.php             ← Loading/splash screen component
│
├── css/
│   └── style.css              ← Full design system (navy/gold theme)
│
├── js/
│   ├── main.js                ← Global JS (nav toggle, animations)
│   ├── splash.js              ← Splash and loading screen logic
│   └── cart.js                ← Cart interactions and updates
│
├── images/
│   └── (product images)       ← Clothing item photos
│
├── data/
│   └── userData.txt           ← 5 fictitious users (pipe-delimited)
│
└── myClothingStore.sql        ← Full DDL export with 30 data entries
```

---

## 🚀 Setup & Installation

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) installed (Apache + MySQL)
- A modern web browser (Chrome, Edge, Firefox)
- VS Code (recommended editor)

### Step-by-Step Setup

#### 1. Place the project
Copy the entire `Pastimes/` folder into your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\Pastimes\
```

#### 2. Start XAMPP
Open XAMPP Control Panel and start:
- ✅ **Apache** (port 80)
- ✅ **MySQL** (port 3306)

#### 3. Create the database and tables
Open your browser and navigate to:
```
http://localhost/Pastimes/includes/createTable.php
```
This will:
- Auto-create the `ClothingStore` database
- Drop and recreate all 7 tables
- Insert 5 test users from `userData.txt`

#### 4. Load clothing data
Navigate to:
```
http://localhost/Pastimes/includes/loadClothingStore.php
```
This will insert:
- 30 clothing listings across all categories
- 3 sample orders with order items
- 5 messages (including 1 admin broadcast)
- 5 wishlist entries
- 5 delivery addresses

#### 5. Open the application
```
http://localhost/Pastimes/
```

---

## 🔐 Test Login Credentials

| Username | Password | Role | Status |
|----------|----------|------|--------|
| `johndoe` | `Password1!` | Buyer | Active |
| `sarahadams` | `Fashion2#` | Seller | Approved ✅ |
| `lebomokoena` | `Vintage3$` | Buyer | Active |
| `adminpastimes` | `Admin123@` | Admin | Active |
| `thandinkosi` | `Style456%` | Seller | Pending ⏳ |

> 🔒 All passwords are stored as **BCRYPT hashes** in the database. Plain-text passwords are never stored.

---

## 📸 Pages Overview

| Page | URL | Access |
|------|-----|--------|
| Home | `/index.php` | Public |
| Browse | `/browse.php` | Public |
| Product Detail | `/product.php?id=X` | Public |
| Register | `/register.php` | Guest only |
| Login | `/login.php` | Guest only |
| Profile | `/profile.php` | Logged in |
| Cart | `/cart.php` | Logged in |
| Checkout | `/checkout.php` | Logged in |
| Messages | `/messages.php` | Logged in |
| Settings | `/settings.php` | Logged in |
| How It Works | `/how-it-works.php` | Public |
| About Us | `/about.php` | Public |
| Admin Dashboard | `/admin/index.php` | Admin only |
| Admin — Users | `/admin/users.php` | Admin only |
| Admin — Listings | `/admin/listings.php` | Admin only |
| Admin — Orders | `/admin/orders.php` | Admin only |

---

## 🔧 Key PHP Functions (DBConn.php)

| Function | Description |
|----------|-------------|
| `sanitise($conn, $value)` | Cleans user input against SQL injection |
| `hashPassword($password)` | BCRYPT hashes a plain-text password |
| `verifyPassword($input, $hash)` | Verifies login password against stored hash |
| `redirect($url)` | Redirects to URL and exits |
| `isLoggedIn()` | Returns true if valid session exists |
| `isAdmin()` | Returns true if session role is 'admin' |
| `requireLogin()` | Redirects to login if not authenticated |
| `requireAdmin()` | Redirects to home if not admin |

---

## 🎨 Design System

### Colours
| Token | Hex | Usage |
|-------|-----|-------|
| `--bg-deep` | `#080c18` | Page background, topbar |
| `--bg-primary` | `#0a0e1a` | Main body background |
| `--bg-card` | `#0f1525` | Card and form backgrounds |
| `--gold` | `#c9a84c` | Primary accent, CTAs, highlights |
| `--gold-light` | `#e6c05a` | Hover state gold |
| `--text-primary` | `#f0ece0` | Main body text |
| `--text-secondary` | `#b8b0a0` | Subtext, descriptions |
| `--text-muted` | `#6b7280` | Placeholders, labels |

### Typography
- **Display / Headings:** Cormorant Garamond (serif, elegant)
- **Body / UI:** Montserrat (sans-serif, clean)

### Animated Background
The signature Pastimes background features 30 clothing emoji items (👕 👗 👟 👠 🧢 👜) falling slowly like rain across the screen, with gold glow filters and staggered timing for a luxury fashion-brand feel.

---

## 📚 IIE Requirements Compliance

| Requirement | Status | Implementation |
|-------------|--------|----------------|
| MySQL database named ClothingStore | ✅ | Auto-created in DBConn.php |
| tblUser table | ✅ | createTable.php |
| Register with name, email, username, password | ✅ | register.php |
| 8-character minimum password confirmed | ✅ | JS + PHP validation |
| Login with username and password from DB | ✅ | login.php with session |
| HTML5 form validation | ✅ | required, minlength, pattern attributes |
| Password compared to hash in DB | ✅ | password_verify() |
| Sticky form on failed login | ✅ | PHP value="" repopulation |
| "User [Name] is logged in" display | ✅ | Session-based banner |
| Admin login page | ✅ | admin/index.php |
| Admin verifies new customers | ✅ | admin/users.php |
| Admin CRUD on customers | ✅ | admin/users.php |
| userData.txt with 5+ users | ✅ | data/userData.txt |
| createTable.php drops and recreates tblUser | ✅ | includes/createTable.php |
| DBConn.php as include file | ✅ | includes/DBConn.php |
| Buyer views clothing pictures | ✅ | browse.php + product.php |
| Buyer sends message to seller | ✅ | messages.php |
| Buy a clothing item | ✅ | cart.php + checkout.php |
| View and edit shopping cart | ✅ | cart.php |
| Admin removes sold items | ✅ | admin/listings.php |
| myClothingStore.sql with 30 entries | ✅ | myClothingStore.sql |
| loadClothingStore.php | ✅ | includes/loadClothingStore.php |
| Export table structure to Word | ✅ | POE documentation |

---

## ⚠️ Important Notes

1. **Run createTable.php BEFORE loadClothingStore.php** — the load script depends on users existing in tblUser.
2. **Do not re-run createTable.php** after loading data — it wipes everything and starts fresh.
3. **Images** — the `images/` folder needs real product images for the full visual experience. Placeholder text shows where images would appear.
4. **PHP version** — this project is compatible with **PHP 7.4+**. No PHP 8-only functions are used (`str_starts_with` has been avoided).
5. **AI tools** — as per IIE policy, AI tools were not used in the production of the actual code files submitted. All code is the original work of the development team where not referenced.

---

## 📖 References

- IIE Rosebank College. (2026). *WEDE6021 Portfolio of Evidence Brief*. The Independent Institute of Education.
- W3Schools. (2025). *PHP Tutorial*. https://www.w3schools.com/php/
- W3Schools. (2025). *MySQL Tutorial*. https://www.w3schools.com/mysql/
- Mozilla Developer Network. (2025). *HTML forms*. https://developer.mozilla.org/en-US/docs/Learn/Forms
- PHP Manual. (2025). *password_hash()*. https://www.php.net/manual/en/function.password-hash.php
- MySQL. (2025). *MySQL 8.0 Reference Manual*. https://dev.mysql.com/doc/refman/8.0/en/
- Google Fonts. (2025). *Cormorant Garamond + Montserrat*. https://fonts.google.com/
- ThredUp. (2025). *2025 Resale Market and Consumer Trend Report*. https://www.thredup.com/resale/

---

## 📄 Declaration

> *We declare that this project is our own work where not referenced. Any external code used has been cited at the point of use within the relevant script files. All student numbers, names, and declarations are included in the header comments of every PHP and CSS file as required by the IIE.*

**Tshiamo Mosetlha — ST10451437**  
**Zandile Selao — ST10436981**  
**Muhluri Nkuna — ST10437226**

---

*© 2026 Pastimes. All rights reserved. Built for WEDE6021 — The IIE Rosebank College.*
