<?php 
include 'includes/db_connect.php'; 
include 'includes/header.php'; 

$specialties = $pdo->query("SELECT * FROM specialties")->fetchAll();
$doctors = $pdo->query("SELECT * FROM doctors")->fetchAll();
?>

<div class="booking-container" style="max-width: 650px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: 15px; box-shadow: var(--shadow); border-top: 5px solid var(--primary);">
    <h2 style="margin-bottom: 0.5rem; color: var(--primary);">SwiftCare Clinic Portal</h2>
    <p style="margin-bottom: 2rem; color: #666; font-size: 0.9rem;">Appointments involving Emergency or Home Service are classified as **VVIP sessions**.</p>
    
    <form action="process_add.php" method="POST">
        
        <div style="background: #fff5f5; padding: 1rem; border-radius: 8px; border: 1px solid #feb2b2; margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: center; color: #c53030; font-weight: bold; cursor: pointer;">
                <input type="checkbox" name="is_emergency" id="is_emergency" value="1" onchange="syncTierAndFees()" style="margin-right: 10px; transform: scale(1.2);"> 
                ⚠️ EMERGENCY APPOINTMENT (Immediate Attention)
            </label>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label>Full Name</label>
                <input type="text" name="patient_name" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label>Phone Number</label>
                <input type="text" name="patient_phone" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label>Service Tier</label>
                <select name="tier" id="tier" onchange="calculateFees()" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
                    <option value="Standard">Standard</option>
                    <option value="VVIP">VVIP (Premium)</option>
                </select>
            </div>
            <div>
                <label>Location</label>
                <select name="service_type" id="service_type" onchange="syncTierAndFees()" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="In-Clinic">In-Clinic (At Hospital)</option>
                    <option value="Home-Service">Home Service (Doctor Visits You)</option>
                </select>
            </div>
        </div>

        <div id="address-field" style="display: none; margin-bottom: 1.5rem; padding: 1rem; background: #fff9e6; border-radius: 8px; border: 1px dashed var(--vvip-gold);">
            <label style="color: #856404; font-weight: bold;">Home Address / GPS Digital Address</label>
            <textarea name="home_address" placeholder="Enter landmarks or digital address..." style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; margin-top: 5px;"></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label>Medical Specialty</label>
                <select name="specialty_id" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    <?php foreach($specialties as $spec): ?>
                        <option value="<?= $spec['id'] ?>"><?= $spec['spec_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Available Doctor</label>
                <select name="doctor_id" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    <?php foreach($doctors as $doc): ?>
                        <option value="<?= $doc['id'] ?>"><?= $doc['doc_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <input type="date" name="app_date" required style="flex: 1; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
            <input type="time" name="app_time" required style="flex: 1; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div id="fee-box" style="background: #f8f9fa; padding: 1.2rem; border-radius: 10px; border-left: 5px solid #ccc; transition: all 0.3s;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 600; color: #444;">Total Estimated Fee:</span>
                <span id="total-fee" style="font-size: 1.4rem; font-weight: 800; color: #222;">GHS 50.00</span>
            </div>
            <p id="fee-note" style="font-size: 0.8rem; color: #666; margin-top: 5px;">Standard clinic visit rate.</p>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 2rem; height: 50px; font-size: 1.1rem;">Confirm Booking</button>
    </form>
</div>

<script>
function syncTierAndFees() {
    const isEmergency = document.getElementById('is_emergency').checked;
    const serviceType = document.getElementById('service_type').value;
    const tierSelect = document.getElementById('tier');
    const addressField = document.getElementById('address-field');

    // Auto-Upgrade Logic: If Home Service OR Emergency is picked, set Tier to VVIP
    if (isEmergency || serviceType === 'Home-Service') {
        tierSelect.value = "VVIP";
    }

    // Toggle Address field visibility
    addressField.style.display = (serviceType === 'Home-Service') ? 'block' : 'none';

    calculateFees();
}

function calculateFees() {
    let base = 50; 
    let extra = 0;
    const isEmergency = document.getElementById('is_emergency').checked;
    const tier = document.getElementById('tier').value;
    const serviceType = document.getElementById('service_type').value;
    
    const feeBox = document.getElementById('fee-box');
    const feeNote = document.getElementById('fee-note');
    const totalDisplay = document.getElementById('total-fee');

    if (serviceType === 'Home-Service') extra += 120; 
    if (tier === 'VVIP') extra += 150; 
    if (isEmergency) extra += 200; 

    let total = base + extra;
    totalDisplay.innerText = "GHS " + total.toFixed(2);

    // Dynamic UI feedback
    if (isEmergency) {
        feeBox.style.borderColor = "#e53e3e";
        feeBox.style.background = "#fff5f5";
        feeNote.innerText = "🚨 Emergency VVIP Priority rates applied.";
    } else if (serviceType === 'Home-Service') {
        feeBox.style.borderColor = "#d4af37";
        feeBox.style.background = "#fffdf5";
        feeNote.innerText = "🏠 VVIP Home Visit charges included.";
    } else if (tier === 'VVIP') {
        feeBox.style.borderColor = "#3182ce";
        feeBox.style.background = "#ebf8ff";
        feeNote.innerText = "💎 Premium VVIP In-Clinic service.";
    } else {
        feeBox.style.borderColor = "#ccc";
        feeBox.style.background = "#f8f9fa";
        feeNote.innerText = "Standard clinic visit rate applies.";
    }
}
</script>