<?php
include 'config.php';

function sendSMS($to, $message) {
    $apiKey = ARKESEL_API_KEY;
    $sender = SMS_SENDER_ID;
    
    // Arkesel API Endpoint
    $url = "https://sms.arkesel.com/sms/api?action=send-sms&api_key=$apiKey&to=$to&from=$sender&sms=" . urlencode($message);

    // Use cURL or file_get_contents to trigger the URL
    $response = file_get_contents($url);
    return $response;
}
?>