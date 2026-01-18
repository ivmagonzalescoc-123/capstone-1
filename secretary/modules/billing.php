<?php
/**
 * Secretary Billing Module
 * Handles invoices, payments, and booking follow-up appointments
 */

// Get completed appointments awaiting billing
$result = $conn->query("
    SELECT a.appointment_id, a.appointment_date, a.appointment_time,
           p.patients_id, p.first_name, p.last_name, p.phone_number,
           d.first_name as doc_fname, d.last_name as doc_lname,
           s.status_name, a.is_online_appointment
    FROM Appointments a
    LEFT JOIN Patients p ON a.patient_id = p.patients_id
    LEFT JOIN User_Account d ON a.doctor_id = d.user_id
    LEFT JOIN Status s ON a.status_id = s.status_id
    WHERE s.status_name = 'Completed'
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 50
");

$completed_appointments = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $completed_appointments[] = $row;
    }
}
?>

<h3 style="color: #1e3c72; font-weight: 700; margin-bottom: 20px;">Billing & Payments</h3>

<ul class="nav nav-tabs mb-4" role="tablist" style="border-bottom: 2px solid #1e3c72;">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" style="color: #1e3c72; font-weight: 600;">
            <i class="bi bi-receipt"></i> Invoices
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" style="color: #666; font-weight: 600;">
            <i class="bi bi-credit-card"></i> Payments
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="follow-up-tab" data-bs-toggle="tab" data-bs-target="#follow-up" type="button" style="color: #666; font-weight: 600;">
            <i class="bi bi-calendar-plus"></i> Book Follow-up
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- INVOICES TAB -->
    <div class="tab-pane fade show active" id="invoices" role="tabpanel">
        <h3 style="color: #1e3c72; font-weight: 700; margin-bottom: 20px;">Generate Invoices</h3>
        
        <div class="data-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Patient Name</th>
                        <th>Date</th>
                        <th>Doctor</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($completed_appointments) > 0): ?>
                        <?php foreach ($completed_appointments as $apt): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($apt['appointment_id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></td>
                                <td><?php echo htmlspecialchars(($apt['doc_fname'] ?? '') . ' ' . ($apt['doc_lname'] ?? '')); ?></td>
                                <td><span class="badge badge-completed">Completed</span></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="generateInvoice(<?php echo $apt['appointment_id']; ?>)">
                                        <i class="bi bi-printer"></i> Generate Invoice
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No completed appointments awaiting invoicing</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PAYMENTS TAB -->
    <div class="tab-pane fade" id="payments" role="tabpanel">
        <h3 style="color: #1e3c72; font-weight: 700; margin-bottom: 20px;">Record Payments</h3>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card p-4">
                    <h5>Search Patient</h5>
                    <input type="text" class="form-control" id="searchPaymentPatient" placeholder="Enter patient name or ID">
                    <button class="btn btn-info mt-3 w-100" onclick="searchPayments()">Search</button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4" id="paymentForm" style="display:none;">
                    <h5>Record Payment</h5>
                    <form id="paymentFormElement">
                        <div class="mb-3">
                            <label class="form-label">Amount Paid</label>
                            <input type="number" class="form-control" id="amountPaid" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-control" id="paymentMethod" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="check">Check</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Record Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- BOOK FOLLOW-UP TAB -->
    <div class="tab-pane fade" id="follow-up" role="tabpanel">
        <h3 style="color: #1e3c72; font-weight: 700; margin-bottom: 20px;">Book Follow-up Appointments</h3>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card p-4">
                    <h5>Select Patient</h5>
                    <input type="text" class="form-control" id="searchFollowUpPatient" placeholder="Search patient...">
                    <button class="btn btn-info mt-3 w-100" onclick="searchFollowUp()">Search</button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4" id="followUpForm" style="display:none;">
                    <h5>New Appointment</h5>
                    <form id="followUpFormElement">
                        <div class="mb-3">
                            <label class="form-label">Doctor</label>
                            <select class="form-control" id="followUpDoctor" required>
                                <option value="">Select Doctor</option>
                                <?php
                                $doctors = $conn->query("SELECT user_id, first_name, last_name FROM User_Account WHERE role_id = 2");
                                while ($doc = $doctors->fetch_assoc()) {
                                    echo '<option value="' . $doc['user_id'] . '">' . htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" id="followUpDate" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time</label>
                            <select class="form-control" id="followUpTime" required>
                                <option value="">Select Time</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Book Appointment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateInvoice(appointmentId) {
    window.open('../ajax/generate-invoice.php?appointment_id=' + appointmentId, '_blank');
}

function searchPayments() {
    alert('Payment search functionality');
}

function searchFollowUp() {
    alert('Follow-up booking search functionality');
}

// Update nav-link colors on tab change
document.querySelectorAll('.nav-link').forEach(tab => {
    tab.addEventListener('shown.bs.tab', function() {
        document.querySelectorAll('.nav-link').forEach(t => {
            t.style.color = '#666';
            t.style.fontWeight = '600';
        });
        this.style.color = '#1e3c72';
        this.style.fontWeight = '700';
    });
});
</script>
