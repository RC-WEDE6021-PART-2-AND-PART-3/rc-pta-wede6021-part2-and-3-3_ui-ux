<?php
/**
 * PASTIMES — includes/DBConn.php
 * Database connection using MySQLi
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ClothingStore');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (mysqli_sql_exception $e) {
        error_log("DB Error: " . $e->getMessage());
        die("Connection failed. Check database config.");
    }
}

function getConnectionWithoutDB() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (mysqli_sql_exception $e) {
        error_log("DB Error: " . $e->getMessage());
        die("Connection failed. Check database config.");
    }
}
?>