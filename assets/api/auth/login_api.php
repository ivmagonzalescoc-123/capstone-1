<?php
/**
 * Simple Login API Endpoint
 * POST /assets/api/auth/login_api.php
 */
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// Get input
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Email and password are required']));
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "azucena_dental");

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Find user by email
$stmt = $conn->prepare("SELECT user_id, email, password, first_name, last_name, role_id FROM User_Account WHERE email = ? AND is_active = 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Invalid email or password']));
}

$user = $result->fetch_assoc();

// Check password (plain text)
if ($password !== $user['password']) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Invalid email or password']));
}

// Get role name
$role_stmt = $conn->prepare("SELECT role_name FROM Role WHERE role_id = ?");
$role_stmt->bind_param("i", $user['role_id']);
$role_stmt->execute();
$role_result = $role_stmt->get_result();
$role_data = $role_result->fetch_assoc();
$role = strtolower($role_data['role_name']);

// Set session
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $role;
$_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];

// Close connection
$stmt->close();
$role_stmt->close();
$conn->close();

// Return success
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
        'id' => $user['user_id'],
        'email' => $user['email'],
        'role' => $role,
        'full_name' => $_SESSION['full_name']
    ]
]);
?>
