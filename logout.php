<?php
session_start();
// Empty the session variables
$_SESSION = array();
// Destroy the session
session_destroy();
// Redirect back to login
header("Location: login.php");
exit();
?>