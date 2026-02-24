<?php 
include 'includes/db_connect.php'; 
include 'includes/header.php'; 

// Fetch all appointments and join with the doctors table to get the doctor's name
$query = "SELECT a.*, d.doc_name 
          FROM appointments a 
          JOIN doctors d ON a.doctor_id = d.id 
          ORDER BY a.created_at DESC";
$stmt = $pdo->query($query);
$appointments = $stmt->fetchAll();
?>

<div class="view-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>All Appointments</h2>
        <a href="add.php" class="btn btn-primary">+ New Booking</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            Booking confirmed successfully! SMS notification sent.
        </div>
    <?php endif; ?>

    <div style="overflow-x: auto; background: white; padding: 1rem; border-radius: 12px; box-shadow: var(--shadow);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid #eee;">
                    <th style="padding: 1rem;">Patient</th>
                    <th style="padding: 1rem;">Doctor</th>
                    <th style="padding: 1rem;">Date/Time</th>
                    <th style="padding: 1rem;">Tier</th>
                    <th style="padding: 1rem;">Status</th>
                    <th style="padding: 1rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $app): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 1rem;">
                        <strong><?= htmlspecialchars($app['patient_name']) ?></strong><br>
                        <small style="color: #666;"><?= $app['patient_phone'] ?></small>
                    </td>
                    <td style="padding: 1rem;"><?= htmlspecialchars($app['doc_name']) ?></td>
                    <td style="padding: 1rem;">
                        <?= $app['appointment_date'] ?><br>
                        <small><?= $app['appointment_time'] ?></small>
                    </td>
                    <td style="padding: 1rem;">
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; 
                            <?= $app['tier'] == 'VVIP' ? 'background: #fff3cd; color: #856404; border: 1px solid #ffeeba;' : 'background: #e2e3e5; color: #383d41;' ?>">
                            <?= $app['tier'] ?>
                        </span>
                        <?php if($app['tier'] == 'VVIP'): ?>
                            <br><small style="color: var(--primary);">Home Service</small>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1rem;">
                        <span style="color: orange; font-weight: 600;">● <?= $app['status'] ?></span>
                    </td>
                    <td style="padding: 1rem;">
                        <a href="edit.php?id=<?= $app['id'] ?>" style="color: var(--primary); text-decoration: none; margin-right: 10px;">Edit</a>
                        <a href="delete.php?id=<?= $app['id'] ?>" style="color: #dc3545; text-decoration: none;" onclick="return confirm('Delete this booking?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($appointments)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem; color: #999;">No appointments found. Start by booking one!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>