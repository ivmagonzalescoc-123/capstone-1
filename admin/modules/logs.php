<?php
/**
 * Admin Activity Logs Module
 */

$logs = [];
$result = $conn->query("
    SELECT ul.user_module_id, ul.login_time, ul.logout_time, ul.ip_address,
           ua.first_name, ua.last_name,
           b.branch_name
    FROM User_Logs ul
    LEFT JOIN User_Account ua ON ul.user_id = ua.user_id
    LEFT JOIN Branch b ON ul.branch_id = b.branch_id
    ORDER BY ul.login_time DESC
    LIMIT 200
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">
                <i class="bi bi-file-text me-2"></i>Activity Logs
            </h2>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Login Time</th>
                        <th>Logout Time</th>
                        <th>Branch</th>
                        <th>IP Address</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($log['login_time'])); ?></td>
                            <td><?php echo $log['logout_time'] ? date('M d, Y H:i', strtotime($log['logout_time'])) : 'Still logged in'; ?></td>
                            <td><?php echo htmlspecialchars($log['branch_name'] ?? 'N/A'); ?></td>
                            <td><small><?php echo htmlspecialchars($log['ip_address']); ?></small></td>
                            <td>
                                <?php
                                if ($log['logout_time']) {
                                    $login = new DateTime($log['login_time']);
                                    $logout = new DateTime($log['logout_time']);
                                    $interval = $login->diff($logout);
                                    echo $interval->format('%H:%I:%S');
                                } else {
                                    echo '<span class="badge bg-success">Active</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
