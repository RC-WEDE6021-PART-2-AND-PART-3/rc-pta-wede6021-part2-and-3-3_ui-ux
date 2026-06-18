<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Goodbye | Pastimes</title>
<link rel="stylesheet" href="/Pastimes/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="goodbye-screen">
    <div style="font-size:4rem;color:var(--gold);margin-bottom:var(--space-md);">
        <i class="fas fa-hand-peace"></i>
    </div>
    <h1 class="goodbye-title">See You Soon!</h1>
    <p class="goodbye-msg">You have been successfully logged out.</p>
    <p class="goodbye-msg">Redirecting to homepage...</p>
    <div class="splash-spinner" style="margin-top:var(--space-lg);"></div>
</div>
<script>
setTimeout(function(){window.location.href='/Pastimes/index.php';},3000);
</script>
</body>
</html>