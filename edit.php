<?php 
include 'includes/db_connect.php'; 
include 'includes/header.php'; 

// 1. Get the Appointment ID from the URL
if (!isset($_GET['id'])) {
    header("Location: view.php");
    exit();
}

$id = $_GET['id'];

// 2. Fetch the existing appointment data
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->execute([$id]);
$app = $stmt->fetch();

if (!$app) {
    die("Appointment not found.");
}

// 3. Fetch specialties and doctors for the dropdowns
$specialties = $pdo->query("SELECT * FROM specialties")->fetchAll();
$doctors = $pdo->query("SELECT * FROM doctors")->fetchAll();
?>

<div class="booking-container" style="max-width: 650px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: 15px; box-shadow: var(--shadow); border-top: 5px solid var(--primary);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="color: var(--primary);">Update Appointment</h2>
        <span style="font-size: 0.8rem; background: #eee; padding: 5px 10px; border-radius: 5px;">Ref: #<?= $app['id'] ?></span>
    </div>
    
    <form action="process_edit.php" method="POST">
        <input type="hidden" name="id" value="<?= $app['id'] ?>">

        <div style="background: #fff5f5; padding: 1rem; border-radius: 8px; border: 1px solid #feb2b2; margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: center; color: #c53030; font-weight: bold; cursor: pointer;">
                <input type="checkbox" name="is_emergency" id="is_emergency" value="1" onchange="syncTierAndFees()" 
                <?= $app['is_emergency'] ? 'checked' : '' ?> style="margin-right: 10px; transform: scale(1.2);"> 
                ⚠️ EMERGENCY STATUS
            </label>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label>Patient Name</label>
                <input type="text" name="patient_name" value="<?= htmlspecialchars($app['patient_name']) ?>" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label>Phone Number</label>
                <input type="text" name="patient_phone" value="<?= htmlspecialchars($app['patient_phone']) ?>" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label>Service Tier</label>
                <select name="tier" id="tier" onchange="calculateFees()" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="Standard" <?= $app['tier'] == 'Standard' ? 'selected' : '' ?>>Standard</option>
                    <option value="VVIP" <?= $app['tier'] == 'VVIP' ? 'selected' : '' ?>>VVIP (Premium)</option>
                </select>
            </div>
            <div>
                <label>Location</label>
                <select name="service_type" id="service_type" onchange="syncTierAndFees()" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="In-Clinic" <?= $app['service_type'] == 'In-Clinic' ? 'selected' : '' ?>>In-Clinic</option>
                    <option value="Home-Service" <?= $app['service_type'] == 'Home-Service' ? 'selected' : '' ?>>Home Service</option>
                </select>
            </div>
        </div>

        <div id="address-field" style="display: <?= $app['service_type'] == 'Home-Service' ? 'block' : 'none' ?>; margin-bottom: 1.5rem; padding: 1rem; background: #fff9e6; border-radius: 8px; border: 1px dashed var(--vvip-gold);">
            <label style="color: #856404; font-weight: bold;">Home Address</label>
            <textarea name="home_address" style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; margin-top: 5px;"><?= htmlspecialchars($app['home_address']) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label>Doctor</label>
                <select name="doctor_id" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    <?php foreach($doctors as $doc): ?>
                        <option value="<?= $doc['id'] ?>" <?= $app['doctor_id'] == $doc['id'] ? 'selected' : '' ?>><?= $doc['doc_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Status</label>
                <select name="status" style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; font-weight: bold; color: orange;">
                    <option value="Pending" <?= $app['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Confirmed" <?= $app['status'] == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="Completed" <?= $app['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <input type="date" name="app_date" value="<?= $app['appointment_date'] ?>" required style="flex: 1; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
            <input type="time" name="app_time" value="<?= $app['appointment_time'] ?>" required style="flex: 1; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div id="fee-box" style="background: #f8f9fa; padding: 1.2rem; border-radius: 10px; border-left: 5px solid #ccc;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 600; color: #444;">Revised Fee:</span>
                <span id="total-fee" style="font-size: 1.4rem; font-weight: 800; color: #222;">GHS 50.00</span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 2rem;">Save Changes</button>
    </form>
</div>

<script>
// We reuse your smart logic from add.php
function syncTierAndFees() {
    const isEmergency = document.getElementById('is_emergency').checked;
    const serviceType = document.getElementById('service_type').value;
    const tierSelect = document.getElementById('tier');
    const addressField = document.getElementById('address-field');

    if (isEmergency || serviceType === 'Home-Service') {
        tierSelect.value = "VVIP";
    }
    addressField.style.display = (serviceType === 'Home-Service') ? 'block' : 'none';
    calculateFees();
}

function calculateFees() {
    let base = 50; 
    let extra = 0;
    const isEmergency = document.getElementById('is_emergency').checked;
    const tier = document.getElementById('tier').value;
    const serviceType = document.getElementById('service_type').value;
    const totalDisplay = document.getElementById('total-fee');

    if (serviceType === 'Home-Service') extra += 120; 
    if (tier === 'VVIP') extra += 150; 
    if (isEmergency) extra += 200; 

    totalDisplay.innerText = "GHS " + (base + extra).toFixed(2);
}
// Run fee calculation once on load to show current price
window.onload = calculateFees;
</script>

<?php include 'includes/footer.php'; ?>