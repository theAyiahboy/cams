<?php 
// 1. Connect to the database
include 'includes/db_connect.php'; 

// 2. Include the modern header
include 'includes/header.php'; 
?>

<div style="text-align: center; padding: 4rem 0;">
    <h1 style="font-size: 3rem; margin-bottom: 1rem;">Seamless Clinic Bookings.</h1>
    
    <?php
    try {
        // This line "asks" the database to count the doctors
        $stmt = $pdo->query("SELECT COUNT(*) FROM doctors");
        $docCount = $stmt->fetchColumn();
    } catch (Exception $e) {
        $docCount = "0"; // Default if something is wrong
    }
    ?>

    <p style="color: #666; font-size: 1.2rem; max-width: 600px; margin: 0 auto 2rem;">
        We have <strong><?php echo $docCount; ?></strong> professional doctors ready to serve you.
    </p>
    
    <a href="add.php" class="btn btn-primary">Book Your Session Now</a>
</div>

<?php 
// 3. Include the footer
include 'includes/footer.php'; 
?>