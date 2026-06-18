<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in — redirect to their dashboard
if (isset($_SESSION['userID'])) {
    $role = $_SESSION['role'];
    if ($role === 'admin')  header('Location: admin/index.php');
    elseif ($role === 'seller') header('Location: dashboards/seller.php');
    else header('Location: dashboards/buyer.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirmPassword'] ?? '';
    $role     = $_POST['role']          ?? 'buyer';

    // Validate
    if (!$fullName || !$username || !$email || !$password || !$confirm) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['buyer', 'seller', 'admin'])) {
        $error = 'Invalid role selected.';
    } else {
        try {
            $conn = getConnection();

            // Check duplicate username or email
            $stmt = $conn->prepare("SELECT userID FROM tblUser WHERE username = ? OR email = ?");
            $stmt->bind_param('ss', $username, $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = 'Username or email already exists.';
            } else {
                $hashed        = password_hash($password, PASSWORD_BCRYPT);
                $seller_status = ($role === 'seller') ? 'pending' : 'none';
                // Admin registrations are pending verification too
                if ($role === 'admin') $seller_status = 'verified';

                $ins = $conn->prepare(
                    "INSERT INTO tblUser (fullName, username, email, password, role, seller_status)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $ins->bind_param('ssssss', $fullName, $username, $email, $hashed, $role, $seller_status);
                $ins->execute();
                $newID = $conn->insert_id;

                // Auto-login after registration
                $_SESSION['userID']   = $newID;
                $_SESSION['username'] = $username;
                $_SESSION['fullName'] = $fullName;
                $_SESSION['role']     = $role;
                $_SESSION['seller_status'] = $seller_status;

                $conn->close();

                // Redirect to correct dashboard
                if ($role === 'admin') {
                    header('Location: admin/index.php');
                } elseif ($role === 'seller') {
                    header('Location: dashboards/seller.php');
                } else {
                    header('Location: dashboards/buyer.php');
                }
                exit;
            }
            $conn->close();
        } catch (Exception $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<section style="padding: var(--space-3xl) var(--space-xl); min-height: 80vh; display:flex; align-items:center; justify-content:center;">
    <div style="width:100%; max-width:560px;">

        <!-- Card -->
        <div class="form-card">
            <div class="form-card-header text-center">
                <div class="form-card-logo">P</div>
                <h1 style="font-family:var(--font-display); font-size:1.8rem; color:var(--gold); margin-bottom:var(--space-xs);">
                    Join Pastimes
                </h1>
                <p style="color:var(--text-muted); font-size:0.9rem;">
                    Create your free account and choose your role
                </p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="register.php" id="registerForm">

                <!-- Full Name -->
                <div class="form-group">
                    <label class="form-label" for="fullName">Full Name <span class="required">*</span></label>
                    <input
                        type="text" id="fullName" name="fullName"
                        class="form-control"
                        placeholder="e.g. John Doe"
                        value="<?php echo htmlspecialchars($_POST['fullName'] ?? ''); ?>"
                        required
                    >
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label class="form-label" for="username">Username <span class="required">*</span></label>
                    <input
                        type="text" id="username" name="username"
                        class="form-control"
                        placeholder="e.g. johndoe"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                    >
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">Email Address <span class="required">*</span></label>
                    <input
                        type="email" id="email" name="email"
                        class="form-control"
                        placeholder="e.g. john@email.co.za"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        required
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input
                            type="password" id="password" name="password"
                            class="form-control"
                            placeholder="Min. 8 characters"
                            required
                        >
                        <button type="button" class="toggle-password" data-target="#password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-requirements mt-sm">
                        <div class="requirement" id="req-length">At least 8 characters</div>
                        <div class="requirement" id="req-upper">At least one uppercase letter</div>
                        <div class="requirement" id="req-number">At least one number</div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label class="form-label" for="confirmPassword">Confirm Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input
                            type="password" id="confirmPassword" name="confirmPassword"
                            class="form-control"
                            placeholder="Repeat your password"
                            required
                        >
                        <button type="button" class="toggle-password" data-target="#confirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="form-hint" id="confirmHint"></div>
                </div>

                <!-- Role Selection -->
                <div class="form-group">
                    <label class="form-label">Select Your Role <span class="required">*</span></label>
                    <div class="role-selector">

                        <div class="role-option">
                            <input type="radio" name="role" id="role-buyer" value="buyer"
                                <?php echo (($_POST['role'] ?? 'buyer') === 'buyer') ? 'checked' : ''; ?>>
                            <label class="role-card" for="role-buyer">
                                <span class="role-icon"><i class="fas fa-shopping-bag" style="color:var(--gold);"></i></span>
                                <span class="role-title">Buyer</span>
                                <span class="role-desc">Browse and purchase clothing</span>
                            </label>
                        </div>

                        <div class="role-option">
                            <input type="radio" name="role" id="role-seller" value="seller"
                                <?php echo (($_POST['role'] ?? '') === 'seller') ? 'checked' : ''; ?>>
                            <label class="role-card" for="role-seller">
                                <span class="role-icon"><i class="fas fa-store" style="color:var(--gold);"></i></span>
                                <span class="role-title">Seller</span>
                                <span class="role-desc">List and sell your clothing</span>
                            </label>
                        </div>

                        <div class="role-option">
                            <input type="radio" name="role" id="role-admin" value="admin"
                                <?php echo (($_POST['role'] ?? '') === 'admin') ? 'checked' : ''; ?>>
                            <label class="role-card" for="role-admin">
                                <span class="role-icon"><i class="fas fa-shield-alt" style="color:var(--gold);"></i></span>
                                <span class="role-title">Admin</span>
                                <span class="role-desc">Manage the platform</span>
                            </label>
                        </div>

                    </div>
                    <p class="form-hint mt-sm">
                        <i class="fas fa-info-circle"></i>
                        Seller accounts require admin verification before listing items.
                    </p>
                </div>

                <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:var(--space-md);">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>

            </form>

            <p class="text-center mt-lg" style="font-size:0.88rem; color:var(--text-muted);">
                Already have an account?
                <a href="login.php" style="color:var(--gold); font-weight:600;">Login here</a>
            </p>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>