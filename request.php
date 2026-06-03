
<?php
session_start();
require_once 'database/database.php';

$conn = connectDB();
$documents = getAllDocumentTypes($conn);
$error = null;
$success = null;
$tracking_code = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isUserLoggedIn()) {
        $error = "You must be logged in to submit a request. Please <a href='login.php'>login</a> first.";
    } else {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $doc_type = trim($_POST['doc_type'] ?? '');
        $purpose = trim($_POST['purpose'] ?? '');
        
        // Validate required fields
        if (!$first_name || !$last_name || !$phone || !$address || !$doc_type || !$purpose) {
            $error = "All required fields must be filled.";
        } else {
            // Find the document ID from selected type
            $doc_id = null;
            foreach ($documents as $doc) {
                if ($doc['name'] === $doc_type) {
                    $doc_id = $doc['id'];
                    break;
                }
            }
            
            if (!$doc_id) {
                $error = "Invalid document type selected.";
            } else {
                // Handle file upload if provided
                $id_photo_path = null;
                if (isset($_FILES['id_photo']) && $_FILES['id_photo']['size'] > 0) {
                    $upload_dir = 'uploads/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_name = basename($_FILES['id_photo']['name']);
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];
                    
                    if (in_array(strtolower($file_ext), $allowed_exts)) {
                        $new_file_name = 'id_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                        $upload_path = $upload_dir . $new_file_name;
                        
                        if (move_uploaded_file($_FILES['id_photo']['tmp_name'], $upload_path)) {
                            $id_photo_path = $upload_path;
                        }
                    }
                }
                
                // Add the request to database
                $tracking_code = addRequest($conn, $_SESSION['user_id'], $doc_id, $purpose, $id_photo_path);
                
                if ($tracking_code) {
                    $success = "Document request submitted successfully! Your tracking code is: <strong>$tracking_code</strong>";
                } else {
                    $error = "Failed to submit request. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Request Document | BarangayConnect</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ==========================================
       SECTION 1: NAVIGATION HEADER (Reused)
       ========================================== -->
  <header class="site-header">
    <div class="container nav-container">
      <a href="index.php" class="logo-link">
        <div class="logo-badge">B</div>
        <span>BarangayConnect</span>
      </a>
      <nav class="main-nav">
        <a href="index.php" class="nav-link">Home</a>
        <?php if(isAdminLoggedIn()): ?>
          <a href="admin.php" class="nav-link" style="color: #fff; background-color: var(--primary-light); border-color: #007bff;">Admin Dashboard</a>
          <a href="logout.php" class="nav-link btn-admin">Logout</a>
        <?php elseif(isUserLoggedIn()): ?>
          
          <a href="request.php" class="nav-link active">Request Document</a>
          <a href="track.php" class="nav-link">Track Request</a>
          <a href="index.php" class="nav-link">Hello, <?php echo htmlspecialchars($_SESSION['first_name']); ?></a>
          <a href="logout.php" class="nav-link btn-admin">Logout</a>
        <?php else: ?>
          <a href="login.php" class="nav-link">Login</a>
          <a href="register.php" class="nav-link btn-admin">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- ==========================================
       SECTION 2: DOCUMENT REQUEST FORM CONTAINER
       ========================================== -->
  <main class="form-section">
    <div class="container">
      
      <!-- Card wrapping the form fields -->
      <div class="card-form-wrapper">
        <div class="form-header">
          <h2>Apply for Barangay Document</h2>
          <p>Provide your details below.</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
          <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: var(--radius-sm); background-color: var(--success-light); color: var(--success); border: 1px solid var(--success);">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: var(--radius-sm); background-color: var(--danger-light); color: var(--danger); border: 1px solid var(--danger);">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Pure HTML Action pointing to '#success' to trigger CSS :target Success Modal -->
        <form method="POST" action="request.php" class="form-body" enctype="multipart/form-data">
          
          <!-- Personal Info: Grid layout for side-by-side inputs on large screens -->
          <h3 style="font-size: 1.15rem; border-bottom: 2px solid var(--bg-main); padding-bottom: 10px; margin-bottom: 20px;">1. Personal Information</h3>
          
          <div class="form-group-grid">
            <div class="form-group">
              <label class="form-label">First Name *</label>
              <input type="text" name="first_name" class="form-input" placeholder="e.g. Juan" required>
            </div>
            
            <div class="form-group">
              <label class="form-label">Last Name *</label>
              <input type="text" name="last_name" class="form-input" placeholder="e.g. Dela Cruz" required>
            </div>
          </div>

          <div class="form-group-grid">
            <div class="form-group">
              <label class="form-label">Contact Number *</label>
              <input type="tel" name="phone" class="form-input" placeholder="e.g. 09171234567" required>
            </div>
            
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input type="email" name="email" class="form-input" placeholder="e.g. juan.dc@gmail.com">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Complete Resident Address *</label>
            <input type="text" name="address" class="form-input" placeholder="Street Number, Block/Lot, Subdivision, Barangay, City" required>
          </div>

          <!-- Document Info -->
          <h3 style="font-size: 1.15rem; border-bottom: 2px solid var(--bg-main); padding-bottom: 10px; margin-bottom: 20px; margin-top: 32px;">2. Request Details</h3>

          <div class="form-group-grid">
            <div class="form-group">
              <label class="form-label">Document Requested *</label>
              <select name="doc_type" class="form-select" required>
                <option value="" disabled selected>-- Select Document Type --</option>
                <?php foreach ($documents as $doc): ?>
                  <option value="<?php echo htmlspecialchars($doc['name']); ?>">
                    <?php echo htmlspecialchars($doc['name']); ?> (<?php echo ($doc['fee'] == 0) ? 'FREE' : '₱' . number_format($doc['fee'], 2); ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Purpose of Request *</label>
              <select name="purpose" class="form-select" required>
                <option value="" disabled selected>-- Select Purpose --</option>
                <option value="employment">Job Application / Employment</option>
                <option value="scholarship">Scholarship / Educational Support</option>
                <option value="financial">Financial / Medical Assistance</option>
                <option value="business-reg">Commercial Business Registration</option>
                <option value="legal">Legal Reference / ID Card Application</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">ID Photo / Attachment (Optional)</label>
            <input type="file" name="id_photo" class="form-input" accept="image/jpeg, image/png, application/pdf">
            <span style="font-size: 0.85rem; color: var(--text-muted);">Upload your ID photo or required attachment (JPG, PNG, PDF)</span>
          </div>

          <!-- Action Buttons -->
          <div style="display: flex; justify-content: flex-end; gap: 16px; margin-top: 36px; border-top: 1px solid var(--border); padding-top: 24px;">
            <a href="index.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Submit Application</button>
          </div>

        </form>
      </div>

    </div>
  </main>



  <!-- ==========================================
       SECTION 4: FOOTER NAVIGATION PANEL
       ========================================== -->
  <footer class="site-footer">
    <div class="container footer-bottom" style="border-top: none; padding-top: 0;">
      <p>&copy; 2026 BarangayConnect Portal.</p>
    </div>
  </footer>

</body>
</html>
