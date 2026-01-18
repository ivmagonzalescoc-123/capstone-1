<?php
/**
 * Patient Book Appointment Module
 */

// Get patient ID from Patients table (not User_Account)
$user_id = $_SESSION['user_id'];
$patient_query = "SELECT patients_id FROM Patients WHERE user_id = ?";
$stmt = $conn->prepare($patient_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$patient_result = $stmt->get_result();

if ($patient_result->num_rows === 0) {
    die('Patient record not found');
}

$patient_data = $patient_result->fetch_assoc();
$patient_id = $patient_data['patients_id'];
$stmt->close();

$doctors = [];
$services = [];

// Get available doctors
$result = $conn->query("
    SELECT u.user_id, u.first_name, u.last_name
    FROM User_Account u
    WHERE u.role_id = 2 AND u.is_active = 1
    ORDER BY u.first_name
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Get services
$result = $conn->query("SELECT service_id, service_name FROM Services ORDER BY service_name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

// Generate time slots based on doctor's schedule (9:00 AM to 3:00 PM, 30-minute intervals)
function generateTimeSlots($available_from = '09:00:00', $available_to = '15:00:00') {
    $slots = [];
    $start = strtotime($available_from);
    $end = strtotime($available_to);
    
    while ($start <= $end) {
        $slots[] = date('H:i:s', $start);
        $start = strtotime('+30 minutes', $start);
    }
    return $slots;
}

// Get available time slots for a specific date
function getAvailableSlots($conn, $date) {
    // Get day of week
    $day_of_week = date('l', strtotime($date)); // e.g., "Monday"
    
    // Get doctor schedule for this day (default or first available)
    $schedule_query = "SELECT available_from, available_to FROM Schedule WHERE day_of_week = ? AND status_id = 1 LIMIT 1";
    
    $stmt = $conn->prepare($schedule_query);
    $stmt->bind_param("s", $day_of_week);
    $stmt->execute();
    $schedule_result = $stmt->get_result();
    
    if ($schedule_result->num_rows === 0) {
        // No schedule found for this day, use default times
        $all_slots = generateTimeSlots();
    } else {
        $schedule = $schedule_result->fetch_assoc();
        $all_slots = generateTimeSlots($schedule['available_from'], $schedule['available_to']);
    }
    $stmt->close();
    
    // Check if appointment_time column exists, if not return all slots
    $check_column = $conn->query("SHOW COLUMNS FROM Appointments LIKE 'appointment_time'");
    if ($check_column->num_rows == 0) {
        return $all_slots;
    }
    
    // Get booked slots for the date
    $stmt = $conn->prepare("SELECT appointment_time FROM Appointments WHERE appointment_date = ? AND status_id != 4 AND appointment_time IS NOT NULL");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $booked_slots = [];
    while ($row = $result->fetch_assoc()) {
        $booked_slots[] = $row['appointment_time'];
    }
    $stmt->close();
    
    // Count total appointments for the day (including those without time)
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM Appointments WHERE appointment_date = ? AND status_id != 4");
    $count_stmt->bind_param("s", $date);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
    
    // If max appointments reached (20), return empty array
    if ($count >= 20) {
        return [];
    }
    
    // Filter out booked slots
    $available_slots = array_diff($all_slots, $booked_slots);
    return array_values($available_slots);
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $service_id = (int)($_POST['service_id'] ?? 0);
    $doctor_id = (int)($_POST['doctor_id'] ?? 0);
    
    if ($appointment_date && $appointment_time && $service_id && $doctor_id) {
        // Check if slot is still available
        $available_slots = getAvailableSlots($conn, $appointment_date);
        
        if (!in_array($appointment_time, $available_slots)) {
            $message = '<div class="alert alert-danger">Sorry, this time slot is no longer available. Please select another time.</div>';
        } else {
            // Create appointment - automatically set as online booking (is_online_appointment = 1)
            $status_id = 1; // Pending
            $is_online = 1; // Patient portal bookings are always online
            
            $stmt = $conn->prepare("INSERT INTO Appointments (patient_id, doctor_id, appointment_date, appointment_time, is_online_appointment, status_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissii", $patient_id, $doctor_id, $appointment_date, $appointment_time, $is_online, $status_id);
            
            if ($stmt->execute()) {
                $appointment_id = $stmt->insert_id;
                
                // Add service
                $stmt2 = $conn->prepare("INSERT INTO Selected_Services (appointment_id, service_id) VALUES (?, ?)");
                $stmt2->bind_param("ii", $appointment_id, $service_id);
                $stmt2->execute();
                $stmt2->close();
                
                $message = '<div class="alert alert-success">Appointment booked successfully! Your appointment is scheduled for ' . date('F j, Y', strtotime($appointment_date)) . ' at ' . date('g:i A', strtotime($appointment_time)) . '</div>';
            } else {
                $message = '<div class="alert alert-danger">Error booking appointment. Please try again.</div>';
            }
            $stmt->close();
        }
    } else {
        $message = '<div class="alert alert-danger">Please fill all fields including time slot</div>';
    }
}
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Book an Appointment</h2>

    <?php echo $message; ?>

    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form method="POST" id="appointmentForm">
                        <div class="mb-3">
                            <label class="form-label">Select Doctor</label>
                            <select name="doctor_id" id="doctor_id" class="form-control" required>
                                <option value="">-- Select Doctor --</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?php echo $doctor['user_id']; ?>">
                                        <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Appointment Date</label>
                            <input type="date" name="appointment_date" id="appointment_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Available Time Slots</label>
                            <select name="appointment_time" id="appointment_time" class="form-control" required disabled>
                                <option value="">First select a date</option>
                            </select>
                            <small class="text-muted">Maximum 20 appointments per day. Select a date to see available slots.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Service</label>
                            <select name="service_id" class="form-control" required>
                                <option value="">-- Select Service --</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['service_id']; ?>">
                                        <?php echo htmlspecialchars($service['service_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-info">Book Appointment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    
    dateInput.addEventListener('change', function() {
        const selectedDate = this.value;
        
        if (!selectedDate) {
            timeSelect.disabled = true;
            timeSelect.innerHTML = '<option value="">First select a date</option>';
            return;
        }
        
        // Fetch available slots
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Loading available slots...</option>';
        
        const url = 'ajax/get-available-slots.php?date=' + encodeURIComponent(selectedDate);
        console.log('Fetching slots from:', url);
        
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Server error (${response.status}): ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                timeSelect.innerHTML = '';
                
                if (!data.success) {
                    timeSelect.innerHTML = '<option value="">Error: ' + (data.error || 'Unknown error') + '</option>';
                    timeSelect.disabled = true;
                    return;
                }
                
                if (data.slots.length === 0) {
                    timeSelect.innerHTML = '<option value="">No available slots (max 20 appointments reached)</option>';
                    timeSelect.disabled = true;
                } else {
                    timeSelect.innerHTML = '<option value="">-- Select Time Slot --</option>';
                    data.slots.forEach(function(slot) {
                        const option = document.createElement('option');
                        option.value = slot;
                        // Convert 24-hour to 12-hour format
                        const time = new Date('2000-01-01 ' + slot);
                        option.textContent = time.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true});
                        timeSelect.appendChild(option);
                    });
                    timeSelect.disabled = false;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                timeSelect.innerHTML = '<option value="">Error loading slots. Check console.</option>';
                timeSelect.disabled = true;
            });
    });
});
</script>
