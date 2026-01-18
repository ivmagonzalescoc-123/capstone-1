<?php
/**
 * Patient Dashboard - New Layout
 */
session_start();

$conn = new mysqli("localhost", "root", "", "azucena_dental");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    $conn->close();
    header('Location: ../index.php');
    exit;
}

// Get patient info
$user_id = $_SESSION['user_id'];
$patient_query = "SELECT patients_id FROM Patients WHERE user_id = ?";
$patient_stmt = $conn->prepare($patient_query);
$patient_stmt->bind_param("i", $user_id);
$patient_stmt->execute();
$patient_result = $patient_stmt->get_result();

if ($patient_result->num_rows === 0) {
    die("Patient record not found");
}

$patient_data = $patient_result->fetch_assoc();
$patient_id = $patient_data['patients_id'];
$patient_stmt->close();

// Get dashboard stats
$result = $conn->query("SELECT COUNT(*) as count FROM Appointments WHERE patient_id = $patient_id");
$total_appointments = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM Appointments WHERE patient_id = $patient_id AND appointment_date >= CURDATE() AND status_id IN (1,2)");
$upcoming_appointments = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM Appointments WHERE patient_id = $patient_id AND status_id = 3");
$completed_appointments = $result->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Azucena Dental Clinic</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .navbar-custom {
            background-color: #fff;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-right: 20px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-heart me-2" style="color: #1e3c72;"></i>Patient Portal
            </a>
            <div class="user-info ms-auto">
                <span class="text-dark">
                    <i class="bi bi-person-circle me-1"></i>
                    <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Patient'; ?>
                </span>
                <a href="../assets/api/auth/logout_api.php" class="btn btn-sm btn-outline-danger" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="sidebar-wrapper">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="bi bi-heart-pulse brand-icon"></i>
                <div class="brand-title">Patient</div>
                <div class="brand-subtitle">clinic.patient.com</div>
            </div>

            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo !isset($_GET['module']) ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] === 'book-appointment') ? 'active' : ''; ?>" href="index.php?module=book-appointment">
                        <i class="bi bi-plus-circle"></i>
                        <span>Book Appointment</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] === 'my-appointments') ? 'active' : ''; ?>" href="index.php?module=my-appointments">
                        <i class="bi bi-calendar2-check"></i>
                        <span>My Appointments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] === 'profile') ? 'active' : ''; ?>" href="index.php?module=profile">
                        <i class="bi bi-person"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>

            <!-- Sidebar footer removed - logout in navbar -->
        </aside>

        <main class="main-content">
            <?php
            $module = isset($_GET['module']) ? $_GET['module'] : 'dashboard';
            $module_file = 'modules/' . preg_replace('/[^a-z0-9_-]/i', '', $module) . '.php';
            
            if (file_exists($module_file) && $module !== 'header' && $module !== 'sidebar' && $module !== 'footer') {
                include $module_file;
            } else {
                ?>
                <div class="content-header">
                    <h1>Dashboard</h1>
                </div>

                <div class="status-cards">
                    <div class="status-card patients">
                        <div class="status-card-label">Total Appointments</div>
                        <div class="status-card-value"><?php echo $total_appointments; ?></div>
                    </div>
                    <div class="status-card bookings">
                        <div class="status-card-label">Upcoming</div>
                        <div class="status-card-value"><?php echo $upcoming_appointments; ?></div>
                    </div>
                    <div class="status-card sessions">
                        <div class="status-card-label">Completed</div>
                        <div class="status-card-value"><?php echo $completed_appointments; ?></div>
                    </div>
                </div>

                <h3 style="margin-top: 40px; margin-bottom: 20px; color: #2c3e50; font-weight: 700;">Your Upcoming Appointments</h3>

                <div class="data-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Doctor</th>
                                <th>Service</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("
                                SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.is_online_appointment,
                                       d.first_name, d.last_name, s.status_name, ser.service_name
                                FROM Appointments a
                                LEFT JOIN User_Account d ON a.doctor_id = d.user_id
                                LEFT JOIN Status s ON a.status_id = s.status_id
                                LEFT JOIN Services ser ON a.appointment_id = ser.service_id
                                WHERE a.patient_id = $patient_id AND a.appointment_date >= CURDATE()
                                ORDER BY a.appointment_date ASC, a.appointment_time ASC
                                LIMIT 5
                            ");

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $time = $row['appointment_time'] ? date('h:i A', strtotime($row['appointment_time'])) : 'N/A';
                                    ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['appointment_date'])) . ' ' . $time; ?></td>
                                        <td><?php echo htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? 'N/A')); ?></td>
                                        <td><?php echo htmlspecialchars($row['service_name'] ?? 'General'); ?></td>
                                        <td>
                                            <?php if ($row['is_online_appointment'] == 1): ?>
                                                <span class="badge badge-online">Online</span>
                                            <?php else: ?>
                                                <span class="badge badge-onsite">In-Clinic</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status = $row['status_name'] ?? 'Pending';
                                            $badge_class = 'badge-pending';
                                            if ($status === 'Confirmed') $badge_class = 'badge-confirmed';
                                            elseif ($status === 'Completed') $badge_class = 'badge-completed';
                                            elseif ($status === 'Cancelled') $badge_class = 'badge-cancelled';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No upcoming appointments</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
            ?>
        </main>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
