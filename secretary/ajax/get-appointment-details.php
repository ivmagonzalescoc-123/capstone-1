<?php
/**
 * Get Appointment Details with Services and Consultation Notes
 */
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "azucena_dental");

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'error' => 'Invalid request method']));
}

$data = json_decode(file_get_contents('php://input'), true);
$appointment_id = $data['appointment_id'] ?? null;

if (!$appointment_id) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'No appointment ID provided']));
}

// Get appointment details
$apt_stmt = $conn->prepare("
    SELECT a.appointment_id, a.appointment_date, a.appointment_time,
           a.patient_id, a.status_id, a.is_online_appointment,
           a.consultation_notes,
           p.patients_id, p.first_name as patient_fname, p.last_name as patient_lname,
           d.user_id, d.first_name as doc_fname, d.last_name as doc_lname,
           s.status_name
    FROM Appointments a
    LEFT JOIN Patients p ON a.patient_id = p.patients_id
    LEFT JOIN User_Account d ON a.doctor_id = d.user_id
    LEFT JOIN Status s ON a.status_id = s.status_id
    WHERE a.appointment_id = ?
");
$apt_stmt->bind_param("i", $appointment_id);
$apt_stmt->execute();
$apt_result = $apt_stmt->get_result();

if ($apt_result->num_rows === 0) {
    http_response_code(404);
    die(json_encode(['success' => false, 'error' => 'Appointment not found']));
}

$appointment = $apt_result->fetch_assoc();
$apt_stmt->close();

// Get services for this appointment
$services_stmt = $conn->prepare("
    SELECT s.service_id, s.service_name, s.initial_deposit
    FROM selected_services ss
    JOIN services s ON ss.service_id = s.service_id
    WHERE ss.appointment_id = ?
");
$services_stmt->bind_param("i", $appointment_id);
$services_stmt->execute();
$services_result = $services_stmt->get_result();

$services = [];
while ($service = $services_result->fetch_assoc()) {
    $services[] = $service;
}
$services_stmt->close();

// Format appointment date/time
$appointment_date = date('M d, Y', strtotime($appointment['appointment_date']));
$appointment_time = date('g:i A', strtotime('2000-01-01 ' . $appointment['appointment_time']));

echo json_encode([
    'success' => true,
    'appointment' => [
        'appointment_id' => $appointment['appointment_id'],
        'patient_id' => $appointment['patient_id'],
        'patient_name' => htmlspecialchars($appointment['patient_fname'] . ' ' . $appointment['patient_lname']),
        'doctor_name' => htmlspecialchars(($appointment['doc_fname'] ?? '') . ' ' . ($appointment['doc_lname'] ?? '')),
        'appointment_date' => $appointment_date,
        'appointment_time' => $appointment_time,
        'status_name' => $appointment['status_name'] ?? 'Pending',
        'is_online_appointment' => $appointment['is_online_appointment'],
        'consultation_notes' => $appointment['consultation_notes'],
        'services' => $services
    ]
]);

$conn->close();
?>
