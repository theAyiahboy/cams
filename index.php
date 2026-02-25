<?php
session_start();

// Smart Routing: If you are logged in, you shouldn't be on the public landing page!
if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] == 'admin') header("Location: admin_portal.php");
    elseif ($_SESSION['user_role'] == 'doctor') header("Location: doctor_portal.php");
    elseif ($_SESSION['user_role'] == 'patient') header("Location: patient_portal.php");
    exit();
}

include 'includes/header.php';
?>

<div class="guest-wrapper" style="max-width: 1200px; margin: 0 auto; width: 100%;">
    <style>
        .hero-section { 
            padding: 100px 20px; 
            text-align: center; 
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(0, 97, 242, 0.8)), url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1920&q=80') center/cover; 
            border-radius: 30px; 
            margin-bottom: 4rem; 
            color: white;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .hero-section h1 { font-size: 4rem; font-weight: 800; margin-bottom: 1rem; line-height: 1.1; }
        .hero-section p { font-size: 1.2rem; max-width: 700px; margin: 0 auto 2.5rem; line-height: 1.6; opacity: 0.9; }
        .btn-main { background: #10b981; color: white; padding: 1.2rem 3rem; border-radius: 50px; text-decoration: none; font-weight: 800; font-size: 1.1rem; display: inline-block; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); transition: 0.3s; }
        .btn-main:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(16, 185, 129, 0.5); }
        .btn-outline { background: transparent; color: white; padding: 1.2rem 3rem; border-radius: 50px; text-decoration: none; font-weight: 800; font-size: 1.1rem; display: inline-block; border: 2px solid white; margin-left: 15px; transition: 0.3s; }
        .btn-outline:hover { background: white; color: var(--dark); }

        .section-header { text-align: center; margin-bottom: 3rem; }
        .section-header h2 { font-size: 2.5rem; color: var(--dark); margin-bottom: 10px; font-weight: 800; }
        .section-header p { color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 0 auto; }

        /* Services Grid */
        .service-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-bottom: 5rem; }
        .service-card { background: white; padding: 2.5rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; transition: 0.3s; }
        .service-card:hover { transform: translateY(-5px); border-color: var(--primary); box-shadow: 0 15px 35px rgba(0,97,242,0.1); }
        .icon-box { width: 60px; height: 60px; background: #eff6ff; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 20px; color: var(--primary); }

        /* Emergency Banner */
        .emergency-banner { background: #fff5f5; border-left: 8px solid var(--danger); padding: 3rem; border-radius: 20px; display: flex; align-items: center; gap: 3rem; margin-bottom: 5rem; box-shadow: 0 10px 20px rgba(229, 62, 62, 0.05); }
        
        /* Pricing Table */
        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 5rem; }
        .price-card { background: white; padding: 3rem; border-radius: 24px; border: 1px solid #e2e8f0; text-align: center; position: relative; overflow: hidden; }
        .price-card.featured { border: 2px solid var(--accent); box-shadow: 0 20px 40px rgba(244, 161, 0, 0.1); }
        .price-badge { position: absolute; top: 20px; right: -30px; background: var(--accent); color: white; padding: 5px 40px; transform: rotate(45deg); font-weight: 800; font-size: 0.8rem; }
        .price-card ul { list-style: none; padding: 0; text-align: left; margin: 2rem 0; }
        .price-card ul li { margin-bottom: 15px; color: #475569; display: flex; align-items: center; gap: 10px; }
        .price-card ul li::before { content: '✓'; color: #10b981; font-weight: bold; }
    </style>

    <div class="hero-section">
        <span style="display: inline-block; padding: 8px 20px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 50px; font-weight: bold; margin-bottom: 20px; letter-spacing: 1px;">GHANA'S PREMIER DIGITAL CLINIC</span>
        <h1>World-Class Healthcare, <br><span style="color: #38bdf8;">Delivered Swiftly.</span></h1>
        <p>From routine check-ups to VVIP home deliveries and 24/7 emergency triage, SwiftCare connects you with verified medical professionals instantly.</p>
        <div>
            <a href="book.php" class="btn-main">Book Appointment</a>
            <a href="login.php" class="btn-outline">Patient Portal</a>
        </div>
    </div>

    <div class="emergency-banner">
        <div style="font-size: 5rem;">🚑</div>
        <div>
            <h2 style="color: var(--danger); font-size: 2.2rem; margin: 0 0 10px; font-weight: 800;">Critical Care Protocol</h2>
            <p style="color: #475569; font-size: 1.1rem; margin: 0 0 15px; line-height: 1.6;">When you mark your booking as an <strong>Emergency</strong>, our triage algorithm bypasses the standard queue. A notification is instantly pushed to the Command Center, and the nearest available doctor is dispatched or prepared for your immediate arrival.</p>
            <a href="book.php" style="color: var(--danger); font-weight: 800; text-decoration: none;">Alert Emergency Dispatch &rarr;</a>
        </div>
    </div>

    <div class="section-header">
        <h2>Clinical Services</h2>
        <p>Select from our range of specialized booking options tailored to your healthcare needs.</p>
    </div>
    <div class="service-grid">
        <div class="service-card">
            <div class="icon-box">🩺</div>
            <h3 style="font-size: 1.4rem; margin: 0 0 15px;">General Consultation</h3>
            <p style="color: #64748b; margin-bottom: 20px;">Book an In-Clinic visit for routine check-ups, chronic disease management, and initial diagnostics with our resident GPs.</p>
            <a href="book.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Book standard visit &rarr;</a>
        </div>
        <div class="service-card">
            <div class="icon-box">🧪</div>
            <h3 style="font-size: 1.4rem; margin: 0 0 15px;">Laboratory & Scans</h3>
            <p style="color: #64748b; margin-bottom: 20px;">Schedule advanced diagnostic imaging or blood work. Results are uploaded directly to your secure Patient Portal.</p>
            <a href="book.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Schedule lab test &rarr;</a>
        </div>
        <div class="service-card">
            <div class="icon-box">👨‍⚕️</div>
            <h3 style="font-size: 1.4rem; margin: 0 0 15px;">Specialist Referrals</h3>
            <p style="color: #64748b; margin-bottom: 20px;">Need a cardiologist, dermatologist, or surgeon? Book a targeted session with our network of verified specialists.</p>
            <a href="book.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Find a specialist &rarr;</a>
        </div>
    </div>

    <div class="section-header">
        <h2>Service Tiers & Pricing</h2>
        <p>Transparent healthcare planning with options to suit every patient's requirements.</p>
    </div>
    <div class="pricing-grid">
        <div class="price-card">
            <h3 style="color: #64748b; text-transform: uppercase; letter-spacing: 1px; font-size: 1rem;">Standard Tier</h3>
            <div style="font-size: 3rem; font-weight: 800; color: var(--dark); margin: 15px 0;">₵150</div>
            <p style="color: #94a3b8; font-size: 0.9rem;">Base consultation fee per visit.</p>
            <ul>
                <li>Access to all General Practitioners</li>
                <li>Standard In-Clinic Waiting Queue</li>
                <li>Digital Validation Slip</li>
                <li>Basic Patient Portal Access</li>
            </ul>
            <a href="book.php" style="display: block; width: 100%; padding: 15px; background: #f1f5f9; color: var(--dark); border-radius: 12px; text-decoration: none; font-weight: bold; margin-top: 30px;">Select Standard</a>
        </div>
        
        <div class="price-card featured">
            <div class="price-badge">POPULAR</div>
            <h3 style="color: var(--accent); text-transform: uppercase; letter-spacing: 1px; font-size: 1rem;">VVIP Home Service</h3>
            <div style="font-size: 3rem; font-weight: 800; color: var(--dark); margin: 15px 0;">₵450</div>
            <p style="color: #94a3b8; font-size: 0.9rem;">Premium care at your location.</p>
            <ul>
                <li><strong>Doctor dispatch to your home/office</strong></li>
                <li>Zero waiting room time</li>
                <li>Priority Specialist booking</li>
                <li>Comprehensive Medical History tracking</li>
            </ul>
            <a href="book.php" style="display: block; width: 100%; padding: 15px; background: var(--accent); color: white; border-radius: 12px; text-decoration: none; font-weight: bold; margin-top: 30px;">Book VVIP Delivery</a>
        </div>
    </div>

    <div style="text-align: center; padding: 4rem 2rem; background: #1e293b; border-radius: 30px; margin-bottom: 4rem; color: white; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
        <h2 style="font-size: 2rem; margin-bottom: 1rem;">Medical Staff & Administration</h2>
        <p style="color: #94a3b8; margin-bottom: 2rem; max-width: 500px; margin-left: auto; margin-right: auto;">
            Access the SwiftCare Command Center to manage daily patient queues, triage emergencies, and update clinical records.
        </p>
        <div>
            <a href="login.php" style="background: rgba(255,255,255,0.1); color: white; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 700; border: 1px solid rgba(255,255,255,0.2); transition: 0.3s; margin-right: 15px;">Access Staff Portal &rarr;</a>
            <a href="doctor_register.php" style="background: var(--primary); color: white; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 700; transition: 0.3s; box-shadow: 0 4px 15px rgba(0,97,242,0.3);">Apply to join network</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>