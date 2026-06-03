
<?php
session_start();
require_once 'database/database.php';

// Check if user is admin
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit;
}

$conn = connectDB();
$recent_requests = [];

if (!$conn) {
    $error = "Database connection failed";
} else {
    $result = $conn->query("SELECT COUNT(*) as count FROM requests");
    $stats['total'] = $result->fetch_assoc()['count'];

    $result = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'Pending'");
    $stats['pending'] = $result->fetch_assoc()['count'];

    $result = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'Ready for Pickup'");
    $stats['ready'] = $result->fetch_assoc()['count'];

    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'Resident'");
    $stats['residents'] = $result->fetch_assoc()['count'];

    $result = $conn->query("SELECT r.*, d.name as doc_name, u.first_name, u.last_name FROM requests r JOIN document_types d ON r.doc_id = d.id JOIN users u ON r.user_id = u.id ORDER BY r.request_date DESC LIMIT 5");
    while ($row = $result->fetch_assoc()) {
        $recent_requests[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | BarangayConnect</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ==========================================
       ADMIN CONTAINER: Splitting sidebar and main desk
       ========================================== -->
  <div class="admin-container">
    
    <!-- LEFT SIDEBAR DRAWER -->
    <aside class="admin-sidebar">
      
      <!-- Logo Branding -->
      <a href="index.php" class="logo-link" style="margin-bottom: 32px; padding-left: 12px;">
        <div class="logo-badge">B</div>
        <span style="font-size: 1.15rem;">AdminConnect</span>
      </a>
      
      <span class="admin-sidebar-header">Core Management</span>
      
      <!-- Sidebar Navigation Menu -->
      <nav style="display: flex; flex-direction: column; gap: 8px;">
        <a href="admin.php" class="sidebar-link active">
          <span class="sidebar-icon">📊</span> Dashboard
        </a>
        <a href="document-types.php" class="sidebar-link">
          <span class="sidebar-icon">⚙️</span> Document Types
        </a>
        <a href="requests-admin.php" class="sidebar-link">
          <span class="sidebar-icon">📝</span> Manage Requests
        </a>
        <a href="residents-admin.php" class="sidebar-link">
          <span class="sidebar-icon">📇</span> Resident Directory
        </a>
      </nav>

      <!-- Sidebar divider -->
      <div style="margin: 20px 0; border-top: 1px solid var(--border);"></div>
      
      <span class="admin-sidebar-header">Public Portals</span>
      <nav style="display: flex; flex-direction: column; gap: 8px;">
        <a href="index.php" class="sidebar-link">
          <span class="sidebar-icon">🏠</span> Back to Home
        </a>
      </nav>

      <!-- Admin Profile Metadata bottom-dock -->
      <div class="admin-profile">
        <div class="profile-avatar">AD</div>
        <div class="profile-info">
          <span class="profile-name">Admin User</span>
          <span class="profile-role">Administrator</span>
        </div>
      </div>

      <!-- Logout Button -->
      <a href="logout.php" class="sidebar-link" style="margin-top: 12px; background-color: var(--danger-light); color: var(--danger);">
        <span class="sidebar-icon">🚪</span> Logout
      </a>
      
    </aside>

    <!-- RIGHT MAIN CONTENT PANEL -->
    <main class="admin-content">
      
      <!-- Page Header title block -->
      <header class="admin-page-header">
        <div class="admin-page-title">
          <h1>Dashboard Overview</h1>
          <p>Statistical summaries and request distribution metrics of your municipality.</p>
        </div>
      </header>

      <section class="metrics-grid">
        
        <!-- Metric Card 1: Total -->
        <article class="metric-card primary-theme">
          <div class="metric-data">
            <h3>Total Applications</h3>
            <span class="metric-num"><?php echo $stats['total']; ?></span>
          </div>
          <div class="metric-icon-circle">📂</div>
        </article>

        <!-- Metric Card 2: Pending -->
        <article class="metric-card warning-theme">
          <div class="metric-data">
            <h3>Pending Reviews</h3>
            <span class="metric-num"><?php echo $stats['pending']; ?></span>
          </div>
          <div class="metric-icon-circle">⏳</div>
        </article>

        <!-- Metric Card 3: Ready -->
        <article class="metric-card success-theme">
          <div class="metric-data">
            <h3>Ready for Pickup</h3>
            <span class="metric-num"><?php echo $stats['ready']; ?></span>
          </div>
          <div class="metric-icon-circle">✅</div>
        </article>

        <!-- Metric Card 4: Residents -->
        <article class="metric-card info-theme">
          <div class="metric-data">
            <h3>Registered Residents</h3>
            <span class="metric-num"><?php echo $stats['residents']; ?></span>
          </div>
          <div class="metric-icon-circle">👥</div>
        </article>

      </section>

      <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
        
        <!-- LEFT PANELS: Recent Requests Summary Table -->
        <section class="card-table-wrapper" style="box-shadow: var(--shadow-sm);">
          <div class="table-header-controls" style="border-bottom: 1px solid var(--border);">
            <h3 class="table-title">Recent Request Stream</h3>
            <a href="requests-admin.php" class="btn btn-secondary btn-size-sm">View All Requests</a>
          </div>
          
          <table class="responsive-table">
            <thead>
              <tr>
                <th>Ref Code</th>
                <th>Resident Name</th>
                <th>Document Name</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($recent_requests as $req): ?>
              <tr>
                <td><span class="ref-code"><?php echo htmlspecialchars($req['tracking_code']); ?></span></td>
                <td style="font-weight: 550;"><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></td>
                <td><?php echo htmlspecialchars($req['doc_name']); ?></td>
                <td>
                  <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $req['status'])); ?>">
                    <span class="badge-dot"></span> <?php echo htmlspecialchars($req['status']); ?>
                  </span>
                </td>
                <td>
                  <a href="requests-admin.php?id=<?php echo $req['id']; ?>" class="btn btn-secondary btn-size-sm">Review</a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($recent_requests)): ?>
              <tr>
                <td colspan="5" style="text-align: center; color: var(--text-muted);">No requests yet</td>
              </tr>
              <?php endif; ?>

            </tbody>
          </table>
        </section>

      </div>

    </main>

  </div>

</body>
</html>
