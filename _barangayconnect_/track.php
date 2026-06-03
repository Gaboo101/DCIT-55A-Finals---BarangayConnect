
<?php
session_start();
require_once 'database/database.php';

$conn = connectDB();
$user_requests = [];

// Get user's requests if logged in
if (isUserLoggedIn()) {
    $user_requests = getRequestsByUserId($conn, $_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Track Request | BarangayConnect</title>
  <link rel="stylesheet" href="style.css">

  <style>
    #results-panel {
      display: none;
    }
    #results-panel:target {
      display: block;
      animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
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
        <a href="request.php" class="nav-link">Request Document</a>
        <a href="track.php" class="nav-link active">Track Request</a>
        <?php if(isUserLoggedIn()): ?>
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
       SECTION 2: TRACKING DISPLAY
       ========================================== -->
  <main style="padding: 60px 0; min-height: calc(100vh - 280px);">
    <div class="container" style="max-width: 900px; margin: 0 auto;">
      
      <div style="text-align: center; margin-bottom: 40px;">
        <span style="font-size: 2.5rem;">📋</span>
        <h1 style="font-size: 2rem; margin-top: 12px; margin-bottom: 8px;">My Document Requests</h1>
        <p style="color: var(--text-muted);">View the status of all your document requests in one place.</p>
      </div>

      <!-- ==========================================
           SECTION 3: USER NOT LOGGED IN MESSAGE
           ========================================== -->
      <?php if (!isUserLoggedIn()): ?>
      <div style="margin-top: 32px; padding: 32px; border-radius: var(--radius-md); background-color: var(--info-light); border: 1px solid var(--info); color: var(--info); text-align: center;">
        <p style="font-size: 1.1rem; margin-bottom: 20px;">ℹ️ You need to log in to view your document requests.</p>
        <a href="login.php" class="btn btn-primary">Log In Now</a>
      </div>
      <?php endif; ?>

      <!-- ==========================================
           SECTION 4: USER'S REQUESTS TABLE
           ========================================== -->
      <?php if (isUserLoggedIn()): ?>
        <?php if (!empty($user_requests)): ?>
      <section class="card-table-wrapper">
        <div class="table-header-controls">
          <h3 class="table-title">Your Submitted Requests</h3>
          <span style="font-weight: 550; font-size: 0.85rem; background-color: var(--bg-card); padding: 8px 16px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
            Total: <?php echo count($user_requests); ?> request(s)
          </span>
        </div>
        
        <table class="responsive-table">
          <thead>
            <tr>
              <th>Tracking Code</th>
              <th>Document Type</th>
              <th>Request Date</th>
              <th>Current Status</th>
              <th>Details</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($user_requests as $req): ?>
              <?php
                $badgeClass = 'badge-pending';
                if ($req['status'] === 'Processing') $badgeClass = 'badge-processing';
                elseif ($req['status'] === 'Ready for Pickup') $badgeClass = 'badge-ready';
                elseif ($req['status'] === 'Completed') $badgeClass = 'badge-completed';
                elseif ($req['status'] === 'Rejected') $badgeClass = 'badge-rejected';
                
                $requestDate = date('M d, Y - h:i A', strtotime($req['request_date']));
              ?>
            <tr>
              <td><span class="ref-code" style="font-family: var(--font-mono);"><?php echo htmlspecialchars($req['tracking_code']); ?></span></td>
              <td style="font-weight: 600;"><?php echo htmlspecialchars($req['doc_name']); ?></td>
              <td style="font-size: 0.85rem;"><?php echo $requestDate; ?></td>
              <td>
                <span class="badge <?php echo $badgeClass; ?>">
                  <span class="badge-dot"></span> <?php echo htmlspecialchars($req['status']); ?>
                </span>
              </td>
              <td>
                <a href="#request-<?php echo $req['id']; ?>" class="btn btn-secondary btn-size-sm">View Details</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

      <!-- ==========================================
           SECTION 5: REQUEST DETAILS MODALS
           ========================================== -->
      <?php foreach ($user_requests as $req): ?>
      <div id="request-<?php echo $req['id']; ?>" class="modal-overlay">
        <div class="modal-card">
          <div class="modal-header-panel">
            <h3>Request Details</h3>
            <a href="#" class="modal-close-icon">&times;</a>
          </div>
          
          <div class="modal-content-panel">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
              <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Tracking Code</span>
                <p style="font-size: 0.95rem; font-weight: 550; font-family: var(--font-mono);"><?php echo htmlspecialchars($req['tracking_code']); ?></p>
              </div>
              <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Document Type</span>
                <p style="font-size: 0.95rem; font-weight: 550;"><?php echo htmlspecialchars($req['doc_name']); ?></p>
              </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
              <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Request Date</span>
                <p style="font-size: 0.95rem; font-weight: 550;"><?php echo date('M d, Y - h:i A', strtotime($req['request_date'])); ?></p>
              </div>
              <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Current Status</span>
                <p style="font-size: 0.95rem; font-weight: 550; color: var(--primary);"><?php echo htmlspecialchars($req['status']); ?></p>
              </div>
            </div>

            <div style="margin-bottom: 20px; padding: 16px; background-color: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md);">
              <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 8px;">Purpose</span>
              <p style="margin: 0; color: var(--text-dark);"><?php echo htmlspecialchars($req['purpose']); ?></p>
            </div>

            <?php if ($req['admin_remarks']): ?>
            <div style="margin-bottom: 20px; padding: 16px; background-color: var(--warning-light); border: 1px solid var(--warning); border-radius: var(--radius-md);">
              <strong style="color: var(--warning); display: block; margin-bottom: 8px;">Admin Remarks:</strong>
              <p style="margin: 0; color: var(--text-dark);"><?php echo htmlspecialchars($req['admin_remarks']); ?></p>
            </div>
            <?php endif; ?>

            <!-- Status Timeline -->
            <h4 style="font-size: 0.9rem; margin-top: 20px; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); font-weight: 700;">Processing Timeline</h4>

            <ul class="timeline">
              <li class="timeline-item <?php echo ($req['status'] !== 'Pending') ? 'completed' : ''; ?>">
                <div class="timeline-marker"><?php echo ($req['status'] !== 'Pending') ? '✔️' : ''; ?></div>
                <div class="timeline-content">
                  <h4 style="font-size: 0.9rem;">Request Submitted</h4>
                  <span class="timeline-date"><?php echo date('M d, Y', strtotime($req['request_date'])); ?></span>
                </div>
              </li>

              <li class="timeline-item <?php echo ($req['status'] === 'Processing' || $req['status'] === 'Ready for Pickup' || $req['status'] === 'Completed') ? 'current' : 'pending'; ?>">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                  <h4 style="font-size: 0.9rem; color: <?php echo ($req['status'] === 'Processing' || $req['status'] === 'Ready for Pickup' || $req['status'] === 'Completed') ? 'var(--primary-light)' : 'inherit'; ?>;">Processing & Review</h4>
                </div>
              </li>

              <li class="timeline-item <?php echo ($req['status'] === 'Ready for Pickup' || $req['status'] === 'Completed') ? 'completed' : 'pending'; ?>">
                <div class="timeline-marker"><?php echo ($req['status'] === 'Ready for Pickup' || $req['status'] === 'Completed') ? '✔️' : ''; ?></div>
                <div class="timeline-content">
                  <h4 style="font-size: 0.9rem;">Ready for Pickup</h4>
                </div>
              </li>

              <li class="timeline-item <?php echo ($req['status'] === 'Completed') ? 'completed' : 'pending'; ?>">
                <div class="timeline-marker"><?php echo ($req['status'] === 'Completed') ? '✔️' : ''; ?></div>
                <div class="timeline-content">
                  <h4 style="font-size: 0.9rem;">Claimed / Completed</h4>
                </div>
              </li>
            </ul>
          </div>

          <footer class="modal-footer">
            <a href="#" class="btn btn-secondary">Close</a>
          </footer>
        </div>
      </div>
      <?php endforeach; ?>

        <?php else: ?>
      <div style="margin-top: 32px; padding: 32px; border-radius: var(--radius-md); background-color: var(--bg-main); border: 1px solid var(--border); text-align: center;">
        <p style="font-size: 1.1rem; margin-bottom: 20px; color: var(--text-muted);">📭 You haven't submitted any document requests yet.</p>
        <a href="request.php" class="btn btn-primary">Submit Your First Request</a>
      </div>
        <?php endif; ?>
      <?php endif; ?>
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
