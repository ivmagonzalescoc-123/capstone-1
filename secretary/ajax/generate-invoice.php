<?php
/**
 * Generate Invoice API
 * Calculates: Consultation fee (1000) + Services rendered costs
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
           p.patients_id, p.first_name as patient_fname, p.last_name as patient_lname,
           d.first_name as doc_fname, d.last_name as doc_lname,
           a.status_id
    FROM Appointments a
    LEFT JOIN Patients p ON a.patient_id = p.patients_id
    LEFT JOIN User_Account d ON a.doctor_id = d.user_id
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

// Base consultation fee
$consultation_fee = 1000;

// Get services rendered for this appointment
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
$services_total = 0;

while ($service = $services_result->fetch_assoc()) {
    $services[] = $service;
    $services_total += $service['initial_deposit'];
}
$services_stmt->close();

// Calculate total
$total_amount = $consultation_fee + $services_total;

// Check if invoice already exists
$check_stmt = $conn->prepare("SELECT billing_id FROM billing WHERE appointment_id = ?");
$check_stmt->bind_param("i", $appointment_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$billing_exists = $check_result->num_rows > 0;
$check_stmt->close();

// If no invoice exists, create one
$billing_id = null;
if (!$billing_exists) {
    $bill_stmt = $conn->prepare("
        INSERT INTO billing (appointment_id, user_id, total_amount, created_at, processed_by)
        VALUES (?, ?, ?, NOW(), ?)
    ");
    $user_id = $_SESSION['user_id'] ?? null;
    $bill_stmt->bind_param("iidi", $appointment_id, $user_id, $total_amount, $user_id);
    if ($bill_stmt->execute()) {
        $billing_id = $bill_stmt->insert_id;
    }
    $bill_stmt->close();
}

// Return invoice data
echo json_encode([
    'success' => true,
    'invoice' => [
        'appointment_id' => str_pad($appointment_id, 5, '0', STR_PAD_LEFT),
        'billing_id' => $billing_id,
        'patient_name' => htmlspecialchars($appointment['patient_fname'] . ' ' . $appointment['patient_lname']),
        'doctor_name' => htmlspecialchars(($appointment['doc_fname'] ?? '') . ' ' . ($appointment['doc_lname'] ?? '')),
        'appointment_date' => date('M d, Y', strtotime($appointment['appointment_date'])),
        'appointment_time' => date('g:i A', strtotime('2000-01-01 ' . $appointment['appointment_time'])),
        'consultation_fee' => $consultation_fee,
        'services' => $services,
        'services_total' => $services_total,
        'total_amount' => $total_amount
    ]
]);

$conn->close();
?>
