# CAMS Technical Documentation - Phase 1

## 1. System Scenario
Premier Care Clinic currently suffers from high no-show rates and inefficient manual booking. **CAMS (Clinic Appointment Management System)** digitizes this process. 
The system's USP (Unique Selling Point) is the **VVIP Home-Service** module, allowing high-priority patients to request doctor visits at their registered home address.

## 2. Functional Requirements
1. **Specialty Filtering:** Users must select a medical specialty before choosing a doctor.
2. **Dynamic Forms:** The booking form must capture home addresses specifically for VVIP/Home-Service tiers.
3. **Automated Feedback:** The system must trigger an Arkesel SMS notification upon database successful `INSERT`.
4. **Data Persistence:** All records must be stored securely in a relational MySQL database.

## 3. Database Design
The system uses a relational schema to ensure data integrity and scalability.



### Table Descriptions:
* **`specialties`**: Defines medical departments (e.g., Dentistry, Optometry).
* **`doctors`**: Stores staff details linked to specific specialties.
* **`appointments`**: The central table managing patient data, service tiers (Standard/VVIP), and appointment status.

## 4. Environment & Setup
* **Localhost:** The project is configured for XAMPP `htdocs`.
* **Connection:** Database communication is handled via a PDO (PHP Data Objects) wrapper in `includes/db_connect.php`.
* **Version Control:** Managed via GitHub to track team contributions.

## 5. Development Roadmap
* **Week 1:** Environment setup, Database Design, System Planning. (COMPLETED)
* **Week 2:** UI/UX Design (Header/Footer) and Database Connection.
* **Week 3:** Core CRUD Development (Add/View Appointments).
* **Week 4:** VVIP Logic & SMS API Integration.