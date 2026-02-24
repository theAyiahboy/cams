-- 1. Create the Database
CREATE DATABASE IF NOT EXISTS cams_db;
USE cams_db;

-- 2. Create the Specialties Table (Categories of care)
CREATE TABLE IF NOT EXISTS specialties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    spec_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Create the Doctors Table (Linked to Specialties)
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doc_name VARCHAR(100) NOT NULL,
    specialty_id INT,
    doc_email VARCHAR(100),
    FOREIGN KEY (specialty_id) REFERENCES specialties(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Create the Appointments Table (The Core logic)
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(100) NOT NULL,
    patient_phone VARCHAR(20) NOT NULL, -- Required for Arkesel SMS logic
    doctor_id INT,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    tier ENUM('Standard', 'VVIP') DEFAULT 'Standard',
    service_type ENUM('In-Clinic', 'Home-Service') DEFAULT 'In-Clinic',
    home_address TEXT DEFAULT NULL, -- Populated only for VVIP Home-Service
    status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Insert Sample Data for Testing
INSERT INTO specialties (spec_name) VALUES 
('General Practitioner'), 
('Dentist'), 
('Optometrist'), 
('Pediatrician');

INSERT INTO doctors (doc_name, specialty_id, doc_email) VALUES 
('Dr. Kwame Mensah', 1, 'kwame@clinic.com'),
('Dr. Sarah Appiah', 2, 'sarah@clinic.com'),
('Dr. John Doe', 3, 'doe@clinic.com');