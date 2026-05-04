<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: register.php
 * Description: User registration page with HTML5 validation
 * ============================================================
 */

session_start();
require_once 'includes/DBConn.php';

$error = '';
$success = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $agreeTerms = isset($_POST['agreeTerms']);
    
    // Validation array for sticky form
    $errors = [];
    
    // Validate full name
    if (empty($fullName)) {
        $errors[] = 'Full name is required.';
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $fullName)) {
        $errors[] = 'Full name must contain only letters and spaces.';
    } elseif (strlen($fullName) > 100) {
        $errors[] = 'Full name must not exceed 100 characters.';
    }
    
    // Validate username
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        $errors[] = 'Username must be alphanumeric only.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    } elseif (strlen($username) > 50) {
        $errors[] = 'Username must not exceed 50 characters.';
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    // Validate password (minimum 8 characters)
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }
    
    // Validate password confirmation
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    
    // Validate terms agreement
    if (!$agreeTerms) {
        $errors[] = 'You must agree to the Terms of Service and Privacy Policy.';
    }
    
    // If no validation errors, proceed with database insert
    if (empty($errors)) {
        try {
            $conn = getConnection();
            
            // Check if username already exists
            $stmt = $conn->prepare("SELECT userID FROM tblUser WHERE username = ?");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = 'Username already exists. Please choose a different one.';
            }
            $stmt->close();
            
            // Check if email already exists
            $stmt = $conn->prepare("SELECT userID FROM tblUser WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = 'Email already registered. Please use a different email or login.';
            }
            $stmt->close();
            
            // If still no errors, insert the user
            if (empty($errors)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $role = 'buyer'; // Default role
                $sellerStatus = 'pending'; // Default status
                
                $stmt = $conn->prepare("INSERT INTO tblUser (fullName, email, username, password, role, seller_status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('ssssss', $fullName, $email, $username, $hashedPassword, $role, $sellerStatus);
                
                if ($stmt->execute()) {
                    // Registration successful - redirect to login
                    header('Location: login.php?registered=1');
                    exit();
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                }
                $stmt->close();
            }
            
            $conn->close();
        } catch (Exception $e) {
            $errors[] = 'An error occurred. Please try again later.';
        }
    }
    
    // Set error message
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}

// Sticky form values
$stickyFullName = isset($_POST['fullName']) ? htmlspecialchars($_POST['fullName']) : '';
$stickyUsername = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
$stickyEmail = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
?>
<?php include 'includes/header.php'; ?>

        <!-- Registration Section -->
        <section class="section">
            <div class="container-sm">
                <!-- Logo Circle -->
                <div class="text-center mb-lg">
                    <div class="gold-divider">
                        <div class="form-card-logo">P</div>
                    </div>
                    <h1 class="section-title">Join Pastimes</h1>
                    <p class="section-subtitle" style="margin-bottom: 0;">Create your account to start buying and selling</p>
                </div>

                <div class="form-card" style="max-width: 620px;">
                    <div class="form-card-header">
                        <h2 style="font-family: var(--font-display); font-size: 1.4rem; color: var(--gold);">Registration Form</h2>
                        <p class="text-muted" style="font-size: 0.85rem;">Fill in your details to create an account</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="register.php" id="registerForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Full Name <span class="required">*</span></label>
                                <div class="form-control-wrapper">
                                    <i class="fas fa-user form-icon"></i>
                                    <input type="text" 
                                           name="fullName" 
                                           class="form-control" 
                                           placeholder="Enter your full name"
                                           value="<?php echo $stickyFullName; ?>"
                                           pattern="[a-zA-Z\s]+"
                                           maxlength="100"
                                           required>
                                </div>
                                <span class="form-hint">As it appears on your ID</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Username <span class="required">*</span></label>
                                <div class="form-control-wrapper">
                                    <i class="fas fa-at form-icon"></i>
                                    <input type="text" 
                                           name="username" 
                                           class="form-control" 
                                           placeholder="Choose a username"
                                           value="<?php echo $stickyUsername; ?>"
                                           pattern="[a-zA-Z0-9]+"
                                           minlength="3"
                                           maxlength="50"
                                           required>
                                </div>
                                <span class="form-hint">Min 3 characters, alphanumeric only</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address <span class="required">*</span></label>
                            <div class="form-control-wrapper">
                                <i class="fas fa-envelope form-icon"></i>
                                <input type="email" 
                                       name="email" 
                                       class="form-control" 
                                       placeholder="Enter your email address"
                                       value="<?php echo $stickyEmail; ?>"
                                       required>
                            </div>
                            <span class="form-hint">We'll send verification and notifications here</span>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Password <span class="required">*</span></label>
                                <div class="form-control-wrapper">
                                    <i class="fas fa-lock form-icon"></i>
                                    <input type="password" 
                                           name="password" 
                                           id="password"
                                           class="form-control has-icon-right" 
                                           placeholder="Create a password"
                                           minlength="8"
                                           required>
                                    <i class="fas fa-eye form-icon-right" onclick="togglePassword('password', this)"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Confirm Password <span class="required">*</span></label>
                                <div class="form-control-wrapper">
                                    <i class="fas fa-lock form-icon"></i>
                                    <input type="password" 
                                           name="confirmPassword" 
                                           id="confirmPassword"
                                           class="form-control has-icon-right" 
                                           placeholder="Confirm your password"
                                           minlength="8"
                                           required>
                                    <i class="fas fa-eye form-icon-right" onclick="togglePassword('confirmPassword', this)"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Password Requirements -->
                        <div class="password-requirements">
                            <p><i class="fas fa-info-circle"></i> Password Requirements</p>
                            <ul class="req-list">
                                <li class="req-item" id="req-length">
                                    <i class="fas fa-check req-icon"></i> Minimum 8 characters
                                </li>
                                <li class="req-item" id="req-number">
                                    <i class="fas fa-check req-icon"></i> At least one number
                                </li>
                                <li class="req-item" id="req-upper">
                                    <i class="fas fa-check req-icon"></i> Upper & lowercase letters
                                </li>
                                <li class="req-item" id="req-match">
                                    <i class="fas fa-check req-icon"></i> Passwords must match
                                </li>
                            </ul>
                        </div>

                        <!-- Account Information Notice -->
                        <div class="alert alert-info mt-lg">
                            <strong>Account Information</strong><br>
                            All new accounts start as <strong>Buyers</strong>. To become a verified seller, you'll need to request seller status after registration. Our admin team will verify your account before you can list items for sale.
                        </div>

                        <div class="form-check mt-lg">
                            <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                            <label for="agreeTerms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a> <span class="required">*</span></label>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" id="newsletter" name="newsletter">
                            <label for="newsletter">Subscribe to our newsletter for updates on new listings and promotions</label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full btn-lg mt-lg">Create Account</button>
                    </form>

                    <div class="form-divider">Already have an account?</div>

                    <a href="login.php" class="btn btn-outline btn-full">Sign In Instead</a>
                </div>
            </div>
        </section>

        <script>
            function togglePassword(inputId, icon) {
                const input = document.getElementById(inputId);
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }

            // Real-time password validation
            document.getElementById('password').addEventListener('input', validatePassword);
            document.getElementById('confirmPassword').addEventListener('input', validatePassword);

            function validatePassword() {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirmPassword').value;

                // Check length
                document.getElementById('req-length').classList.toggle('met', password.length >= 8);
                
                // Check for number
                document.getElementById('req-number').classList.toggle('met', /\d/.test(password));
                
                // Check for upper and lowercase
                document.getElementById('req-upper').classList.toggle('met', /[A-Z]/.test(password) && /[a-z]/.test(password));
                
                // Check match
                document.getElementById('req-match').classList.toggle('met', password === confirmPassword && password.length > 0);
            }
        </script>

<?php include 'includes/footer.php'; ?>