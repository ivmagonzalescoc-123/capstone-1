<?php
/**
 * Complete Consultation & Close Appointment
 */
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "azucena_dental");

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($data['appointment_id'])) {
    die(json_encode(['success' => false, 'error' => 'Invalid request']));
}

$appointment_id = $data['appointment_id'];
$services = $data['services'] ?? [];
$tooth_recordings = $data['tooth_recordings'] ?? [];  // Array of {tooth_number, status}
$prescription = $data['prescription'] ?? '';
$consultation_notes = $data['consultation_notes'] ?? '';
$follow_up_notes = $data['follow_up_notes'] ?? '';

try {
    // Get "Completed" status ID - use status_id 27
    $completed_status_id = 27;
    
    // Start transaction
    $conn->begin_transaction();
    
    // Update appointment status to completed
    $stmt = $conn->prepare("UPDATE Appointments SET status_id = ? WHERE appointment_id = ?");
    $stmt->bind_param("ii", $completed_status_id, $appointment_id);
    if (!$stmt->execute()) throw new Exception("Failed to update appointment: " . $stmt->error);
    $stmt->close();
    
    // Insert services (clear old ones first if needed, then add new)
    $conn->query("DELETE FROM Selected_Services WHERE appointment_id = $appointment_id");
    
    foreach ($services as $service_id) {
        $stmt = $conn->prepare("INSERT INTO Selected_Services (appointment_id, service_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $appointment_id, $service_id);
        if (!$stmt->execute()) throw new Exception("Failed to insert service: " . $stmt->error);
        $stmt->close();
    }
    
    // Record teeth from odontogram - delete old records first
    $conn->query("DELETE FROM Tooth_Records WHERE appointment_id = $appointment_id");
    
    foreach ($tooth_recordings as $tooth) {
        $tooth_number = intval($tooth['tooth_number']);
        $tooth_status = $tooth['status'];
        
        $stmt = $conn->prepare("INSERT INTO Tooth_Records (appointment_id, tooth_number, status) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $appointment_id, $tooth_number, $tooth_status);
        if (!$stmt->execute()) throw new Exception("Failed to record tooth $tooth_number: " . $stmt->error);
        $stmt->close();
    }
    
    // Create prescription record
    if ($prescription) {
        $patient_id = $data['patient_id'];
        $doctor_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO Prescriptions (appointment_id, patient_id, doctor_id, prescription_details) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $appointment_id, $patient_id, $doctor_id, $prescription);
        if (!$stmt->execute()) throw new Exception("Failed to create prescription: " . $stmt->error);
        $stmt->close();
    }
    
    // Update appointment with consultation and follow-up notes if needed
    // (Assuming these columns exist in Appointments table)
    if ($consultation_notes || $follow_up_notes) {
        $stmt = $conn->prepare("UPDATE Appointments SET consultation_notes = ?, follow_up_notes = ? WHERE appointment_id = ?");
        $stmt->bind_param("ssi", $consultation_notes, $follow_up_notes, $appointment_id);
        if (!$stmt->execute()) throw new Exception("Failed to update notes: " . $stmt->error);
        $stmt->close();
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Consultation completed successfully',
        'appointment_id' => $appointment_id,
        'teeth_recorded' => count($tooth_recordings)
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>
