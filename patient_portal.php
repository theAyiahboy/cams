<?php
session_start();
include 'includes/db_connect.php';

// 1. SECURITY CHECK: Kick out anyone who is NOT a logged-in patient
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// 2. FETCH PATIENT'S APPOINTMENTS
$phone = $_SESSION['user_contact'];
$patientName = $_SESSION['user_name'];

try {
    $query = "SELECT a.*, d.doc_name 
              FROM appointments a 
              JOIN doctors d ON a.doctor_id = d.id 
              WHERE a.patient_phone = ? 
              ORDER BY a.appointment_date ASC, a.appointment_time ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$phone]);
    $appointments = $stmt->fetchAll();

    $upcoming = [];
    $past = [];
    $today = date('Y-m-d');

    foreach ($appointments as $app) {
        if ($app['appointment_date'] >= $today) {
            $upcoming[] = $app;
        } else {
            $past[] = $app;
        }
    }

} catch (PDOException $e) {
    die("Error fetching your records: " . $e->getMessage());
}

include 'includes/header.php';
?>

<style>
    /* Patient Dashboard Layout */
    .portal-container {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 2rem;
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 20px;
    }

    /* Portal Sidebar */
    .portal-sidebar {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
        overflow: hidden;
        align-self: start;
        position: sticky;
        top: 100px;
    }
    
    .patient-profile-brief {
        background: linear-gradient(135deg, var(--primary) 0%, #00cfd5 100%);
        color: white;
        padding: 2rem 1.5rem;
        text-align: center;
    }
    
    .avatar-circle {
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto 15px;
        border: 2px solid white;
    }

    .portal-menu {
        list-style: none;
        padding: 1rem 0;
        margin: 0;
    }
    
    .portal-menu li a {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 20px;
        color: #475569;
        text-decoration: none;
        font-weight: 600;
        transition: 0.3s;
        border-left: 3px solid transparent;
    }
    
    .portal-menu li a:hover { background: #f8fafc; color: var(--primary); }
    .portal-menu li a.active { background: #eff6ff; color: var(--primary); border-left-color: var(--primary); }
    
    /* Welcome Card Styling */
    .welcome-card { 
        background: white; 
        padding: 2rem; 
        border-radius: 20px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.03); 
        border: 1px solid #f1f5f9; 
        margin-bottom: 2rem; 
        border-left: 6px solid #10b981; 
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        gap: 20px;
    }

    .section-title { color: var(--dark); font-size: 1.4rem; font-weight: 800; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; }
    
    /* FIXED APPOINTMENT CARD */
    .appointment-card { 
        background: white; 
        border: 1px solid #e2e8f0; 
        padding: 1.5rem; 
        border-radius: 16px; 
        margin-bottom: 1rem; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        box-shadow: 0 4px 6px rgba(0,0,0,0.02); 
        transition: 0.3s;
        gap: 20px; /* Space between text and button */
    }
    .appointment-card:hover { border-color: #bfdbfe; box-shadow: 0 10px 20px rgba(0,97,242,0.05); transform: translateY(-2px); }
    
    .app-info-side { flex-grow: 1; } /* Makes text take up available space */
    .app-date { font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 5px;}
    .app-details { color: #64748b; font-size: 0.95rem; line-height: 1.4; }
    
    /* FIXED PRINT BUTTON */
    .btn-print { 
        background: #eff6ff; 
        color: var(--primary); 
        padding: 12px 20px; 
        text-decoration: none; 
        border-radius: 10px; 
        font-weight: 700; 
        transition: 0.3s; 
        white-space: nowrap; /* PREVENTS OVERLAP/WRAPPING */
        border: 1px solid #bfdbfe;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-print:hover { background: var(--primary); color: white; border-color: var(--primary); }
    
    .history-table { width: 100%; border-collapse: collapse; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; }
    .history-table th, .history-table td { padding: 1.2rem; text-align: left; border-bottom: 1px solid #f1f5f9; }
    .history-table th { background: #f8fafc; color: #64748b; text-transform: uppercase; font-size: 0.85rem; font-weight: 800; }
    .badge { padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; background: #f1f5f9; color: #475569; }

    @media (max-width: 992px) {
        .portal-container { grid-template-columns: 1fr; }
        .portal-sidebar { position: relative; top: 0; margin-bottom: 2rem; }
    }

    @media (max-width: 600px) {
        .welcome-card { flex-direction: column; text-align: center; }
        .appointment-card { flex-direction: column; text-align: center; padding: 1.5rem 1rem; }
        .btn-print { width: 100%; justify-content: center; }
    }
</style>

<div class="portal-container">
    
    <aside class="portal-sidebar">
        <div class="patient-profile-brief">
            <div class="avatar-circle">👤</div>
            <h3 style="margin: 0 0 5px; font-size: 1.2rem; font-weight: 800;"><?= htmlspecialchars($patientName) ?></h3>
            <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Patient ID: #SC-<?= str_pad($_SESSION['user_id'], 4, '0', STR_PAD_LEFT) ?></p>
        </div>
        <ul class="portal-menu">
            <li><a href="patient_portal.php" class="active"><span class="menu-icon">📊</span> Overview Dashboard</a></li>
            <li><a href="book.php"><span class="menu-icon">➕</span> Book Appointment</a></li>
            <li><a href="#" onclick="alert('Profile editing will be available in V2.0'); return false;"><span class="menu-icon">🧑‍⚕️</span> My Health Profile</a></li>
            <li><a href="#" onclick="alert('No pending prescriptions found.'); return false;"><span class="menu-icon">💊</span> Prescriptions</a></li>
            <li><a href="#" onclick="alert('Laboratory systems syncing...'); return false;"><span class="menu-icon">🧪</span> Lab Results <span class="nav-badge" style="background:var(--primary)">New</span></a></li>
            <li style="border-top: 1px solid #f1f5f9; margin-top: 10px; padding-top: 10px;">
                <a href="logout.php" style="color: #ef4444;"><span class="menu-icon">🚪</span> Secure Logout</a>
            </li>
        </ul>
    </aside>

    <main>
        <div class="welcome-card">
            <div>
                <h1 style="margin: 0 0 5px; color: #0f172a; font-size: 1.8rem; font-weight: 800;">Welcome back, <?= htmlspecialchars($patientName) ?></h1>
                <p style="margin: 0; color: #64748b;">Manage your appointments and stay on top of your health.</p>
            </div>
            <a href="book.php" style="background: #10b981; color: white; padding: 12px 25px; border-radius: 8px; font-weight: 800; text-decoration: none; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); white-space: nowrap;">Book New Visit</a>
        </div>

        <h2 class="section-title">⏳ Upcoming Appointments</h2>
        <?php if (empty($upcoming)): ?>
            <div style="background: white; padding: 3rem; border-radius: 16px; text-align: center; border: 1px dashed #cbd5e1; margin-bottom: 3rem;">
                <div style="font-size: 3rem; margin-bottom: 10px;">📅</div>
                <h3 style="margin: 0 0 10px; color: var(--dark);">No upcoming visits</h3>
                <p style="color: #64748b; margin: 0;">You are all caught up!</p>
            </div>
        <?php else: ?>
            <div style="margin-bottom: 3rem;">
                <?php foreach ($upcoming as $app): ?>
                    <div class="appointment-card">
                        <div class="app-info-side">
                            <div class="app-date"><?= date("l, M j, Y", strtotime($app['appointment_date'])) ?></div>
                            <div style="font-weight: 700; color: var(--dark); margin-bottom: 4px;">🕒 Time: <?= date("h:i A", strtotime($app['appointment_time'])) ?></div>
                            <div class="app-details">
                                <strong>Doctor:</strong> Dr. <?= htmlspecialchars($app['doc_name']) ?> <br>
                                <strong>Service:</strong> <?= $app['service_type'] ?>
                                <?php if($app['is_emergency']): ?>
                                    <span style="color: #e53e3e; font-weight: bold; font-size: 0.75rem; background: #fee2e2; padding: 2px 6px; border-radius: 4px; margin-left: 5px;">🚨 EMERGENCY</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="app-action-side">
                            <a href="print_slip.php?id=<?= $app['id'] ?>" target="_blank" class="btn-print">
                                <span>🖨️</span> Print Slip
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2 class="section-title">📖 Medical History</h2>
        <?php if (empty($past)): ?>
            <p style="color: #64748b; padding: 1rem 0;">No past history found.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Attending Doctor</th>
                            <th>Service</th>
                            <th>Care Tier</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($past as $app): ?>
                            <tr>
                                <td style="font-weight: bold; color: var(--dark);"><?= date("M d, Y", strtotime($app['appointment_date'])) ?></td>
                                <td>Dr. <?= htmlspecialchars($app['doc_name']) ?></td>
                                <td><?= $app['service_type'] ?></td>
                                <td><span class="badge"><?= $app['tier'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

</div>

<?php include 'includes/footer.php'; ?>