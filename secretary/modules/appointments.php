<?php
/**
 * Secretary Appointments Module
 * Shows appointments grouped by date with check-in functionality
 */

$result = $conn->query("
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, 
           a.status_id, a.is_online_appointment,
           p.patients_id, p.first_name, p.last_name, p.phone_number,
           d.first_name as doc_fname, d.last_name as doc_lname,
           s.status_name
    FROM Appointments a
    LEFT JOIN Patients p ON a.patient_id = p.patients_id
    LEFT JOIN User_Account d ON a.doctor_id = d.user_id
    LEFT JOIN Status s ON a.status_id = s.status_id
    WHERE a.appointment_date >= CURDATE()
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");

$appointments_by_date = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $date = $row['appointment_date'];
        if (!isset($appointments_by_date[$date])) {
            $appointments_by_date[$date] = [];
        }
        $appointments_by_date[$date][] = $row;
    }
}
?>

<h3 style="color: #1e3c72; font-weight: 700; margin-bottom: 20px;">Appointments Management</h3>

<div class="search-section">
    <input type="text" class="search-input" id="searchPatient" placeholder="Search Patient Name or ID">
    <button class="search-btn" onclick="filterAppointments()">Search</button>
</div>

<?php foreach ($appointments_by_date as $date => $appointments): ?>
    <div style="margin-bottom: 40px;">
        <h3 style="color: #1e3c72; font-weight: 700; margin-bottom: 20px;">
            <?php 
            $is_today = ($date === date('Y-m-d'));
            echo $is_today ? '📅 Today - ' . date('M d, Y', strtotime($date)) : '📆 ' . date('M d, Y', strtotime($date)); 
            ?>
            <span style="font-size: 0.9rem; color: #666; margin-left: 10px;">(<?php echo count($appointments); ?> appointments)</span>
        </h3>

        <div class="data-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Patient Name</th>
                        <th>Contact</th>
                        <th>Doctor</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $apt): ?>
                        <tr>
                            <td>
                                <strong><?php echo $apt['appointment_time'] ? date('h:i A', strtotime($apt['appointment_time'])) : 'N/A'; ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($apt['phone_number'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(($apt['doc_fname'] ?? '') . ' ' . ($apt['doc_lname'] ?? '')); ?></td>
                            <td>
                                <?php if ($apt['is_online_appointment'] == 1): ?>
                                    <span class="badge badge-online">Online</span>
                                <?php else: ?>
                                    <span class="badge badge-onsite">In-Clinic</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status = $apt['status_name'] ?? 'Pending';
                                $badge_class = 'badge-pending';
                                if ($status === 'Confirmed') $badge_class = 'badge-confirmed';
                                elseif ($status === 'Completed') $badge_class = 'badge-completed';
                                elseif ($status === 'In-Queue') $badge_class = 'badge-info';
                                elseif ($status === 'Cancelled') $badge_class = 'badge-cancelled';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#appointmentModal" 
                                    onclick="viewAppointment(<?php echo $apt['appointment_id']; ?>, '<?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?>', '<?php echo $apt['appointment_date']; ?>', '<?php echo $apt['appointment_time']; ?>')">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                
                                <?php if ($is_today && $apt['status_id'] == 1): ?>
                                    <button class="btn btn-sm btn-success" onclick="checkInPatient(<?php echo $apt['appointment_id']; ?>, '<?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?>')">
                                        <i class="bi bi-check-circle"></i> Check In
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>

<!-- APPOINTMENT DETAILS MODAL -->
<div class="modal fade" id="appointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Appointment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appointmentDetails">
                <p class="text-muted">Loading...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .badge-info {
        background-color: #0dcaf0;
        color: #000;
    }
</style>

<script>
function viewAppointment(appointmentId, patientName, date, time) {
    const details = `
        <div class="row">
            <div class="col-md-6">
                <p><strong>Patient Name:</strong> ${patientName}</p>
                <p><strong>Date:</strong> ${new Date(date).toLocaleDateString()}</p>
                <p><strong>Time:</strong> ${time ? new Date('2000-01-01 ' + time).toLocaleTimeString() : 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Appointment ID:</strong> #${String(appointmentId).padStart(5, '0')}</p>
                <p><strong>Type:</strong> <span class="badge bg-primary">In-Clinic</span></p>
                <p><strong>Status:</strong> <span class="badge bg-warning">Pending</span></p>
            </div>
        </div>
    `;
    document.getElementById('appointmentDetails').innerHTML = details;
}

function checkInPatient(appointmentId, patientName) {
    if (confirm('Check in ' + patientName + ' to the queue?')) {
        fetch('../ajax/check-in.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ appointment_id: appointmentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(patientName + ' checked in successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Check-in failed'));
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function filterAppointments() {
    const searchTerm = document.getElementById('searchPatient').value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const patientName = row.cells[1].textContent.toLowerCase();
        row.style.display = patientName.includes(searchTerm) ? '' : 'none';
    });
}
</script>
