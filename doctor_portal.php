<?php
session_start();
include 'includes/db_connect.php';

// 1. SECURITY CHECK: Kick out anyone who is NOT a logged-in doctor
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctorId = $_SESSION['user_id'];
$doctorName = $_SESSION['user_name'];
$today = date('Y-m-d');
$actionMsg = '';

// 2. PROCESS ACTIONS (Marking an appointment as Completed)
if (isset($_GET['complete_id'])) {
    $completeId = intval($_GET['complete_id']);
    try {
        $updateStmt = $pdo->prepare("UPDATE appointments SET status = 'Completed' WHERE id = ? AND doctor_id = ?");
        $updateStmt->execute([$completeId, $doctorId]);
        $actionMsg = "Patient consultation marked as completed.";
    } catch (PDOException $e) {
        $actionMsg = "Error updating status: " . $e->getMessage();
    }
}

// 3. FETCH DATA FOR THE DOCTOR
try {
    // Fetch Today's Queue (Pending only, ordered by Emergency first, then time)
    $queueQuery = "SELECT * FROM appointments 
                   WHERE doctor_id = ? AND status = 'Pending' AND appointment_date = ? 
                   ORDER BY is_emergency DESC, appointment_time ASC";
    $queueStmt = $pdo->prepare($queueQuery);
    $queueStmt->execute([$doctorId, $today]);
    $todaysQueue = $queueStmt->fetchAll();

    // Fetch Upcoming/Other Pending Appointments
    $upcomingQuery = "SELECT * FROM appointments 
                      WHERE doctor_id = ? AND status = 'Pending' AND appointment_date > ? 
                      ORDER BY appointment_date ASC, appointment_time ASC LIMIT 10";
    $upcomingStmt = $pdo->prepare($upcomingQuery);
    $upcomingStmt->execute([$doctorId, $today]);
    $upcomingQueue = $upcomingStmt->fetchAll();

    // Quick Stats
    $totalToday = count($todaysQueue);
    $emergenciesToday = 0;
    foreach($todaysQueue as $q) { if($q['is_emergency']) $emergenciesToday++; }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

include 'includes/header.php';
?>

<style>
    .portal-container { display: grid; grid-template-columns: 260px 1fr; gap: 2rem; max-width: 1200px; margin: 2rem auto; padding: 0 20px; }
    
    /* Sidebar */
    .portal-sidebar { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; overflow: hidden; align-self: start; position: sticky; top: 100px; }
    .doc-profile-brief { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; padding: 2rem 1.5rem; text-align: center; }
    .avatar-circle { width: 80px; height: 80px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 15px; border: 2px solid #38bdf8; }
    
    .portal-menu { list-style: none; padding: 1rem 0; margin: 0; }
    .portal-menu li a { display: flex; align-items: center; gap: 15px; padding: 12px 20px; color: #475569; text-decoration: none; font-weight: 600; transition: 0.3s; border-left: 3px solid transparent; }
    .portal-menu li a:hover { background: #f8fafc; color: var(--primary); }
    .portal-menu li a.active { background: #eff6ff; color: var(--primary); border-left-color: var(--primary); }
    .menu-icon { font-size: 1.2rem; }

    /* Content Area */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-card { background: white; padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .stat-card h3 { margin: 0 0 5px; font-size: 2rem; color: var(--dark); }
    .stat-card p { margin: 0; color: #64748b; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; }

    .section-title { color: var(--dark); font-size: 1.4rem; font-weight: 800; margin-bottom: 1.5rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;}
    
    /* Tables */
    .queue-table { width: 100%; border-collapse: collapse; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin-bottom: 3rem; }
    .queue-table th, .queue-table td { padding: 1.2rem; text-align: left; border-bottom: 1px solid #f1f5f9; }
    .queue-table th { background: #f8fafc; color: #64748b; text-transform: uppercase; font-size: 0.85rem; font-weight: 800; }
    .queue-table tr:last-child td { border-bottom: none; }
    .queue-table tr:hover { background: #f8fafc; }

    .badge-emergency { background: #fee2e2; color: #dc2626; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; animation: pulse 2s infinite; }
    .badge-tier { background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; }
    .badge-vvip { background: #fef3c7; color: #d97706; }
    
    .btn-action { background: #10b981; color: white; padding: 8px 15px; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; transition: 0.3s; display: inline-block;}
    .btn-action:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2); }

    .alert-info { background: #dbeafe; color: #1e40af; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600; border: 1px solid #bfdbfe; }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(220, 38, 38, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
    }

    @media (max-width: 768px) { .portal-container { grid-template-columns: 1fr; } }
</style>

<div class="portal-container">
    
    <aside class="portal-sidebar">
        <div class="doc-profile-brief">
            <div class="avatar-circle">👨‍⚕️</div>
            <h3 style="margin: 0 0 5px; font-size: 1.2rem; font-weight: 800;">Dr. <?= htmlspecialchars($doctorName) ?></h3>
            <p style="margin: 0; font-size: 0.9rem; color: #94a3b8;">Attending Physician</p>
        </div>
        <ul class="portal-menu">
            <li><a href="doctor_portal.php" class="active"><span class="menu-icon">📋</span> Triage Queue</a></li>
            <li><a href="#" onclick="alert('Patient Medical Records module coming soon.'); return false;"><span class="menu-icon">📁</span> Patient Records</a></li>
            <li><a href="#" onclick="alert('Prescription pad module coming soon.'); return false;"><span class="menu-icon">✍️</span> E-Prescribe</a></li>
            <li><a href="#" onclick="alert('Schedule management coming soon.'); return false;"><span class="menu-icon">📅</span> My Schedule</a></li>
            <li style="border-top: 1px solid #f1f5f9; margin-top: 10px; padding-top: 10px;">
                <a href="logout.php" style="color: #ef4444;"><span class="menu-icon">🚪</span> Secure Logout</a>
            </li>
        </ul>
    </aside>

    <main>
        <?php if($actionMsg): ?>
            <div class="alert-info">✅ <?= $actionMsg ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card" style="border-left: 4px solid var(--primary);">
                <div>
                    <p>Patients Today</p>
                    <h3><?= $totalToday ?></h3>
                </div>
                <div style="font-size: 2rem; color: #bfdbfe;">👥</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--danger);">
                <div>
                    <p>Critical Emergencies</p>
                    <h3><?= $emergenciesToday ?></h3>
                </div>
                <div style="font-size: 2rem; color: #fca5a5;">🚨</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #10b981;">
                <div>
                    <p>Date</p>
                    <h3 style="font-size: 1.2rem; margin-top: 10px;"><?= date('M j, Y') ?></h3>
                </div>
                <div style="font-size: 2rem; color: #a7f3d0;">📅</div>
            </div>
        </div>

        <h2 class="section-title">🏥 Today's Triage Queue</h2>
        <?php if (empty($todaysQueue)): ?>
            <div style="background: white; padding: 3rem; border-radius: 16px; text-align: center; border: 1px dashed #cbd5e1; margin-bottom: 3rem;">
                <div style="font-size: 3rem; margin-bottom: 10px;">☕</div>
                <h3 style="margin: 0 0 10px; color: var(--dark);">Queue is clear</h3>
                <p style="color: #64748b; margin: 0;">You have no pending patients assigned for today.</p>
            </div>
        <?php else: ?>
            <table class="queue-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Patient Name</th>
                        <th>Tier / Type</th>
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todaysQueue as $app): ?>
                        <tr style="<?= $app['is_emergency'] ? 'background: #fff5f5;' : '' ?>">
                            <td style="font-weight: 700; color: var(--dark);">
                                <?= date("h:i A", strtotime($app['appointment_time'])) ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($app['patient_name']) ?></strong><br>
                                <?php if($app['is_emergency']): ?>
                                    <span class="badge-emergency">CRITICAL PRIORITY</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-tier <?= $app['tier'] == 'VVIP' ? 'badge-vvip' : '' ?>"><?= $app['tier'] ?></span><br>
                                <small style="color: #64748b;"><?= $app['service_type'] ?></small>
                            </td>
                            <td style="color: #475569; font-size: 0.9rem;">
                                <?= htmlspecialchars($app['patient_phone']) ?>
                            </td>
                            <td>
                                <a href="doctor_portal.php?complete_id=<?= $app['id'] ?>" class="btn-action" onclick="return confirm('Mark this consultation as completed?');">✔ Complete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2 class="section-title">📅 Upcoming Scheduled Patients</h2>
        <?php if (empty($upcomingQueue)): ?>
            <p style="color: #64748b;">No future appointments scheduled at this time.</p>
        <?php else: ?>
            <table class="queue-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Patient Name</th>
                        <th>Service Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcomingQueue as $app): ?>
                        <tr>
                            <td style="font-weight: 600; color: #475569;">
                                <?= date("M d, Y", strtotime($app['appointment_date'])) ?> <br>
                                <small><?= date("h:i A", strtotime($app['appointment_time'])) ?></small>
                            </td>
                            <td><?= htmlspecialchars($app['patient_name']) ?></td>
                            <td><span class="badge-tier"><?= $app['tier'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </main>
</div>

<?php include 'includes/footer.php'; ?>