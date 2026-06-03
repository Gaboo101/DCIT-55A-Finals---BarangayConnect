
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
$documents = getAllDocumentTypes($conn);

// Handle DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && isset($_POST['doc_id'])) {
        $doc_id = intval($_POST['doc_id']);
        if (deleteDocumentType($conn, $doc_id)) {
            $success = "Document type deleted successfully.";
            $documents = getAllDocumentTypes($conn);
        } else {
            $error = "Failed to delete document type.";
        }
    }
    // Handle ADD/EDIT request
    elseif ($_POST['action'] === 'save') {
        $name = trim($_POST['name'] ?? '');
        $fee = floatval($_POST['fee'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        
        if (!$name) {
            $error = "Document name is required.";
        } else {
            $doc_id = intval($_POST['doc_id'] ?? 0);
            
            if ($doc_id > 0) {
                // Update existing
                if (updateDocumentType($conn, $doc_id, $name, $fee, $description)) {
                    $success = "Document type updated successfully.";
                    $documents = getAllDocumentTypes($conn);
                } else {
                    $error = "Failed to update document type.";
                }
            } else {
                // Add new
                if (addDocumentType($conn, $name, $fee, $description)) {
                    $success = "Document type added successfully.";
                    $documents = getAllDocumentTypes($conn);
                } else {
                    $error = "Failed to add document type.";
                }
            }
        }
    }
    // Handle status toggle
    elseif ($_POST['action'] === 'toggle_status' && isset($_POST['doc_id'])) {
        $doc_id = intval($_POST['doc_id']);
        $new_status = trim($_POST['status'] ?? 'Active');
        
        if (updateDocumentStatus($conn, $doc_id, $new_status)) {
            $success = "Document status updated to " . htmlspecialchars($new_status) . ".";
            $documents = getAllDocumentTypes($conn);
        } else {
            $error = "Failed to update document status.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Document Types | Admin BarangayConnect</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ==========================================
       ADMIN CONTAINER: Sidebar and Content splitter
       ========================================== -->
  <div class="admin-container">
    
    <!-- LEFT SIDEBAR DRAWER (Reused structure with Active state on Document Types) -->
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
        <a href="document-types.php" class="sidebar-link active">
          <span class="sidebar-icon">⚙️</span> Document Types
        </a>
        <a href="requests-admin.php" class="sidebar-link">
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
      
      <!-- Page Header: Title & Trigger to Open CSS Modal -->
      <header class="admin-page-header">
        <div class="admin-page-title">
          <h1>Manage Document Types</h1>
          <p>Setup general processing durations, base pricing, and official document requirements.</p>
        </div>
        <div>
          <!-- Anchor link pointing to '#add-type-modal' to launch CSS target overlay -->
          <a href="#add-type-modal" class="btn btn-primary">
            <span>➕</span> Add Document Type
          </a>
        </div>
      </header>

      <!-- Success/Error Messages -->
      <?php if ($success): ?>
        <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: var(--radius-sm); background-color: var(--success-light); color: var(--success); border: 1px solid var(--success);">✅ <?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: var(--radius-sm); background-color: var(--danger-light); color: var(--danger); border: 1px solid var(--danger);">❌ <?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <!-- SECTION 1: SYSTEM SERVICES TABLE -->
      <section class="card-table-wrapper">
        
        <table class="responsive-table">
          <thead>
            <tr>
              <th>Document Name</th>
              <th>Processing Fee</th>
              <th>Description</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (!empty($documents)) {
              foreach ($documents as $doc) {
                $feeDisplay = ($doc['fee'] == 0) ? '<span style="color: var(--success);">FREE</span>' : '₱' . number_format($doc['fee'], 2);
                $statusColor = ($doc['status'] === 'Active') ? 'badge-ready' : 'badge-rejected';
                $statusText = htmlspecialchars($doc['status']);
                $toggleStatus = ($doc['status'] === 'Active') ? 'Inactive' : 'Active';
                $toggleText = ($doc['status'] === 'Active') ? 'Make Unavailable' : 'Make Available';
            ?>
            <tr>
              <td>
                <div style="font-weight: 600;"><?php echo htmlspecialchars($doc['name']); ?></div>
              </td>
              <td style="font-family: var(--font-mono); font-weight: 550;"><?php echo $feeDisplay; ?></td>
              <td><?php echo htmlspecialchars(substr($doc['description'] ?? '', 0, 50)); ?></td>
              <td>
                <span class="badge <?php echo $statusColor; ?>"><?php echo $statusText; ?></span>
              </td>
              <td>
                <div class="table-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
                  <a href="#edit-doc-<?php echo $doc['id']; ?>" class="btn btn-secondary btn-size-sm">✏️ Edit</a>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="doc_id" value="<?php echo $doc['id']; ?>">
                    <input type="hidden" name="status" value="<?php echo $toggleStatus; ?>">
                    <button type="submit" class="btn btn-secondary btn-size-sm"><?php echo $toggleText; ?></button>
                  </form>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="doc_id" value="<?php echo $doc['id']; ?>">
                    <button type="submit" class="btn btn-secondary btn-size-sm btn-delete" onclick="return confirm('Delete this document type? This action cannot be undone.');">🗑️ Delete</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php
              }
            } else {
            ?>
            <tr>
              <td colspan="5" style="text-align: center; padding: 32px; color: var(--text-muted);">No document types found.</td>
            </tr>
            <?php
            }
            ?>
          </tbody>
        </table>
      </section>

    </main>

  </div>

  <!-- ==========================================
       SECTION 2: CSS-ONLY POPUP MODAL (Add New Document)
       ========================================== -->
  <div id="add-type-modal" class="modal-overlay">
    <div class="modal-card">
      
      <!-- Modal Header Panel -->
      <div class="modal-header-panel">
        <h3>Create New Document Type</h3>
        <a href="#" class="modal-close-icon">&times;</a>
      </div>
      
      <!-- Modal Body (Form Fields) -->
      <div class="modal-content-panel">
        <form method="POST" action="document-types.php" id="add-type-form">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="doc_id" value="0">
          
          <div class="form-group">
            <label class="form-label">Document Title *</label>
            <input type="text" name="name" class="form-input" placeholder="e.g. Barangay Clearance" required>
          </div>

          <div class="form-group">
            <label class="form-label">Brief Description</label>
            <textarea name="description" class="form-textarea" placeholder="Describe the purpose or details of this certificate..." rows="3"></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Base Processing Fee (PHP)</label>
            <input type="number" name="fee" class="form-input" placeholder="e.g. 50" min="0" step="10" value="0">
          </div>
        </form>
      </div>

      <!-- Modal Footer Panel with Action Triggers -->
      <footer class="modal-footer">
        <a href="#" class="btn btn-secondary">Close</a>
        <button form="add-type-form" type="submit" class="btn btn-primary">Save Document Type</button>
      </footer>

    </div>
  </div>

  <!-- ==========================================
       SECTION 3: DYNAMIC EDIT MODALS FOR EACH DOCUMENT
       ========================================== -->
  <?php foreach ($documents as $doc): ?>
  <div id="edit-doc-<?php echo $doc['id']; ?>" class="modal-overlay">
    <div class="modal-card">
      
      <!-- Modal Header Panel -->
      <div class="modal-header-panel">
        <h3>Edit Document Type: <?php echo htmlspecialchars($doc['name']); ?></h3>
        <a href="#" class="modal-close-icon">&times;</a>
      </div>
      
      <!-- Modal Body (Form Fields) -->
      <div class="modal-content-panel">
        <form method="POST" action="document-types.php" id="edit-doc-form-<?php echo $doc['id']; ?>">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="doc_id" value="<?php echo $doc['id']; ?>">
          
          <div class="form-group">
            <label class="form-label">Document Title *</label>
            <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($doc['name']); ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Brief Description</label>
            <textarea name="description" class="form-textarea" rows="3"><?php echo htmlspecialchars($doc['description'] ?? ''); ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Base Processing Fee (PHP)</label>
            <input type="number" name="fee" class="form-input" value="<?php echo htmlspecialchars($doc['fee']); ?>" min="0" step="10">
          </div>

          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status_display" class="form-input" disabled>
              <option value="Active" <?php echo ($doc['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
              <option value="Inactive" <?php echo ($doc['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 8px;">⚠️ Use the "Make Available/Unavailable" button in the table to change status</p>
          </div>
        </form>
      </div>

      <!-- Modal Footer Panel with Action Triggers -->
      <footer class="modal-footer">
        <a href="#" class="btn btn-secondary">Close</a>
        <button form="edit-doc-form-<?php echo $doc['id']; ?>" type="submit" class="btn btn-primary">Update Document</button>
      </footer>

    </div>
  </div>
  <?php endforeach; ?>

</body>
</html>
