<?php
/**
 * Get Available Services
 */
@session_start();
header('Content-Type: application/json; charset=utf-8');

// Prevent PHP warnings/notices from breaking JSON responses
ini_set('display_errors', '0');
error_reporting(0);

// Some environments enable mysqli exceptions globally; force them off for JSON endpoints.
mysqli_report(MYSQLI_REPORT_OFF);

$conn = null;

try {
    $conn = @new mysqli("localhost", "root", "", "azucena_dental");
    if (!$conn || $conn->connect_errno) {
        http_response_code(500);
        echo json_encode([
            'services' => [],
            'error' => 'Database connection failed'
        ]);
        exit;
    }

    // Prefer filtering active services, but fall back if schema differs.
    $result = @$conn->query("SELECT service_id, service_name FROM Services WHERE status_id = 1 ORDER BY service_name");
    if (!$result) {
        $result = @$conn->query("SELECT service_id, service_name FROM Services ORDER BY service_name");
    }

    $services = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
    }

    echo json_encode([
        'services' => $services
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'services' => [],
        'error' => 'Server error'
    ]);
} finally {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
}
?>
