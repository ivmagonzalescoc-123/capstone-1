<?php
/**
 * Patient My Appointments Module
 */

// Get patient_id from user_id
$user_id = $_SESSION['user_id'];
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

$appointments = [];

$result = $conn->query("
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.is_online_appointment,
           s.status_name, ser.service_name, u.first_name, u.last_name
    FROM Appointments a
    LEFT JOIN Status s ON a.status_id = s.status_id
    LEFT JOIN Services ser ON a.appointment_id = ser.service_id
    LEFT JOIN User_Account u ON a.doctor_id = u.user_id
    WHERE a.patient_id = $patient_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 50
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">My Appointments</h2>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Doctor</th>
                        <th>Service</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $apt): ?>
                        <tr>
                            <td>
                                <?php 
                                    $datetime = $apt['appointment_date'];
                                    if ($apt['appointment_time']) {
                                        $datetime .= ' ' . $apt['appointment_time'];
                                    }
                                    echo date('M d, Y H:i', strtotime($datetime)); 
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars(($apt['first_name'] ?? '') . ' ' . ($apt['last_name'] ?? 'N/A')); ?></td>
                            <td><?php echo htmlspecialchars($apt['service_name'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($apt['is_online_appointment'] == 1): ?>
                                    <span class="badge bg-secondary">Online</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">In-Clinic</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars($apt['status_name'] ?? 'N/A'); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
