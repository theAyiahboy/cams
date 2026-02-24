<?php
include 'includes/db_connect.php';

// 1. Check if an ID was actually sent in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // 2. Prepare the delete statement to prevent SQL injection
        $sql = "DELETE FROM appointments WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        // 3. Execute the deletion
        if ($stmt->execute([$id])) {
            // 4. Redirect back to the view page with a success message
            header("Location: view.php?deleted=1");
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