<?php 
session_start();
include 'includes/db_connect.php'; 

// 1. SECURITY CHECK: Only Admins can edit records
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$successMsg = '';
$errorMsg = '';

// 2. PROCESS FORM SUBMISSION (No need for process_edit.php anymore!)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $update_id = $_POST['id'];
    $name = htmlspecialchars(trim($_POST['patient_name']));
    $phone = htmlspecialchars(trim($_POST['patient_phone']));
    $tier = $_POST['tier'];
    $service_type = $_POST['service_type'];
    $doctor_id = $_POST['doctor_id'];
    $status = $_POST['status'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $address = htmlspecialchars(trim($_POST['home_address']));
    $is_emergency = isset($_POST['is_emergency']) ? 1 : 0;

    try {
        $updateQuery = "UPDATE appointments SET 
                        patient_name = ?, patient_phone = ?, tier = ?, service_type = ?, 
                        doctor_id = ?, status = ?, appointment_date = ?, appointment_time = ?, 
                        home_address = ?, is_emergency = ? 
                        WHERE id = ?";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([$name, $phone, $tier, $service_type, $doctor_id, $status, $date, $time, $address, $is_emergency, $update_id]);
        
        $successMsg = "Record updated successfully!";
    } catch (PDOException $e) {
        $errorMsg = "Update failed: " . $e->getMessage();
    }
}

// 3. Get the Appointment ID from the URL
if (!isset($_GET['id']) && !isset($update_id)) {
    header("Location: view.php");
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : $update_id;

// 4. Fetch the existing appointment data
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->execute([$id]);
$app = $stmt->fetch();

if (!$app) {
    die("Appointment not found.");
}

// 5. Fetch doctors for the dropdown
$doctors = $pdo->query("SELECT id, doc_name, specialty FROM doctors")->fetchAll();

include 'includes/header.php'; 
?>

<style>
    .edit-container { max-width: 800px; margin: 2rem auto; background: white; padding: 3rem; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid #f1f5f9; }
    .form-group { margin-bottom: 1.5rem; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 8px; color: var(--dark); font-size: 0.95rem; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: 0.3s; background: #f8fafc; }
    .form-group input:focus, .form-group select:focus { border-color: var(--primary); background: white; outline: none; box-shadow: 0 0 0 4px rgba(0,97,242,0.1); }
    
    .btn-submit { width: 100%; padding: 1.2rem; background: var(--primary); color: white; border: none; border-radius: 12px; font-weight: 800; font-size: 1.2rem; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(0,97,242,0.2); margin-top: 1rem; }
    .btn-submit:hover { background: #004ecc; transform: translateY(-3px); }
    
    .price-display { background: var(--dark); padding: 25px; border-radius: 16px; margin-bottom: 1.5rem; color: white; display: flex; justify-content: space-between; align-items: center; }
    .price-display h2 { margin: 0; font-size: 2.5rem; color: #38bdf8; font-weight: 800; }
</style>

<div class="edit-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 1rem;">
        <h2 style="margin: 0; color: var(--dark); font-weight: 800;">Edit Patient Record</h2>
        <span style="font-size: 0.9rem; background: #f1f5f9; color: #475569; padding: 8px 15px; border-radius: 8px; font-weight: bold;">Ref: #<?= str_pad($app['id'], 4, '0', STR_PAD_LEFT) ?></span>
    </div>
    
    <?php if ($successMsg): ?>
        <div style="background: #d1fae5; color: #047857; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border-left: 5px solid #10b981; font-weight: bold;">✅ <?= $successMsg ?></div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border-left: 5px solid #ef4444; font-weight: bold;">⚠️ <?= $errorMsg ?></div>
    <?php endif; ?>

    <form action="edit.php?id=<?= $app['id'] ?>" method="POST">
        <input type="hidden" name="id" value="<?= $app['id'] ?>">

        <div class="form-group" style="background: #fef2f2; padding: 20px; border-radius: 12px; border: 2px solid #fca5a5; transition: 0.3s;" id="emergencyContainer">
            <label style="display: flex; align-items: center; gap: 15px; cursor: pointer; color: #b91c1c; font-weight: 800; margin: 0; font-size: 1.1rem;">
                <input type="checkbox" name="is_emergency" id="emergencyToggle" value="1" <?= $app['is_emergency'] ? 'checked' : '' ?> style="width: 25px; height: 25px; accent-color: #dc2626;" onchange="calculateCost()">
                🚨 CRITICAL EMERGENCY STATUS
            </label>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div class="form-group">
                <label>Patient Name</label>
                <input type="text" name="patient_name" value="<?= htmlspecialchars($app['patient_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="patient_phone" value="<?= htmlspecialchars($app['patient_phone']) ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div class="form-group">
                <label>Care Package</label>
                <select name="tier" id="tierSelect" required onchange="calculateCost()">
                    <option value="Standard" data-price="150" <?= $app['tier'] == 'Standard' ? 'selected' : '' ?>>Standard Tier (₵150)</option>
                    <option value="VVIP" data-price="450" <?= $app['tier'] == 'VVIP' ? 'selected' : '' ?>>VVIP Tier (₵450)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Service Location</label>
                <select name="service_type" id="serviceTypeSelect" required onchange="calculateCost()">
                    <option value="In-Clinic" <?= $app['service_type'] == 'In-Clinic' ? 'selected' : '' ?>>🏥 In-Clinic Visit</option>
                    <option value="Home-Service" <?= $app['service_type'] == 'Home-Service' ? 'selected' : '' ?>>🏠 Home Service Delivery</option>
                </select>
            </div>
        </div>

        <div class="form-group" id="addressBox" style="display: <?= $app['service_type'] == 'Home-Service' ? 'block' : 'none' ?>; background: #f0fdf4; padding: 20px; border-radius: 12px; border: 2px solid #86efac;">
            <label style="color: #166534;">📍 Delivery Address</label>
            <textarea name="home_address" rows="2" style="border-color: #86efac;"><?= htmlspecialchars($app['home_address']) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div class="form-group">
                <label>Assigned Doctor</label>
                <select name="doctor_id" required>
                    <?php foreach($doctors as $doc): ?>
                        <option value="<?= $doc['id'] ?>" <?= $app['doctor_id'] == $doc['id'] ? 'selected' : '' ?>>
                            Dr. <?= htmlspecialchars($doc['doc_name']) ?> (<?= htmlspecialchars($doc['specialty']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Appointment Status</label>
                <select name="status" style="background: #fffbeb; border-color: #fcd34d; font-weight: bold; color: #b45309;">
                    <option value="Pending" <?= $app['status'] == 'Pending' ? 'selected' : '' ?>>⏳ Pending</option>
                    <option value="Completed" <?= $app['status'] == 'Completed' ? 'selected' : '' ?>>✔ Completed</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="appointment_date" value="<?= $app['appointment_date'] ?>" required>
            </div>
            <div class="form-group">
                <label>Time</label>
                <input type="time" name="appointment_time" value="<?= $app['appointment_time'] ?>" required>
            </div>
        </div>

        <div class="price-display">
            <span>Revised Total Fee</span>
            <h2 id="totalPriceDisplay">₵150</h2>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 2rem;">
            <a href="view.php" style="padding: 1.2rem; background: #f1f5f9; color: #475569; border-radius: 12px; text-decoration: none; font-weight: 800; text-align: center; flex: 1;">Cancel</a>
            <button type="submit" class="btn-submit" style="margin-top: 0; flex: 2;">Update Record</button>
        </div>
    </form>
</div>

<script>
    function calculateCost() {
        const tierSelect = document.getElementById('tierSelect');
        const emergencyToggle = document.getElementById('emergencyToggle');
        const emergencyContainer = document.getElementById('emergencyContainer');
        const addressBox = document.getElementById('addressBox');
        const serviceTypeSelect = document.getElementById('serviceTypeSelect');
        const priceDisplay = document.getElementById('totalPriceDisplay');

        // Logic for pricing
        let selectedOption = tierSelect.options[tierSelect.selectedIndex];
        let basePrice = parseInt(selectedOption.getAttribute('data-price'));

        // Logic for Address Box
        if (serviceTypeSelect.value === 'Home-Service') {
            addressBox.style.display = 'block';
        } else {
            addressBox.style.display = 'none';
        }

        // Logic for Emergency
        let total = basePrice;
        if (emergencyToggle.checked) {
            total += 100;
            emergencyContainer.style.background = '#fee2e2';
            emergencyContainer.style.borderColor = '#ef4444';
        } else {
            emergencyContainer.style.background = '#fef2f2';
            emergencyContainer.style.borderColor = '#fca5a5';
        }

        priceDisplay.innerText = '₵' + total;
    }
    window.onload = calculateCost;
</script>

<?php include 'includes/footer.php'; ?>