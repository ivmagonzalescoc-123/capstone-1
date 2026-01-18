<?php
/**
 * Check-in Appointment - Move to Queue
 */
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "azucena_dental");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'error' => 'Invalid method']));
}

$data = json_decode(file_get_contents('php://input'), true);
$appointment_id = $data['appointment_id'] ?? null;

if (!$appointment_id) {
    die(json_encode(['success' => false, 'error' => 'No appointment ID provided']));
}

// Get "In-Queue" status ID - use status_id 25
$in_queue_status = 25;

// Update appointment status
$stmt = $conn->prepare("UPDATE Appointments SET status_id = ? WHERE appointment_id = ?");
$stmt->bind_param("ii", $in_queue_status, $appointment_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Patient checked in to queue']);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
