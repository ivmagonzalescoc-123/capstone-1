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
                                        onclick="startConsultation(<?php echo $apt['appointment_id']; ?>, <?php echo $apt['patients_id']; ?>, '<?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?>')">
                                            <i class="bi bi-stethoscope"></i> Consultation & Odontogram
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

<!-- CONSULTATION MODAL -->
<div class="modal fade" id="consultationModal" tabindex="-1" size="lg">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-stethoscope"></i> Consultation & Odontogram
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <form id="consultationForm">
                    <input type="hidden" id="consultationAppointmentId" />
                    <input type="hidden" id="consultationPatientId" />
                    
                    <!-- Header Info -->
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #0d6efd;">
                        <div class="row">
                            <div class="col-md-6">
                                <p style="margin: 0;"><strong>Patient:</strong> <span id="consultationPatientName"></span></p>
                            </div>
                            <div class="col-md-3">
                                <p style="margin: 0;"><strong>Date:</strong> <span id="consultationDate"></span></p>
                            </div>
                            <div class="col-md-3">
                                <p style="margin: 0;"><strong>Time:</strong> <span id="consultationTime"></span></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Consultation Details Tabs -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes-content" type="button" role="tab">
                                <i class="bi bi-file-text"></i> Notes
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="odontogram-tab" data-bs-toggle="tab" data-bs-target="#odontogram-content" type="button" role="tab">
                                <i class="bi bi-tooth"></i> Odontogram
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <!-- NOTES TAB -->
                        <div class="tab-pane fade show active" id="notes-content" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label"><strong>Consultation Notes</strong></label>
                                <textarea class="form-control" id="consultationNotes" rows="4" placeholder="Record consultation details, findings, and treatment plan..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><strong>Follow-up Notes</strong></label>
                                <textarea class="form-control" id="followUpNotes" rows="3" placeholder="Add notes if follow-up appointment is needed (mention 'follow-up' or 'revisit' for automatic detection)..."></textarea>
                            </div>
                        </div>
                        
                        <!-- ODONTOGRAM TAB -->
                        <div class="tab-pane fade" id="odontogram-content" role="tabpanel">
                            <div class="row">
                                <!-- Teeth Grid -->
                                <div class="col-md-8">
                                    <h6 style="color: #1e3c72; font-weight: 700; margin-bottom: 15px;">Click teeth to mark status</h6>
                                    <div id="odontogramTeeth" style="display: flex; flex-wrap: wrap; gap: 5px; padding: 15px; background: #f8f9fa; border-radius: 5px;"></div>
                                </div>
                                
                                <!-- Controls & Legend -->
                                <div class="col-md-4">
                                    <h6 style="color: #1e3c72; font-weight: 700; margin-bottom: 10px;">Selected Tooth</h6>
                                    <div style="background: #e7f3ff; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                                        <p style="margin: 5px 0;"><strong>Tooth #:</strong> <span id="selectedToothNum">—</span></p>
                                        <p style="margin: 5px 0;"><strong>Status:</strong> <span id="selectedToothStatus">—</span></p>
                                    </div>
                                    
                                    <h6 style="color: #1e3c72; font-weight: 700; margin-bottom: 10px;">Status Options</h6>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px; margin-bottom: 15px;">
                                        <button type="button" class="btn btn-sm btn-success" onclick="setToothStatus('healthy')" title="Healthy">Healthy</button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="setToothStatus('caries')" title="Caries">Caries</button>
                                        <button type="button" class="btn btn-sm btn-info" onclick="setToothStatus('filled')" title="Filled">Filled</button>
                                        <button type="button" class="btn btn-sm btn-purple" onclick="setToothStatus('crown')" style="background: #9b59b6; color: white; border: none;" title="Crown">Crown</button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="setToothStatus('missing')" title="Missing">Missing</button>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="clearToothStatus()" title="Clear">Clear</button>
                                    </div>
                                    
                                    <h6 style="color: #1e3c72; font-weight: 700; margin-bottom: 10px;">Legend</h6>
                                    <div style="font-size: 0.9rem;">
                                        <div style="margin: 5px 0;"><span style="display: inline-block; width: 20px; height: 20px; background: #51cf66; border-radius: 3px; margin-right: 5px;"></span>Healthy</div>
                                        <div style="margin: 5px 0;"><span style="display: inline-block; width: 20px; height: 20px; background: #ffa500; border-radius: 3px; margin-right: 5px;"></span>Caries</div>
                                        <div style="margin: 5px 0;"><span style="display: inline-block; width: 20px; height: 20px; background: #4ecdc4; border-radius: 3px; margin-right: 5px;"></span>Filled</div>
                                        <div style="margin: 5px 0;"><span style="display: inline-block; width: 20px; height: 20px; background: #9b59b6; border-radius: 3px; margin-right: 5px;"></span>Crown</div>
                                        <div style="margin: 5px 0;"><span style="display: inline-block; width: 20px; height: 20px; background: #e74c3c; border-radius: 3px; margin-right: 5px;"></span>Missing</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveConsultation()">
                    <i class="bi bi-check-circle"></i> Save Consultation
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-purple {
        background: #9b59b6;
        color: white;
        border: none;
    }
    
    .btn-purple:hover {
        background: #8e44ad;
        color: white;
    }
    
    .tooth-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
</style>

<script>
function startConsultation(appointmentId, patientId, patientName) {
    // Fetch appointment and patient details
    fetch('/capstone/doctor/ajax/get-consultation-data.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ appointment_id: appointmentId, patient_id: patientId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadConsultationModal(data, appointmentId, patientId, patientName);
        } else {
            alert('Error: ' + (data.error || 'Failed to load consultation data'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading consultation data');
    });
}

function loadConsultationModal(data, appointmentId, patientId, patientName) {
    // Set hidden values for form submission
    document.getElementById('consultationAppointmentId').value = appointmentId;
    document.getElementById('consultationPatientId').value = patientId;
    
    // Display appointment details
    document.getElementById('consultationPatientName').textContent = patientName;
    document.getElementById('consultationDate').textContent = data.appointment_date;
    document.getElementById('consultationTime').textContent = data.appointment_time;
    
    // Load existing consultation notes if any
    if (data.consultation_notes) {
        document.getElementById('consultationNotes').value = data.consultation_notes;
    } else {
        document.getElementById('consultationNotes').value = '';
    }
    
    // Load existing follow-up notes if any
    if (data.follow_up_notes) {
        document.getElementById('followUpNotes').value = data.follow_up_notes;
    } else {
        document.getElementById('followUpNotes').value = '';
    }
    
    // Initialize odontogram with existing tooth records
    initializeOdontogram(appointmentId, patientId, data.teeth_records || {});
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('consultationModal'));
    modal.show();
}

function initializeOdontogram(appointmentId, patientId, existingTeeth) {
    const teethGrid = document.getElementById('odontogramTeeth');
    teethGrid.innerHTML = '';
    teethGrid.dataset.appointmentId = appointmentId;
    teethGrid.dataset.patientId = patientId;
    
    const toothStatuses = {
        healthy: { color: '#51cf66', icon: '✓', label: 'Healthy' },
        caries: { color: '#ffa500', icon: '●', label: 'Caries' },
        filled: { color: '#4ecdc4', icon: '■', label: 'Filled' },
        crown: { color: '#9b59b6', icon: '◆', label: 'Crown' },
        missing: { color: '#e74c3c', icon: '✗', label: 'Missing' },
        normal: { color: '#ffffff', icon: '', label: 'Normal' }
    };
    
    // Create teeth buttons (teeth 1-32)
    for (let i = 1; i <= 32; i++) {
        const toothBtn = document.createElement('button');
        toothBtn.type = 'button';
        toothBtn.className = 'tooth-btn';
        toothBtn.dataset.toothNum = i;
        toothBtn.textContent = i;
        toothBtn.style.cursor = 'pointer';
        toothBtn.style.width = '50px';
        toothBtn.style.height = '50px';
        toothBtn.style.margin = '5px';
        toothBtn.style.border = '2px solid #ddd';
        toothBtn.style.borderRadius = '5px';
        toothBtn.style.fontWeight = 'bold';
        toothBtn.style.fontSize = '12px';
        toothBtn.style.backgroundColor = '#ffffff';
        toothBtn.style.transition = 'all 0.2s ease';
        
        // Set color if tooth has existing status
        if (existingTeeth[i]) {
            const status = existingTeeth[i];
            const statusInfo = toothStatuses[status] || toothStatuses.normal;
            toothBtn.style.backgroundColor = statusInfo.color;
            toothBtn.style.color = status === 'caries' ? '#fff' : '#000';
            toothBtn.style.fontWeight = 'bold';
            toothBtn.dataset.status = status;
        }
        
        // Click to select and show status options
        toothBtn.addEventListener('click', function(e) {
            e.preventDefault();
            selectTooth(i, toothBtn);
        });
        
        // Right-click to clear
        toothBtn.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            clearToothStatus(toothBtn);
        });
        
        teethGrid.appendChild(toothBtn);
    }
}

function selectTooth(toothNum, button) {
    // Highlight selected tooth
    document.querySelectorAll('.tooth-btn').forEach(btn => {
        btn.style.border = '2px solid #ddd';
    });
    button.style.border = '3px solid #1e3c72';
    
    document.getElementById('selectedToothNum').textContent = toothNum;
    document.getElementById('selectedToothNum').dataset.toothNum = toothNum;
    
    const currentStatus = button.dataset.status || 'normal';
    document.getElementById('selectedToothStatus').textContent = currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1);
}

function setToothStatus(status) {
    const selectedTooth = document.getElementById('selectedToothNum');
    const toothNum = selectedTooth.dataset.toothNum;
    
    if (!toothNum) {
        alert('Please select a tooth first');
        return;
    }
    
    const toothStatuses = {
        healthy: { color: '#51cf66', icon: '✓' },
        caries: { color: '#ffa500', icon: '●' },
        filled: { color: '#4ecdc4', icon: '■' },
        crown: { color: '#9b59b6', icon: '◆' },
        missing: { color: '#e74c3c', icon: '✗' }
    };
    
    const button = document.querySelector(`.tooth-btn[data-tooth-num="${toothNum}"]`);
    const statusInfo = toothStatuses[status];
    
    button.style.backgroundColor = statusInfo.color;
    button.style.color = status === 'caries' ? '#fff' : '#000';
    button.style.fontWeight = 'bold';
    button.dataset.status = status;
    
    document.getElementById('selectedToothStatus').textContent = status.charAt(0).toUpperCase() + status.slice(1);
}

function clearToothStatus(button = null) {
    const selectedTooth = document.getElementById('selectedToothNum');
    const toothNum = selectedTooth.dataset.toothNum;
    
    if (!toothNum) {
        alert('Please select a tooth first');
        return;
    }
    
    const btn = button || document.querySelector(`.tooth-btn[data-tooth-num="${toothNum}"]`);
    btn.style.backgroundColor = '#ffffff';
    btn.style.color = '#000';
    btn.dataset.status = 'normal';
    
    document.getElementById('selectedToothStatus').textContent = '—';
}

function saveConsultation() {
    const appointmentId = document.getElementById('consultationAppointmentId').value;
    const patientId = document.getElementById('consultationPatientId').value;
    const notes = document.getElementById('consultationNotes').value;
    const followUpNotes = document.getElementById('followUpNotes').value;
    
    // Collect tooth records
    const teethRecords = {};
    document.querySelectorAll('.tooth-btn').forEach(btn => {
        const toothNum = btn.dataset.toothNum;
        const status = btn.dataset.status || 'normal';
        if (status !== 'normal') {
            teethRecords[toothNum] = status;
        }
    });
    
    fetch('/capstone/doctor/ajax/save-consultation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            appointment_id: appointmentId,
            patient_id: patientId,
            consultation_notes: notes,
            follow_up_notes: followUpNotes,
            teeth_records: teethRecords
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Consultation saved successfully!');
            bootstrap.Modal.getInstance(document.getElementById('consultationModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to save consultation'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving consultation');
    });
}
</script>