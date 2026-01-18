<?php
/**
 * Patient Medical History Module
 */

$patient_id = $_SESSION['user_id'];
$medical_history = [];

$result = $conn->query("
    SELECT h.history_id, h.medical_condition, h.treatment_type, h.treatment_date, h.notes
    FROM Medical_History h
    WHERE h.patient_id = $patient_id
    ORDER BY h.treatment_date DESC
    LIMIT 50
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $medical_history[] = $row;
    }
}
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">My Medical History</h2>

    <div class="card">
        <?php if (count($medical_history) > 0): ?>
            <div class="card-body">
                <?php foreach ($medical_history as $record): ?>
                    <div class="border-bottom pb-3 mb-3" style="<?php echo end($medical_history) === $record ? 'border-bottom: none !important; margin-bottom: 0 !important;' : ''; ?>">
                        <h6 class="mb-1">
                            <span class="badge bg-info"><?php echo htmlspecialchars($record['treatment_type']); ?></span>
                        </h6>
                        <p class="mb-1"><strong><?php echo htmlspecialchars($record['medical_condition']); ?></strong></p>
                        <small class="text-muted">
                            Date: <?php echo date('M d, Y', strtotime($record['treatment_date'])); ?>
                        </small>
                        <?php if ($record['notes']): ?>
                            <p class="mb-0 mt-2"><em><?php echo htmlspecialchars($record['notes']); ?></em></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card-body">
                <p class="text-muted">No medical history records found</p>
            </div>
        <?php endif; ?>
    </div>
</div>
