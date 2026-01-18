<?php
/**
 * Save Odontogram Data - AJAX Endpoint
 */

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "azucena_dental");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$appointment_id = intval($data['appointment_id'] ?? 0);
$teeth = $data['teeth'] ?? [];

if (!$appointment_id || !is_array($teeth)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    $conn->close();
    exit;
}

try {
    // Delete existing records for this appointment
    $conn->query("DELETE FROM Tooth_Records WHERE appointment_id = $appointment_id");

    // Insert new records
    $insertedCount = 0;
    foreach ($teeth as $tooth_number => $status) {
        if ($status !== null && !empty($status)) {
            $tooth_number = intval($tooth_number);
            $status = $conn->real_escape_string($status);
            
            $conn->query("
                INSERT INTO Tooth_Records (appointment_id, tooth_number, status, created_at)
                VALUES ($appointment_id, $tooth_number, '$status', NOW())
            ");
            $insertedCount++;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Saved $insertedCount tooth records"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error saving data: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>
