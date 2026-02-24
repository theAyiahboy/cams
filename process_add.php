<?php
include 'includes/db_connect.php';
include 'includes/functions.php'; // Contains the sendSMS function

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
        // 2. Prepare the SQL Statement
        $sql = "INSERT INTO appointments (patient_name, patient_phone, doctor_id, appointment_date, appointment_time, tier, service_type, home_address, status, is_emergency) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)";
        
        $stmt = $pdo->prepare($sql);
        
        // 3. Execute with data
        if ($stmt->execute([$name, $phone, $doc_id, $date, $time, $tier, $service_type, $address, $is_emergency])) {
            
            // --- START SMS LOGIC ---
            // Format phone number (Arkesel prefers 233 format, but usually handles 0...)
            // If your Arkesel settings require 233, we can add a helper here later.
            
            $msg = "Hi $name, your $tier appointment is confirmed for $date at $time. ";
            
            if ($is_emergency) {
                $msg = "🚨 EMERGENCY CONFIRMED: $msg Our team is on high alert.";
            } elseif ($service_type === 'Home-Service') {
                $msg .= "Our doctor will meet you at your home address.";
            } else {
                $msg .= "Please arrive at the clinic 10 mins before your slot.";
            }

            // Call the function from functions.php
            // We wrap this in a check so the app doesn't crash if SMS fails
            @sendSMS($phone, $msg); 
            // --- END SMS LOGIC ---

            // 4. Success! Redirect
            header("Location: view.php?success=1");
            exit();
        }

    } catch (PDOException $e) {
        die("Error saving appointment: " . $e->getMessage());
    }
}
?>