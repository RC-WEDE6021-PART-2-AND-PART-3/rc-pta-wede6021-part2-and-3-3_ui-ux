<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: logout.php
 * Description: Logout page with goodbye splash screen
 * ============================================================
 */

session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goodbye | Pastimes</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Goodbye Splash Screen -->
    <div class="goodbye-screen">
        <div class="goodbye-icon">
            <i class="fas fa-hand-peace"></i>
        </div>
        <h1 class="goodbye-title">See You Soon!</h1>
        <p class="goodbye-msg">You have been successfully logged out.</p>
        <p class="goodbye-msg text-muted" style="margin-top: var(--space-md);">Redirecting to homepage...</p>
        
        <div class="splash-spinner" style="margin-top: var(--space-xl);"></div>
    </div>

    <script>
        // Redirect to homepage after 2.5 seconds
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 2500);
    </script>
</body>
</html>