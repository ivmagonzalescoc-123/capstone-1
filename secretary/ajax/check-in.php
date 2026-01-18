<?php
/**
 * Check-in Appointment - Move to Queue
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

// Validate appointment exists
$check_stmt = $conn->prepare("SELECT appointment_id FROM Appointments WHERE appointment_id = ?");
$check_stmt->bind_param("i", $appointment_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    http_response_code(404);
    die(json_encode(['success' => false, 'error' => 'Appointment not found']));
}

$check_stmt->close();

// Get "In-Queue" status ID
$in_queue_status = 25;

// Update appointment status
$stmt = $conn->prepare("UPDATE Appointments SET status_id = ? WHERE appointment_id = ?");
if (!$stmt) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]));
}

$stmt->bind_param("ii", $in_queue_status, $appointment_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Patient checked in to queue']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Update failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
