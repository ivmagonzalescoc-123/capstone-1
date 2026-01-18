<?php
/**
 * Admin Payments Management Module
 */

$payments = [];
$result = $conn->query("
    SELECT p.payment_id, p.payment_date, p.amount_paid, p.payment_method, p.reference_no,
           b.total_amount, b.billing_id,
           a.appointment_date,
           pat.first_name as patient_first, pat.last_name as patient_last
    FROM Payment p
    LEFT JOIN Billing b ON p.billing_id = b.billing_id
    LEFT JOIN Appointments a ON b.appointment_id = a.appointment_id
    LEFT JOIN Patients pat ON a.patient_id = pat.patients_id
    ORDER BY p.payment_date DESC
    LIMIT 100
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}

$total_payments = 0;
foreach ($payments as $payment) {
    $total_payments += $payment['amount_paid'];
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">
                <i class="bi bi-cash-coin me-2"></i>Payments
            </h2>
        </div>
        <div class="col-md-6 text-end">
            <div class="badge bg-success p-3">
                Total Collected: <strong>₱<?php echo number_format($total_payments, 2); ?></strong>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Payments</h6>
                    <h3 class="text-success">₱<?php echo number_format($total_payments, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Transactions</h6>
                    <h3><?php echo count($payments); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Payment Date</th>
                        <th>Patient</th>
                        <th>Amount Paid</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                            <td><?php echo htmlspecialchars($payment['patient_first'] . ' ' . $payment['patient_last']); ?></td>
                            <td><strong>₱<?php echo number_format($payment['amount_paid'], 2); ?></strong></td>
                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                            <td><?php echo htmlspecialchars($payment['reference_no']); ?></td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
