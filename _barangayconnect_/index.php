<?php
session_start();
require_once 'database/database.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home | BarangayConnect Portal</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ==========================================
       SECTION 1: NAVIGATION HEADER
       ========================================== -->
  <header class="site-header">
    <div class="container nav-container">
      <!-- Logo Branding -->
      <a href="index.php" class="logo-link">
        <div class="logo-badge">B</div>
        <span>BarangayConnect</span>
      </a>
      
      <!-- Public Navigation Menu Links -->
      <nav class="main-nav">
        <a href="index.php" class="nav-link active">Home</a>
        <a href="request.php" class="nav-link">Request Document</a>
        <a href="track.php" class="nav-link">Track Request</a>
        <?php if(isAdminLoggedIn()): ?>
          <a href="admin.php" class="nav-link" style="color: #fff; background-color: var(--primary-light); border-color: #007bff;">Admin Dashboard</a>
          <a href="logout.php" class="nav-link btn-admin">Logout</a>
        <?php elseif(isUserLoggedIn()): ?>
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
       SECTION 2: HERO BANNER SECTION
       ========================================== -->
  <section class="hero">
    <div class="container">
      <span class="hero-tag">Official Barangay Digital Services Portal</span>
      <h1>Skip the Line. Request Official Barangay Documents Online.</h1>
      <p>A streamlined digital portal for residents to request official barangay documents online, reducing wait times and improving community transparency.</p>
      
      <!-- Primary Core Operations Call-To-Action buttons -->
      <div class="hero-actions">
        <a href="request.php" class="btn btn-primary btn-size-lg">Request a Document Now</a>
        <a href="track.php" class="btn btn-secondary btn-size-lg">Track Existing Request</a>
      </div>
    </div>
  </section>

  <!-- ==========================================
       SECTION 3: FEATURES GRID (BENTO CARD DISPLAY)
       ========================================== -->
  <main class="container">
    <section style="padding-top: 64px; text-align: center;">
      <h2 style="font-size: 2rem; margin-bottom: 12px;">Available Document Services</h2>
      <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto 16px;">We process a wide range of administrative documents for Barangay residents with minimal requirements and rapid turnaround times.</p>
    </section>

    <div class="features-grid">
      <!-- Feature Card 1: Clearance -->
      <article class="feature-card">
        <div class="feature-icon-wrapper">📄</div>
        <h3>Barangay Clearance</h3>
        <p>Required for job employment, bank registration, legal reference, and general local background verification.</p>
        <span style="font-size: 0.85rem; font-weight: 600; color: var(--primary);">Fee: ₱50.00 | Processing: 1 Day</span>
      </article>

      <!-- Feature Card 2: Indigency -->
      <article class="feature-card">
        <div class="feature-icon-wrapper">🤝</div>
        <h3>Certificate of Indigency</h3>
        <p>Issued to low-income residents for scholarships, educational grants, medical aid, social assistance, and free legal services.</p>
        <span style="font-size: 0.85rem; font-weight: 600; color: var(--primary);">Fee: FREE | Processing: Immediate</span>
      </article>

      <!-- Feature Card 3: Residency -->
      <article class="feature-card">
        <div class="feature-icon-wrapper">🏠</div>
        <h3>Certificate of Residency</h3>
        <p>Provides validated official proof that you are currently residing inside the physical jurisdiction of this Barangay.</p>
        <span style="font-size: 0.85rem; font-weight: 600; color: var(--primary);">Fee: FREE | Processing: 1 Day</span>
      </article>

      <!-- Feature Card 4: Business Permit -->
      <article class="feature-card">
        <div class="feature-icon-wrapper">💼</div>
        <h3>Business Clearance</h3>
        <p>Mandatory preliminary document requested by the City/Municipality before acquiring a commercial Business Permit.</p>
        <span style="font-size: 0.85rem; font-weight: 600; color: var(--primary);">Fee: ₱150.00 | Processing: 2 Days</span>
      </article>
    </div>

    <!-- ==========================================
         SECTION 4: PROCESS WALKTHROUGH
         ========================================== -->
    <section style="padding: 64px 0; border-top: 1px solid var(--border); text-align: center;">
      <h2 style="font-size: 2rem; margin-bottom: 40px;">How It Works</h2>
      
      <div style="display: flex; flex-wrap: wrap; justify-content: space-around; gap: 20px;">
        <div style="flex: 1; min-width: 200px; max-width: 280px;">
          <div style="font-size: 2.5rem; color: var(--primary-light); margin-bottom: 16px; font-weight: 700;">01</div>
          <h4 style="font-size: 1.125rem; margin-bottom: 8px;">Submit Request</h4>
          <p style="font-size: 0.9rem; color: var(--text-muted);">Fill out our digital application form with your personal details and attach a valid ID.</p>
        </div>
        
        <div style="flex: 1; min-width: 200px; max-width: 280px;">
          <div style="font-size: 2.5rem; color: var(--primary-light); margin-bottom: 16px; font-weight: 700;">02</div>
          <h4 style="font-size: 1.125rem; margin-bottom: 8px;">Track Progress</h4>
          <p style="font-size: 0.9rem; color: var(--text-muted);;">Use your unique transaction reference code to monitor state amendments in real-time.</p>
        </div>
        
        <div style="flex: 1; min-width: 200px; max-width: 280px;">
          <div style="font-size: 2.5rem; color: var(--primary-light); margin-bottom: 16px; font-weight: 700;">03</div>
          <h4 style="font-size: 1.125rem; margin-bottom: 8px;">Claim Document</h4>
          <p style="font-size: 0.9rem; color: var(--text-muted);;">Visit the Barangay Hall when status returns "Ready", present your requirements, and claim it.</p>
        </div>
      </div>
    </section>
  </main>

  <!-- ==========================================
       SECTION 5: FOOTER NAVIGATION PANEL
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
