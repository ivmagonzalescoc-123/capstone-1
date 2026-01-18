<?php
/**
 * Patient Dashboard Module
 */

$patient_id = $_SESSION['user_id'];

// Get upcoming appointments
$upcoming = [];
$result = $conn->query("
    SELECT a.appointment_id, a.appointment_date,
           s.status_name, ser.service_name
    FROM Appointments a
    LEFT JOIN Status s ON a.status_id = s.status_id
    LEFT JOIN Selected_Services ss ON a.appointment_id = ss.appointment_id
    LEFT JOIN Services ser ON ss.service_id = ser.service_id
    WHERE a.patient_id = $patient_id AND a.appointment_date > NOW()
    ORDER BY a.appointment_date ASC
    LIMIT 5
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $upcoming[] = $row;
    }
}

// Get recent appointments count
$result = $conn->query("SELECT COUNT(*) as count FROM Appointments WHERE patient_id = $patient_id");
$appointment_count = $result->fetch_assoc()['count'] ?? 0;
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h2>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info"><i class="bi bi-calendar-check" style="font-size: 2rem;"></i></div>
                    <h6 class="text-muted mt-2">Total Appointments</h6>
                    <h3><?php echo $appointment_count; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Upcoming Appointments</h6>
        </div>
        <div class="card-body">
            <?php if (count($upcoming) > 0): ?>
                <div class="list-group">
                    <?php foreach ($upcoming as $apt): ?>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($apt['service_name'] ?? 'Appointment'); ?></h6>
                                    <small class="text-muted"><?php echo date('F d, Y H:i', strtotime($apt['appointment_date'])); ?></small>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-info"><?php echo htmlspecialchars($apt['status_name']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">No upcoming appointments</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="index.php?module=book-appointment" class="btn btn-outline-info me-2">
                        <i class="bi bi-plus-circle me-1"></i>Book Appointment
                    </a>
                    <a href="index.php?module=my-appointments" class="btn btn-outline-info me-2">
                        <i class="bi bi-calendar-check me-1"></i>My Appointments
                    </a>
                    <a href="index.php?module=profile" class="btn btn-outline-info">
                        <i class="bi bi-person me-1"></i>My Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-info { border-left: 4px solid #17a2b8 !important; }
</style>
