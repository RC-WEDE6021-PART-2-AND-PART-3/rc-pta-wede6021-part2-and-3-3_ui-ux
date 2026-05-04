<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: login.php
 * Description: User login page with HTML5 validation
 * ============================================================
 */

session_start();
require_once 'includes/DBConn.php';

$error = '';
$success = '';

// Check if user just registered
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success = 'Registration successful! Please login with your credentials.';
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $conn = getConnection();
            
            // Query user by username or email
            $stmt = $conn->prepare("SELECT userID, fullName, username, email, password, role, seller_status FROM tblUser WHERE username = ? OR email = ?");
            $stmt->bind_param('ss', $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password using password_verify (compares to hashed password)
                if (password_verify($password, $user['password'])) {
                    // Check if user is verified (for sellers)
                    if ($user['role'] === 'seller' && $user['seller_status'] === 'pending') {
                        $error = 'Your seller account is pending verification. Please wait for admin approval.';
                    } elseif ($user['role'] === 'buyer' && $user['seller_status'] === 'pending') {
                        // Buyers with pending status can still log in
                        $_SESSION['userID'] = $user['userID'];
                        $_SESSION['fullName'] = $user['fullName'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['seller_status'] = $user['seller_status'];
                        
                        header('Location: profile.php');
                        exit();
                    } else {
                        // Login successful - create session
                        $_SESSION['userID'] = $user['userID'];
                        $_SESSION['fullName'] = $user['fullName'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['seller_status'] = $user['seller_status'];
                        
                        // Redirect based on role
                        if ($user['role'] === 'admin') {
                            header('Location: admin/index.php');
                        } else {
                            header('Location: profile.php');
                        }
                        exit();
                    }
                } else {
                    $error = 'Invalid username or password.';
                }
            } else {
                $error = 'Invalid username or password.';
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}

// Sticky form values
$stickyUsername = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
?>
<?php include 'includes/header.php'; ?>

        <!-- Login Section -->
        <section class="section">
            <div class="container-sm">
                <!-- Logo Circle -->
                <div class="text-center mb-lg">
                    <div class="gold-divider">
                        <div class="form-card-logo">P</div>
                    </div>
                    <h1 class="section-title">Welcome Back</h1>
                    <p class="section-subtitle" style="margin-bottom: 0;">Sign in to your Pastimes account</p>
                </div>

                <div class="form-card">
                    <div class="form-card-header">
                        <h2 style="font-family: var(--font-display); font-size: 1.4rem; color: var(--gold);">Login</h2>
                        <p class="text-muted" style="font-size: 0.85rem;">Enter your credentials to continue</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['userID'])): ?>
                        <!-- Display logged in user info -->
                        <div class="logged-in-banner">
                            <i class="fas fa-user-check"></i>
                            User "<?php echo htmlspecialchars($_SESSION['fullName']); ?>" is logged in
                        </div>
                        
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>User ID</td>
                                        <td><?php echo $_SESSION['userID']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Full Name</td>
                                        <td><?php echo htmlspecialchars($_SESSION['fullName']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Username</td>
                                        <td><?php echo htmlspecialchars($_SESSION['username']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td><?php echo htmlspecialchars($_SESSION['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Role</td>
                                        <td><span class="badge badge-gold"><?php echo ucfirst($_SESSION['role']); ?></span></td>
                                    </tr>
                                    <tr>
                                        <td>Seller Status</td>
                                        <td>
                                            <span class="badge <?php echo $_SESSION['seller_status'] === 'verified' ? 'badge-success' : 'badge-warning'; ?>">
                                                <?php echo ucfirst($_SESSION['seller_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-lg">
                            <a href="profile.php" class="btn btn-primary btn-full">Go to Profile</a>
                            <a href="logout.php" class="btn btn-outline btn-full mt-md">Logout</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="login.php">
                            <div class="form-group">
                                <label class="form-label">Username <span class="required">*</span></label>
                                <div class="form-control-wrapper">
                                    <i class="fas fa-user form-icon"></i>
                                    <input type="text" 
                                           name="username" 
                                           class="form-control" 
                                           placeholder="Enter your username"
                                           value="<?php echo $stickyUsername; ?>"
                                           required>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="flex-between">
                                    <label class="form-label">Password <span class="required">*</span></label>
                                    <a href="#" class="text-gold" style="font-size: 0.8rem;">Forgot password?</a>
                                </div>
                                <div class="form-control-wrapper">
                                    <i class="fas fa-lock form-icon"></i>
                                    <input type="password" 
                                           name="password" 
                                           id="password"
                                           class="form-control has-icon-right" 
                                           placeholder="Enter your password"
                                           required
                                           minlength="8">
                                    <i class="fas fa-eye form-icon-right" id="togglePassword" onclick="togglePasswordVisibility()"></i>
                                </div>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">Remember me on this device</label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-full btn-lg">Sign In</button>
                        </form>

                        <div class="form-divider">New to Pastimes?</div>

                        <p class="text-center text-muted" style="font-size: 0.85rem; margin-bottom: var(--space-md);">
                            Join thousands of South Africans buying and selling pre-loved fashion
                        </p>

                        <a href="register.php" class="btn btn-outline btn-full">Create an Account</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <script>
            function togglePasswordVisibility() {
                const passwordInput = document.getElementById('password');
                const toggleIcon = document.getElementById('togglePassword');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            }
        </script>

<?php include 'includes/footer.php'; ?>