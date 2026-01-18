<?php
/**
 * Doctor Appointments Module
 */

$appointments = [];
$result = $conn->query("
    SELECT a.appointment_id, a.appointment_date, a.is_online_appointment,
           p.first_name, p.last_name, p.phone_number,
           s.status_name,
           ss.selected_service_id, ser.service_name
    FROM Appointments a
    LEFT JOIN Patients p ON a.patient_id = p.patients_id
    LEFT JOIN Status s ON a.status_id = s.status_id
    LEFT JOIN Selected_Services ss ON a.appointment_id = ss.appointment_id
    LEFT JOIN Services ser ON ss.service_id = ser.service_id
    WHERE a.appointment_date >= DATE(NOW())
    ORDER BY a.appointment_date DESC
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
                        <th>Patient</th>
                        <th>Phone</th>
                        <th>Service</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $apt): ?>
                        <tr>
                            <td><strong><?php echo date('M d, Y H:i', strtotime($apt['appointment_date'])); ?></strong></td>
                            <td><?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($apt['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($apt['service_name'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge <?php echo $apt['is_online_appointment'] ? 'bg-info' : 'bg-success'; ?>">
                                    <?php echo $apt['is_online_appointment'] ? 'Online' : 'Clinic'; ?>
                                </span>
                            </td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($apt['status_name'] ?? 'Pending'); ?></span></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
