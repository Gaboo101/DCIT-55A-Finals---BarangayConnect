
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
$residents = getAllResidents($conn);

// Handle DELETE resident
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'Resident'");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success = "Resident record deleted successfully.";
            $residents = getAllResidents($conn);
        } else {
            $error = "Failed to delete resident record.";
        }
        $stmt->close();
    }
    // Handle ADD/EDIT resident
    elseif ($_POST['action'] === 'save') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        if (!$first_name || !$last_name || !$email) {
            $error = "First name, last name, and email are required.";
        } else {
            if ($user_id > 0) {
                // Update existing resident
                $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ? AND role = 'Resident'");
                $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $address, $user_id);
                if ($stmt->execute()) {
                    $success = "Resident record updated successfully.";
                    $residents = getAllResidents($conn);
                } else {
                    $error = "Failed to update resident record.";
                }
                $stmt->close();

            $check_stmt->close();
                
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
  <title>Resident Directory | Admin BarangayConnect</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ==========================================
       ADMIN CONTAINER: Sidebar and Content splitter
       ========================================== -->
  <div class="admin-container">
    
    <!-- LEFT SIDEBAR DRAWER (Reused structure with Active state on Resident Directory) -->
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
        <a href="requests-admin.php" class="sidebar-link">
          <span class="sidebar-icon">📝</span> Manage Requests
        </a>
        <a href="residents-admin.php" class="sidebar-link active">
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
      
      <!-- Page Header with Button targeting CSS Modal overlay -->
      <header class="admin-page-header">
        <div class="admin-page-title">
          <h1>Resident Directory</h1>
          <p>Maintain the official registration profiles and address verifications of local neighborhood citizens.</p>
        </div>
      </header>

      <!-- Success/Error Messages -->
      <?php if ($success): ?>
        <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: var(--radius-sm); background-color: var(--success-light); color: var(--success); border: 1px solid var(--success);">✅ <?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: var(--radius-sm); background-color: var(--danger-light); color: var(--danger); border: 1px solid var(--danger);">❌ <?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <!-- SECTION 1: CENSUS CITIZENS MASTER DIRECTORY -->
      <section class="card-table-wrapper">
        <div class="table-header-controls">
          <h3 class="table-title">Registered Neighborhood Citizens</h3>
          <input type="text" id="resident-search" class="table-search-input" placeholder="Search by name, email, phone, or address...">
        </div>
        <table class="responsive-table" id="residents-table">
          <thead>
            <tr data-search-row="true">
              <th>Full Name</th>
              <th>Email Address</th>
              <th>Phone</th>
              <th>Address</th>
              <th>Registration Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (!empty($residents)) {
              foreach ($residents as $resident) {
                $regDate = date('M d, Y', strtotime($resident['created_at']));
            ?>
            <tr data-search-row="true">
              <td style="font-weight: 600;"><?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?></td>
              <td style="font-family: var(--font-mono); font-size: 0.85rem;"><?php echo htmlspecialchars($resident['email']); ?></td>
              <td><?php echo htmlspecialchars($resident['phone']); ?></td>
              <td><?php echo htmlspecialchars($resident['address']); ?></td>
              <td style="font-family: var(--font-mono); font-size: 0.85rem;"><?php echo $regDate; ?></td>
              <td>
                <div class="table-actions">
                  <a href="#edit-resident-<?php echo $resident['id']; ?>" class="btn btn-secondary btn-size-sm">View/Edit</a>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="<?php echo $resident['id']; ?>">
                    <button type="submit" class="btn btn-secondary btn-size-sm" style="background-color: var(--danger-light); color: var(--danger);" onclick="return confirm('Delete this resident?');">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php
              }
            } else {
            ?>
            <tr data-static-row="true">
              <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">No residents registered yet.</td>
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
      const searchInput = document.getElementById('resident-search');
      const table = document.getElementById('residents-table');
      if (!searchInput || !table) return;

      const tbody = table.tBodies[0];
      const dataRows = Array.from(tbody.querySelectorAll('tr[data-search-row="true"]'));
      const columnCount = table.querySelectorAll('thead th').length;
      let noResultsRow = null;

      function ensureNoResultsRow() {
        if (!noResultsRow) {
          noResultsRow = document.createElement('tr');
          noResultsRow.setAttribute('data-search-empty', 'true');
          noResultsRow.innerHTML = '<td colspan="' + columnCount + '" style="text-align: center; padding: 32px; color: var(--text-muted);">No matching residents found.</td>';
          tbody.appendChild(noResultsRow);
        }
      }

      function removeNoResultsRow() {
        if (noResultsRow) {
          noResultsRow.remove();
          noResultsRow = null;
        }
      }

      function filterResidents() {
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

      searchInput.addEventListener('input', filterResidents);
    })();
  </script>

  <!-- ==========================================
       SECTION 2: EDIT MODALS FOR EACH RESIDENT
       ========================================== -->
  <?php foreach ($residents as $resident): ?>
  <div id="edit-resident-<?php echo $resident['id']; ?>" class="modal-overlay">
    <div class="modal-card">
      
      <!-- Modal Header -->
      <div class="modal-header-panel">
        <h3>Edit Resident Record</h3>
        <a href="#" class="modal-close-icon">&times;</a>
      </div>
      
      <!-- Modal Form Body -->
      <div class="modal-content-panel">
        <form method="POST" action="residents-admin.php">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="user_id" value="<?php echo $resident['id']; ?>">
          
          <div class="form-group-grid">
            <div class="form-group">
              <label class="form-label">First Name *</label>
              <input type="text" name="first_name" class="form-input" value="<?php echo htmlspecialchars($resident['first_name']); ?>" required>
            </div>

            <div class="form-group">
              <label class="form-label">Last Name *</label>
              <input type="text" name="last_name" class="form-input" value="<?php echo htmlspecialchars($resident['last_name']); ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($resident['email']); ?>" required>
          </div>

          <div class="form-group-grid">
            <div class="form-group">
              <label class="form-label">Phone Number</label>
              <input type="tel" name="phone" class="form-input" value="<?php echo htmlspecialchars($resident['phone'] ?? ''); ?>">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-input" value="<?php echo htmlspecialchars($resident['address'] ?? ''); ?>">
          </div>

        </form>
      </div>

      <!-- Modal Footer -->
      <footer class="modal-footer">
        <a href="#" class="btn btn-secondary">Close</a>
        <button type="submit" class="btn btn-primary" onclick="document.getElementById('edit-form-<?php echo $resident['id']; ?>').submit();">Update Resident</button>
      </footer>

    </div>
  </div>
  <?php endforeach; ?>

</body>
</html>
