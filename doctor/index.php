<?php
/**
 * Doctor Dashboard - New Layout
 */
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "azucena_dental");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    $conn->close();
    header('Location: ../index.php');
    exit;
}

// Get dashboard stats
$doctor_id = $_SESSION['user_id'];

// Total patients
$result = $conn->query("SELECT COUNT(DISTINCT patient_id) as count FROM Appointments WHERE doctor_id = $doctor_id");
$total_patients = $result->fetch_assoc()['count'];

// New bookings (appointments in next 7 days)
$result = $conn->query("
    SELECT COUNT(*) as count FROM Appointments 
    WHERE doctor_id = $doctor_id 
    AND appointment_date >= CURDATE() 
    AND appointment_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND status_id IN (1, 2)
");
$new_bookings = $result->fetch_assoc()['count'];

// Today's sessions
$result = $conn->query("
    SELECT COUNT(*) as count FROM Appointments 
    WHERE doctor_id = $doctor_id 
    AND appointment_date = CURDATE()
    AND status_id IN (1, 2)
");
$today_sessions = $result->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Azucena Dental Clinic</title>
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
    <!-- NAVBAR -->
    <nav class="navbar navbar-custom sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-stethoscope me-2" style="color: #1e3c72;"></i>Doctor Portal
            </a>
            <div class="user-info ms-auto">
                <span class="text-dark">
                    <i class="bi bi-person-circle me-1"></i>
                    <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Doctor'; ?>
                </span>
                <a href="../assets/api/auth/logout_api.php" class="btn btn-sm btn-outline-danger" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- MAIN LAYOUT -->
    <div class="sidebar-wrapper">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="bi bi-stethoscope brand-icon"></i>
                <div class="brand-title">Dentist</div>
                <div class="brand-subtitle">doctor.coc.com</div>
            </div>

            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo !isset($_GET['module']) ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] === 'consultation') ? 'active' : ''; ?>" href="index.php?module=consultation">
                        <i class="bi bi-stethoscope"></i>
                        <span>Consultation</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] === 'appointments') ? 'active' : ''; ?>" href="index.php?module=appointments">
                        <i class="bi bi-calendar2"></i>
                        <span>Schedule</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] === 'patients') ? 'active' : ''; ?>" href="index.php?module=patients">
                        <i class="bi bi-people"></i>
                        <span>Appointment</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] === 'profile') ? 'active' : ''; ?>" href="index.php?module=profile">
                        <i class="bi bi-person"></i>
                        <span>Patient</span>
                    </a>
                </li>
            </ul>

            <!-- Sidebar footer removed - logout in navbar -->
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <?php
            $module = isset($_GET['module']) ? $_GET['module'] : 'dashboard';
            $module_file = 'modules/' . preg_replace('/[^a-z0-9_-]/i', '', $module) . '.php';
            
            if (file_exists($module_file) && $module !== 'header' && $module !== 'sidebar' && $module !== 'footer') {
                include $module_file;
            } else {
                // Default Dashboard
                ?>
                <div class="content-header">
                    <h1>Dashboard</h1>
                </div>

                <div class="status-cards">
                    <div class="status-card patients">
                        <div class="status-card-label">Patients</div>
                        <div class="status-card-value"><?php echo $total_patients; ?></div>
                    </div>
                    <div class="status-card bookings">
                        <div class="status-card-label">New Booking</div>
                        <div class="status-card-value"><?php echo $new_bookings; ?></div>
                    </div>
                    <div class="status-card sessions">
                        <div class="status-card-label">Today Sessions</div>
                        <div class="status-card-value"><?php echo $today_sessions; ?></div>
                    </div>
                </div>

                <h3 style="margin-top: 40px; margin-bottom: 20px; color: #2c3e50; font-weight: 700;">Today's Appointments</h3>

                <div class="data-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Appointment #</th>
                                <th>Patient Name</th>
                                <th>Date & Time</th>
                                <th>Service</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("
                                SELECT a.appointment_id, a.appointment_date, a.appointment_time,
                                       p.first_name, p.last_name, s.status_name, ser.service_name
                                FROM Appointments a
                                LEFT JOIN Patients p ON a.patient_id = p.patients_id
                                LEFT JOIN Status s ON a.status_id = s.status_id
                                LEFT JOIN Services ser ON a.appointment_id = ser.service_id
                                WHERE a.doctor_id = $doctor_id AND a.appointment_date = CURDATE()
                                ORDER BY a.appointment_time ASC
                            ");

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $time = $row['appointment_time'] ? date('h:i A', strtotime($row['appointment_time'])) : 'N/A';
                                    ?>
                                    <tr>
                                        <td>#<?php echo str_pad($row['appointment_id'], 5, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo date('M d, Y') . ' ' . $time; ?></td>
                                        <td><?php echo htmlspecialchars($row['service_name'] ?? 'General'); ?></td>
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
                                    <td colspan="5" class="text-center py-4 text-muted">No appointments today</td>
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
    <script>
        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>
