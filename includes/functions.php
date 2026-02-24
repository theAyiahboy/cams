<?php
include 'config.php';

/**
 * Sends SMS via Arkesel Gateway
 * Handles phone number normalization for Ghana (233 format)
 */
function sendSMS($to, $message) {
    $apiKey = ARKESEL_API_KEY;
    $sender = SMS_SENDER_ID;

    // 1. Remove all non-numeric characters (spaces, dots, dashes, plus signs)
    $to = preg_replace('/[^0-9]/', '', $to);

    // 2. Convert local 0 format to international 233 format
    // Example: 0244123456 becomes 233244123456
    if (substr($to, 0, 1) === '0') {
        $to = '233' . substr($to, 1);
    }

    // 3. Arkesel API Endpoint
    $url = "https://sms.arkesel.com/sms/api?action=send-sms&api_key=$apiKey&to=$to&from=$sender&sms=" . urlencode($message);

    // 4. Trigger the URL
    // We use @ to suppress errors so the app doesn't crash if there's no internet
    $response = @file_get_contents($url);
    
    return $response;
}
?>