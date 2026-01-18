<?php
/**
 * Secretary Profile Module
 */

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    
    $stmt = $conn->prepare("UPDATE User_Account SET first_name=?, last_name=?, phone_number=? WHERE user_id=?");
    $stmt->bind_param("sssi", $first_name, $last_name, $phone, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['full_name'] = $first_name . ' ' . $last_name;
        $_SESSION['success'] = 'Profile updated';
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM User_Account WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">My Profile</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
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

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
