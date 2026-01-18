<?php
/**
 * Save Consultation Data API
 * Saves consultation notes, treatment plan, prescription, follow-up notes, and tooth records
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
$consultation_notes = $data['consultation_notes'] ?? '';
$treatment_plan = $data['treatment_plan'] ?? '';
$prescription = $data['prescription'] ?? '';
$follow_up_notes = $data['follow_up_notes'] ?? '';
$selected_services = $data['selected_services'] ?? [];
$teeth_records = $data['teeth_records'] ?? [];

if (!$appointment_id || !$patient_id) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Missing required fields']));
}

// Start transaction
$conn->begin_transaction();

try {
    // Update appointment with all consultation data
    $update_stmt = $conn->prepare("
        UPDATE Appointments 
        SET consultation_notes = ?, follow_up_notes = ?
        WHERE appointment_id = ? AND patient_id = ?
    ");
    $update_stmt->bind_param("ssii", $consultation_notes, $follow_up_notes, $appointment_id, $patient_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update appointment: " . $update_stmt->error);
    }
    $update_stmt->close();
    
    // Clear existing selected services
    $delete_services = $conn->prepare("DELETE FROM selected_services WHERE appointment_id = ?");
    $delete_services->bind_param("i", $appointment_id);
    $delete_services->execute();
    $delete_services->close();
    
    // Insert new selected services
    if (!empty($selected_services)) {
        $service_stmt = $conn->prepare("
            INSERT INTO selected_services (appointment_id, service_id)
            VALUES (?, ?)
        ");
        
        foreach ($selected_services as $service) {
            $service_id = $service['service_id'];
            $service_stmt->bind_param("ii", $appointment_id, $service_id);
            if (!$service_stmt->execute()) {
                throw new Exception("Failed to insert service: " . $service_stmt->error);
            }
        }
        $service_stmt->close();
    }
    
    // Delete existing tooth records for this appointment
    $delete_stmt = $conn->prepare("DELETE FROM Tooth_Records WHERE appointment_id = ?");
    $delete_stmt->bind_param("i", $appointment_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    
    // Insert new tooth records
    if (!empty($teeth_records)) {
        $insert_stmt = $conn->prepare("
            INSERT INTO Tooth_Records (appointment_id, tooth_number, status, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        
        foreach ($teeth_records as $tooth_number => $status) {
            $insert_stmt->bind_param("iis", $appointment_id, $tooth_number, $status);
            if (!$insert_stmt->execute()) {
                throw new Exception("Failed to insert tooth record: " . $insert_stmt->error);
            }
        }
        $insert_stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Consultation saved successfully',
        'teeth_count' => count($teeth_records),
        'services_count' => count($selected_services)
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
