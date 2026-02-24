<?php 
include 'includes/db_connect.php'; 
include 'includes/header.php'; 

// Fetch stats for the dashboard
try {
    // 1. Total Appointments
    $total = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
    
    // 2. Emergency Count
    $emergencies = $pdo->query("SELECT COUNT(*) FROM appointments WHERE is_emergency = 1")->fetchColumn();
    
    // 3. VVIP Count
    $vvips = $pdo->query("SELECT COUNT(*) FROM appointments WHERE tier = 'VVIP'")->fetchColumn();
    
    // 4. Home Visits
    $homeVisits = $pdo->query("SELECT COUNT(*) FROM appointments WHERE service_type = 'Home-Service'")->fetchColumn();
} catch (PDOException $e) {
    die("Stats Error: " . $e->getMessage());
}
?>

<div class="dashboard-container" style="padding: 2rem;">
    <h2 style="font-weight: 800; color: var(--dark); margin-bottom: 0.5rem;">Clinic Overview</h2>
    <p style="color: #64748b; margin-bottom: 2.5rem;">Welcome back. Here is what's happening at SwiftCare today.</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 3rem;">
        
        <div style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: var(--shadow); border-left: 5px solid var(--primary);">
            <small style="color: #64748b; font-weight: 700; text-transform: uppercase;">Total Registry</small>
            <h1 style="margin: 10px 0 0; font-size: 2.5rem; color: var(--dark);"><?= $total ?></h1>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: var(--shadow); border-left: 5px solid var(--danger);">
            <small style="color: #64748b; font-weight: 700; text-transform: uppercase;">Urgent Cases</small>
            <h1 style="margin: 10px 0 0; font-size: 2.5rem; color: var(--danger);"><?= $emergencies ?></h1>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: var(--shadow); border-left: 5px solid #f59e0b;">
            <small style="color: #64748b; font-weight: 700; text-transform: uppercase;">VVIP Tier</small>
            <h1 style="margin: 10px 0 0; font-size: 2.5rem; color: #b45309;"><?= $vvips ?></h1>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: var(--shadow); border-left: 5px solid #10b981;">
            <small style="color: #64748b; font-weight: 700; text-transform: uppercase;">Home Visits</small>
            <h1 style="margin: 10px 0 0; font-size: 2.5rem; color: #059669;"><?= $homeVisits ?></h1>
        </div>

    </div>

    <div style="background: #f8fafc; padding: 2rem; border-radius: 20px; border: 2px dashed #e2e8f0; text-align: center;">
        <h3 style="margin-top: 0; color: #1e293b;">Quick Management</h3>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="index.php" style="background: var(--primary); color: white; padding: 12px 25px; border-radius: 10px; text-decoration: none; font-weight: bold;">➕ New Appointment</a>
            <a href="view.php" style="background: white; color: var(--primary); border: 2px solid var(--primary); padding: 12px 25px; border-radius: 10px; text-decoration: none; font-weight: bold;">📋 View Registry</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>