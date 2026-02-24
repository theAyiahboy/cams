<?php
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Capture Form Data
    $name         = $_POST['patient_name'];
    $phone        = $_POST['patient_phone'];
    $doc_id       = $_POST['doctor_id'];
    $date         = $_POST['app_date'];
    $time         = $_POST['app_time'];
    $tier         = $_POST['tier'];
    $service_type = $_POST['service_type'];
    $is_emergency = isset($_POST['is_emergency']) ? 1 : 0;
    
    // Address is only saved if they actually chose Home-Service
    $address = ($service_type === 'Home-Service') ? $_POST['home_address'] : NULL;

    try {
        // 2. Prepare the SQL Statement (Includes the new is_emergency and service_type columns)
        $sql = "INSERT INTO appointments (patient_name, patient_phone, doctor_id, appointment_date, appointment_time, tier, service_type, home_address, status, is_emergency) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)";
        
        $stmt = $pdo->prepare($sql);
        
        // 3. Execute with data
        $stmt->execute([$name, $phone, $doc_id, $date, $time, $tier, $service_type, $address, $is_emergency]);

        // 4. Success!
        header("Location: view.php?success=1");
        exit();

    } catch (PDOException $e) {
        die("Error saving appointment: " . $e->getMessage());
    }
}
?>