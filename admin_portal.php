<?php
session_start();
include 'includes/db_connect.php';

// 1. SECURITY CHECK: Only Admins allowed
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. PROCESS ADMIN "SUPER POWERS" (Suspend, Activate, Delete)
if (isset($_GET['action']) && isset($_GET['uid'])) {
    $action = $_GET['action'];
    $uid = intval($_GET['uid']);
    $actionMsg = "";

    try {
        if ($action === 'suspend') {
            $pdo->prepare("UPDATE users SET role = 'suspended' WHERE id = ?")->execute([$uid]);
            $actionMsg = "Staff member has been suspended and locked out.";
        } elseif ($action === 'activate') {
            $pdo->prepare("UPDATE users SET role = 'doctor' WHERE id = ?")->execute([$uid]);
            $actionMsg = "Staff member access restored.";
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
            $actionMsg = "User account permanently deleted.";
        }
        
        // Redirect to clear the URL parameters
        header("Location: admin_portal.php?msg=" . urlencode($actionMsg));
        exit();
    } catch (PDOException $e) {
        die("Admin Action Failed: " . $e->getMessage());
    }
}

// 3. FETCH ADMIN DASHBOARD DATA
$today = date('Y-m-d');
try {
    // Quick Stats
    $emergencyCount = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE is_emergency = 1 AND appointment_date = ?");
    $emergencyCount->execute([$today]);
    $emergencyCount = $emergencyCount->fetchColumn();

    $totalToday = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ?");
    $totalToday->execute([$today]);
    $totalToday = $totalToday->fetchColumn();

    // Fetch Pending Doctor Applications
    $pendingQuery = "SELECT da.*, u.full_name, u.contact, u.email FROM doctor_applications da JOIN users u ON da.user_id = u.id WHERE da.status = 'Pending' ORDER BY da.applied_on ASC";
    $pendingDocs = $pdo->query($pendingQuery)->fetchAll();
    
    // Fetch Active & Suspended Staff
    $staffQuery = "SELECT * FROM users WHERE role IN ('doctor', 'suspended') ORDER BY full_name ASC";
    $medicalStaff = $pdo->query($staffQuery)->fetchAll();

    // Fetch Registered Patients
    $patientQuery = "SELECT * FROM users WHERE role = 'patient' ORDER BY id DESC";
    $patients = $pdo->query($patientQuery)->fetchAll();

} catch (Exception $e) {
    die("Data fetch error: " . $e->getMessage());
}

include 'includes/header.php';
?>

<style>
    .admin-section-title { color: var(--dark); font-size: 1.6rem; font-weight: 800; margin: 3rem 0 1rem; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
    
    .admin-table { width: 100%; border-collapse: collapse; white-space: nowrap; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; }
    .admin-table th, .admin-table td { padding: 1.2rem 1.5rem; text-align: left; border-bottom: 1px solid #f1f5f9; }
    .admin-table th { background: #f8fafc; color: #64748b; text-transform: uppercase; font-size: 0.85rem; font-weight: 800; letter-spacing: 0.5px; }
    .admin-table tr:hover { background: #f8fafc; }

    .btn-super { padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: bold; transition: 0.3s; margin-right: 5px; display: inline-block; }
    .btn-suspend { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .btn-suspend:hover { background: #f59e0b; color: white; }
    .btn-activate { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .btn-activate:hover { background: #10b981; color: white; }
    .btn-delete { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
    .btn-delete:hover { background: #ef4444; color: white; }
    
    .status-active { background: #d1fae5; color: #047857; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; }
    .status-suspended { background: #fee2e2; color: #dc2626; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; }
</style>

<div class="dashboard-wrapper" style="padding: 1rem 0; max-width: 1200px; margin: 0 auto;">
    
    <?php if (isset($_GET['msg'])): ?>
        <div style="background: #d1fae5; color: #047857; padding: 1.2rem; border-radius: 12px; margin-bottom: 2rem; border-left: 5px solid #10b981; font-weight: bold; font-size: 1.1rem;">
            ✅ <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 2.5rem;">
        <h1 style="font-size: 2.2rem; font-weight: 800; color: var(--dark); margin: 0;">SuperAdmin Command Center</h1>
        <p style="color: #64748b; font-size: 1.1rem; margin-top: 5px;">Total Control & Oversight for <strong><?php echo date('l, F jS'); ?></strong></p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <div style="background: white; padding: 2rem; border-radius: 20px; box-shadow: var(--shadow); border-top: 6px solid var(--danger);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <span style="color: var(--danger); font-weight: 800; font-size: 0.85rem; text-transform: uppercase;">Emergency Pulse</span>
                    <h2 style="font-size: 3rem; margin: 0.5rem 0; color: var(--dark);"><?php echo $emergencyCount; ?></h2>
                </div>
                <div style="background: #fff5f5; padding: 10px; border-radius: 12px; font-size: 1.5rem;">🚨</div>
            </div>
        </div>
        <div style="background: white; padding: 2rem; border-radius: 20px; box-shadow: var(--shadow); border-top: 6px solid var(--primary);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <span style="color: var(--primary); font-weight: 800; font-size: 0.85rem; text-transform: uppercase;">Daily Volume</span>
                    <h2 style="font-size: 3rem; margin: 0.5rem 0; color: var(--dark);"><?php echo $totalToday; ?></h2>
                </div>
                <div style="background: #ebf8ff; padding: 10px; border-radius: 12px; font-size: 1.5rem;">📅</div>
            </div>
        </div>
        <div style="background: white; padding: 2rem; border-radius: 20px; box-shadow: var(--shadow); border-top: 6px solid #8b5cf6;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <span style="color: #8b5cf6; font-weight: 800; font-size: 0.85rem; text-transform: uppercase;">Registered Users</span>
                    <h2 style="font-size: 3rem; margin: 0.5rem 0; color: var(--dark);"><?php echo count($patients); ?></h2>
                </div>
                <div style="background: #f5f3ff; padding: 10px; border-radius: 12px; font-size: 1.5rem;">🧑‍🦱</div>
            </div>
        </div>
    </div>

    <h2 class="admin-section-title">🧑‍⚕️ Pending Staff Applications <?php if(count($pendingDocs) > 0) echo "<span style='background: var(--danger); color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.9rem;'>".count($pendingDocs)." New</span>"; ?></h2>
    <div style="overflow-x: auto; margin-bottom: 3rem;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Applicant Info</th>
                    <th>Specialty & License</th>
                    <th>Documentation</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendingDocs)): ?>
                    <tr><td colspan="4" style="text-align: center; padding: 2rem; color: #64748b;">No pending applications.</td></tr>
                <?php else: ?>
                    <?php foreach ($pendingDocs as $doc): ?>
                        <tr>
                            <td>
                                <strong>Dr. <?= htmlspecialchars($doc['full_name']) ?></strong><br>
                                <small>📞 <?= htmlspecialchars($doc['contact']) ?> | ✉️ <?= htmlspecialchars($doc['email']) ?></small>
                            </td>
                            <td><span style="color: var(--primary); font-weight: bold;"><?= htmlspecialchars($doc['specialty']) ?></span><br><small>GDC: <?= htmlspecialchars($doc['license_number']) ?></small></td>
                            <td><a href="<?= htmlspecialchars($doc['cv_file_path']) ?>" target="_blank" style="color: var(--dark); font-weight: bold; text-decoration: none;">📄 View CV</a></td>
                            <td><a href="approve_doctor.php?id=<?= $doc['id'] ?>" class="btn-super btn-activate" onclick="return confirm('Approve this applicant?');">✔ Approve</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h2 class="admin-section-title">⚕️ Manage Medical Staff</h2>
    <div style="overflow-x: auto; margin-bottom: 3rem;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Contact Credentials</th>
                    <th>System Status</th>
                    <th>Admin Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($medicalStaff as $staff): ?>
                    <tr>
                        <td><strong>Dr. <?= htmlspecialchars($staff['full_name']) ?></strong></td>
                        <td><small><?= htmlspecialchars($staff['email']) ?><br><?= htmlspecialchars($staff['contact']) ?></small></td>
                        <td>
                            <?php if($staff['role'] == 'suspended'): ?>
                                <span class="status-suspended">Suspended</span>
                            <?php else: ?>
                                <span class="status-active">Active</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($staff['role'] == 'doctor'): ?>
                                <a href="admin_portal.php?action=suspend&uid=<?= $staff['id'] ?>" class="btn-super btn-suspend" onclick="return confirm('Suspend this doctor? They will not be able to log in.');">⏸ Suspend</a>
                            <?php else: ?>
                                <a href="admin_portal.php?action=activate&uid=<?= $staff['id'] ?>" class="btn-super btn-activate">▶ Activate</a>
                            <?php endif; ?>
                            <a href="admin_portal.php?action=delete&uid=<?= $staff['id'] ?>" class="btn-super btn-delete" onclick="return confirm('WARNING: Permanently delete this staff account?');">🗑 Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h2 class="admin-section-title">🧑‍🦱 Patient Directory</h2>
    <div style="overflow-x: auto; margin-bottom: 3rem;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>Full Name</th>
                    <th>Contact Information</th>
                    <th>Admin Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $pat): ?>
                    <tr>
                        <td style="color: #94a3b8; font-weight: bold;">#SC-<?= str_pad($pat['id'], 4, '0', STR_PAD_LEFT) ?></td>
                        <td><strong><?= htmlspecialchars($pat['full_name']) ?></strong></td>
                        <td><?= htmlspecialchars($pat['email']) ?> <br> <small><?= htmlspecialchars($pat['contact']) ?></small></td>
                        <td>
                            <a href="admin_portal.php?action=delete&uid=<?= $pat['id'] ?>" class="btn-super btn-delete" onclick="return confirm('Permanently delete this patient account?');">🗑 Delete User</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include 'includes/footer.php'; ?>