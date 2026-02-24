<?php 
include 'includes/db_connect.php'; 
include 'includes/header.php'; 

// 1. Capture the search query from the URL
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // 2. Build the SQL with Search Logic
    if (!empty($searchTerm)) {
        // We use unique placeholders (:searchN and :searchP) to avoid the "Invalid parameter number" error
        $query = "SELECT a.*, d.doc_name 
                  FROM appointments a 
                  JOIN doctors d ON a.doctor_id = d.id 
                  WHERE a.patient_name LIKE :searchN 
                  OR a.patient_phone LIKE :searchP
                  ORDER BY a.is_emergency DESC, a.appointment_date ASC";
        
        $stmt = $pdo->prepare($query);
        
        // Bind the same search term to both unique placeholders
        $wildcardSearch = "%$searchTerm%";
        $stmt->execute([
            'searchN' => $wildcardSearch,
            'searchP' => $wildcardSearch
        ]);
    } else {
        // Default view: No search active
        $query = "SELECT a.*, d.doc_name 
                  FROM appointments a 
                  JOIN doctors d ON a.doctor_id = d.id 
                  ORDER BY a.is_emergency DESC, a.appointment_date ASC, a.appointment_time ASC";
        $stmt = $pdo->query($query);
    }
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Query Failed: " . $e->getMessage());
}
?>

<div class="view-container">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 20px;">
        <div>
            <h2 style="margin: 0; color: var(--dark); font-weight: 800;">SwiftCare Registry</h2>
            <p style="margin: 5px 0 0; color: #64748b; font-size: 0.9rem;">Filter the registry by <strong>Name</strong> or <strong>Phone</strong>.</p>
        </div>

        <form method="GET" style="display: flex; gap: 0;">
            <input type="text" name="search" placeholder="Type name or number..." 
                   value="<?= htmlspecialchars($searchTerm) ?>" 
                   style="padding: 0.8rem 1rem; border: 2px solid #e2e8f0; border-radius: 10px 0 0 10px; width: 280px; outline: none; transition: border-color 0.3s;">
            <button type="submit" style="background: var(--primary); color: white; border-radius: 0 10px 10px 0; padding: 0 1.5rem; border: none; cursor: pointer; font-weight: bold;">
                Search
            </button>
            <?php if(!empty($searchTerm)): ?>
                <a href="view.php" style="margin-left: 10px; align-self: center; color: var(--danger); text-decoration: none; font-size: 0.85rem; font-weight: bold;">✕ Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: #e6fffa; color: #234e52; padding: 1.2rem; border-radius: 12px; margin-bottom: 2rem; border-left: 5px solid #38b2ac; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.2rem;">✅</span>
            <strong>SwiftCare Validation:</strong> Appointment secured and SMS dispatched.
        </div>
    <?php endif; ?>

    <div style="overflow-x: auto; background: white; padding: 1rem; border-radius: 16px; box-shadow: var(--shadow);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #f1f5f9;">
                    <th style="padding: 1.2rem;">Patient Details</th>
                    <th style="padding: 1.2rem;">Assigned Doctor</th>
                    <th style="padding: 1.2rem;">Service & Location</th>
                    <th style="padding: 1.2rem;">Schedule</th>
                    <th style="padding: 1.2rem;">Tier</th>
                    <th style="padding: 1.2rem; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $app): ?>
                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s; <?= $app['is_emergency'] ? 'background-color: #fffafb;' : '' ?>">
                    <td style="padding: 1.2rem;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <strong style="color: var(--dark);"><?= htmlspecialchars($app['patient_name']) ?></strong>
                            <?php if($app['is_emergency']): ?>
                                <span style="background: var(--danger); color: white; padding: 2px 8px; border-radius: 50px; font-size: 0.6rem; font-weight: 800;">EMERGENCY</span>
                            <?php endif; ?>
                        </div>
                        <small style="color: #94a3b8;"><?= htmlspecialchars($app['patient_phone']) ?></small>
                    </td>
                    <td style="padding: 1.2rem; color: #475569; font-weight: 500;">
                        Dr. <?= htmlspecialchars($app['doc_name']) ?>
                    </td>
                    <td style="padding: 1.2rem;">
                        <span style="font-size: 0.9rem; font-weight: 600; color: #334155;">
                            <?= $app['service_type'] == 'Home-Service' ? '🏠 Home Visit' : '🏥 In-Clinic' ?>
                        </span>
                        <?php if($app['service_type'] == 'Home-Service'): ?>
                            <br><small style="color: #b45309; font-size: 0.7rem; font-style: italic;">Loc: <?= htmlspecialchars($app['home_address']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1.2rem;">
                        <span style="font-weight: 600; color: var(--dark);"><?= date("M d, Y", strtotime($app['appointment_date'])) ?></span><br>
                        <small style="color: #94a3b8;"><?= date("h:i A", strtotime($app['appointment_time'])) ?></small>
                    </td>
                    <td style="padding: 1.2rem;">
                        <span style="padding: 5px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.5px;
                            <?= $app['tier'] == 'VVIP' ? 'background: #fef3c7; color: #92400e;' : 'background: #f1f5f9; color: #475569;' ?>">
                            <?= $app['tier'] ?>
                        </span>
                    </td>
                    <td style="padding: 1.2rem; text-align: center;">
                        <div style="display: flex; gap: 12px; justify-content: center; align-items: center;">
                            <a href="print_slip.php?id=<?= $app['id'] ?>" target="_blank" title="Print Validation Slip" style="text-decoration: none; font-size: 1.1rem;">🖨️</a>
                            <a href="edit.php?id=<?= $app['id'] ?>" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 0.85rem;">Edit</a>
                            <a href="delete.php?id=<?= $app['id'] ?>" style="color: var(--danger); text-decoration: none; font-weight: 700; font-size: 0.85rem;" onclick="return confirm('Archive this record?')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($appointments)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 5rem; color: #94a3b8;">
                            <div style="font-size: 2rem;">🔍</div>
                            <p>No records found matching "<strong><?= htmlspecialchars($searchTerm) ?></strong>"</p>
                            <a href="view.php" style="color: var(--primary); font-weight: bold; text-decoration: none;">Reset Search</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>