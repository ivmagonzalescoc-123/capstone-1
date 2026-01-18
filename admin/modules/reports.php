<?php
/**
 * Admin Reports Module
 */

// Get statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM User_Account WHERE is_active = 1");
$stats['active_users'] = $result->fetch_assoc()['count'] ?? 0;

// Total doctors
$result = $conn->query("SELECT COUNT(*) as count FROM User_Account WHERE role_id = 2 AND is_active = 1");
$stats['active_doctors'] = $result->fetch_assoc()['count'] ?? 0;

// Total patients
$result = $conn->query("SELECT COUNT(*) as count FROM Patients");
$stats['total_patients'] = $result->fetch_assoc()['count'] ?? 0;

// Today's appointments
$result = $conn->query("SELECT COUNT(*) as count FROM Appointments WHERE DATE(appointment_date) = DATE(NOW())");
$stats['today_appointments'] = $result->fetch_assoc()['count'] ?? 0;

// Total revenue
$result = $conn->query("SELECT COALESCE(SUM(amount_paid), 0) as total FROM Payment");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Appointment status breakdown
$status_data = [];
$result = $conn->query("
    SELECT s.status_name, COUNT(a.appointment_id) as count
    FROM Appointments a
    LEFT JOIN Status s ON a.status_id = s.status_id
    GROUP BY a.status_id
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status_data[] = $row;
    }
}
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">
        <i class="bi bi-graph-up me-2"></i>System Reports
    </h2>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary"><i class="bi bi-people" style="font-size: 2rem;"></i></div>
                    <h6 class="text-muted mt-2">Active Users</h6>
                    <h3><?php echo $stats['active_users']; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success"><i class="bi bi-hospital" style="font-size: 2rem;"></i></div>
                    <h6 class="text-muted mt-2">Active Doctors</h6>
                    <h3><?php echo $stats['active_doctors']; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info"><i class="bi bi-person-heart" style="font-size: 2rem;"></i></div>
                    <h6 class="text-muted mt-2">Total Patients</h6>
                    <h3><?php echo $stats['total_patients']; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning"><i class="bi bi-calendar-event" style="font-size: 2rem;"></i></div>
                    <h6 class="text-muted mt-2">Today's Appointments</h6>
                    <h3><?php echo $stats['today_appointments']; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Total Revenue</h6>
                </div>
                <div class="card-body">
                    <h2 class="text-success">₱<?php echo number_format($stats['total_revenue'], 2); ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Appointment Status Distribution</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($status_data as $status): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <strong><?php echo htmlspecialchars($status['status_name']); ?></strong>
                                <span><?php echo $status['count']; ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo ($status['count'] / max($status_data[0]['count'], 1) * 100); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary { border-left: 4px solid #0066cc !important; }
.border-left-success { border-left: 4px solid #28a745 !important; }
.border-left-info { border-left: 4px solid #17a2b8 !important; }
.border-left-warning { border-left: 4px solid #fd7e14 !important; }

.card-body > div:first-child { font-size: 2rem; }
</style>
