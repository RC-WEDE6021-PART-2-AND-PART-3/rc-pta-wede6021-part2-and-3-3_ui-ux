<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: includes/DBConn.php
 * Authors: Tshiamo Mosetlha (ST10451437)
 *          Zandile Selao (ST10436981)
 *          Muhluri Nkuna (ST10437226)
 * Description: Database connection using MySQLi (Improved).
 *              This file creates and returns a connection to
 *              the ClothingStore database.
 * ============================================================
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ClothingStore');

// Enable MySQLi error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * Create and return a MySQLi connection
 * @return mysqli The database connection object
 */
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Set charset to UTF-8 for proper encoding
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (mysqli_sql_exception $e) {
        // Log error and display user-friendly message
        error_log("Database Connection Error: " . $e->getMessage());
        die("Connection failed. Please check your database configuration.");
    }
}

/**
 * Create connection without selecting a database
 * Used for initial database creation
 * @return mysqli The database connection object
 */
function getConnectionWithoutDB() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        // Set charset to UTF-8 for proper encoding
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (mysqli_sql_exception $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        die("Connection failed. Please check your database configuration.");
    }
}
?>