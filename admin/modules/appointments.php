<?php
/**
 * Admin Appointments Management Module
 */

// Get all appointments with patient and doctor info
$appointments = [];
$result = $conn->query("
    SELECT a.appointment_id, a.appointment_date, a.is_online_appointment, 
           p.first_name as patient_first, p.last_name as patient_last, p.phone_number as patient_phone,
           s.status_name, a.created_at
    FROM Appointments a
    LEFT JOIN Patients p ON a.patient_id = p.patients_id
    LEFT JOIN Status s ON a.status_id = s.status_id
    ORDER BY a.appointment_date DESC
    LIMIT 100
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">
                <i class="bi bi-calendar2 me-2"></i>Appointments
            </h2>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Patient Name</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $apt): ?>
                        <tr>
                            <td><strong><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></strong></td>
                            <td><?php echo htmlspecialchars($apt['patient_first'] . ' ' . $apt['patient_last']); ?></td>
                            <td><?php echo htmlspecialchars($apt['patient_phone'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge <?php echo $apt['is_online_appointment'] ? 'bg-info' : 'bg-success'; ?>">
                                    <?php echo $apt['is_online_appointment'] ? 'Online' : 'Clinic'; ?>
                                </span>
                            </td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($apt['status_name'] ?? 'Pending'); ?></span></td>
                            <td><small><?php echo date('M d, Y', strtotime($apt['created_at'])); ?></small></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
