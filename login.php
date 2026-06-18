<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['userID'])) {
    $role = $_SESSION['role'];
    if ($role === 'admin')      header('Location: /Pastimes/admin/index.php');
    elseif ($role === 'seller') header('Location: /Pastimes/dashboards/seller.php');
    else                        header('Location: /Pastimes/dashboards/buyer.php');
    exit;
}

$error   = '';
$success = '';
$showModal = false;

// Handle forgot password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password'])) {
    $showModal = true;
    $email = trim($_POST['reset_email'] ?? '');
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $conn = getConnection();
            $stmt = $conn->prepare("SELECT userID, fullName FROM tblUser WHERE email = ? LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            if ($user) {
                $tempPass = 'Temp' . rand(1000,9999) . '!';
                $hashed   = password_hash($tempPass, PASSWORD_BCRYPT);
                $upd      = $conn->prepare("UPDATE tblUser SET password=? WHERE userID=?");
                $upd->bind_param('si', $hashed, $user['userID']);
                $upd->execute();
                $conn->close();
                $success = "Password reset! Your temporary password is: <strong style='color:var(--gold);font-size:1.1rem;'>$tempPass</strong><br><small>Login and change it immediately.</small>";
            } else {
                $error = 'No account found with that email address.';
                $conn->close();
            }
        } catch (Exception $e) {
            $error = 'Reset failed. Please try again.';
        }
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$username || !$password) {
        $error = 'Please enter your username and password.';
    } else {
        try {
            $conn = getConnection();
            $stmt = $conn->prepare("SELECT userID,fullName,username,password,role,seller_status FROM tblUser WHERE username=? OR email=? LIMIT 1");
            $stmt->bind_param('ss', $username, $username);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['userID']        = $user['userID'];
                $_SESSION['username']      = $user['username'];
                $_SESSION['fullName']      = $user['fullName'];
                $_SESSION['role']          = $user['role'];
                $_SESSION['seller_status'] = $user['seller_status'];
                $conn->close();
                if ($user['role']==='admin')      header('Location: /Pastimes/admin/index.php');
                elseif ($user['role']==='seller') header('Location: /Pastimes/dashboards/seller.php');
                else                              header('Location: /Pastimes/dashboards/buyer.php');
                exit;
            } else {
                $error = 'Incorrect username or password.';
            }
            $conn->close();
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<!-- Forgot Password Modal -->
<div id="forgotModal" style="display:none;position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,.8);align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid var(--border-gold);border-radius:var(--radius-lg);padding:var(--space-2xl);width:100%;max-width:420px;margin:var(--space-lg);position:relative;">
        <button onclick="closeForgotModal()" style="position:absolute;top:12px;right:16px;background:none;border:none;color:var(--text-muted);font-size:1.5rem;cursor:pointer;">&times;</button>
        <div style="text-align:center;margin-bottom:var(--space-xl);">
            <div style="font-size:2.5rem;color:var(--gold);margin-bottom:var(--space-sm);"><i class="fas fa-key"></i></div>
            <h2 style="font-family:var(--font-display);color:var(--gold);font-size:1.5rem;margin-bottom:var(--space-xs);">Reset Password</h2>
            <p style="color:var(--text-muted);font-size:0.88rem;">Enter your email and we will generate a temporary password.</p>
        </div>
        <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <span><?php echo $success; ?></span></div>
        <button onclick="closeForgotModal()" class="btn btn-primary btn-full" style="margin-top:var(--space-md);"><i class="fas fa-sign-in-alt"></i> Back to Login</button>
        <?php else: ?>
        <?php if ($error && $showModal): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Email Address <span class="required">*</span></label>
                <input type="email" name="reset_email" class="form-control" placeholder="Enter your registered email" required>
            </div>
            <button type="submit" name="forgot_password" class="btn btn-primary btn-full"><i class="fas fa-paper-plane"></i> Reset My Password</button>
        </form>
        <?php endif; ?>
        <p style="text-align:center;margin-top:var(--space-md);font-size:0.8rem;color:var(--text-muted);">
            Remembered it? <a href="#" onclick="closeForgotModal()" style="color:var(--gold);">Back to Login</a>
        </p>
    </div>
</div>

<!-- Login Form -->
<section style="padding:var(--space-3xl) var(--space-xl);min-height:80vh;display:flex;align-items:center;justify-content:center;">
    <div style="width:100%;max-width:460px;">
        <div class="form-card">
            <div class="form-card-header text-center">
                <div class="form-card-logo">P</div>
                <h1 style="font-family:var(--font-display);font-size:1.8rem;color:var(--gold);margin-bottom:var(--space-xs);">Welcome Back</h1>
                <p style="color:var(--text-muted);font-size:0.9rem;">Login to your Pastimes account</p>
            </div>

            <?php if ($error && !$showModal): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> Account created! Please login.</div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="username">Username or Email <span class="required">*</span></label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username or email" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password" data-target="#password"><i class="fas fa-eye"></i></button>
                    </div>
                </div>

                <!-- Forgot Password -->
                <div style="text-align:right;margin-top:calc(-1 * var(--space-sm));margin-bottom:var(--space-lg);">
                    <a href="#" onclick="openForgotModal();return false;" style="font-size:0.82rem;color:var(--text-muted);">
                        <i class="fas fa-key"></i> Forgot Password?
                    </a>
                </div>

                <button type="submit" name="login" class="btn btn-primary btn-full btn-lg"><i class="fas fa-sign-in-alt"></i> Login</button>
            </form>

            <div style="margin-top:var(--space-lg);padding:var(--space-md);background:rgba(201,168,76,.06);border:1px solid var(--border-gold);border-radius:var(--radius-md);font-size:0.8rem;color:var(--text-muted);">
                <strong style="color:var(--gold);">Test Credentials:</strong><br>
                Admin: <code>admin</code> / <code>AdminPass1</code><br>
                Buyer: <code>johndoe</code> / <code>Password123</code><br>
                Seller: <code>janesmith</code> / <code>SecurePass1</code>
            </div>

            <p class="text-center mt-lg" style="font-size:0.88rem;color:var(--text-muted);">
                Don't have an account? <a href="/Pastimes/register.php" style="color:var(--gold);font-weight:600;">Register here</a>
            </p>
        </div>
    </div>
</section>

<script>
function openForgotModal() {
    document.getElementById('forgotModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeForgotModal() {
    document.getElementById('forgotModal').style.display = 'none';
    document.body.style.overflow = '';
}
document.getElementById('forgotModal').addEventListener('click', function(e) {
    if (e.target === this) closeForgotModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeForgotModal();
});
<?php if ($showModal): ?>
window.addEventListener('load', function() { openForgotModal(); });
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>