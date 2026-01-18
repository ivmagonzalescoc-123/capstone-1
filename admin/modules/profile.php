<?php
/**
 * Admin Profile Module
 */

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    $stmt = $conn->prepare("UPDATE User_Account SET first_name=?, last_name=?, phone_number=?, address=? WHERE user_id=?");
    $stmt->bind_param("ssssi", $first_name, $last_name, $phone, $address, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['full_name'] = $first_name . ' ' . $last_name;
        $_SESSION['success'] = 'Profile updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update profile';
    }
    $stmt->close();
}

// Get user info
$stmt = $conn->prepare("SELECT * FROM User_Account WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get role name
$role_result = $conn->query("SELECT role_name FROM Role WHERE role_id = " . $user['role_id']);
$role_data = $role_result->fetch_assoc();
$role_name = $role_data['role_name'];
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle me-2"></i>My Profile
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($role_name); ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Member Since</label>
                                <input type="text" class="form-control" value="<?php echo date('M d, Y', strtotime($user['created_at'])); ?>" disabled>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
