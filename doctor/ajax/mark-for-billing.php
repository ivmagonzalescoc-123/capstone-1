<?php
/**
 * Mark Appointment for Billing
 * Changes appointment status to "Completed" so it shows in secretary billing module
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

// Get status_id for "Completed"
$status_result = $conn->query("SELECT status_id FROM Status WHERE status_name = 'Completed' LIMIT 1");
$status_row = $status_result->fetch_assoc();
$completed_status_id = $status_row['status_id'] ?? 27; // Fallback to 27 if not found

// Update appointment status to Completed
$update_stmt = $conn->prepare("
    UPDATE Appointments 
    SET status_id = ?
    WHERE appointment_id = ? AND patient_id = ?
");
$update_stmt->bind_param("iii", $completed_status_id, $appointment_id, $patient_id);

if ($update_stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Appointment marked for billing',
        'status_id' => $completed_status_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update appointment: ' . $update_stmt->error]);
}

$update_stmt->close();
$conn->close();
?>
