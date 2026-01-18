<?php
/**
 * Secretary Patients Module
 */

$patients = [];
$result = $conn->query("
    SELECT p.patients_id, p.first_name, p.last_name, p.phone_number, p.email, p.gender,
           COUNT(a.appointment_id) as total_visits
    FROM Patients p
    LEFT JOIN Appointments a ON p.patients_id = a.patient_id
    GROUP BY p.patients_id
    ORDER BY p.first_name
    LIMIT 100
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Patients</h2>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Total Visits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($patient['email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($patient['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($patient['gender'] ?? 'N/A'); ?></td>
                            <td><span class="badge bg-info"><?php echo $patient['total_visits']; ?></span></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-warning">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
