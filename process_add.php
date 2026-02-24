<?php
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Capture Form Data
    $name    = $_POST['patient_name'];
    $phone   = $_POST['patient_phone'];
    $doc_id  = $_POST['doctor_id'];
    $date    = $_POST['app_date'];
    $time    = $_POST['app_time'];
    $tier    = $_POST['tier'];
    $address = ($tier === 'VVIP') ? $_POST['home_address'] : NULL;
    $service = ($tier === 'VVIP') ? 'Home-Service' : 'In-Clinic';

    try {
        // 2. Prepare the SQL Statement
        $sql = "INSERT INTO appointments (patient_name, patient_phone, doctor_id, appointment_date, appointment_time, tier, service_type, home_address, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        
        $stmt = $pdo->prepare($sql);
        
        // 3. Execute with data
        $stmt->execute([$name, $phone, $doc_id, $date, $time, $tier, $service, $address]);

        // 4. Success! Redirect to View page
        // (Note: We will add the Arkesel SMS code right here in Phase 4!)
        header("Location: view.php?success=1");
        exit();

    } catch (PDOException $e) {
        die("Error saving appointment: " . $e->getMessage());
    }
}
?>