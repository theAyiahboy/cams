<?php 
include 'includes/db_connect.php'; 
include 'includes/header.php'; 

// Fetch all specialties to populate the dropdown
$spec_stmt = $pdo->query("SELECT * FROM specialties");
$specialties = $spec_stmt->fetchAll();

// Fetch all doctors for the initial list
$doc_stmt = $pdo->query("SELECT * FROM doctors");
$doctors = $doc_stmt->fetchAll();
?>

<div class="booking-container" style="max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border-radius: 12px; box-shadow: var(--shadow);">
    <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Book an Appointment</h2>
    
    <form action="process_add.php" method="POST">
        <div style="margin-bottom: 1rem;">
            <label>Full Name</label>
            <input type="text" name="patient_name" required style="width: 100%; padding: 0.8rem; margin-top: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 1rem;">
            <label>Phone Number (for SMS confirmation)</label>
            <input type="text" name="patient_phone" placeholder="e.g. 0244000000" required style="width: 100%; padding: 0.8rem; margin-top: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 1rem;">
            <label>Select Specialty</label>
            <select name="specialty_id" id="specialty" required style="width: 100%; padding: 0.8rem; margin-top: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                <option value="">-- Choose Specialty --</option>
                <?php foreach($specialties as $spec): ?>
                    <option value="<?= $spec['id'] ?>"><?= $spec['spec_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 1rem;">
            <label>Select Doctor</label>
            <select name="doctor_id" id="doctor" required style="width: 100%; padding: 0.8rem; margin-top: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                <option value="">-- Choose Doctor --</option>
                <?php foreach($doctors as $doc): ?>
                    <option value="<?= $doc['id'] ?>"><?= $doc['doc_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 1rem;">
            <label>Service Tier</label>
            <select name="tier" id="tier" onchange="toggleVVIP()" required style="width: 100%; padding: 0.8rem; margin-top: 0.5rem; border: 1px solid #ddd; border-radius: 5px; border-left: 5px solid var(--primary);">
                <option value="Standard">Standard (In-Clinic)</option>
                <option value="VVIP">VVIP (Home-Service)</option>
            </select>
        </div>

        <div id="address-field" style="display: none; margin-bottom: 1rem; padding: 1rem; background: #fff9e6; border-radius: 8px; border: 1px dashed var(--vvip-gold);">
            <label style="color: #856404; font-weight: bold;">Home Address for VVIP Service</label>
            <textarea name="home_address" placeholder="Enter your full digital address or landmarks" style="width: 100%; padding: 0.8rem; margin-top: 0.5rem; border: 1px solid #ddd; border-radius: 5px;"></textarea>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <input type="date" name="app_date" required style="flex: 1; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
            <input type="time" name="app_time" required style="flex: 1; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 2rem;">Confirm Booking</button>
    </form>
</div>

<script>
// This function shows/hides the address field based on tier selection
function toggleVVIP() {
    const tier = document.getElementById('tier').value;
    const addressField = document.getElementById('address-field');
    
    if (tier === 'VVIP') {
        addressField.style.display = 'block';
    } else {
        addressField.style.display = 'none';
    }
}
</script>

<?php include 'includes/footer.php'; ?>