<?php
/**
 * Patient Profile Module
 */

$user_id = $_SESSION['user_id'];

// Get patient_id from user_id
$patient_query = "SELECT patients_id FROM Patients WHERE user_id = ?";
$patient_stmt = $conn->prepare($patient_query);
$patient_stmt->bind_param("i", $user_id);
$patient_stmt->execute();
$patient_result = $patient_stmt->get_result();

if ($patient_result->num_rows === 0) {
    die("Patient record not found");
}

$patient_data = $patient_result->fetch_assoc();
$patient_id = $patient_data['patients_id'];
$patient_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    
    $stmt = $conn->prepare("UPDATE Patients SET first_name=?, last_name=?, phone_number=? WHERE patients_id=?");
    $stmt->bind_param("sssi", $first_name, $last_name, $phone, $patient_id);
    
    if ($stmt->execute()) {
        $_SESSION['full_name'] = $first_name . ' ' . $last_name;
        $_SESSION['success'] = 'Profile updated';
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM Patients WHERE patients_id=?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-info text-white">
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
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($patient['first_name']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($patient['last_name']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($patient['email'] ?? ''); ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($patient['phone_number']); ?>">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-info">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
