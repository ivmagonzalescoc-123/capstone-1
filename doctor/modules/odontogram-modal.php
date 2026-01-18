<?php
/**
 * Odontogram Modal Component - 2D Clickable Teeth
 * Simplified clickable odontogram for consultation workflow
 */

// This is included by consultation.php module
// No standalone execution needed

$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;

// Load existing tooth records if editing
$existing_teeth = [];
if ($appointment_id > 0 && isset($conn)) {
    $result = $conn->query("
        SELECT tooth_number, status 
        FROM Tooth_Records 
        WHERE appointment_id = $appointment_id
    ");
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $existing_teeth[$row['tooth_number']] = $row['status'];
        }
    }
}

$existingTeethJson = json_encode($existing_teeth);
?>

<!-- Odontogram Modal -->
<div class="modal fade" id="odontogramModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-tooth"></i> Odontogram - Tooth Recording
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <div class="row">
                    <!-- Left: Tooth Grid -->
                    <div class="col-md-8">
                        <div class="odontogram-grid">
                            <h6 class="mb-3">Click teeth to mark status</h6>
                            <div id="odontogramTeeth" class="teeth-grid"></div>
                        </div>
                    </div>
                    
                    <!-- Right: Controls & Legend -->
                    <div class="col-md-4">
                        <div class="odontogram-controls">
                            <h6 class="mb-3">Selected Tooth</h6>
                            <div class="selected-tooth-info mb-3">
                                <div class="mb-2">
                                    <strong>Tooth #:</strong> <span id="selectedToothNum">—</span>
                                </div>
                                <div>
                                    <strong>Status:</strong> <span id="selectedToothStatus">—</span>
                                </div>
                            </div>

                            <h6 class="mb-2">Status Options</h6>
                            <div class="status-buttons-grid mb-3">
                                <button class="btn btn-sm btn-outline-success" data-status="healthy" title="Healthy">
                                    <i class="bi bi-check-circle"></i> Healthy
                                </button>
                                <button class="btn btn-sm btn-outline-warning" data-status="caries" title="Caries">
                                    <i class="bi bi-exclamation-circle"></i> Caries
                                </button>
                                <button class="btn btn-sm btn-outline-info" data-status="filled" title="Filled">
                                    <i class="bi bi-wrench"></i> Filled
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" data-status="crown" title="Crown">
                                    <i class="bi bi-gem"></i> Crown
                                </button>
                                <button class="btn btn-sm btn-outline-danger" data-status="missing" title="Missing">
                                    <i class="bi bi-x-circle"></i> Missing
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" id="clearToothStatus" title="Clear">
                                    <i class="bi bi-eraser"></i> Clear
                                </button>
                            </div>

                            <h6 class="mb-2">Legend</h6>
                            <div class="legend-box">
                                <div class="legend-item">
                                    <span class="legend-color" style="background: #51cf66;"></span>
                                    <span>Healthy</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-color" style="background: #ffa500;"></span>
                                    <span>Caries</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-color" style="background: #4ecdc4;"></span>
                                    <span>Filled</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-color" style="background: #95e1d3;"></span>
                                    <span>Crown</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-color" style="background: #ff6b6b;"></span>
                                    <span>Missing</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveOdontogramBtn">Save Odontogram</button>
            </div>
        </div>
    </div>
</div>

<style>
    .teeth-grid {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 8px;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .tooth-btn {
        aspect-ratio: 1;
        padding: 0;
        border: 2px solid #dee2e6;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        font-size: 11px;
        font-weight: 600;
        color: #333;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    .tooth-btn:hover:not(.marked) {
        border-color: #0d6efd;
        background: #f0f4ff;
        transform: translateY(-2px);
    }

    .tooth-btn.selected {
        border-color: #0d6efd;
        background: #0d6efd;
        color: white;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
    }

    .tooth-btn.marked {
        color: white;
        border: none;
    }

    .tooth-btn .tooth-number {
        font-size: 13px;
        font-weight: 700;
    }

    .tooth-btn .tooth-label {
        font-size: 8px;
        opacity: 0.7;
    }

    .selected-tooth-info {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 6px;
        border-left: 4px solid #0d6efd;
        font-size: 13px;
    }

    .status-buttons-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }

    .legend-box {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        font-size: 12px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .legend-item:last-child {
        margin-bottom: 0;
    }

    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        flex-shrink: 0;
    }

    .odontogram-controls {
        font-size: 13px;
    }

    .odontogram-controls h6 {
        font-size: 13px;
        font-weight: 600;
        color: #333;
    }
</style>

<script>
    // Initialize on modal show
    document.getElementById('odontogramModal').addEventListener('show.bs.modal', function() {
        initializeOdontogram();
    });

    function initializeOdontogram() {
        const modal = document.getElementById('odontogramModal');
        const appointmentId = modal.dataset.appointmentId;

        if (!appointmentId) {
            console.warn('No appointment ID for odontogram');
            return;
        }

        // Load existing tooth data
        fetch(`../../doctor/ajax/get-odontogram.php?appointment_id=${appointmentId}`)
            .then(res => res.json())
            .then(data => setupOdontogramUI(data, appointmentId))
            .catch(err => console.error('Error loading odontogram:', err));
    }

    function setupOdontogramUI(existingData, appointmentId) {
        const teethContainer = document.getElementById('odontogramTeeth');
        const selectedToothNumEl = document.getElementById('selectedToothNum');
        const selectedToothStatusEl = document.getElementById('selectedToothStatus');
        const statusButtons = document.querySelectorAll('.status-buttons-grid .btn[data-status]');
        const clearBtn = document.getElementById('clearToothStatus');
        const saveBtn = document.getElementById('saveOdontogramBtn');

        let selectedTooth = null;
        const toothStates = {};

        // Initialize tooth state
        for (let i = 1; i <= 32; i++) {
            toothStates[i] = existingData[i] || null;
        }

        // Status colors matching legend
        const statusColors = {
            'healthy': '#51cf66',
            'caries': '#ffa500',
            'filled': '#4ecdc4',
            'crown': '#95e1d3',
            'missing': '#ff6b6b'
        };

        // Clear container and rebuild teeth
        teethContainer.innerHTML = '';
        for (let i = 1; i <= 32; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'tooth-btn';
            btn.dataset.tooth = i;
            btn.innerHTML = `<span class="tooth-number">${i}</span><span class="tooth-label">T</span>`;
            btn.title = `Tooth #${i}`;

            // Apply color if has status
            if (toothStates[i]) {
                btn.classList.add('marked');
                btn.style.backgroundColor = statusColors[toothStates[i]] || '#999';
                btn.style.color = 'white';
            }

            btn.addEventListener('click', () => selectTooth(i));
            teethContainer.appendChild(btn);
        }

        function selectTooth(toothNum) {
            // Clear old selection
            document.querySelectorAll('.tooth-btn').forEach(btn => {
                btn.classList.remove('selected');
            });

            // Toggle selection
            if (selectedTooth === toothNum) {
                selectedTooth = null;
                selectedToothNumEl.textContent = '—';
                selectedToothStatusEl.textContent = '—';
                statusButtons.forEach(btn => btn.classList.remove('active'));
            } else {
                selectedTooth = toothNum;
                const btn = document.querySelector(`[data-tooth="${toothNum}"]`);
                btn.classList.add('selected');

                selectedToothNumEl.textContent = toothNum;
                const status = toothStates[toothNum];
                selectedToothStatusEl.textContent = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Not Set';

                // Highlight active status button
                statusButtons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.status === status);
                });
            }
        }

        // Status button handlers
        statusButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                if (!selectedTooth) return;
                const status = btn.dataset.status;
                toothStates[selectedTooth] = status;

                // Update button appearance
                const toothBtn = document.querySelector(`[data-tooth="${selectedTooth}"]`);
                toothBtn.classList.add('marked');
                toothBtn.style.backgroundColor = statusColors[status];
                toothBtn.style.color = 'white';

                // Update selected display
                selectedToothStatusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);

                // Update button states
                statusButtons.forEach(b => {
                    b.classList.toggle('active', b.dataset.status === status);
                });
            });
        });

        // Clear status
        clearBtn.addEventListener('click', () => {
            if (!selectedTooth) return;
            toothStates[selectedTooth] = null;

            const toothBtn = document.querySelector(`[data-tooth="${selectedTooth}"]`);
            toothBtn.classList.remove('marked');
            toothBtn.style.backgroundColor = '';
            toothBtn.style.color = '';

            selectedToothStatusEl.textContent = 'Not Set';
            statusButtons.forEach(b => b.classList.remove('active'));
        });

        // Save odontogram
        saveBtn.onclick = () => {
            fetch('../../doctor/ajax/save-odontogram.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    appointment_id: parseInt(appointmentId),
                    teeth: toothStates
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Odontogram saved successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('odontogramModal')).hide();
                } else {
                    alert('Error saving: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Failed to save odontogram');
            });
        };
    }
</script>
