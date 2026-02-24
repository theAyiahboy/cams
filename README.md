# CAMS: Clinic Appointment Management System

## 🏥 Project Overview
CAMS is a web-based solution developed by **GoldByte** to streamline healthcare scheduling. The system allows patients to book medical appointments, choose between standard and VVIP service tiers, and receive automated SMS confirmations via the Arkesel API.

## 🚀 Core Features
* **Modular PHP Architecture:** Built with clean, vanilla PHP using a modular `includes` structure.
* **Tiered Service Logic:** Supports 'Standard' (In-Clinic) and 'VVIP' (Home-Service) bookings.
* **SMS Integration:** Real-time feedback and notifications powered by Arkesel.
* **Full CRUD Functionality:** Patients can Create, Read, Update, and Delete appointments.

## 🛠️ Tech Stack
* **Language:** PHP 8.x (Vanilla)
* **Database:** MySQL (InnoDB)
* **Server:** Apache (via XAMPP)
* **API:** Arkesel SMS Gateway
* **Frontend:** HTML5, CSS3

## 📂 Folder Structure
* `/css`: Stylesheets for the UI.
* `/database`: SQL schema and database scripts.
* `/includes`: Reusable components (DB connection, Header, Footer).
* `/root`: Main application pages (index, add, view, edit, delete).

## 👥 Team Members
* **[Your Name]** - Project Lead / Backend Dev
* **[Member Name]** - Database Administrator
* **[Member Name]** - Frontend Developer

## 📊 Current Progress (Phase 2)
- [x] Folder structure & Git setup.
- [x] Database schema design & import.
- [x] Modern UI Shell (Header/Footer/CSS).
- [x] Established successful PHP-to-MySQL connection.
- [ ] CRUD: Appointment Booking (`add.php`).
- [ ] API: Arkesel SMS Integration.