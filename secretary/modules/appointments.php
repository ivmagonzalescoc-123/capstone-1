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
                                $status_desc = $status;
                                $badge_class = 'badge-pending';
                                
                                if ($status === 'Confirmed') {
                                    $badge_class = 'badge-confirmed';
                                    $status_desc = 'Confirmed';
                                }
                                elseif ($status === 'Completed') {
                                    $badge_class = 'badge-completed';
                                    $status_desc = 'Completed';
                                }
                                elseif ($status === 'In-Queue') {
                                    $badge_class = 'badge-info';
                                    $status_desc = 'In Queue';
                                }
                                elseif ($status === 'Cancelled') {
                                    $badge_class = 'badge-cancelled';
                                    $status_desc = 'Cancelled';
                                }
                                elseif ($status === 'Pending') {
                                    $status_desc = 'Pending - Awaiting Confirmation';
                                }
                                ?>
                                <span class="badge <?php echo $badge_class; ?>" title="<?php echo $status_desc; ?>"><?php echo $status_desc; ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#appointmentModal" 
                                    onclick="viewAppointmentDetails(<?php echo $apt['appointment_id']; ?>)">
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
                <p class="text-muted text-center">Loading appointment details...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- FOLLOW-UP BOOKING MODAL -->
<div class="modal fade" id="followUpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-calendar-check"></i> Book Follow-up Appointment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="followUpForm">
                    <input type="hidden" id="followUpPatientId" />
                    <input type="hidden" id="followUpAppointmentId" />
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Doctor</strong></label>
                        <select class="form-control" id="followUpDoctor" required>
                            <option value="">Select Doctor</option>
                            <?php
                            $doctors = $conn->query("SELECT user_id, first_name, last_name FROM User_Account WHERE role_id = 2 ORDER BY first_name");
                            while ($doc = $doctors->fetch_assoc()) {
                                echo '<option value="' . $doc['user_id'] . '">Dr. ' . htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Appointment Date</strong></label>
                        <input type="date" class="form-control" id="followUpDate" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Appointment Time</strong></label>
                        <select class="form-control" id="followUpTime" required>
                            <option value="">Select Time</option>
                            <option value="08:00">8:00 AM</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="17:00">5:00 PM</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Follow-up Notes</strong></label>
                        <textarea class="form-control" id="followUpNotes" rows="3" placeholder="Optional: Add any additional notes for the follow-up appointment"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveFollowUpAppointment()">Book Follow-up</button>
            </div>
        </div>
    </div>
</div>

<style>
    .badge-info {
        background-color: #0dcaf0;
        color: #000;
    }
    
    .appointment-details-section {
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 5px;
        border-left: 4px solid #1e3c72;
    }
    
    .appointment-details-section h6 {
        color: #1e3c72;
        font-weight: 700;
        margin-bottom: 10px;
        text-transform: uppercase;
        font-size: 0.85rem;
    }
    
    .appointment-details-section p {
        margin: 5px 0;
        font-size: 0.95rem;
    }
    
    .consultation-notes {
        background: white;
        padding: 12px;
        border-radius: 4px;
        border-left: 3px solid #17a2b8;
        margin-top: 10px;
        font-style: italic;
        color: #555;
    }
</style>

<script>
function viewAppointmentDetails(appointmentId) {
    fetch('/capstone/secretary/ajax/get-appointment-details.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ appointment_id: appointmentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayAppointmentDetails(data.appointment);
        } else {
            alert('Error: ' + (data.error || 'Failed to load appointment details'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading appointment details');
    });
}

function displayAppointmentDetails(apt) {
    const hasFollowUp = apt.consultation_notes && (apt.consultation_notes.toLowerCase().includes('follow-up') || apt.consultation_notes.toLowerCase().includes('follow up') || apt.consultation_notes.toLowerCase().includes('revisit'));
    
    let servicesHTML = '';
    if (apt.services && apt.services.length > 0) {
        servicesHTML = '<ul style="margin: 10px 0;">';
        apt.services.forEach(service => {
            servicesHTML += '<li>' + service.service_name + ' - ₱' + parseFloat(service.initial_deposit).toFixed(2) + '</li>';
        });
        servicesHTML += '</ul>';
    }
    
    let detailsHTML = `
        <div class="appointment-details-section">
            <h6>Basic Information</h6>
            <p><strong>Appointment ID:</strong> #${String(apt.appointment_id).padStart(5, '0')}</p>
            <p><strong>Patient:</strong> ${apt.patient_name}</p>
            <p><strong>Doctor:</strong> Dr. ${apt.doctor_name}</p>
            <p><strong>Date:</strong> ${apt.appointment_date}</p>
            <p><strong>Time:</strong> ${apt.appointment_time}</p>
            <p><strong>Type:</strong> <span class="badge ${apt.is_online_appointment ? 'bg-warning' : 'bg-primary'}">${apt.is_online_appointment ? 'Online' : 'In-Clinic'}</span></p>
            <p><strong>Status:</strong> <span class="badge bg-info">${apt.status_name}</span></p>
        </div>
        
        <div class="appointment-details-section">
            <h6>Services Rendered</h6>
            ${servicesHTML ? servicesHTML : '<p class="text-muted">No services recorded</p>'}
        </div>
    `;
    
    if (apt.consultation_notes) {
        detailsHTML += `
            <div class="appointment-details-section">
                <h6>Consultation Notes</h6>
                <div class="consultation-notes">
                    ${apt.consultation_notes}
                </div>
                ${hasFollowUp ? '<div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 4px; border-left: 3px solid #ffc107;"><strong>📌 Follow-up Recommended:</strong> Doctor notes indicate a follow-up appointment is recommended.</div>' : ''}
            </div>
        `;
    }
    
    if (hasFollowUp) {
        detailsHTML += `
            <div style="margin-top: 15px; padding: 15px; background: #d4edda; border-radius: 5px; text-align: center;">
                <p style="margin: 0; color: #155724;"><strong>This appointment has a follow-up recommendation</strong></p>
                <button type="button" class="btn btn-sm btn-success mt-2" data-bs-dismiss="modal" onclick="showFollowUpBooking(${apt.appointment_id}, ${apt.patient_id})">
                    <i class="bi bi-calendar-check"></i> Book Follow-up Appointment
                </button>
            </div>
        `;
    }
    
    document.getElementById('appointmentDetails').innerHTML = detailsHTML;
}

function showFollowUpBooking(appointmentId, patientId) {
    document.getElementById('followUpAppointmentId').value = appointmentId;
    document.getElementById('followUpPatientId').value = patientId;
    document.getElementById('followUpForm').reset();
    
    const modal = new bootstrap.Modal(document.getElementById('followUpModal'));
    modal.show();
}

function saveFollowUpAppointment() {
    const form = document.getElementById('followUpForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const followUpData = {
        patient_id: document.getElementById('followUpPatientId').value,
        original_appointment_id: document.getElementById('followUpAppointmentId').value,
        doctor_id: document.getElementById('followUpDoctor').value,
        appointment_date: document.getElementById('followUpDate').value,
        appointment_time: document.getElementById('followUpTime').value,
        follow_up_notes: document.getElementById('followUpNotes').value,
        is_follow_up: 1
    };
    
    fetch('/capstone/secretary/ajax/book-follow-up.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(followUpData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Follow-up appointment booked successfully!');
            bootstrap.Modal.getInstance(document.getElementById('followUpModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to book follow-up appointment'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error booking follow-up appointment');
    });
}

function checkInPatient(appointmentId, patientName) {
    if (confirm('Check in ' + patientName + ' to the queue?')) {
        fetch('/capstone/secretary/ajax/check-in.php', {
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
