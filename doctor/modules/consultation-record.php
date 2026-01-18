<?php
/**
 * Consultation Recording Page
 * Full page for recording consultation with 3D odontogram
 */

session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "azucena_dental");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    $conn->close();
    header('Location: ../../index.php');
    exit;
}

// Get appointment details
$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;
$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;

if (!$appointment_id || !$patient_id) {
    die("Invalid appointment");
}

// Get appointment and patient details
$result = $conn->query("
    SELECT a.appointment_id, a.appointment_date, a.appointment_time,
           p.patients_id, p.first_name, p.last_name, p.phone_number, p.email,
           d.first_name as doc_fname, d.last_name as doc_lname
    FROM Appointments a
    LEFT JOIN Patients p ON a.patient_id = p.patients_id
    LEFT JOIN User_Account d ON a.doctor_id = d.user_id
    WHERE a.appointment_id = $appointment_id AND a.patient_id = $patient_id
");

if (!$result || $result->num_rows == 0) {
    die("Appointment not found");
}

$appointment = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation Recording - <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></title>
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            padding: 20px;
        }

        .consultation-container {
            max-width: 1600px;
            margin: 0 auto;
        }

        .consultation-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #1e3c72;
        }

        .consultation-header h1 {
            margin: 0;
            color: #1e3c72;
            font-size: 28px;
            font-weight: 700;
        }

        .patient-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid #667eea;
        }

        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 16px;
            color: #1e3c72;
            font-weight: 600;
            margin-top: 4px;
        }

        .consultation-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }

        .consultation-form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-height: 90vh;
            overflow-y: auto;
        }

        .consultation-odontogram {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .form-section {
            margin-bottom: 25px;
        }

        .form-section h6 {
            color: #1e3c72;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #667eea;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-check-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 8px;
        }

        .form-check {
            display: flex;
            align-items: center;
        }

        .form-check-input {
            margin-right: 8px;
            cursor: pointer;
        }

        .form-check-label {
            margin: 0;
            font-size: 14px;
            cursor: pointer;
        }

        .textarea-field {
            min-height: 80px;
            resize: vertical;
            font-family: inherit;
        }

        .btn-group-action {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 25px;
        }

        .btn-complete {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-complete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(81, 207, 102, 0.3);
        }

        .btn-cancel {
            background: #e0e0e0;
            color: #333;
            border: none;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background: #d0d0d0;
        }

        #odontogramContainer {
            flex: 1;
            overflow: hidden;
            border-radius: 6px;
        }

        #odontogramFrame {
            width: 100%;
            height: 100%;
            border: none;
        }

        @media (max-width: 1200px) {
            .consultation-content {
                grid-template-columns: 1fr;
            }

            .consultation-odontogram {
                height: 600px;
            }

            .consultation-form {
                max-height: none;
            }
        }

        .loading-spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #1e3c72;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .top-navbar {
            background: white;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .top-navbar a {
            color: #1e3c72;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .top-navbar a:hover {
            color: #2a5298;
        }
    </style>
</head>
<body>

<div class="consultation-container">
    <!-- Back Navigation -->
    <div class="top-navbar">
        <div>
            <a href="consultation.php">
                <i class="bi bi-arrow-left"></i> Back to Queue
            </a>
        </div>
        <div style="text-align: center; color: #666; font-size: 14px;">
            <strong>Consultation Recording</strong>
        </div>
        <div>
            <a href="../index.php">
                <i class="bi bi-house"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Header with Patient Info -->
    <div class="consultation-header">
        <h1>
            <i class="bi bi-stethoscope"></i> Consultation Recording
        </h1>
        <div class="patient-info">
            <div class="info-item">
                <div class="info-label">Patient Name</div>
                <div class="info-value"><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Contact</div>
                <div class="info-value"><?php echo htmlspecialchars($appointment['phone_number'] ?? 'N/A'); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Appointment Date</div>
                <div class="info-value"><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Appointment Time</div>
                <div class="info-value"><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="consultation-content">
        <!-- Left: Form -->
        <div class="consultation-form">
            <form id="consultationForm">
                <input type="hidden" id="appointmentId" value="<?php echo $appointment_id; ?>">
                <input type="hidden" id="patientId" value="<?php echo $patient_id; ?>">

                <!-- Treatment/Services Section -->
                <div class="form-section">
                    <h6>1. Treatment & Services</h6>
                    <div id="treatmentServices" class="form-check-group">
                        <p class="text-muted">Loading services...</p>
                    </div>
                </div>

                <!-- Prescription Section -->
                <div class="form-section">
                    <h6>2. Prescription</h6>
                    <div class="form-group">
                        <label class="form-label">Prescription Details</label>
                        <textarea id="prescription" class="form-control textarea-field" 
                                placeholder="e.g., Amoxicillin 500mg, 3 times daily for 7 days&#10;Ibuprofen 400mg as needed for pain"></textarea>
                    </div>
                </div>

                <!-- Consultation Notes Section -->
                <div class="form-section">
                    <h6>3. Consultation Notes</h6>
                    <div class="form-group">
                        <label class="form-label">Clinical Findings & Observations</label>
                        <textarea id="consultationNotes" class="form-control textarea-field"
                                placeholder="e.g., Patient presented with mild sensitivity to cold&#10;Recommended professional cleaning and improved brushing technique"></textarea>
                    </div>
                </div>

                <!-- Follow-up Section -->
                <div class="form-section">
                    <h6>4. Follow-up Recommendations</h6>
                    <div class="form-group">
                        <label class="form-label">Notes for Next Appointment</label>
                        <textarea id="followUpNotes" class="form-control textarea-field"
                                placeholder="e.g., Schedule crown placement&#10;Patient to return for root canal treatment"></textarea>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="btn-group-action">
                    <button type="button" class="btn-cancel" onclick="goBack()">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn-complete" onclick="saveConsultation()">
                        <i class="bi bi-check-circle"></i> Complete & Save
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Odontogram -->
        <div class="consultation-odontogram">
            <div style="padding: 10px; background: #f8f9fa; border-radius: 6px; margin-bottom: 10px; text-align: center;">
                <small style="color: #666;"><strong>3D Odontogram</strong> - Click teeth to record status</small>
            </div>
            <div id="odontogramContainer">
                <iframe id="odontogramFrame" src="./odontogram.php?appointment_id=<?php echo $appointment_id; ?>&patient_id=<?php echo $patient_id; ?>"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
// Load services on page load
document.addEventListener('DOMContentLoaded', function() {
    loadServices();
});

function loadServices() {
    fetch('../ajax/get-services.php')
        .then(async (r) => {
            const text = await r.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error(`Invalid JSON from get-services.php. First 200 chars: ${text.slice(0, 200)}`);
            }
        })
        .then(data => {
            let html = '';
            (data.services || []).forEach(service => {
                html += `
                    <div class="form-check">
                        <input class="form-check-input service-checkbox" type="checkbox" 
                               value="${service.service_id}" id="service${service.service_id}">
                        <label class="form-check-label" for="service${service.service_id}">
                            ${service.service_name}
                        </label>
                    </div>
                `;
            });
            document.getElementById('treatmentServices').innerHTML = html;
        })
        .catch(e => {
            console.error('Error loading services:', e);
            document.getElementById('treatmentServices').innerHTML = '<p class="text-danger">Failed to load services</p>';
        });
}

function saveConsultation() {
    const appointmentId = document.getElementById('appointmentId').value;
    const patientId = document.getElementById('patientId').value;
    
    const selectedServices = Array.from(document.querySelectorAll('.service-checkbox:checked'))
        .map(el => el.value);
    
    const prescription = document.getElementById('prescription').value;
    const consultationNotes = document.getElementById('consultationNotes').value;
    const followUpNotes = document.getElementById('followUpNotes').value;
    
    if (selectedServices.length === 0) {
        alert('Please select at least one service');
        return;
    }
    
    // Get tooth data from odontogram
    let toothRecordings = [];
    try {
        const iframeDoc = document.getElementById('odontogramFrame').contentWindow;
        if (iframeDoc && iframeDoc.getOdontogramData) {
            const toothState = iframeDoc.getOdontogramData();
            toothRecordings = Object.entries(toothState)
                .filter(([tooth, status]) => status !== null)
                .map(([tooth, status]) => ({
                    tooth_number: parseInt(tooth),
                    status: status
                }));
        }
    } catch (e) {
        console.error('Error accessing odontogram:', e);
    }
    
    const payload = {
        appointment_id: appointmentId,
        patient_id: patientId,
        services: selectedServices,
        tooth_recordings: toothRecordings,
        prescription: prescription,
        consultation_notes: consultationNotes,
        follow_up_notes: followUpNotes
    };
    
    console.log('Saving consultation:', payload);
    
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="loading-spinner"></span> Saving...';
    btn.disabled = true;
    
    fetch('../ajax/complete-consultation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ Consultation saved successfully!\n\nTeeth recorded: ' + data.teeth_recorded);
            window.location.href = '../index.php';
        } else {
            alert('❌ Error: ' + (data.error || 'Failed to save consultation'));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(e => {
        console.error('Error:', e);
        alert('❌ Error saving consultation: ' + e.message);
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function goBack() {
    if (confirm('Are you sure? Any unsaved changes will be lost.')) {
        window.location.href = '../index.php';
    }
}
</script>

</body>
</html>
