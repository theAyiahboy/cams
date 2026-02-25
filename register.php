<?php
session_start();
include 'includes/db_connect.php';

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = htmlspecialchars(trim($_POST['full_name']));
    $contact = htmlspecialchars(trim($_POST['contact']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Basic Validation
    if (empty($fullName) || empty($contact) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            // 2. Check if the phone number is already registered
            $stmt = $pdo->prepare("SELECT id FROM users WHERE contact = ?");
            $stmt->execute([$contact]);
            if ($stmt->fetch()) {
                $error = "This phone number is already registered. Please log in.";
            } else {
                // 3. Hash the password securely and Insert
                $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $pdo->prepare("INSERT INTO users (full_name, contact, password, role) VALUES (?, ?, ?, 'patient')");
                
                if ($insertStmt->execute([$fullName, $contact, $hashedPwd])) {
                    $success = "Account created successfully! You can now log in.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Registration - SwiftCare</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .register-card { background: white; padding: 2.5rem; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 450px; }
        .register-card h2 { margin-top: 0; color: #0f172a; text-align: center; font-weight: 800; }
        .register-card p { text-align: center; color: #64748b; margin-bottom: 2rem; font-size: 0.9rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 5px; color: #475569; font-size: 0.85rem; }
        .form-group input { width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; outline: none; transition: border-color 0.3s; }
        .form-group input:focus { border-color: #0061f2; }
        .btn-register { width: 100%; padding: 0.8rem; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1rem; transition: background 0.3s; margin-top: 10px; }
        .btn-register:hover { background: #059669; }
        .alert-error { background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 8px; margin-bottom: 1rem; font-size: 0.85rem; text-align: center; border: 1px solid #f87171; }
        .alert-success { background: #d1fae5; color: #047857; padding: 10px; border-radius: 8px; margin-bottom: 1rem; font-size: 0.85rem; text-align: center; border: 1px solid #34d399; }
        .login-link { display: block; text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: #64748b; text-decoration: none; }
        .login-link span { color: #0061f2; font-weight: bold; }
    </style>
</head>
<body>

<div class="register-card">
    <h2>Join SwiftCare</h2>
    <p>Create your secure patient portal account</p>

    <?php if ($error): ?> <div class="alert-error"><?= $error ?></div> <?php endif; ?>
    <?php if ($success): ?> <div class="alert-success"><?= $success ?></div> <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="E.g., John Doe" required>
        </div>
        <div class="form-group">
            <label>Phone Number (This will be your username)</label>
            <input type="text" name="contact" placeholder="0541234567" required>
        </div>
        <div class="form-group">
            <label>Create Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-register">Create Account</button>
    </form>
    
    <a href="login.php" class="login-link">Already have an account? <span>Log In here</span></a>
</div>

</body>
</html>