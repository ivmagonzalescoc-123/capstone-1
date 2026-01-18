<?php
/**
 * Admin Services Management Module
 * Manage dental services offered
 */

$action = isset($_GET['action']) ? $_GET['action'] : null;
$service_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action'] ?? null) {
        case 'add':
            $name = trim($_POST['service_name'] ?? '');
            $deposit = floatval($_POST['initial_deposit'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            
            if (!empty($name)) {
                $stmt = $conn->prepare("INSERT INTO Services (service_name, initial_deposit, description, added_by, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("sdsi", $name, $deposit, $description, $_SESSION['user_id']);
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Service added successfully';
                } else {
                    $_SESSION['error'] = 'Failed to add service';
                }
                $stmt->close();
            }
            break;
            
        case 'edit':
            $id = (int)($_POST['service_id'] ?? 0);
            $name = trim($_POST['service_name'] ?? '');
            $deposit = floatval($_POST['initial_deposit'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            
            $stmt = $conn->prepare("UPDATE Services SET service_name=?, initial_deposit=?, description=? WHERE service_id=?");
            $stmt->bind_param("sdsi", $name, $deposit, $description, $id);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Service updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update service';
            }
            $stmt->close();
            break;
            
        case 'delete':
            $id = (int)($_POST['service_id'] ?? 0);
            $stmt = $conn->prepare("DELETE FROM Services WHERE service_id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Service deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete service';
            }
            $stmt->close();
            break;
    }
}

// Get service to edit
$edit_service = null;
if ($action === 'edit' && $service_id) {
    $stmt = $conn->prepare("SELECT service_id, service_name, initial_deposit, description FROM Services WHERE service_id=?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $edit_service = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get all services
$services = [];
$result = $conn->query("SELECT service_id, service_name, initial_deposit, description, created_at FROM Services ORDER BY service_name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">
                <i class="bi bi-list-check me-2"></i>Services
            </h2>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                <i class="bi bi-plus-circle me-1"></i>Add Service
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

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Service Name</th>
                        <th>Initial Deposit</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($service['service_name']); ?></strong></td>
                            <td>₱<?php echo number_format($service['initial_deposit'], 2); ?></td>
                            <td><?php echo htmlspecialchars(substr($service['description'] ?? '', 0, 50)); ?></td>
                            <td><small><?php echo date('M d, Y', strtotime($service['created_at'])); ?></small></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="index.php?module=services&action=edit&id=<?php echo $service['service_id']; ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="service_id" value="<?php echo $service['service_id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Delete this service?');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Service Name *</label>
                        <input type="text" name="service_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Deposit</label>
                        <input type="number" name="initial_deposit" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<?php if ($edit_service): ?>
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="service_id" value="<?php echo $edit_service['service_id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Service Name *</label>
                        <input type="text" name="service_name" class="form-control" value="<?php echo htmlspecialchars($edit_service['service_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Deposit</label>
                        <input type="number" name="initial_deposit" class="form-control" step="0.01" min="0" value="<?php echo $edit_service['initial_deposit']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($edit_service['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="index.php?module=services" class="btn btn-secondary">Close</a>
                    <button type="submit" class="btn btn-primary">Update Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var editModal = new bootstrap.Modal(document.getElementById('editServiceModal'), { backdrop: 'static', keyboard: false });
    editModal.show();
</script>
<?php endif; ?>
