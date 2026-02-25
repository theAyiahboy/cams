<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Logic: Show sidebar for Admin and Doctor, but keep Patient/Guest on the clean top-nav layout
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'guest';
$showSidebar = ($role == 'admin' || $role == 'doctor');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SwiftCare | Professional Clinic Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0061f2;
            --accent: #f4a100;
            --danger: #e53e3e;
            --dark: #1a202c;
            --light: #f8fafc;
            --sidebar-width: 260px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--light); 
            color: var(--dark); 
            margin: 0; 
            display: block; 
        }
        
        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: #1e293b;
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 2rem 1.5rem;
            box-sizing: border-box;
            z-index: 1001;
        }

        /* Top Header for Guests & Patients */
        .public-header {
            background: white;
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        /* Logos */
        .logo { font-size: 1.6rem; font-weight: 800; text-decoration: none; color: var(--dark); }
        .sidebar .logo { color: white; display: block; margin-bottom: 2.5rem; }
        .logo span { color: #38bdf8; }
        .public-header .logo span { color: var(--primary); }

        /* Navigation */
        .btn-nav { background: var(--primary); color: white !important; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 700; text-decoration: none; }
        nav ul { list-style: none; display: flex; gap: 2rem; margin: 0; padding: 0; }
        .sidebar nav ul { flex-direction: column; gap: 0.5rem; }
        
        .sidebar-label { color: #64748b; font-size: 0.75rem; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; margin: 1.5rem 0 0.5rem; }

        nav ul li a { text-decoration: none; color: #64748b; font-weight: 600; }
        .sidebar nav ul li a { color: #cbd5e1; display: block; padding: 10px 15px; border-radius: 8px; transition: 0.3s; }
        .sidebar nav ul li a:hover { color: white; background: rgba(255,255,255,0.05); }

        /* Main Content Adjustments */
        .main-content {
            padding: 2rem 5%;
            min-height: 85vh;
            box-sizing: border-box;
        }

        /* Layout Shifts based on Body Class */
        .has-sidebar .main-content {
            margin-left: var(--sidebar-width);
        }

        .no-sidebar .main-content {
            margin-left: 0;
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 100px; 
        }
    </style>
</head>

<body class="<?= $showSidebar ? 'has-sidebar' : 'no-sidebar' ?>">

<?php if ($showSidebar): ?>
    <aside class="sidebar">
        <a href="index.php" class="logo">Swift<span>Care</span></a>
        <nav>
            <ul>
                <?php if ($role === 'admin'): ?>
                    <div class="sidebar-label">Management</div>
                    <li><a href="admin_portal.php">📊 Command Center</a></li>
                    <li><a href="view.php">📋 Master Registry</a></li>
                    <li><a href="add.php">➕ Add Appointment</a></li>
                    
                <?php elseif ($role === 'doctor'): ?>
                    <div class="sidebar-label">Medical Tools</div>
                    <li><a href="doctor_portal.php">⚕️ Triage Queue</a></li>
                    <li><a href="view.php">📋 Patient History</a></li>
                <?php endif; ?>

                <li style="margin-top: 2rem; border-top: 1px solid #334155; padding-top: 1rem;">
                    <a href="logout.php" style="color: #fb7185;">🚪 Logout</a>
                </li>
            </ul>
        </nav>
    </aside>
<?php else: ?>
    <header class="public-header">
        <a href="index.php" class="logo">Swift<span>Care</span></a>
        <nav>
            <ul>
                <?php if ($role === 'patient'): ?>
                    <li><a href="patient_portal.php">Dashboard</a></li>
                    <li><a href="book.php" class="btn-nav">New Booking</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="book.php" class="btn-nav">Book Now</a></li>
                    <li><a href="login.php">Portal Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
<?php endif; ?>

<div class="main-content">