<?php
/**
 * Admin Doctors Management Module
 * Manage dentists and doctors
 */

$action = isset($_GET['action']) ? $_GET['action'] : null;
$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                addDoctor();
                break;
            case 'edit':
                editDoctor();
                break;
            case 'delete':
                deleteDoctor();
                break;
        }
    }
}

function addDoctor() {
    global $conn;
    
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $license = trim($_POST['license'] ?? '');
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $_SESSION['error'] = 'First name, last name, and email are required';
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO Doctor (first_name, last_name, email, phone_number, specialization, license_number, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssss", $first_name, $last_name, $email, $phone, $specialization, $license);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Doctor added successfully';
    } else {
        $_SESSION['error'] = 'Failed to add doctor';
    }
    $stmt->close();
}

function editDoctor() {
    global $conn;
    
    $doctor_id = (int)($_POST['doctor_id'] ?? 0);
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $license = trim($_POST['license'] ?? '');
    
    $stmt = $conn->prepare("UPDATE Doctor SET first_name=?, last_name=?, email=?, phone_number=?, specialization=?, license_number=? WHERE doctor_id=?");
    $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $phone, $specialization, $license, $doctor_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Doctor updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update doctor';
    }
    $stmt->close();
}

function deleteDoctor() {
    global $conn;
    
    $doctor_id = (int)($_POST['doctor_id'] ?? 0);
    
    $stmt = $conn->prepare("DELETE FROM Doctor WHERE doctor_id=?");
    $stmt->bind_param("i", $doctor_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Doctor deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete doctor';
    }
    $stmt->close();
}

// Get doctor to edit
$edit_doctor = null;
if ($action === 'edit' && $doctor_id) {
    $stmt = $conn->prepare("SELECT doctor_id, first_name, last_name, email, phone_number, specialization, license_number FROM Doctor WHERE doctor_id=?");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $edit_doctor = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get all doctors
$doctors = [];
$doctor_result = $conn->query("SELECT doctor_id, first_name, last_name, email, phone_number, specialization, license_number, created_at FROM Doctor ORDER BY first_name, last_name");
if ($doctor_result) {
    while ($row = $doctor_result->fetch_assoc()) {
        $doctors[] = $row;
    }
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">
                <i class="bi bi-hospital me-2"></i>Doctors
            </h2>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                <i class="bi bi-plus-circle me-1"></i>Add New Doctor
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

    <!-- Doctors Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Specialization</th>
                        <th>License</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($doctors)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No doctors found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['phone_number'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($doctor['specialization'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($doctor['license_number'] ?? 'N/A'); ?></td>
                                <td>
                                    <small><?php echo date('M d, Y', strtotime($doctor['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?module=doctors&action=edit&id=<?php echo $doctor['doctor_id']; ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="doctor_id" value="<?php echo $doctor['doctor_id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger" title="Delete" 
                                                    onclick="return confirm('Delete this doctor?');">
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

<!-- Add Doctor Modal -->
<div class="modal fade" id="addDoctorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Doctor</h5>
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

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Specialization</label>
                        <input type="text" name="specialization" class="form-control" placeholder="e.g., Orthodontics, Periodontics">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">License Number</label>
                        <input type="text" name="license" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Doctor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Doctor Modal -->
<?php if ($edit_doctor): ?>
<div class="modal fade" id="editDoctorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Doctor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="doctor_id" value="<?php echo $edit_doctor['doctor_id']; ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($edit_doctor['first_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($edit_doctor['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit_doctor['email']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($edit_doctor['phone_number'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Specialization</label>
                        <input type="text" name="specialization" class="form-control" value="<?php echo htmlspecialchars($edit_doctor['specialization'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">License Number</label>
                        <input type="text" name="license" class="form-control" value="<?php echo htmlspecialchars($edit_doctor['license_number'] ?? ''); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="index.php?module=doctors" class="btn btn-secondary">Close</a>
                    <button type="submit" class="btn btn-primary">Update Doctor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Show edit modal on page load
    var editModal = new bootstrap.Modal(document.getElementById('editDoctorModal'), {
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
