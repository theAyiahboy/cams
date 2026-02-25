<aside style="width: 250px; background: #1e293b; color: white; height: 100vh; position: fixed; top: 0; left: 0; padding: 1.5rem; box-sizing: border-box; display: flex; flex-direction: column;">
    <div style="font-size: 1.5rem; font-weight: 800; margin-bottom: 2rem; color: #38bdf8;">
        🏥 SwiftCare
    </div>

    <nav style="display: flex; flex-direction: column; gap: 10px; flex-grow: 1;">
        <a href="index.php" style="color: white; text-decoration: none; padding: 12px; border-radius: 8px; transition: 0.3s; display: block; background: rgba(255,255,255,0.05);">
            📊 Dashboard
        </a>
        <a href="view.php" style="color: #cbd5e1; text-decoration: none; padding: 12px; border-radius: 8px; transition: 0.3s; display: block;">
            📋 Patient Registry
        </a>
        <a href="add.php" style="color: #cbd5e1; text-decoration: none; padding: 12px; border-radius: 8px; transition: 0.3s; display: block;">
            ➕ New Appointment
        </a>
        <a href="book.php" style="color: #cbd5e1; text-decoration: none; padding: 12px; border-radius: 8px; transition: 0.3s; display: block;">
            🌐 Public Booking
        </a>
    </nav>

    <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid #334155;">
        <div style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 10px;">
            Logged in as: <br>
            <strong style="color: white;"><?= $_SESSION['user_name'] ?? 'Admin' ?></strong>
        </div>
        <a href="logout.php" style="color: #fb7185; text-decoration: none; font-weight: bold; font-size: 0.9rem;">
            🚪 Log Out
        </a>
    </div>
</aside>