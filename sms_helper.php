function sendSMS($phone, $name, $message) {
    // We add the First Name to the start of the message automatically
    $firstName = explode(' ', trim($name))[0]; 
    $finalMessage = "Hi " . $firstName . ", " . $message;

    // Your existing SMS Provider API code goes here...
    // Example: $api->send($phone, $finalMessage);
    
    return true; 
}