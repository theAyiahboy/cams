<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php'; // Using our smart header!

$error = '';

// If already logged in, route them away from the login page
if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] == 'admin') header("Location: index.php");
    elseif ($_SESSION['user_role'] == 'doctor') header("Location: doctor_portal.php");
    elseif ($_SESSION['user_role'] == 'patient') header("Location: patient_portal.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_id = trim($_POST['login_id']); // This captures either Email OR Username/Phone
    $password = $_POST['password'];

    if (empty($login_id) || empty($password)) {
        $error = "Please enter your email and password.";
    } else {
        try {
            // THE FIX: Check BOTH email and contact columns 
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR contact = ?");
            $stmt->execute([$login_id, $login_id]);
            $user = $stmt->fetch();

            // Verify the user exists AND the password matches the secure hash
            if ($user && password_verify($password, $user['password'])) {
                
                // Set the Session Variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_contact'] = $user['contact'];
                $_SESSION['user_email'] = $user['email'] ?? ''; // Safely add email to session

                // Route them to their specific dashboard
                switch ($user['role']) {
                    case 'admin':
                        header("Location: index.php");
                        break;
                    case 'doctor':
                        header("Location: doctor_portal.php");
                        break;
                    case 'patient':
                        header("Location: patient_portal.php");
                        break;
                }
                exit();
            } else {
                $error = "Invalid email or password. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "System Error: " . $e->getMessage();
        }
    }
}
?>

<style>
    .login-wrapper {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .login-card {
        background: white;
        padding: 3rem;
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        width: 100%;
        max-width: 450px;
        border: 1px solid #f1f5f9;
        text-align: center;
    }

    .login-icon {
        width: 70px;
        height: 70px;
        background: #eff6ff;
        color: var(--primary);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 1.5rem;
        box-shadow: inset 0 0 0 1px #bfdbfe;
    }

    .login-card h2 { margin: 0 0 10px; color: var(--dark); font-size: 2rem; font-weight: 800; }
    .login-card p { color: #64748b; margin-bottom: 2.5rem; font-size: 1rem; }

    .form-group { margin-bottom: 1.5rem; text-align: left; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 8px; color: var(--dark); font-size: 0.95rem; }
    .form-group input { width: 100%; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 12px; box-sizing: border-box; font-size: 1rem; transition: all 0.3s; background: #f8fafc; }
    .form-group input:focus { border-color: var(--primary); background: white; outline: none; box-shadow: 0 0 0 4px rgba(0,97,242,0.1); }

    .btn-login { width: 100%; padding: 1.2rem; background: var(--primary); color: white; border: none; border-radius: 12px; font-weight: 800; font-size: 1.1rem; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(0,97,242,0.2); margin-top: 1rem; }
    .btn-login:hover { background: #004ecc; transform: translateY(-3px); box-shadow: 0 15px 25px rgba(0,97,242,0.3); }

    .alert-error { background: #fef2f2; color: #b91c1c; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; border-left: 5px solid #ef4444; font-weight: 600; text-align: left;}
    
    .register-link { display: block; margin-top: 2rem; color: #64748b; text-decoration: none; font-weight: 600; }
    .register-link span { color: var(--primary); }
    .register-link:hover span { text-decoration: underline; }
</style>

<div class="guest-wrapper">
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-icon">🔐</div>
            <h2>Welcome Back</h2>
            <p>Enter your credentials to access your portal.</p>

            <?php if ($error): ?>
                <div class="alert-error">⚠️ <?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label>Email Address or Username</label>
                    <input type="text" name="login_id" placeholder="patient@example.com or staff ID" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label>Secure Password</label>
                    <input type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn-login">Secure Login &rarr;</button>
            </form>

            <a href="book.php" class="register-link">New to SwiftCare? <br><span>Book a visit to create an account</span></a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>