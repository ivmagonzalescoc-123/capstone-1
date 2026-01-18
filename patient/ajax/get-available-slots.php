<?php
/**
 * AJAX endpoint for getting available appointment time slots
 */
error_reporting(0); // Suppress error output to console
ini_set('display_errors', 0);

session_start();

// Set JSON header FIRST before any output
header('Content-Type: application/json; charset=utf-8');

// Database connection
$conn = new mysqli("localhost", "root", "", "azucena_dental");
if ($conn->connect_error) {
    echo json_encode(['slots' => [], 'success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Check if user is logged in as patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    echo json_encode(['slots' => [], 'success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Generate time slots based on doctor's schedule
function generateTimeSlots($available_from, $available_to) {
    $slots = [];
    $start = strtotime($available_from);
    $end = strtotime($available_to);
    
    while ($start <= $end) {
        $slots[] = date('H:i:s', $start);
        $start = strtotime('+30 minutes', $start);
    }
    return $slots;
}

// Get available time slots for a specific date
function getAvailableSlots($conn, $date, $doctor_id = null) {
    // Get day of week
    $day_of_week = date('l', strtotime($date)); // e.g., "Monday"
    
    // Get doctor's schedule for this day of week
    $schedule_query = "SELECT available_from, available_to FROM Schedule WHERE day_of_week = ? AND status_id = 1";
    $params = [$day_of_week];
    
    // If doctor_id provided, get their specific schedule
    if ($doctor_id) {
        $schedule_query .= " AND user_id = ?";
        $params[] = $doctor_id;
    }
    
    $stmt = $conn->prepare($schedule_query);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $schedule_result = $stmt->get_result();
    
    if ($schedule_result->num_rows === 0) {
        // No schedule found for this day
        return [];
    }
    
    $schedule = $schedule_result->fetch_assoc();
    $stmt->close();
    
    // Generate time slots based on doctor's available hours
    $all_slots = generateTimeSlots($schedule['available_from'], $schedule['available_to']);
    
    // Check if appointment_time column exists
    $check_column = $conn->query("SHOW COLUMNS FROM Appointments LIKE 'appointment_time'");
    if ($check_column->num_rows == 0) {
        return $all_slots;
    }
    
    // Get booked slots for the date
    $stmt = $conn->prepare("SELECT appointment_time FROM Appointments WHERE appointment_date = ? AND status_id != 4 AND appointment_time IS NOT NULL");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $booked_slots = [];
    while ($row = $result->fetch_assoc()) {
        $booked_slots[] = $row['appointment_time'];
    }
    $stmt->close();
    
    // Count total appointments for the day
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM Appointments WHERE appointment_date = ? AND status_id != 4");
    $count_stmt->bind_param("s", $date);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
    
    // If max appointments reached (20), return empty array
    if ($count >= 20) {
        return [];
    }
    
    // Filter out booked slots
    $available_slots = array_diff($all_slots, $booked_slots);
    return array_values($available_slots);
}

// Main logic wrapped in try-catch
try {
    $date = $_GET['date'] ?? '';
    $doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : null;

    if (!$date) {
        throw new Exception('No date provided');
    }

    $slots = getAvailableSlots($conn, $date, $doctor_id);
    echo json_encode(['slots' => $slots, 'success' => true]);
} catch (Exception $e) {
    echo json_encode(['slots' => [], 'success' => false, 'error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>
