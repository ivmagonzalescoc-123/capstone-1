<?php
/**
 * Admin Users Management Module
 * Manage system users: add, edit, archive, restore
 */

$action = isset($_GET['action']) ? $_GET['action'] : null;
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                addUser();
                break;
            case 'edit':
                editUser();
                break;
            case 'archive':
                archiveUser();
                break;
            case 'restore':
                restoreUser();
                break;
        }
    }
}

function addUser() {
    global $conn;
    
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role_id = (int)($_POST['role_id'] ?? 1);
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'All required fields must be filled';
        return;
    }
    
    // Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM User_Account WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'Email already exists';
        $check->close();
        return;
    }
    $check->close();
    
    $stmt = $conn->prepare("INSERT INTO User_Account (first_name, last_name, email, username, password, phone_number, role_id, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())");
    $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $username, $password, $phone, $role_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'User added successfully';
    } else {
        $_SESSION['error'] = 'Failed to add user: ' . $stmt->error;
    }
    $stmt->close();
}

function editUser() {
    global $conn;
    
    $user_id = (int)($_POST['user_id'] ?? 0);
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role_id = (int)($_POST['role_id'] ?? 1);
    
    $stmt = $conn->prepare("UPDATE User_Account SET first_name=?, last_name=?, email=?, phone_number=?, role_id=? WHERE user_id=?");
    $stmt->bind_param("ssssii", $first_name, $last_name, $email, $phone, $role_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'User updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update user';
    }
    $stmt->close();
}

function archiveUser() {
    global $conn;
    
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    // Prevent archiving own account
    if ($user_id === $_SESSION['user_id']) {
        $_SESSION['error'] = 'Cannot archive your own account';
        return;
    }
    
    $stmt = $conn->prepare("UPDATE User_Account SET is_active=0 WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'User archived successfully';
    } else {
        $_SESSION['error'] = 'Failed to archive user';
    }
    $stmt->close();
}

function restoreUser() {
    global $conn;
    
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    $stmt = $conn->prepare("UPDATE User_Account SET is_active=1 WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'User restored successfully';
    } else {
        $_SESSION['error'] = 'Failed to restore user';
    }
    $stmt->close();
}

// Get all roles
$roles = [];
$role_result = $conn->query("SELECT role_id, role_name FROM Role ORDER BY role_name");
if ($role_result) {
    while ($row = $role_result->fetch_assoc()) {
        $roles[] = $row;
    }
}

// Get user to edit
$edit_user = null;
if ($action === 'edit' && $user_id) {
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, username, phone_number, role_id, is_active FROM User_Account WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $edit_user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get all users
$users = [];
$user_result = $conn->query("SELECT u.user_id, u.first_name, u.last_name, u.email, u.username, u.phone_number, u.is_active, u.created_at, r.role_name 
                             FROM User_Account u 
                             LEFT JOIN Role r ON u.role_id = r.role_id 
                             ORDER BY u.created_at DESC");
if ($user_result) {
    while ($row = $user_result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">
                <i class="bi bi-people me-2"></i>User Management
            </h2>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-circle me-1"></i>Add New User
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

    <!-- Users Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo htmlspecialchars($user['role_name']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['phone_number'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Archived</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo date('M d, Y', strtotime($user['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?module=users&action=edit&id=<?php echo $user['user_id']; ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <?php if ($user['is_active'] && $user['user_id'] !== $_SESSION['user_id']): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="archive">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Archive" 
                                                        onclick="return confirm('Archive this user?');">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                        <?php elseif (!$user['is_active']): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <button type="submit" class="btn btn-outline-success" title="Restore">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                        <small class="text-muted">Note: Passwords are stored as plain text for testing</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role_id" class="form-select" required>
                            <option value="">-- Select Role --</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['role_id']; ?>">
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<?php if ($edit_user): ?>
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($edit_user['first_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($edit_user['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($edit_user['username'] ?? ''); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($edit_user['phone_number'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role_id" class="form-select" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['role_id']; ?>" 
                                    <?php echo $role['role_id'] === $edit_user['role_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="index.php?module=users" class="btn btn-secondary">Close</a>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Show edit modal on page load
    var editModal = new bootstrap.Modal(document.getElementById('editUserModal'), {
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

.badge {
    padding: 0.35rem 0.65rem;
    font-size: 0.8rem;
}
</style>
