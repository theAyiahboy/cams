<?php 
// 1. Connect to the database
include 'includes/db_connect.php'; 

// 2. Include the modern header
include 'includes/header.php'; 

// --- START DATA VALIDATION LOGIC ---
$today = date('Y-m-d');

try {
    // Count Active Emergencies for today
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE is_emergency = 1 AND appointment_date = ?");
    $stmt->execute([$today]);
    $emergencyCount = $stmt->fetchColumn();

    // Count Home-Service (VVIP) for today
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE service_type = 'Home-Service' AND appointment_date = ?");
    $stmt->execute([$today]);
    $homeCount = $stmt->fetchColumn();

    // Total Validated Bookings for today
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ?");
    $stmt->execute([$today]);
    $totalToday = $stmt->fetchColumn();
    
    // Total Doctors Available (General Info)
    $stmt = $pdo->query("SELECT COUNT(*) FROM doctors");
    $docCount = $stmt->fetchColumn();

} catch (Exception $e) {
    // Fallback if database table isn't updated yet
    $emergencyCount = $homeCount = $totalToday = 0;
    $docCount = "Data Error";
}
// --- END DATA VALIDATION LOGIC ---
?>

<div class="dashboard-wrapper" style="padding: 2rem 0;">
    <div style="margin-bottom: 2.5rem;">
        <h1 style="font-size: 2.2rem; font-weight: 800; color: var(--dark); margin: 0;">SwiftCare Command Center</h1>
        <p style="color: #64748b; font-size: 1.1rem; margin-top: 5px;">
            Operational Overview for <strong><?php echo date('l, F jS'); ?></strong>
        </p>
    </div>

    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        
        <div style="background: white; padding: 2rem; border-radius: 20px; box-shadow: var(--shadow); border-top: 6px solid var(--danger);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <span style="color: var(--danger); font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Emergency Pulse</span>
                    <h2 style="font-size: 3rem; margin: 0.5rem 0; color: var(--dark);"><?php echo $emergencyCount; ?></h2>
                </div>
                <div style="background: #fff5f5; padding: 10px; border-radius: 12px; font-size: 1.5rem;">🚨</div>
            </div>
            <p style="color: #718096; margin: 0; font-size: 0.95rem;">Immediate response required</p>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 20px; box-shadow: var(--shadow); border-top: 6px solid var(--accent);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <span style="color: var(--accent); font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Home Services</span>
                    <h2 style="font-size: 3rem; margin: 0.5rem 0; color: var(--dark);"><?php echo $homeCount; ?></h2>
                </div>
                <div style="background: #fffaf0; padding: 10px; border-radius: 12px; font-size: 1.5rem;">🏠</div>
            </div>
            <p style="color: #718096; margin: 0; font-size: 0.95rem;">VVIP dispatch validations</p>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 20px; box-shadow: var(--shadow); border-top: 6px solid var(--primary);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <span style="color: var(--primary); font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Daily Capacity</span>
                    <h2 style="font-size: 3rem; margin: 0.5rem 0; color: var(--dark);"><?php echo $totalToday; ?></h2>
                </div>
                <div style="background: #ebf8ff; padding: 10px; border-radius: 12px; font-size: 1.5rem;">📅</div>
            </div>
            <p style="color: #718096; margin: 0; font-size: 0.95rem;">Total validated bookings today</p>
        </div>
    </div>

    <div style="background: linear-gradient(135deg, var(--primary) 0%, #0045ab 100%); padding: 2.5rem; border-radius: 24px; color: white; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="flex: 1; min-width: 300px;">
            <h3 style="font-size: 1.8rem; margin: 0;">Ready to Triage?</h3>
            <p style="font-size: 1.1rem; opacity: 0.9; margin-top: 10px;">
                SwiftCare currently has <strong><?php echo $docCount; ?></strong> medical professionals online for validation.
            </p>
        </div>
        <a href="add.php" style="background: white; color: var(--primary); padding: 1rem 2rem; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 1.1rem; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
            New Appointment +
        </a>
    </div>
</div>

<?php 
// 3. Include the footer
include 'includes/footer.php'; 
?>