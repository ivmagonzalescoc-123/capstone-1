<?php
/**
 * Book Follow-up Appointment API
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

$patient_id = $data['patient_id'] ?? null;
$original_appointment_id = $data['original_appointment_id'] ?? null;
$doctor_id = $data['doctor_id'] ?? null;
$appointment_date = $data['appointment_date'] ?? null;
$appointment_time = $data['appointment_time'] ?? null;
$follow_up_notes = $data['follow_up_notes'] ?? '';

// Validate required fields
if (!$patient_id || !$doctor_id || !$appointment_date || !$appointment_time) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Missing required fields']));
}

// Get next appointment ID
$result = $conn->query("SELECT MAX(appointment_id) as max_id FROM Appointments");
$row = $result->fetch_assoc();
$new_appointment_id = ($row['max_id'] ?? 0) + 1;

// Create follow-up appointment
$stmt = $conn->prepare("
    INSERT INTO Appointments (
        appointment_id, appointment_date, appointment_time, 
        patient_id, doctor_id, status_id, rescheduled_from,
        follow_up_notes, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$status_id = 1; // Pending confirmation
$stmt->bind_param(
    "issiiiis",
    $new_appointment_id,
    $appointment_date,
    $appointment_time,
    $patient_id,
    $doctor_id,
    $status_id,
    $original_appointment_id,
    $follow_up_notes
);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Follow-up appointment booked successfully',
        'appointment_id' => $new_appointment_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to book follow-up appointment: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
