<?php
/**
 * Get Odontogram Data - AJAX Endpoint
 */

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "azucena_dental");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$appointment_id = intval($_GET['appointment_id'] ?? 0);

if (!$appointment_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid appointment ID']);
    $conn->close();
    exit;
}

$result = $conn->query("
    SELECT tooth_number, status
    FROM Tooth_Records
    WHERE appointment_id = $appointment_id
");

$teeth = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $teeth[$row['tooth_number']] = $row['status'];
    }
}

echo json_encode($teeth);
$conn->close();
?>
