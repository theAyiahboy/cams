<?php 
include 'includes/db_connect.php'; 
include 'includes/header.php'; 

// Fetch all appointments, joining with doctors
// We order by is_emergency first (1 before 0) and then by date
$query = "SELECT a.*, d.doc_name 
          FROM appointments a 
          JOIN doctors d ON a.doctor_id = d.id 
          ORDER BY a.is_emergency DESC, a.appointment_date ASC, a.appointment_time ASC";
$stmt = $pdo->query($query);
$appointments = $stmt->fetchAll();
?>

<div class="view-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Clinic Appointment Schedule</h2>
        <a href="add.php" class="btn btn-primary">+ New Booking</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #c3e6cb;">
            <strong>Success!</strong> The appointment has been recorded and the doctor has been notified.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #f5c6cb;">
            Appointment successfully removed from the system.
        </div>
    <?php endif; ?>

    <div style="overflow-x: auto; background: white; padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid #eee; color: #666; font-size: 0.9rem;">
                    <th style="padding: 1rem;">Patient Details</th>
                    <th style="padding: 1rem;">Assigned Doctor</th>
                    <th style="padding: 1rem;">Service Type</th>
                    <th style="padding: 1rem;">Schedule</th>
                    <th style="padding: 1rem;">Tier</th>
                    <th style="padding: 1rem; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $app): ?>
                <tr style="border-bottom: 1px solid #eee; transition: background 0.2s; <?= $app['is_emergency'] ? 'background-color: #fff5f5;' : '' ?>">
                    
                    <td style="padding: 1rem;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <strong><?= htmlspecialchars($app['patient_name']) ?></strong>
                            <?php if($app['is_emergency']): ?>
                                <span style="background: #e53e3e; color: white; padding: 2px 8px; border-radius: 20px; font-size: 0.65rem; font-weight: bold; letter-spacing: 0.5px;">EMERGENCY</span>
                            <?php endif; ?>
                        </div>
                        <small style="color: #666;"><?= htmlspecialchars($app['patient_phone']) ?></small>
                    </td>

                    <td style="padding: 1rem; color: #444;"><?= htmlspecialchars($app['doc_name']) ?></td>

                    <td style="padding: 1rem;">
                        <span style="font-size: 0.85rem; color: #555;">
                            <?= $app['service_type'] == 'Home-Service' ? '🏠 Home Visit' : '🏥 In-Clinic' ?>
                        </span>
                        <?php if($app['service_type'] == 'Home-Service'): ?>
                            <br><small style="color: #856404; font-style: italic;">Loc: <?= htmlspecialchars($app['home_address']) ?></small>
                        <?php endif; ?>
                    </td>

                    <td style="padding: 1rem;">
                        <span style="font-weight: 500;"><?= date("M d, Y", strtotime($app['appointment_date'])) ?></span><br>
                        <small style="color: #888;"><?= date("h:i A", strtotime($app['appointment_time'])) ?></small>
                    </td>

                    <td style="padding: 1rem;">
                        <span style="padding: 5px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
                            <?= $app['tier'] == 'VVIP' ? 'background: #fff3cd; color: #856404; border: 1px solid #ffeeba;' : 'background: #f1f3f5; color: #495057;' ?>">
                            <?= $app['tier'] ?>
                        </span>
                    </td>

                    <td style="padding: 1rem; text-align: center;">
                        <a href="edit.php?id=<?= $app['id'] ?>" style="color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.9rem;">Edit</a>
                        <span style="color: #ddd; margin: 0 5px;">|</span>
                        <a href="delete.php?id=<?= $app['id'] ?>" style="color: #dc3545; text-decoration: none; font-weight: 600; font-size: 0.9rem;" onclick="return confirm('Are you sure you want to delete this appointment?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($appointments)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 4rem; color: #adb5bd;">
                            <p style="font-size: 1.2rem;">No appointments scheduled yet.</p>
                            <a href="add.php" style="color: var(--primary);">Create the first booking</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>