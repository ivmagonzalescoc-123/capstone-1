<?php
/**
 * Doctor Consultation Module
 * Doctor sees queue and manages consultation, treatment, prescription, and completion
 */

// This module is usually included by doctor/index.php. If accessed directly,
// we need to initialize session + DB connection.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    $conn = new mysqli("localhost", "root", "", "azucena_dental");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== 'doctor') {
    $conn->close();
    header('Location: ../../index.php');
    exit;
}

// Get queued and in-progress appointments
$doctor_id = (int) $_SESSION['user_id'];

$result = $conn->query("
    SELECT a.appointment_id, a.appointment_date, a.appointment_time,
           p.patients_id, p.first_name, p.last_name, p.phone_number,
           s.status_name
    FROM Appointments a
    LEFT JOIN Patients p ON a.patient_id = p.patients_id
    LEFT JOIN Status s ON a.status_id = s.status_id
    WHERE a.doctor_id = $doctor_id
    AND a.status_id IN (25, 26)
    AND a.appointment_date >= CURDATE()
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");

$queue_appointments = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $queue_appointments[] = $row;
    }
}
?>

<h3 style="color: #1e3c72; font-weight: 700; margin-bottom: 20px;">Patient Queue & Consultation</h3>

<div class="row mb-4">
    <div class="col-md-8">
        <h5 style="color: #1e3c72; font-weight: 600; margin-bottom: 15px;">Today's Queue</h5>
        
        <div class="data-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Patient Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($queue_appointments) > 0): ?>
                        <?php foreach ($queue_appointments as $apt): ?>
                            <tr>
                                <td><strong><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></strong></td>
                                <td><?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?></td>
                                <td>
                                    <span class="badge badge-info"><?php echo $apt['status_name']; ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" 
                                        onclick="startConsultation(<?php echo $apt['appointment_id']; ?>, <?php echo $apt['patients_id']; ?>)">
                                            <i class="bi bi-stethoscope"></i> Consultation
                                    </button>
                                    <button class="btn btn-sm btn-info" 
                                        onclick="openOdontogram(<?php echo $apt['appointment_id']; ?>, <?php echo $apt['patients_id']; ?>)"
                                        title="Record tooth status">
                                            <i class="bi bi-tooth"></i> Odontogram
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No patients in queue</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
            <h5>Queue Statistics</h5>
            <p class="mb-2">
                <strong style="font-size: 2rem;"><?php echo count($queue_appointments); ?></strong><br>
                <small>Patients Waiting</small>
            </p>
            <hr style="border-color: rgba(255,255,255,0.3);">
            <p style="font-size: 0.9rem;">Complete consultations and proceed to generate prescriptions and close appointments.</p>
        </div>
    </div>
</div>

<script>
function startConsultation(appointmentId, patientId) {
    // Navigate to dedicated consultation recording page
    window.location.href = `./modules/consultation-record.php?appointment_id=${appointmentId}&patient_id=${patientId}`;
}

function openOdontogram(appointmentId, patientId) {
    // Set appointment ID and open odontogram modal
    document.getElementById('odontogramModal').dataset.appointmentId = appointmentId;
    document.getElementById('odontogramModal').dataset.patientId = patientId;
    const modal = new bootstrap.Modal(document.getElementById('odontogramModal'));
    modal.show();
}
</script>

<?php
// Include odontogram modal component
include(__DIR__ . '/odontogram-modal.php');
?>

