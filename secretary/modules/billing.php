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
                                    <button class="btn btn-sm btn-warning" onclick="generateAndShowInvoice(<?php echo $apt['appointment_id']; ?>)">
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

    <!-- BOOK FOLLOW-UP TAB REMOVED - Now in Appointments Module -->
</div>

<!-- INVOICE MODAL -->
<div class="modal fade" id="invoiceModal" tabindex="-1" size="lg">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-receipt"></i> Invoice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="invoiceContent">
                <p class="text-center text-muted">Loading invoice...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printInvoice()">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function generateAndShowInvoice(appointmentId) {
    fetch('/capstone/secretary/ajax/generate-invoice.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ appointment_id: appointmentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayInvoice(data.invoice);
            const modal = new bootstrap.Modal(document.getElementById('invoiceModal'));
            modal.show();
        } else {
            alert('Error: ' + (data.error || 'Failed to generate invoice'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating invoice');
    });
}

function displayInvoice(invoice) {
    let servicesHTML = '';
    if (invoice.services.length > 0) {
        servicesHTML = '<table class="table table-sm mt-3"><thead><tr><th>Service/Treatment</th><th class="text-end">Amount</th></tr></thead><tbody>';
        invoice.services.forEach(service => {
            servicesHTML += `<tr><td>${service.service_name}</td><td class="text-end">₱${parseFloat(service.initial_deposit).toFixed(2)}</td></tr>`;
        });
        servicesHTML += '</tbody></table>';
    }

    const invoiceHTML = `
        <div style="font-family: Arial, sans-serif; padding: 20px; background: white;">
            <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1e3c72; padding-bottom: 20px;">
                <h2 style="color: #1e3c72; margin: 0;">AZUCENA DENTAL CLINIC</h2>
                <p style="color: #666; margin: 5px 0;">Metz Arcade, Cagayan de Oro</p>
                <p style="color: #666; margin: 0;">(088) 123-4567</p>
            </div>

            <div style="margin-bottom: 20px;">
                <h4 style="color: #1e3c72; margin-bottom: 10px;">INVOICE</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.9rem;">
                    <div><strong>Invoice #:</strong> INV-${invoice.appointment_id}</div>
                    <div><strong>Date:</strong> ${new Date().toLocaleDateString()}</div>
                </div>
            </div>

            <div style="margin-bottom: 20px; background: #f5f5f5; padding: 15px; border-radius: 5px;">
                <h5 style="color: #1e3c72; margin-top: 0;">PATIENT INFORMATION</h5>
                <p style="margin: 5px 0;"><strong>Name:</strong> ${invoice.patient_name}</p>
                <p style="margin: 5px 0;"><strong>Appointment Date:</strong> ${invoice.appointment_date}</p>
                <p style="margin: 5px 0;"><strong>Appointment Time:</strong> ${invoice.appointment_time}</p>
                <p style="margin: 5px 0;"><strong>Doctor:</strong> Dr. ${invoice.doctor_name}</p>
            </div>

            <div style="margin-bottom: 20px;">
                <h5 style="color: #1e3c72;">CHARGES</h5>
                <table style="width: 100%; border-collapse: collapse;">
                    <tbody>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 10px; text-align: left;"><strong>Consultation Fee</strong></td>
                            <td style="padding: 10px; text-align: right;"><strong>₱${parseFloat(invoice.consultation_fee).toFixed(2)}</strong></td>
                        </tr>
                        ${servicesHTML}
                    </tbody>
                </table>
            </div>

            <div style="background: #e8f4f8; padding: 15px; border-radius: 5px; margin-top: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 1rem;">
                    <div style="text-align: left;">
                        <p style="margin: 5px 0;">Services Total:</p>
                        <p style="margin: 5px 0;"><strong>TOTAL AMOUNT:</strong></p>
                    </div>
                    <div style="text-align: right;">
                        <p style="margin: 5px 0;">₱${parseFloat(invoice.services_total).toFixed(2)}</p>
                        <p style="margin: 5px 0; color: #1e3c72; font-size: 1.2rem;"><strong>₱${parseFloat(invoice.total_amount).toFixed(2)}</strong></p>
                    </div>
                </div>
            </div>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 0.85rem;">
                <p style="margin: 5px 0;">Thank you for choosing Azucena Dental Clinic!</p>
                <p style="margin: 5px 0;">Please settle your payment at the clinic or contact us for payment arrangements.</p>
            </div>
        </div>
    `;

    document.getElementById('invoiceContent').innerHTML = invoiceHTML;
}

function printInvoice() {
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write(document.getElementById('invoiceContent').innerHTML);
    printWindow.document.close();
    printWindow.print();
}

function searchPayments() {
    alert('Payment search functionality');
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
