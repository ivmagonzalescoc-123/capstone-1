<?php
/**
 * Secretary Dashboard Module
 */

// Get secretary stats
$stats = [];

$result = $conn->query("SELECT COUNT(*) as count FROM Appointments WHERE DATE(appointment_date) = DATE(NOW())");
$stats['today_appointments'] = $result->fetch_assoc()['count'] ?? 0;

$result = $conn->query("SELECT COUNT(*) as count FROM Patients");
$stats['total_patients'] = $result->fetch_assoc()['count'] ?? 0;

$result = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM Billing WHERE DATE(created_at) = DATE(NOW())");
$stats['today_billing'] = $result->fetch_assoc()['total'] ?? 0;
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h2>

    <div class="row mb-4 justify-content-center">
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning"><i class="bi bi-calendar-check" style="font-size: 2rem;"></i></div>
                    <h6 class="text-muted mt-2">Today's Appointments</h6>
                    <h3><?php echo $stats['today_appointments']; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info"><i class="bi bi-people" style="font-size: 2rem;"></i></div>
                    <h6 class="text-muted mt-2">Total Patients</h6>
                    <h3><?php echo $stats['total_patients']; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success"><i class="bi bi-cash-coin" style="font-size: 2rem;"></i></div>
                    <h6 class="text-muted mt-2">Today's Billing</h6>
                    <h3>₱<?php echo number_format($stats['today_billing'], 2); ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>
