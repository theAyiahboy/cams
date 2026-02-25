<?php
// setup.php - Run this ONCE in your browser to create the default users
include 'includes/db_connect.php';

// The default password for everyone will be: password123
$hashedPassword = password_hash('password123', PASSWORD_DEFAULT);

$users = [
    ['Admin User', 'admin', $hashedPassword, 'admin'],
    ['Dr. Smith', 'doctor', $hashedPassword, 'doctor'],
    ['John Doe', '0541234567', $hashedPassword, 'patient']
];

try {
    $stmt = $pdo->prepare("INSERT INTO users (full_name, contact, password, role) VALUES (?, ?, ?, ?)");
    foreach ($users as $u) {
        $stmt->execute($u);
    }
    echo "<h1>✅ Success! Users created.</h1>";
    echo "<p>You can now log in with the following usernames (Password is <strong>password123</strong> for all):</p>";
    echo "<ul><li><strong>Admin:</strong> admin</li><li><strong>Doctor:</strong> doctor</li><li><strong>Patient:</strong> 0541234567</li></ul>";
    echo "<p><em>Please delete this setup.php file now for security.</em></p>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>