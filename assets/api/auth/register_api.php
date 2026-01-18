<?php
/**
 * Register API Endpoint
 * POST /assets/api/auth/register_api.php
 * Creates patient account in both User_Account and Patients tables
 */
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// Get input
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$first_name = isset($input['first_name']) ? trim($input['first_name']) : '';
$middle_name = isset($input['middle_name']) ? trim($input['middle_name']) : '';
$last_name = isset($input['last_name']) ? trim($input['last_name']) : '';
$username = isset($input['username']) ? trim($input['username']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';
$date_of_birth = isset($input['date_of_birth']) ? trim($input['date_of_birth']) : '';
$gender = isset($input['gender']) ? trim($input['gender']) : '';
$phone_number = isset($input['phone_number']) ? trim($input['phone_number']) : '';
$address = isset($input['address']) ? trim($input['address']) : '';

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || 
    empty($password) || empty($date_of_birth) || empty($gender) || empty($phone_number) || empty($address)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'All fields are required']));
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid email format']));
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "azucena_dental");

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Check if email already exists in User_Account
$check_email = $conn->prepare("SELECT user_id FROM User_Account WHERE email = ?");
$check_email->bind_param("s", $email);
$check_email->execute();
$email_result = $check_email->get_result();

if ($email_result->num_rows > 0) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Email already registered']));
}

// Check if username already exists in User_Account
$check_username = $conn->prepare("SELECT user_id FROM User_Account WHERE username = ?");
$check_username->bind_param("s", $username);
$check_username->execute();
$username_result = $check_username->get_result();

if ($username_result->num_rows > 0) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Username already taken']));
}

// Hash password
$stored_password = $password; // Store plain text password for now

// Patient role_id = 4
$role_id = 4;

// Start transaction
$conn->begin_transaction();

try {
    // 1. Insert into User_Account table
    $insert_user = $conn->prepare("INSERT INTO User_Account (first_name, last_name, username, password, date_of_birth, gender, phone_number, email, address, role_id, is_active, created_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
    $insert_user->bind_param("sssssssssi", $first_name, $last_name, $username, $stored_password, $date_of_birth, $gender, $phone_number, $email, $address, $role_id);
    
    if (!$insert_user->execute()) {
        throw new Exception("Failed to create user account: " . $insert_user->error);
    }
    
    $user_id = $conn->insert_id;
    
    // 2. Insert into Patients table
    $added_by = $_SESSION['user_id'] ?? 1; // Use current user ID or default to 1
    $insert_patient = $conn->prepare("INSERT INTO Patients (user_id, first_name, last_name, middle_name, username, password, address, date_of_birth, gender, phone_number, email, created_at, added_by) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
    $insert_patient->bind_param("issssssssssi", $user_id, $first_name, $last_name, $middle_name, $username, $stored_password, $address, $date_of_birth, $gender, $phone_number, $email, $added_by);
    
    if (!$insert_patient->execute()) {
        throw new Exception("Failed to create patient record: " . $insert_patient->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    // Set session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'patient';
    $_SESSION['full_name'] = $first_name . ' ' . $last_name;
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully',
        'user' => [
            'id' => $user_id,
            'username' => $username,
            'email' => $email,
            'role' => 'patient',
            'full_name' => $_SESSION['full_name']
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$check_email->close();
$check_username->close();
$insert_user->close();
$insert_patient->close();
$conn->close();
?>
