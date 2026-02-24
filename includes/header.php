<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SwiftCare | Professional Clinic Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0061f2;    /* Medical Blue */
            --accent: #f4a100;     /* Gold for VVIP */
            --danger: #e53e3e;     /* Red for Emergencies */
            --dark: #1a202c;
            --light: #f8fafc;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--light); color: var(--dark); margin: 0; }
        
        header {
            background: white;
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: -1px;
            text-decoration: none;
            color: var(--dark);
        }

        .logo span {
            color: var(--primary);
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 2rem;
            margin: 0;
            padding: 0;
        }

        nav ul li a {
            text-decoration: none;
            color: #64748b;
            font-weight: 600;
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        nav ul li a:hover {
            color: var(--primary);
        }

        .btn-nav {
            background: var(--primary);
            color: white !important;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 700 !important;
        }
        
        .container {
            padding: 2rem 5%;
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo">Swift<span>Care</span></a>
        
        <nav>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="view.php">Appointments</a></li>
                <li><a href="add.php" class="btn-nav">Book Now</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">