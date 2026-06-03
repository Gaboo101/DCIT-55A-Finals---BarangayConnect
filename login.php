<?php
session_start();
require_once 'database/database.php';

if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== '') {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connectDB();
    if (!$conn) {
        $error = "Database connection failed";
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $error = "Email and password are required";
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['first_name'] = $row['first_name'];
                    $_SESSION['last_name'] = $row['last_name'];
                    $_SESSION['phone'] = $row['phone'];
                    $_SESSION['address'] = $row['address'];
                    $_SESSION['role'] = $row['role'];
                    $_SESSION['is_admin'] = ($row['role'] === 'Admin');
                    header("Location: " . ($_SESSION['is_admin'] ? "admin.php" : "index.php"));
                    exit;
                } else {
                    $error = "Invalid email or password";
                }
            } else {
                $error = "Invalid email or password";
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
  <title>Login | BarangayConnect Portal</title>
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
          <a href="login.php" class="nav-link active">Login</a>
          <a href="register.php" class="nav-link btn-admin">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- ==========================================
       SECTION 2: LOGIN FORM CONTAINER
       ========================================== -->
  <main class="form-section" style="min-height: calc(100vh - 280px); display: flex; align-items: center; justify-content: center; padding: 60px 0;">
    <div class="container" style="max-width: 480px; margin: 0 auto; width: 100%;">
      
      <!-- Card wrapping the form fields -->
      <div class="card-form-wrapper" style="box-shadow: var(--shadow-lg);">
        <div class="form-header" style="text-align: center;">
          <span style="font-size: 2.5rem; display: block; margin-bottom: 12px;">🔑</span>
          <h2>Citizen Login Portal</h2>
          <p>Access your personal dashboard to track document requests, verify residency status and manage your official profile.</p>
        </div>
        
        <form class="form-body" method="POST" action="login.php">
          <div id="alert-messages" style="display: none; margin-bottom: 20px; padding: 12px 16px; border-radius: var(--radius-sm); font-size: 0.9rem; font-weight: 550;"></div>
          <?php if(isset($error)): ?>
            <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: var(--radius-sm); background-color: var(--danger-light); color: var(--danger); border: 1px solid var(--danger);">❌ <?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-input" placeholder="name@example.com" required>
          </div>

          <div class="form-group" style="margin-top: 16px;">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-input" placeholder="••••••••" required>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; margin: 16px 0; font-size: 0.85rem;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-muted);">
              <input type="checkbox" style="accent-color: var(--primary);"> Remember me
            </label>
          </div>

          <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 14px 20px; margin-top: 12px; font-weight: 700;">
            <span>🔓</span> Sign In Safely
          </button>

          <p style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: var(--text-muted);">
            Don't have a registered account yet? 
            <a href="register.php" class="accent-link" style="color: var(--primary-light); font-weight: 600; text-decoration: underline;">Create Account</a>
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
