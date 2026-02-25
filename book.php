<?php
session_start();
include 'includes/db_connect.php';
include 'includes/config.php'; // Pulls in your ARKESEL_API_KEY
include 'includes/header.php';

$isLoggedIn = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'patient';
$defaultName = $isLoggedIn ? $_SESSION['user_name'] : '';
$defaultPhone = $isLoggedIn ? $_SESSION['user_contact'] : '';

$successMsg = '';
$errorMsg = '';

// Fetch available specialties dynamically from the doctors table
try {
    $specStmt = $pdo->query("SELECT DISTINCT specialty FROM doctors WHERE specialty IS NOT NULL");
    $specialties = $specStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("System Error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Capture Form Data
    $firstName = htmlspecialchars(trim($_POST['first_name'] ?? ''));
    $surname = htmlspecialchars(trim($_POST['surname'] ?? ''));
    $phone = htmlspecialchars(trim($_POST['patient_phone']));
    $specialty = $_POST['specialty'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $address = htmlspecialchars(trim($_POST['home_address']));
    $tier = $_POST['tier'];
    $service_type = $_POST['service_type']; 
    $is_emergency = isset($_POST['is_emergency']) ? 1 : 0;
    
    // Combine names for the appointment record
    $fullName = $isLoggedIn ? $defaultName : trim($firstName . ' ' . $surname);

    try {
        $pdo->beginTransaction();

        // 2. AUTO-ACCOUNT CREATION (If not logged in)
        if (!$isLoggedIn) {
            $email = htmlspecialchars(trim($_POST['email']));
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $consent = isset($_POST['data_consent']);

            // Basic Validation
            if (!$consent) throw new Exception("You must agree to the data policy to create an account.");
            if (empty($firstName) || empty($surname) || empty($password)) throw new Exception("Please fill in all account creation fields.");
            if ($password !== $confirm_password) throw new Exception("Passwords do not match. Please try again.");

            // --- STRICT PASSWORD SECURITY CHECKS ---
            if (strlen($password) < 8) {
                throw new Exception("Password must be at least 8 characters long.");
            }
            if (!preg_match("/[A-Z]/", $password)) {
                throw new Exception("Password must contain at least one uppercase letter.");
            }
            if (!preg_match("/[0-9]/", $password)) {
                throw new Exception("Password must contain at least one number.");
            }
            // Prevent using name in password
            if (stripos($password, $firstName) !== false || stripos($password, $surname) !== false) {
                throw new Exception("For security reasons, your password cannot contain your name.");
            }
            // ---------------------------------------

            // Check if user already exists
            $checkUser = $pdo->prepare("SELECT id FROM users WHERE contact = ? OR email = ?");
            $checkUser->execute([$phone, $email]);
            if ($checkUser->fetch()) throw new Exception("An account with this phone or email already exists. Please log in.");

            // Create User
            $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
            $userStmt = $pdo->prepare("INSERT INTO users (full_name, contact, email, password, role) VALUES (?, ?, ?, ?, 'patient')");
            $userStmt->execute([$fullName, $phone, $email, $hashedPwd]);
            
            // Auto-Login the new user
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_role'] = 'patient';
            $_SESSION['user_contact'] = $phone;
            $isLoggedIn = true; 
        }

        // 3. AUTO-ASSIGN DOCTOR BY SPECIALTY
        $docStmt = $pdo->prepare("SELECT id FROM doctors WHERE specialty = ? LIMIT 1");
        $docStmt->execute([$specialty]);
        $assignedDoc = $docStmt->fetch();
        
        if (!$assignedDoc) throw new Exception("No $specialty is currently available. Please select another service.");
        $doctor_id = $assignedDoc['id'];

        // 4. BOOK THE APPOINTMENT
        $query = "INSERT INTO appointments 
                  (patient_name, patient_phone, doctor_id, service_type, appointment_date, appointment_time, tier, home_address, is_emergency) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$fullName, $phone, $doctor_id, $service_type, $date, $time, $tier, $address, $is_emergency]);
        
        $pdo->commit();
        $successMsg = "Appointment secured! You have been assigned to our lead $specialty.";

        // 5. --- REAL SMS API INTEGRATION (ARKESEL) ---
        if (defined('ARKESEL_API_KEY') && !empty(ARKESEL_API_KEY)) {
            $formattedDate = date("M d, Y", strtotime($date));
            $formattedTime = date("h:i A", strtotime($time));
            
            // Extract just the first name for a friendlier SMS
            $smsFirstName = $isLoggedIn ? explode(' ', $defaultName)[0] : $firstName;
            
            // Format the message: Removed "SwiftCare:" and used first name only
            $smsMessage = "Hello $smsFirstName, your $specialty consultation is confirmed for $formattedDate at $formattedTime. Log in to your portal for details.";

            $curl = curl_init();
            $smsData = [
                'sender' => 'SwiftCare', // Must be exactly 11 characters or fewer
                'message' => $smsMessage,
                'recipients' => [$phone]
            ];

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://sms.arkesel.com/api/v2/sms/send",
                CURLOPT_HTTPHEADER => [
                    "api-key: " . ARKESEL_API_KEY, 
                    "Content-Type: application/json"
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($smsData),
                CURLOPT_SSL_VERIFYPEER => false // Prevents XAMPP local SSL errors
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
        }
        // ---------------------------------------------

    } catch (Exception $e) {
        $pdo->rollBack();
        $errorMsg = $e->getMessage();
    }
}
?>

<style>
    .booking-hero { background: linear-gradient(135deg, var(--primary) 0%, #00cfd5 100%); color: white; text-align: center; padding: 4rem 1rem 6rem; border-radius: 24px; margin-bottom: 3rem; }
    .booking-hero h1 { margin: 0 0 10px 0; font-size: 2.8rem; font-weight: 800; }
    .form-container { max-width: 800px; margin: -80px auto 3rem; background: white; padding: 3rem; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid #f1f5f9; }
    .form-group { margin-bottom: 1.5rem; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 8px; color: var(--dark); font-size: 0.95rem; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 12px; box-sizing: border-box; font-size: 1rem; transition: all 0.3s; background: #f8fafc; }
    .form-group input:focus, .form-group select:focus { border-color: var(--primary); background: white; outline: none; box-shadow: 0 0 0 4px rgba(0,97,242,0.1); }
    .btn-submit { width: 100%; padding: 1.2rem; background: #10b981; color: white; border: none; border-radius: 12px; font-weight: 800; font-size: 1.2rem; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2); margin-top: 1rem; }
    .btn-submit:hover { background: #059669; transform: translateY(-3px); }
    .alert-success { background: #d1fae5; color: #047857; padding: 2.5rem; border-radius: 16px; text-align: center; border: 2px solid #34d399; margin-bottom: 2rem;}
    .alert-error { background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; border-left: 5px solid #ef4444; font-weight: 600; }
    .price-display { background: var(--dark); padding: 25px; border-radius: 16px; margin-bottom: 1.5rem; color: white; display: flex; justify-content: space-between; align-items: center; }
    .price-display h2 { margin: 0; font-size: 3rem; color: #38bdf8; font-weight: 800; }
    .section-label { font-size: 1.2rem; font-weight: 800; color: var(--primary); border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin: 2rem 0 1.5rem; }
    .password-hint { font-size: 0.8rem; color: #64748b; margin-top: 5px; display: block; }
</style>

<div class="guest-wrapper" style="max-width: 1000px; margin: 0 auto; width: 100%;">
    <div class="booking-hero">
        <h1>Secure Your Appointment</h1>
        <p>Select your required medical service and we will assign our best specialist.</p>
    </div>

    <div class="form-container">
        <?php if ($successMsg): ?>
            <div class="alert-success">
                <div style="font-size: 4rem; margin-bottom: 15px;">✅</div>
                <h2 style="margin: 0 0 10px 0; font-weight: 800;"><?= $successMsg ?></h2>
                <p>Your Patient Portal account has been created and your SMS confirmation has been sent.</p>
                <br>
                <a href="patient_portal.php" style="background: #047857; color: white; padding: 12px 30px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block;">Enter Patient Portal</a>
            </div>
        <?php else: ?>

            <?php if ($errorMsg): ?>
                <div class="alert-error">⚠️ <?= $errorMsg ?></div>
            <?php endif; ?>

            <form action="book.php" method="POST" id="bookingForm">
                
                <div class="section-label">1. Patient Details & Account Setup</div>
                
                <?php if ($isLoggedIn): ?>
                    <div style="background: #e0f2fe; padding: 15px; border-radius: 10px; margin-bottom: 20px; color: #0369a1; font-weight: bold;">
                        👋 Welcome back, <?= htmlspecialchars($defaultName) ?>. You are booking under your verified account.
                    </div>
                    <input type="hidden" name="patient_phone" value="<?= htmlspecialchars($defaultPhone) ?>">
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" id="firstName" required placeholder="E.g., Kwame">
                        </div>
                        <div class="form-group">
                            <label>Surname *</label>
                            <input type="text" name="surname" id="surname" required placeholder="E.g., Mensah">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div class="form-group">
                            <label>Phone Number *</label>
                            <input type="text" name="patient_phone" required placeholder="0541234567">
                        </div>
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" required placeholder="kwame@example.com">
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div class="form-group">
                            <label>Create Secure Password *</label>
                            <input type="password" name="password" id="password" required placeholder="Enter password">
                            <span class="password-hint">Min 8 chars, 1 uppercase, 1 number. Do not use your name.</span>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password *</label>
                            <input type="password" name="confirm_password" id="confirmPassword" required placeholder="Re-enter password">
                            <span id="passwordMatchError" style="color: #ef4444; font-size: 0.85rem; font-weight: bold; display: none;">Passwords do not match!</span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="section-label">2. Medical Requirements</div>

                <div class="form-group">
                    <label>Required Clinical Service *</label>
                    <select name="specialty" required>
                        <option value="">-- What kind of specialist do you need? --</option>
                        <?php foreach ($specialties as $spec): ?>
                            <option value="<?= htmlspecialchars($spec) ?>"><?= htmlspecialchars($spec) ?> Consultation</option>
                        <?php endforeach; ?>
                        <?php if(empty($specialties)) echo '<option value="General Practice">General Practice</option>'; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Select Care Package *</label>
                    <select name="tier" id="tierSelect" required onchange="calculateCost()">
                        <option value="Standard" data-price="150" data-service="In-Clinic">Standard Tier - In-Clinic Visit (₵150)</option>
                        <option value="VVIP" data-price="450" data-service="Home-Service">VVIP Tier - Home Service Delivery (₵450)</option>
                    </select>
                    <input type="hidden" name="service_type" id="serviceTypeInput" value="In-Clinic">
                </div>

                <div class="form-group" style="background: #fef2f2; padding: 25px; border-radius: 16px; border: 2px solid #fca5a5; transition: 0.3s;" id="emergencyContainer">
                    <label style="display: flex; align-items: center; gap: 15px; cursor: pointer; color: #b91c1c; font-weight: 800; margin: 0; font-size: 1.2rem;">
                        <input type="checkbox" name="is_emergency" id="emergencyToggle" value="1" style="width: 25px; height: 25px; accent-color: #dc2626;" onchange="calculateCost()">
                        🚨 Mark as Critical Emergency
                    </label>
                    <p style="margin: 10px 0 0 40px; font-size: 0.95rem; color: #991b1b;">Applies a <strong>₵100 Priority Dispatch Fee</strong> to bypass the standard queue.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div class="form-group">
                        <label>Preferred Date *</label>
                        <input type="date" name="appointment_date" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Preferred Time *</label>
                        <input type="time" name="appointment_time" required>
                    </div>
                </div>

                <div class="form-group" id="addressBox" style="display: none; background: #f0fdf4; padding: 25px; border-radius: 16px; border: 2px solid #86efac;">
                    <label style="color: #166534; font-size: 1.1rem;">📍 VVIP Delivery Address *</label>
                    <textarea name="home_address" id="home_address" rows="3" placeholder="Enter GPS Address or clear directions..." style="border-color: #86efac;"></textarea>
                </div>

                <div class="price-display">
                    <span>Estimated Total<br><small style="color: #64748b; font-weight: normal; text-transform: none;">Payable at consultation</small></span>
                    <h2 id="totalPriceDisplay">₵150</h2>
                </div>

                <?php if (!$isLoggedIn): ?>
                    <div class="form-group" style="background: #f1f5f9; padding: 15px; border-radius: 10px;">
                        <label style="display: flex; align-items: start; gap: 10px; cursor: pointer; font-weight: 500; font-size: 0.9rem; color: #475569;">
                            <input type="checkbox" name="data_consent" required style="width: 20px; height: 20px; margin-top: 3px;">
                            I agree to share my medical data with SwiftCare and consent to the automatic creation of my secure Patient Portal account.
                        </label>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn-submit" id="submitBtn">Confirm Booking & Create Account</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    // 1. Live Price Calculator
    function calculateCost() {
        const tierSelect = document.getElementById('tierSelect');
        const emergencyToggle = document.getElementById('emergencyToggle');
        const emergencyContainer = document.getElementById('emergencyContainer');
        const addressBox = document.getElementById('addressBox');
        const homeAddressInput = document.getElementById('home_address');
        const serviceTypeInput = document.getElementById('serviceTypeInput');
        const priceDisplay = document.getElementById('totalPriceDisplay');

        let selectedOption = tierSelect.options[tierSelect.selectedIndex];
        let basePrice = parseInt(selectedOption.getAttribute('data-price'));
        serviceTypeInput.value = selectedOption.getAttribute('data-service');

        if (serviceTypeInput.value === 'Home-Service') {
            addressBox.style.display = 'block';
            homeAddressInput.setAttribute('required', 'required');
        } else {
            addressBox.style.display = 'none';
            homeAddressInput.removeAttribute('required');
        }

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

    // 2. Real-time Password Match Checking
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    const matchError = document.getElementById('passwordMatchError');
    const submitBtn = document.getElementById('submitBtn');

    if (password && confirmPassword) {
        function validatePassword() {
            if (confirmPassword.value !== '' && password.value !== confirmPassword.value) {
                matchError.style.display = 'block';
                submitBtn.style.opacity = '0.5';
                submitBtn.style.pointerEvents = 'none'; // Prevent clicking
            } else {
                matchError.style.display = 'none';
                submitBtn.style.opacity = '1';
                submitBtn.style.pointerEvents = 'auto'; // Allow clicking
            }
        }
        password.addEventListener('keyup', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
    }

    window.onload = calculateCost;
</script>

<?php include 'includes/footer.php'; ?>