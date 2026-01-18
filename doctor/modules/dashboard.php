<?php
/**
 * Doctor Dashboard Module
 */

$doctor_id = $_SESSION['user_id'];

// Get doctor stats
$stats = [];

// Today's appointments
$result = $conn->query("
    SELECT COUNT(*) as count FROM Appointments a
    WHERE DATE(a.appointment_date) = DATE(NOW())
");
$stats['today_appointments'] = $result->fetch_assoc()['count'] ?? 0;

// Total patients
$result = $conn->query("SELECT COUNT(DISTINCT a.patient_id) as count FROM Appointments a");
$stats['total_patients'] = $result->fetch_assoc()['count'] ?? 0;

// Upcoming appointments
$result = $conn->query("
    SELECT COUNT(*) as count FROM Appointments a
    WHERE a.appointment_date >= DATE(NOW())
");
$stats['upcoming_appointments'] = $result->fetch_assoc()['count'] ?? 0;
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Welcome, Dr. <?php echo htmlspecialchars($_SESSION['full_name']); ?></h2>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success"><i class="bi bi-calendar-check" style="font-size: 2rem;"></i></div>
                    <h6 class="text-muted mt-2">Today's Appointments</h6>
                    <h3><?php echo $stats['today_appointments']; ?></h3>
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
                    <h6 class="text-muted mt-2">Upcoming</h6>
                    <h3><?php echo $stats['upcoming_appointments']; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Today's Schedule</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $today = [];
                            $result = $conn->query("
                                SELECT a.appointment_id, a.appointment_date, a.is_online_appointment,
                                       p.first_name, p.last_name,
                                       s.status_name
                                FROM Appointments a
                                LEFT JOIN Patients p ON a.patient_id = p.patients_id
                                LEFT JOIN Status s ON a.status_id = s.status_id
                                WHERE DATE(a.appointment_date) = DATE(NOW())
                                ORDER BY a.appointment_date
                                LIMIT 5
                            ");
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo date('H:i', strtotime($row['appointment_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $row['is_online_appointment'] ? 'bg-info' : 'bg-success'; ?>">
                                                <?php echo $row['is_online_appointment'] ? 'Online' : 'Clinic'; ?>
                                            </span>
                                        </td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['status_name'] ?? 'Pending'); ?></span></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="4" class="text-center text-muted">No appointments today</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="index.php?module=appointments" class="btn btn-outline-success btn-block mb-2 w-100">
                        <i class="bi bi-calendar2 me-1"></i>View Appointments
                    </a>
                    <a href="index.php?module=patients" class="btn btn-outline-info btn-block mb-2 w-100">
                        <i class="bi bi-people me-1"></i>View Patients
                    </a>
                    <a href="index.php?module=profile" class="btn btn-outline-primary btn-block w-100">
                        <i class="bi bi-person me-1"></i>My Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-success { border-left: 4px solid #28a745 !important; }
.border-left-info { border-left: 4px solid #17a2b8 !important; }
.border-left-warning { border-left: 4px solid #fd7e14 !important; }
.btn-block { display: block; width: 100%; }
</style>
