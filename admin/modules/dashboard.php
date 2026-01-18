<?php
/**
 * Admin Dashboard Module
 * Main statistics and overview
 */

// Get statistics
$result = $conn->query("SELECT COUNT(*) as count FROM User_Account WHERE is_active = 1");
$users_count = $result->fetch_assoc()['count'] ?? 0;

$result = $conn->query("SELECT COUNT(*) as count FROM User_Account WHERE role_id = 2 AND is_active = 1");
$doctors_count = $result->fetch_assoc()['count'] ?? 0;

$result = $conn->query("SELECT COUNT(*) as count FROM Patients");
$patients_count = $result->fetch_assoc()['count'] ?? 0;

$result = $conn->query("SELECT COUNT(*) as count FROM Appointments WHERE DATE(appointment_date) = CURDATE()");
$today_appointments = $result->fetch_assoc()['count'] ?? 0;
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Admin Dashboard</h2>

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary"><i class="bi bi-people" style="font-size: 2rem;"></i></div>
                    <h6 class="text-muted mt-2">Active Users</h6>
                    <h3><?php echo $users_count; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success"><i class="bi bi-hospital" style="font-size: 2rem;"></i></div>
                    <h6 class="text-muted mt-2">Active Doctors</h6>
                    <h3><?php echo $doctors_count; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info"><i class="bi bi-person-hearts" style="font-size: 2rem;"></i></div>
                    <h6 class="text-muted mt-2">Total Patients</h6>
                    <h3><?php echo $patients_count; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning"><i class="bi bi-calendar2" style="font-size: 2rem;"></i></div>
                    <h6 class="text-muted mt-2">Today's Appointments</h6>
                    <h3><?php echo $today_appointments; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="index.php?module=users" class="btn btn-outline-primary me-2">
                        <i class="bi bi-plus me-1"></i>Add User
                    </a>
                    <a href="index.php?module=doctors" class="btn btn-outline-success me-2">
                        <i class="bi bi-plus me-1"></i>Add Doctor
                    </a>
                    <a href="index.php?module=branches" class="btn btn-outline-info me-2">
                        <i class="bi bi-plus me-1"></i>Add Branch
                    </a>
                    <a href="index.php?module=appointments" class="btn btn-outline-warning">
                        <i class="bi bi-calendar me-1"></i>View Appointments
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary { border-left: 4px solid #007bff !important; }
.border-left-success { border-left: 4px solid #28a745 !important; }
.border-left-info { border-left: 4px solid #17a2b8 !important; }
.border-left-warning { border-left: 4px solid #ffc107 !important; }
</style>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
