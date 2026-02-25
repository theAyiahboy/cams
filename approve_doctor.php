<?php
session_start();
include 'includes/db_connect.php';
include 'includes/config.php'; // Needed for SMS

// SECURITY: Only Admins can approve doctors
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Unauthorized access.");
}

if (isset($_GET['id'])) {
    $appId = intval($_GET['id']);

    try {
        // 1. Fetch the application and user details
        $stmt = $pdo->prepare("
            SELECT da.*, u.full_name, u.contact 
            FROM doctor_applications da
            JOIN users u ON da.user_id = u.id
            WHERE da.id = ? AND da.status = 'Pending'
        ");
        $stmt->execute([$appId]);
        $app = $stmt->fetch();

        if (!$app) {
            die("Application not found or already processed.");
        }

        // 2. Extract Doctor Name details for SMS and DB
        $docName = $app['full_name'];
        $phone = $app['contact'];
        $surname = explode(' ', $docName);
        $surname = end($surname); // Get the last word of the name

        $pdo->beginTransaction();

        // 3. Update application status
        $updateApp = $pdo->prepare("UPDATE doctor_applications SET status = 'Approved' WHERE id = ?");
        $updateApp->execute([$appId]);

        // 4. Upgrade user role so they can log in to the Doctor Portal
        $updateUser = $pdo->prepare("UPDATE users SET role = 'doctor' WHERE id = ?");
        $updateUser->execute([$app['user_id']]);

        // 5. Add them to the public `doctors` table for booking
        $insertDoc = $pdo->prepare("INSERT INTO doctors (doc_name, specialty) VALUES (?, ?)");
        $insertDoc->execute([$docName, $app['specialty']]);

        $pdo->commit();

        // 6. --- SEND CONGRATULATIONS SMS ---
        if (defined('ARKESEL_API_KEY') && !empty(ARKESEL_API_KEY)) {
            $smsMessage = "Congratulations Dr. $surname! Your SwiftCare medical account has been approved. You can now log into your Doctor Portal to start managing patients.";
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://sms.arkesel.com/api/v2/sms/send",
                CURLOPT_HTTPHEADER => [
                    "api-key: " . ARKESEL_API_KEY, 
                    "Content-Type: application/json"
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode([
                    'sender' => 'SwiftCare',
                    'message' => $smsMessage,
                    'recipients' => [$phone]
                ]),
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            curl_exec($curl);
            curl_close($curl);
        }
        // ------------------------------------

        // Redirect back to Admin dashboard
        header("Location: index.php?msg=doctor_approved");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error approving doctor: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
}