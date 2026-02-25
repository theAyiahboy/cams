</div> <?php 
// Match the margin to the sidebar status
$showSidebar = isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'doctor');
?>

<footer style="margin-left: <?= $showSidebar ? '260px' : '0' ?>; text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.85rem; border-top: 1px solid #e2e8f0; background: white; margin-top: auto;">
    <p style="margin: 0;">&copy; <?php echo date("Y"); ?> <strong>SwiftCare</strong> by GoldByte Systems. All Rights Reserved.</p>
</footer> </body>
</html>