<?php
/**
 * Get Consultation Data API
 * Fetches appointment details, consultation notes, and existing tooth records
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
$patient_id = $data['patient_id'] ?? null;

if (!$appointment_id || !$patient_id) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Missing required fields']));
}

// Get appointment details
$apt_stmt = $conn->prepare("
    SELECT a.appointment_id, a.appointment_date, a.appointment_time,
           a.consultation_notes, a.follow_up_notes,
           p.patients_id, p.first_name, p.last_name
    FROM Appointments a
    LEFT JOIN Patients p ON a.patient_id = p.patients_id
    WHERE a.appointment_id = ? AND a.patient_id = ?
");
$apt_stmt->bind_param("ii", $appointment_id, $patient_id);
$apt_stmt->execute();
$apt_result = $apt_stmt->get_result();

if ($apt_result->num_rows === 0) {
    http_response_code(404);
    die(json_encode(['success' => false, 'error' => 'Appointment not found']));
}

$appointment = $apt_result->fetch_assoc();
$apt_stmt->close();

// Get existing tooth records
$teeth_stmt = $conn->prepare("
    SELECT tooth_number, status 
    FROM Tooth_Records 
    WHERE appointment_id = ?
    ORDER BY tooth_number ASC
");
$teeth_stmt->bind_param("i", $appointment_id);
$teeth_stmt->execute();
$teeth_result = $teeth_stmt->get_result();

$teeth_records = [];
while ($tooth = $teeth_result->fetch_assoc()) {
    $teeth_records[$tooth['tooth_number']] = $tooth['status'];
}
$teeth_stmt->close();

// Get selected services
$services_stmt = $conn->prepare("
    SELECT s.service_id, s.service_name
    FROM selected_services ss
    JOIN services s ON ss.service_id = s.service_id
    WHERE ss.appointment_id = ?
    ORDER BY s.service_name ASC
");
$services_stmt->bind_param("i", $appointment_id);
$services_stmt->execute();
$services_result = $services_stmt->get_result();

$selected_services = [];
while ($service = $services_result->fetch_assoc()) {
    $selected_services[] = $service;
}
$services_stmt->close();

$appointment_date = date('M d, Y', strtotime($appointment['appointment_date']));
$appointment_time = date('g:i A', strtotime('2000-01-01 ' . $appointment['appointment_time']));

echo json_encode([
    'success' => true,
    'appointment_date' => $appointment_date,
    'appointment_time' => $appointment_time,
    'consultation_notes' => $appointment['consultation_notes'],
    'treatment_plan' => $appointment['treatment_plan'] ?? '',
    'prescription' => $appointment['prescription'] ?? '',
    'follow_up_notes' => $appointment['follow_up_notes'],
    'teeth_records' => $teeth_records,
    'selected_services' => $selected_services
]);

$conn->close();
?>
