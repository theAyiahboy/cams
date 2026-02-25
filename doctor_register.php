<?php
session_start();
include 'includes/db_connect.php';
include 'includes/config.php'; // Pulls in ARKESEL_API_KEY
include 'includes/header.php';

$successMsg = '';
$errorMsg = '';

// Process the form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = htmlspecialchars(trim($_POST['first_name']));
    $surname = htmlspecialchars(trim($_POST['surname']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $email = htmlspecialchars(trim($_POST['email']));
    $specialty = htmlspecialchars(trim($_POST['specialty']));
    $license_number = htmlspecialchars(trim($_POST['license_number']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $fullName = trim($firstName . ' ' . $surname);

    try {
        // 1. Password & Basic Validation
        if (empty($firstName) || empty($surname) || empty($password) || empty($email) || empty($license_number)) {
            throw new Exception("Please fill in all required fields.");
        }
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match.");
        }
        if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
            throw new Exception("Password must be 8+ chars, with 1 uppercase and 1 number.");
        }
        
        // 2. Check if Email/Phone already exists
        $checkUser = $pdo->prepare("SELECT id FROM users WHERE contact = ? OR email = ?");
        $checkUser->execute([$phone, $email]);
        if ($checkUser->fetch()) {
            throw new Exception("An account with this email or phone number already exists.");
        }

        // 3. SECURE FILE UPLOAD HANDLING (CV / License Proof)
        if (!isset($_FILES['cv_file']) || $_FILES['cv_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Please upload your CV / Medical License proof.");
        }

        $fileTmpPath = $_FILES['cv_file']['tmp_name'];
        $fileName = $_FILES['cv_file']['name'];
        $fileSize = $_FILES['cv_file']['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Restrict file types and size (Max 5MB)
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Invalid file format. Only PDF, DOC, and DOCX are allowed.");
        }
        if ($fileSize > 5242880) { // 5MB in bytes
            throw new Exception("File is too large. Maximum size is 5MB.");
        }

        // Create a unique, secure file name and save it
        $newFileName = "MD_" . time() . "_" . bin2hex(random_bytes(5)) . "." . $fileExtension;
        $uploadDir = 'uploads/';
        // Ensure directory exists
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $destPath = $uploadDir . $newFileName;
        if (!move_uploaded_file($fileTmpPath, $destPath)) {
            throw new Exception("Error saving your uploaded file. Check directory permissions.");
        }

        // 4. DATABASE TRANSACTION
        $pdo->beginTransaction();

        // A. Insert into users with a special 'pending_doctor' role
        $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
        $userStmt = $pdo->prepare("INSERT INTO users (full_name, contact, email, password, role) VALUES (?, ?, ?, ?, 'pending_doctor')");
        $userStmt->execute([$fullName, $phone, $email, $hashedPwd]);
        $newUserId = $pdo->lastInsertId();

        // B. Insert into doctor_applications
        $appStmt = $pdo->prepare("INSERT INTO doctor_applications (user_id, specialty, license_number, cv_file_path) VALUES (?, ?, ?, ?)");
        $appStmt->execute([$newUserId, $specialty, $license_number, $destPath]);

        $pdo->commit();
        $successMsg = "Application submitted successfully! Our Admin team will review your credentials.";

        // 5. --- SEND APPLICATION RECEIVED SMS ---
        if (defined('ARKESEL_API_KEY') && !empty(ARKESEL_API_KEY)) {
            $smsMessage = "Hello Dr. $surname, your application to SwiftCare has been received. Our Admin team is reviewing your credentials. We will notify you upon approval.";
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://sms.arkesel.com/api/v2/sms/send",
                CURLOPT_HTTPHEADER => [
                    "api-key: " . ARKESEL_API_KEY, 
                    "Content-Type: application/json"
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode([
                    'sender' => 'SwiftCare', // Max 11 characters
                    'message' => $smsMessage,
                    'recipients' => [$phone]
                ]),
                CURLOPT_SSL_VERIFYPEER => false // Prevents local XAMPP SSL errors
            ]);
            curl_exec($curl);
            curl_close($curl);
        }
        // ----------------------------------------

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $errorMsg = $e->getMessage();
    }
}
?>

<style>
    .onboarding-hero { background: linear-gradient(135deg, #1e293b 0%, var(--dark) 100%); color: white; text-align: center; padding: 4rem 1rem; border-radius: 24px; margin-bottom: -50px; }
    .form-container { max-width: 800px; margin: 0 auto 3rem; background: white; padding: 3rem; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; position: relative; z-index: 10; }
    .form-group { margin-bottom: 1.5rem; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 8px; color: var(--dark); font-size: 0.95rem; }
    .form-group input, .form-group select { width: 100%; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: 0.3s; background: #f8fafc; }
    .form-group input:focus, .form-group select:focus { border-color: var(--primary); background: white; outline: none; box-shadow: 0 0 0 4px rgba(0,97,242,0.1); }
    
    .file-drop-area { border: 2px dashed #cbd5e1; padding: 2rem; text-align: center; border-radius: 12px; background: #f8fafc; transition: 0.3s; cursor: pointer; }
    .file-drop-area:hover { border-color: var(--primary); background: #eff6ff; }
    .file-drop-area input[type="file"] { display: none; }
    
    .btn-submit { width: 100%; padding: 1.2rem; background: var(--dark); color: white; border: none; border-radius: 12px; font-weight: 800; font-size: 1.1rem; cursor: pointer; transition: 0.3s; margin-top: 1rem; }
    .btn-submit:hover { background: #0f172a; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
    .section-label { font-size: 1.2rem; font-weight: 800; color: var(--dark); border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin: 2rem 0 1.5rem; }
</style>

<div class="guest-wrapper" style="max-width: 1000px; margin: 0 auto; width: 100%;">
    <div class="onboarding-hero">
        <h1 style="margin: 0 0 10px; font-size: 2.8rem; font-weight: 800;">Join SwiftCare Network</h1>
        <p style="margin: 0; font-size: 1.1rem; opacity: 0.9;">Apply to become a verified medical specialist on Ghana's top digital clinic platform.</p>
    </div>

    <div class="form-container">
        <?php if ($successMsg): ?>
            <div style="background: #d1fae5; color: #047857; padding: 2.5rem; border-radius: 16px; text-align: center; border: 2px solid #34d399;">
                <div style="font-size: 4rem; margin-bottom: 15px;">📜</div>
                <h2 style="margin: 0 0 10px 0; font-weight: 800;">Application Received</h2>
                <p><?= $successMsg ?></p>
                <p style="font-size: 0.9rem;">You will receive an SMS and email once your medical license is verified.</p>
                <a href="index.php" style="display:inline-block; margin-top:15px; padding: 10px 20px; background: #047857; color:white; text-decoration:none; border-radius: 8px;">Return to Home</a>
            </div>
        <?php else: ?>

            <?php if ($errorMsg): ?>
                <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; border-left: 5px solid #ef4444; font-weight: 600;">⚠️ <?= $errorMsg ?></div>
            <?php endif; ?>

            <form action="doctor_register.php" method="POST" enctype="multipart/form-data" id="docForm">
                
                <div class="section-label">1. Personal & Contact Info</div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
                    <div class="form-group"><label>Surname</label><input type="text" name="surname" required></div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div class="form-group"><label>Phone Number</label><input type="text" name="phone" required></div>
                    <div class="form-group"><label>Professional Email</label><input type="email" name="email" required></div>
                </div>

                <div class="section-label">2. Credentials & Verification</div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div class="form-group">
                        <label>Primary Specialty</label>
                        <select name="specialty" required>
                            <option value="">-- Select Field --</option>
                            <option value="General Practice">General Practice</option>
                            <option value="Cardiologist">Cardiologist</option>
                            <option value="Dermatologist">Dermatologist</option>
                            <option value="Pediatrician">Pediatrician</option>
                            <option value="Neurologist">Neurologist</option>
                            <option value="Dentist">Dentist</option>
                            <option value="Optometrist">Optometrist</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>GDC Medical License Number</label>
                        <input type="text" name="license_number" placeholder="e.g. MDC/GHA/2026/123" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Upload CV & License Proof (PDF/DOCX)</label>
                    <label class="file-drop-area" id="fileDropLabel">
                        <span style="font-size: 2rem; display: block; margin-bottom: 10px;">📄</span>
                        <span id="fileText">Click to browse or drag file here</span>
                        <input type="file" name="cv_file" id="cv_file" accept=".pdf,.doc,.docx" required>
                    </label>
                </div>

                <div class="section-label">3. Secure Account Setup</div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div class="form-group">
                        <label>Create Password</label>
                        <input type="password" name="password" id="password" required>
                        <span style="font-size: 0.8rem; color: #64748b;">Min 8 chars, 1 uppercase, 1 number</span>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirmPassword" required>
                        <span id="passwordMatchError" style="color: #ef4444; font-size: 0.85rem; font-weight: bold; display: none;">Passwords do not match!</span>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">Submit Application for Review</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    // Show selected file name
    document.getElementById('cv_file').addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : "Click to browse or drag file here";
        document.getElementById('fileText').innerHTML = "<strong>Selected:</strong> " + fileName;
        document.getElementById('fileDropLabel').style.borderColor = "#10b981";
        document.getElementById('fileDropLabel').style.background = "#d1fae5";
    });

    // Password Match Validation
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    const matchError = document.getElementById('passwordMatchError');
    const submitBtn = document.getElementById('submitBtn');

    function validatePassword() {
        if (confirmPassword.value !== '' && password.value !== confirmPassword.value) {
            matchError.style.display = 'block';
            submitBtn.style.opacity = '0.5';
            submitBtn.style.pointerEvents = 'none';
        } else {
            matchError.style.display = 'none';
            submitBtn.style.opacity = '1';
            submitBtn.style.pointerEvents = 'auto';
        }
    }
    if(password && confirmPassword){
        password.addEventListener('keyup', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
    }
</script>

<?php include 'includes/footer.php'; ?>