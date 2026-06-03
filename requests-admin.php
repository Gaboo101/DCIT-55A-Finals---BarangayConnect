<?php
session_start();
require_once 'database/database.php';

// Check if user is admin
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit;
}

$conn = connectDB();
$error = null;
$success = null;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $request_id = intval($_POST['request_id']);
        $status = trim($_POST['status']);
        $remarks = trim($_POST['remarks'] ?? '');
        
        if (updateRequestStatus($conn, $request_id, $status, $remarks)) {
            $success = "Request status updated successfully.";
        } else {
            $error = "Failed to update request status.";
        }
    }
}

// Get filter status from query parameter
$filter_status = null;
if (isset($_GET['status'])) {
    $filter_status = $_GET['status'];
}

// Get requests
$requests = getAllRequests($conn, $filter_status);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Requests | Admin BarangayConnect</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ==========================================
       ADMIN CONTAINER: Sidebar and Content splitter
       ========================================== -->
  <div class="admin-container">
    
    <!-- LEFT SIDEBAR DRAWER (Reused structure with Active state on Manage Requests) -->
    <aside class="admin-sidebar">
      <a href="index.php" class="logo-link" style="margin-bottom: 32px; padding-left: 12px;">
        <div class="logo-badge">B</div>
        <span style="font-size: 1.15rem;">AdminConnect</span>
      </a>
      
      <span class="admin-sidebar-header">Core Management</span>
      <nav style="display: flex; flex-direction: column; gap: 8px;">
        <a href="admin.php" class="sidebar-link">
          <span class="sidebar-icon">📊</span> Dashboard
        </a>
        <a href="document-types.php" class="sidebar-link">
          <span class="sidebar-icon">⚙️</span> Document Types
        </a>
        <a href="requests-admin.php" class="sidebar-link active">
          <span class="sidebar-icon">📝</span> Manage Requests
        </a>
        <a href="residents-admin.php" class="sidebar-link">
          <span class="sidebar-icon">📇</span> Resident Directory
        </a>
      </nav>

      <div style="margin: 20px 0; border-top: 1px solid var(--border);"></div>
      
      <span class="admin-sidebar-header">Public Portals</span>
      <nav style="display: flex; flex-direction: column; gap: 8px;">
        <a href="index.php" class="sidebar-link">
          <span class="sidebar-icon">🏠</span> Back to Home
        </a>
      </nav>

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
      
      <!-- Page Header: Title Block -->
      <header class="admin-page-header">
        <div class="admin-page-title">
          <h1>Manage Resident Requests</h1>
          <p>Review attached requirements, issue pre-approvals, write officer notes, and track processing states.</p>
        </div>
      </header>

      <!-- Success/Error Messages -->
      <?php if ($success): ?>
        <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: var(--radius-sm); background-color: var(--success-light); color: var(--success); border: 1px solid var(--success);">✅ <?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: var(--radius-sm); background-color: var(--danger-light); color: var(--danger); border: 1px solid var(--danger);">❌ <?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <!-- NAVIGATION TABS: Filters requests list metrics -->
      <div class="tabs-nav">
        <?php
        $statuses = ['Pending', 'Processing', 'Ready for Pickup', 'Completed', 'Rejected'];
        $allCount = count(getAllRequests($conn));
        ?>
        <a href="requests-admin.php" class="tab-btn <?php echo (!$filter_status) ? 'active' : ''; ?>">All Requests (<?php echo $allCount; ?>)</a>
        <?php foreach ($statuses as $status): ?>
          <?php 
            $count = count(getAllRequests($conn, $status));
          ?>
          <a href="requests-admin.php?status=<?php echo urlencode($status); ?>" class="tab-btn <?php echo ($filter_status === $status) ? 'active' : ''; ?>"><?php echo htmlspecialchars($status); ?> (<?php echo $count; ?>)</a>
        <?php endforeach; ?>
      </div>

      <!-- SECTION 1: REQUESTS MASTER DIRECTORY -->
      <section class="card-table-wrapper">
        <div class="table-header-controls">
          <h3 class="table-title font-sans">Active Document Queues</h3>
          <input type="text" id="request-search" class="table-search-input" placeholder="Search ref code, applicant, document, or status...">
        </div>
        <table class="responsive-table" id="requests-table">
          <thead>
            <tr>
              <th>Ref Code</th>
              <th>Applicant Name</th>
              <th>Document</th>
              <th>Date Filed</th>
              <th>Status State</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (!empty($requests)) {
              $rowIndex = 1;
              foreach ($requests as $req) {
                $badgeClass = 'badge-pending';
                if ($req['status'] === 'Processing') $badgeClass = 'badge-processing';
                elseif ($req['status'] === 'Ready for Pickup') $badgeClass = 'badge-ready';
                elseif ($req['status'] === 'Completed') $badgeClass = 'badge-completed';
                elseif ($req['status'] === 'Rejected') $badgeClass = 'badge-rejected';
                
                $requestDate = date('M d, Y', strtotime($req['request_date']));
            ?>
            <tr data-search-row="true">
              <td><span class="ref-code"><?php echo htmlspecialchars($req['tracking_code']); ?></span></td>
              <td>
                <div style="font-weight: 600;"><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></div>
                <span style="font-size: 0.8rem; color: var(--text-muted);">ID: <?php echo htmlspecialchars($req['user_id']); ?></span>
              </td>
              <td><?php echo htmlspecialchars($req['doc_name']); ?></td>
              <td style="font-family: var(--font-mono); font-size: 0.85rem;"><?php echo $requestDate; ?></td>
              <td>
                <span class="badge <?php echo $badgeClass; ?>">
                  <span class="badge-dot"></span> <?php echo htmlspecialchars($req['status']); ?>
                </span>
              </td>
              <td>
                <div class="table-actions">
                  <a href="#open-row<?php echo $rowIndex; ?>" class="btn btn-secondary btn-size-sm">Inspect / Edit</a>
                </div>
              </td>
            </tr>
            <?php
                $rowIndex++;
              }
            } else {
            ?>
            <tr data-static-row="true">
              <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">No requests found.</td>
            </tr>
            <?php
            }
            ?>
          </tbody>
        </table>
      </section>

    </main>

  </div>

  <script>
    (function () {
      const searchInput = document.getElementById('request-search');
      const table = document.getElementById('requests-table');
      if (!searchInput || !table) return;

      const tbody = table.tBodies[0];
      const dataRows = Array.from(tbody.querySelectorAll('tr[data-search-row="true"]'));
      const columnCount = table.querySelectorAll('thead th').length;
      let noResultsRow = null;

      function ensureNoResultsRow() {
        if (!noResultsRow) {
          noResultsRow = document.createElement('tr');
          noResultsRow.setAttribute('data-search-empty', 'true');
          noResultsRow.innerHTML = '<td colspan="' + columnCount + '" style="text-align: center; padding: 32px; color: var(--text-muted);">No matching requests found.</td>';
          tbody.appendChild(noResultsRow);
        }
      }

      function removeNoResultsRow() {
        if (noResultsRow) {
          noResultsRow.remove();
          noResultsRow = null;
        }
      }

      function filterRequests() {
        const query = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        dataRows.forEach((row) => {
          const matches = row.textContent.toLowerCase().includes(query);
          row.style.display = matches ? '' : 'none';
          if (matches) visibleCount++;
        });

        if (query && visibleCount === 0) {
          ensureNoResultsRow();
        } else {
          removeNoResultsRow();
        }
      }

      searchInput.addEventListener('input', filterRequests);
    })();
  </script>

  <!-- Status Update Modal for each request -->
  <?php if (!empty($requests)): ?>
    <?php foreach ($requests as $index => $req): ?>
    <div id="open-row<?php echo ($index + 1); ?>" class="modal-overlay">
      <div class="modal-card">
        <div class="modal-header-panel">
          <h3>Update Request Status</h3>
          <a href="#" class="modal-close-icon">&times;</a>
        </div>
        
        <div class="modal-content-panel">
          <div style="margin-bottom: 20px;">
            <p><strong>Tracking Code:</strong> <?php echo htmlspecialchars($req['tracking_code']); ?></p>
            <p><strong>Applicant:</strong> <?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></p>
            <p><strong>Document:</strong> <?php echo htmlspecialchars($req['doc_name']); ?></p>
            <p><strong>Current Status:</strong> <span style="font-weight: 600;"><?php echo htmlspecialchars($req['status']); ?></span></p>
          </div>

          <form method="POST" action="requests-admin.php">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">

            <div class="form-group">
              <label class="form-label">Update Status *</label>
              <select name="status" class="form-select" required>
                <option value="Pending" <?php echo ($req['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Processing" <?php echo ($req['status'] === 'Processing') ? 'selected' : ''; ?>>Processing</option>
                <option value="Ready for Pickup" <?php echo ($req['status'] === 'Ready for Pickup') ? 'selected' : ''; ?>>Ready for Pickup</option>
                <option value="Completed" <?php echo ($req['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="Rejected" <?php echo ($req['status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Admin Remarks</label>
              <textarea name="remarks" class="form-textarea" placeholder="Add any notes or remarks..." rows="4"><?php echo htmlspecialchars($req['admin_remarks'] ?? ''); ?></textarea>
            </div>

            <footer class="modal-footer">
              <a href="#" class="btn btn-secondary">Close</a>
              <button type="submit" class="btn btn-primary">Update Status</button>
            </footer>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>

</body>
</html>
