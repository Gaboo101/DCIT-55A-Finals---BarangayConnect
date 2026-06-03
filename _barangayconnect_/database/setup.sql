CREATE DATABASE IF NOT EXISTS barangayconnect;

USE barangayconnect;

CREATE TABLE IF NOT EXISTS users (    
	id INT AUTO_INCREMENT PRIMARY KEY,
	email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
	role ENUM('Admin', 'Resident') DEFAULT 'Resident',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS document_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    fee DECIMAL(10,2) DEFAULT 0.00,
    description TEXT,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doc_id INT NOT NULL,
    tracking_code VARCHAR(255) NOT NULL UNIQUE,
    id_photo_path VARCHAR(255),
    purpose TEXT,
    status ENUM('Pending', 'Processing', 'Ready for Pickup', 'Completed', 'Rejected') DEFAULT 'Pending',
    admin_remarks TEXT,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample Admin User (password: admin123)
INSERT IGNORE INTO users (email, password, first_name, last_name, role) 
VALUES ('admin@barangay.gov', '$2y$10$K1N8qL8v6YnQ9B2z3jK9JOu0Zy7E5xC3pL0D2m9K8X9Y7V3u5I7hm', 'Admin', 'User', 'Admin');

-- Sample Document Types
INSERT IGNORE INTO document_types (name, fee, description) 
VALUES 
('Barangay Clearance', 50.00, 'Required for job employment, bank registration, legal reference, and general local background verification.'),
('Certificate of Indigency', 0.00, 'Issued to low-income residents for scholarships, educational grants, medical aid, social assistance, and free legal services.'),
('Certificate of Residency', 0.00, 'Provides validated official proof that you are currently residing inside the physical jurisdiction of this Barangay.'),
('Business Clearance', 150.00, 'Mandatory preliminary document requested by the City/Municipality before acquiring a commercial Business Permit.');

