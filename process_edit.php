<?php
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id           = $_POST['id'];
    $name         = $_POST['patient_name'];
    $phone        = $_POST['patient_phone'];
    $doc_id       = $_POST['doctor_id'];
    $date         = $_POST['app_date'];
    $time         = $_POST['app_time'];
    $tier         = $_POST['tier'];
    $service_type = $_POST['service_type'];
    $status       = $_POST['status'];
    $is_emergency = isset($_POST['is_emergency']) ? 1 : 0;
    $address      = ($service_type === 'Home-Service') ? $_POST['home_address'] : NULL;

    try {
        $sql = "UPDATE appointments SET 
                patient_name = ?, 
                patient_phone = ?, 
                doctor_id = ?, 
                appointment_date = ?, 
                appointment_time = ?, 
                tier = ?, 
                service_type = ?, 
                home_address = ?, 
                status = ?, 
                is_emergency = ? 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $phone, $doc_id, $date, $time, $tier, $service_type, $address, $status, $is_emergency, $id]);

        header("Location: view.php?updated=1");
        exit();

    } catch (PDOException $e) {
        die("Error updating record: " . $e->getMessage());
    }
}
?>