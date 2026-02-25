<?php
session_start();
include 'includes/db_connect.php';

// 1. SECURITY CHECK: Only Admins can delete records!
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Check if an ID was actually sent in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // intval() adds an extra layer of security

    try {
        // 3. Prepare the delete statement to prevent SQL injection
        $sql = "DELETE FROM appointments WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        // 4. Execute the deletion
        if ($stmt->execute([$id])) {
            // Redirect back to the view page with a success message
            header("Location: view.php?msg=deleted");
            exit();
        }
    } catch (PDOException $e) {
        die("Error deleting record: " . $e->getMessage());
    }
} else {
    // If someone tries to access delete.php without an ID, send them back
    header("Location: view.php");
    exit();
}
?>