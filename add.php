<?php 
include 'includes/db_connect.php'; 
include 'includes/header.php'; 

// Fetch specialties and doctors for dropdowns
$specialties = $pdo->query("SELECT * FROM specialties")->fetchAll();
$doctors = $pdo->query("SELECT * FROM doctors")->fetchAll();
?>

<div class="booking-container" style="max-width: 650px; margin: 0 auto; background: white; padding: 2.5rem; border-radius: 15px; box-shadow: var(--shadow); border-top: 5px solid var(--primary);">
    <h2 style="margin-bottom: 0.5rem; color: var(--primary);">Clinic Booking Portal</h2>
    <p style="margin-bottom: 2rem; color: #666; font-size: 0.9rem;">Fill in the details below to secure your medical appointment.</p>
    
    <form action="process_add.php" method="POST">
        
        <div style="background: #fff5f5; padding: 1rem; border-radius: 8px; border: 1px solid #feb2b2; margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: center; color: #c53030; font-weight: bold; cursor: pointer;">
                <input type="checkbox" name="is_emergency" value="1" style="margin-right: 10px; transform: scale(1.2);"> 
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
                <input type="text" name="patient_phone" placeholder="e.g. 0244000000" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label>Specialty</label>
                <select name="specialty_id" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">-- Choose --</option>
                    <?php foreach($specialties as $spec): ?>
                        <option value="<?= $spec['id'] ?>"><?= $spec['spec_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Doctor</label>
                <select name="doctor_id" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">-- Choose --</option>
                    <?php foreach($doctors as $doc): ?>
                        <option value="<?= $doc['id'] ?>"><?= $doc['doc_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <label>Service Tier</label>
                <select name="tier" id="tier" onchange="updateServiceOptions()" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="Standard">Standard</option>
                    <option value="VVIP">VVIP (Premium)</option>
                </select>
            </div>
            <div>
                <label>Location</label>
                <select name="service_type" id="service_type" onchange="toggleAddress()" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="In-Clinic">In-Clinic</option>
                    <option value="Home-Service" id="opt_home" disabled>Home Service (VVIP Only)</option>
                </select>
            </div>
        </div>

        <div id="address-field" style="display: none; margin-bottom: 1.5rem; padding: 1rem; background: #fff9e6; border-radius: 8px; border: 1px dashed var(--vvip-gold);">
            <label style="color: #856404; font-weight: bold;">Home Address for Visit</label>
            <textarea name="home_address" placeholder="Digital address or landmarks..." style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; margin-top: 5px;"></textarea>
        </div>

        <div style="display: flex; gap: 1rem;">
            <input type="date" name="app_date" required style="flex: 1; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
            <input type="time" name="app_time" required style="flex: 1; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 2rem; height: 50px; font-size: 1.1rem;">Confirm Appointment</button>
    </form>
</div>

<script>
function updateServiceOptions() {
    const tier = document.getElementById('tier').value;
    const homeOpt = document.getElementById('opt_home');
    const serviceType = document.getElementById('service_type');
    
    if (tier === 'VVIP') {
        homeOpt.disabled = false;
    } else {
        homeOpt.disabled = true;
        serviceType.value = "In-Clinic"; // Reset to clinic if they downgrade
        toggleAddress();
    }
}

function toggleAddress() {
    const type = document.getElementById('service_type').value;
    const addr = document.getElementById('address-field');
    addr.style.display = (type === 'Home-Service') ? 'block' : 'none';
}
</script>

<?php include 'includes/footer.php'; ?>