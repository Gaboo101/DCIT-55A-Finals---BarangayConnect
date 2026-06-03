<?php
session_start();
require_once 'database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connectDB();
    if (!$conn) {
        $error = "Database connection failed";
    } else {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (!$firstName || !$lastName || !$email || !$password) {
            $error = "All required fields must be filled";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Email already registered";
            } else {
                // Hash password and insert user
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, 'Resident')");
                $stmt->bind_param("ssssss", $email, $hashedPassword, $firstName, $lastName, $phone, $address);
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Registration successful! Please login.";
                    header("Location: login.php");
                    exit;
                } else {
                    $error = "Registration failed. Please try again";
                }
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register Account | BarangayConnect Portal</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ==========================================
       SECTION 1: NAVIGATION HEADER
       ========================================== -->
  <header class="site-header">
    <div class="container nav-container">
      <a href="index.php" class="logo-link">
        <div class="logo-badge">B</div>
        <span>BarangayConnect</span>
      </a>
      <nav class="main-nav">
        <a href="index.php" class="nav-link">Home</a>
        <a href="request.php" class="nav-link">Request Document</a>
        <a href="track.php" class="nav-link">Track Request</a>
        <?php if(isUserLoggedIn()): ?>
          <a href="index.php" class="nav-link">Hello, <?php echo htmlspecialchars($_SESSION['first_name']); ?></a>
          <a href="logout.php" class="nav-link btn-admin">Logout</a>
        <?php else: ?>
          <a href="login.php" class="nav-link">Login</a>
          <a href="register.php" class="nav-link btn-admin active">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- ==========================================
       SECTION 2: REGISTRATION FORM CONTAINER
       ========================================== -->
  <main class="form-section" style="min-height: calc(100vh - 280px); display: flex; align-items: center; justify-content: center; padding: 60px 0;">
    <div class="container" style="max-width: 640px; margin: 0 auto; width: 100%;">
      
      <!-- Card wrapping the form fields -->
      <div class="card-form-wrapper" style="box-shadow: var(--shadow-lg);">
        <div class="form-header" style="text-align: center;">
          <span style="font-size: 2.5rem; display: block; margin-bottom: 12px;">📝</span>
          <h2>Official Resident Registry</h2>
          <p>Register to securely apply for documents, request clearances, and verify your residency record online.</p>
        </div>
        
        <form class="form-body" method="POST" action="register.php">
          <div id="alert-messages" style="display: none; margin-bottom: 20px; padding: 12px 16px; border-radius: var(--radius-sm); font-size: 0.9rem; font-weight: 550;"></div>
          <?php if(isset($error)): ?>
            <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: var(--radius-sm); background-color: var(--danger-light); color: var(--danger); border: 1px solid var(--danger);">❌ <?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>
          <?php if(isset($_SESSION['success'])): ?>
            <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: var(--radius-sm); background-color: var(--success-light); color: var(--success); border: 1px solid var(--success);">✅ <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
          <?php endif; ?>

          <!-- Section heading -->
          <h3 style="font-size: 1.15rem; border-bottom: 2px solid var(--bg-main); padding-bottom: 10px; margin-bottom: 20px; font-weight: 700; color: var(--primary);">
            1. Resident Identification
          </h3>

          <!-- Grid layout for First Name and Last Name -->
          <div class="form-group-grid">
            <div class="form-group">
              <label class="form-label" for="first-name">First Name *</label>
              <input type="text" id="first-name" name="first_name" class="form-input" placeholder="e.g. Maria" required>
            </div>
            
            <div class="form-group">
              <label class="form-label" for="last-name">Last Name *</label>
              <input type="text" id="last-name" name="last_name" class="form-input" placeholder="e.g. Santos" required>
            </div>
          </div>

          <!-- Grid layout for Contact Info and Email -->
          <div class="form-group-grid" style="margin-top: 16px;">
            <div class="form-group">
              <label class="form-label" for="phone">Phone Number *</label>
              <input type="tel" id="phone" name="phone" class="form-input" placeholder="e.g. 09171234567" required>
            </div>

            <div class="form-group">
              <label class="form-label" for="email">Email Address *</label>
              <input type="email" id="email" name="email" class="form-input" placeholder="e.g. maria.s@gmail.com" required>
            </div>
          </div>

          <div class="form-group" style="margin-top: 16px;">
            <label class="form-label" for="address">Complete Physical Address inside Barangay *</label>
            <input type="text" id="address" name="address" class="form-input" placeholder="Street Number, Block/Lot, Subdivision, Street, District" required>
            <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 4px;">Must be a valid residence registered under the local barangay census.</span>
          </div>

          <!-- Section heading for account credentials -->
          <h3 style="font-size: 1.15rem; border-bottom: 2px solid var(--bg-main); padding-bottom: 10px; margin-bottom: 20px; margin-top: 32px; font-weight: 700; color: var(--primary);">
            2. Secure Credentials
          </h3>

          <div class="form-group">
            <label class="form-label" for="password">Create Secure Account Password *</label>
            <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" minlength="6" required>
            <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 4px;">Password must have at least 6 characters in length.</span>
          </div>

          <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 14px 20px; font-weight: 700; font-size: 1rem;">
            <span>🛡️</span> Submit Registration Profile
          </button>

          <p style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: var(--text-muted);">
            Already have a registered resident account? 
            <a href="login.php" class="accent-link" style="color: var(--primary-light); font-weight: 600; text-decoration: underline;">Access Citizen Login</a>
          </p>
        </form>
      </div>

    </div>
  </main>

  <!-- ==========================================
       SECTION 3: FOOTER PANEL
       ========================================== -->
  <footer class="site-footer">
    <div class="container site-footer-grid">
      <!-- Col 1: About -->
      <div class="footer-column footer-about">
        <h4>BarangayConnect</h4>
        <p>A digital platform modernizing municipal transactions, minimizing wait times, and building a transparent connected future for local communities.</p>
      </div>
      
      <!-- Col 2: Services -->
      <div class="footer-column">
        <h4>Document Services</h4>
        <ul class="footer-links">
          <li><a href="request.php">Barangay Clearance</a></li>
          <li><a href="request.php">Certificate of Indigency</a></li>
          <li><a href="request.php">Certificate of Residency</a></li>
          <li><a href="request.php">Business Clearance</a></li>
        </ul>
      </div>
      
      <!-- Col 3: Portal Links -->
      <div class="footer-column">
        <h4>Resident Portal</h4>
        <ul class="footer-links">
          <li><a href="request.php">Create a Request</a></li>
          <li><a href="track.php">Track Existing Request</a></li>
          <li><a href="admin.php">Office Staff Dashboard</a></li>
        </ul>
      </div>
    </div>
    
    <!-- Legal and Attribution -->
    <div class="container footer-bottom">
      <p>&copy; 2026 BarangayConnect Portal.</p>
    </div>
  </footer>

</body>
</html>
