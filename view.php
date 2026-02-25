<?php
session_start();
include 'includes/db_connect.php';

// 1. SECURITY CHECK: Only Admins belong here
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Capture the search query from the URL
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // 3. Build the SQL with Search Logic
    if (!empty($searchTerm)) {
        // We use unique placeholders to search across Name or Phone
        $query = "SELECT a.*, d.doc_name, d.specialty 
                  FROM appointments a 
                  LEFT JOIN doctors d ON a.doctor_id = d.id 
                  WHERE a.patient_name LIKE :searchN 
                  OR a.patient_phone LIKE :searchP
                  ORDER BY a.is_emergency DESC, a.appointment_date DESC, a.appointment_time DESC";
        
        $stmt = $pdo->prepare($query);
        $wildcardSearch = "%$searchTerm%";
        $stmt->execute([
            'searchN' => $wildcardSearch,
            'searchP' => $wildcardSearch
        ]);
    } else {
        // Default view: No search active
        $query = "SELECT a.*, d.doc_name, d.specialty 
                  FROM appointments a 
                  LEFT JOIN doctors d ON a.doctor_id = d.id 
                  ORDER BY a.is_emergency DESC, a.appointment_date DESC, a.appointment_time DESC";
        $stmt = $pdo->query($query);
    }
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

include 'includes/header.php';
?>

<style>
    .registry-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 1.5rem; flex-wrap: wrap; gap: 20px; }
    .registry-header h1 { margin: 0; color: var(--dark); font-size: 2rem; font-weight: 800; }
    
    .btn-add { background: var(--primary); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 800; box-shadow: 0 4px 15px rgba(0,97,242,0.2); transition: 0.3s; display: inline-block;}
    .btn-add:hover { background: #004ecc; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,97,242,0.3); }

    .search-box { display: flex; gap: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border-radius: 12px; }
    .search-input { padding: 0.8rem 1.2rem; border: 2px solid #e2e8f0; border-radius: 12px 0 0 12px; width: 300px; outline: none; transition: 0.3s; font-family: inherit; font-size: 0.95rem; }
    .search-input:focus { border-color: var(--primary); background: white; }
    .search-btn { background: var(--dark); color: white; border-radius: 0 12px 12px 0; padding: 0 1.5rem; border: none; cursor: pointer; font-weight: bold; transition: 0.3s; }
    .search-btn:hover { background: #334155; }

    .table-container { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; overflow-x: auto; }
    
    .admin-table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    .admin-table th, .admin-table td { padding: 1.2rem 1.5rem; text-align: left; border-bottom: 1px solid #f1f5f9; }
    .admin-table th { background: #f8fafc; color: #64748b; text-transform: uppercase; font-size: 0.85rem; font-weight: 800; letter-spacing: 0.5px; }
    .admin-table tr:last-child td { border-bottom: none; }
    .admin-table tr:hover { background: #f8fafc; }
    
    .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; }
    .status-pending { background: #fef3c7; color: #d97706; }
    .status-completed { background: #d1fae5; color: #047857; }
    
    .emergency-badge { background: #fee2e2; color: #dc2626; padding: 3px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 800; margin-left: 8px; }
    
    .action-btn { padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: bold; transition: 0.3s; margin-right: 5px; }
    .btn-edit { background: #eff6ff; color: var(--primary); border: 1px solid #bfdbfe; }
    .btn-edit:hover { background: var(--primary); color: white; }
    .btn-delete { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
    .btn-delete:hover { background: #ef4444; color: white; }
</style>

<div class="registry-header">
    <div>
        <h1>📋 Master Patient Registry</h1>
        <p style="color: #64748b; margin: 5px 0 0;">Complete oversight of all clinic appointments and statuses.</p>
    </div>
    
    <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
        <form method="GET" class="search-box">
            <input type="text" name="search" class="search-input" placeholder="Search name or phone..." value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit" class="search-btn">Search</button>
            <?php if(!empty($searchTerm)): ?>
                <a href="view.php" style="margin-left: 15px; align-self: center; color: var(--danger); text-decoration: none; font-size: 0.85rem; font-weight: bold;">✕ Clear Filter</a>
            <?php endif; ?>
        </form>

        <a href="book.php" class="btn-add">➕ Add Walk-In</a>
    </div>
</div>

<div class="table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient Details</th>
                <th>Schedule</th>
                <th>Assigned Specialist</th>
                <th>Care Tier</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($appointments)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 5rem; color: #94a3b8;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">🔍</div>
                        <?php if(!empty($searchTerm)): ?>
                            <p>No records found matching "<strong><?= htmlspecialchars($searchTerm) ?></strong>"</p>
                            <a href="view.php" style="color: var(--primary); font-weight: bold; text-decoration: none;">Reset Search</a>
                        <?php else: ?>
                            <p>The registry is currently empty.</p>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($appointments as $app): ?>
                    <tr style="<?= $app['is_emergency'] ? 'background-color: #fffafb;' : '' ?>">
                        <td style="color: #94a3b8; font-weight: bold; font-size: 0.9rem;">
                            #<?= str_pad($app['id'], 4, '0', STR_PAD_LEFT) ?>
                        </td>
                        
                        <td>
                            <strong style="color: var(--dark); font-size: 1.05rem;"><?= htmlspecialchars($app['patient_name']) ?></strong>
                            <?php if($app['is_emergency']) echo '<span class="emergency-badge">EMERGENCY</span>'; ?>
                            <br>
                            <span style="color: #64748b; font-size: 0.85rem;">📞 <?= htmlspecialchars($app['patient_phone']) ?></span>
                        </td>
                        
                        <td>
                            <span style="color: var(--dark); font-weight: 700;"><?= date("M d, Y", strtotime($app['appointment_date'])) ?></span><br>
                            <span style="color: #64748b; font-size: 0.85rem;">⏰ <?= date("h:i A", strtotime($app['appointment_time'])) ?></span>
                        </td>
                        
                        <td>
                            <strong style="color: #334155;">Dr. <?= htmlspecialchars($app['doc_name'] ?? 'Unassigned') ?></strong><br>
                            <span style="color: #64748b; font-size: 0.85rem;"><?= htmlspecialchars($app['specialty'] ?? 'General Practice') ?></span>
                        </td>
                        
                        <td>
                            <span style="font-weight: 800; font-size: 0.85rem; color: <?= $app['tier'] == 'VVIP' ? '#d97706' : '#475569' ?>;"><?= $app['tier'] ?></span><br>
                            <span style="color: #94a3b8; font-size: 0.8rem;"><?= $app['service_type'] ?></span>
                        </td>
                        
                        <td>
                            <?php if($app['status'] === 'Completed'): ?>
                                <span class="status-badge status-completed">✔ Completed</span>
                            <?php else: ?>
                                <span class="status-badge status-pending">⏳ Pending</span>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <a href="print_slip.php?id=<?= $app['id'] ?>" target="_blank" title="Print Validation Slip" style="text-decoration: none; font-size: 1.2rem; margin-right: 10px; display: inline-block; vertical-align: middle;">🖨️</a>
                            <a href="edit.php?id=<?= $app['id'] ?>" class="action-btn btn-edit">Edit</a>
                            <a href="delete.php?id=<?= $app['id'] ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to permanently delete this record?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>