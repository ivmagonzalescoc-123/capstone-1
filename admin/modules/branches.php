<?php
/**
 * Admin Branches Management Module
 * Manage dental clinic branches
 */

$action = isset($_GET['action']) ? $_GET['action'] : null;
$branch_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                addBranch();
                break;
            case 'edit':
                editBranch();
                break;
            case 'delete':
                deleteBranch();
                break;
        }
    }
}

function addBranch() {
    global $conn;
    
    $branch_name = trim($_POST['branch_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $manager = trim($_POST['manager'] ?? '');
    
    if (empty($branch_name) || empty($location)) {
        $_SESSION['error'] = 'Branch name and location are required';
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO Branch (branch_name, location, phone_number, email, manager_name, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $branch_name, $location, $phone, $email, $manager);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Branch added successfully';
    } else {
        $_SESSION['error'] = 'Failed to add branch';
    }
    $stmt->close();
}

function editBranch() {
    global $conn;
    
    $branch_id = (int)($_POST['branch_id'] ?? 0);
    $branch_name = trim($_POST['branch_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $manager = trim($_POST['manager'] ?? '');
    
    $stmt = $conn->prepare("UPDATE Branch SET branch_name=?, location=?, phone_number=?, email=?, manager_name=? WHERE branch_id=?");
    $stmt->bind_param("sssssi", $branch_name, $location, $phone, $email, $manager, $branch_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Branch updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update branch';
    }
    $stmt->close();
}

function deleteBranch() {
    global $conn;
    
    $branch_id = (int)($_POST['branch_id'] ?? 0);
    
    $stmt = $conn->prepare("DELETE FROM Branch WHERE branch_id=?");
    $stmt->bind_param("i", $branch_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Branch deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete branch';
    }
    $stmt->close();
}

// Get branch to edit
$edit_branch = null;
if ($action === 'edit' && $branch_id) {
    $stmt = $conn->prepare("SELECT branch_id, branch_name, location, phone_number, email, manager_name FROM Branch WHERE branch_id=?");
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $edit_branch = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get all branches
$branches = [];
$branch_result = $conn->query("SELECT branch_id, branch_name, location, phone_number, email, manager_name, created_at FROM Branch ORDER BY branch_name");
if ($branch_result) {
    while ($row = $branch_result->fetch_assoc()) {
        $branches[] = $row;
    }
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">
                <i class="bi bi-diagram-2 me-2"></i>Branches
            </h2>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                <i class="bi bi-plus-circle me-1"></i>Add New Branch
            </button>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Branches Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Branch Name</th>
                        <th>Location</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Manager</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($branches)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No branches found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($branches as $branch): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($branch['branch_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($branch['location']); ?></td>
                                <td><?php echo htmlspecialchars($branch['phone_number'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($branch['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($branch['manager_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <small><?php echo date('M d, Y', strtotime($branch['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?module=branches&action=edit&id=<?php echo $branch['branch_id']; ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="branch_id" value="<?php echo $branch['branch_id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger" title="Delete" 
                                                    onclick="return confirm('Delete this branch?');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Branch Modal -->
<div class="modal fade" id="addBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label">Branch Name *</label>
                        <input type="text" name="branch_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location *</label>
                        <input type="text" name="location" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Manager Name</label>
                        <input type="text" name="manager" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Branch Modal -->
<?php if ($edit_branch): ?>
<div class="modal fade" id="editBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="branch_id" value="<?php echo $edit_branch['branch_id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Branch Name *</label>
                        <input type="text" name="branch_name" class="form-control" value="<?php echo htmlspecialchars($edit_branch['branch_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location *</label>
                        <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($edit_branch['location']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($edit_branch['phone_number'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit_branch['email'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Manager Name</label>
                        <input type="text" name="manager" class="form-control" value="<?php echo htmlspecialchars($edit_branch['manager_name'] ?? ''); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="index.php?module=branches" class="btn btn-secondary">Close</a>
                    <button type="submit" class="btn btn-primary">Update Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Show edit modal on page load
    var editModal = new bootstrap.Modal(document.getElementById('editBranchModal'), {
        backdrop: 'static',
        keyboard: false
    });
    editModal.show();
</script>
<?php endif; ?>

<style>
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

table.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
