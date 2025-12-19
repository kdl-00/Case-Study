-- Create Database
CREATE DATABASE IF NOT EXISTS database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE database;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('student', 'adviser', 'admin') NOT NULL,
    department_id INT,
    program_id INT,
    profile_picture VARCHAR(255),
    signature VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_department (department_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Departments Table
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL,
    department_code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Programs Table
CREATE TABLE programs (
    program_id INT AUTO_INCREMENT PRIMARY KEY,
    program_name VARCHAR(100) NOT NULL,
    program_code VARCHAR(20) UNIQUE NOT NULL,
    department_id INT NOT NULL,
    degree_level ENUM('undergraduate', 'masters', 'doctorate') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Thesis Table
CREATE TABLE thesis (
    thesis_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    abstract TEXT NOT NULL,
    keywords VARCHAR(500),
    author_id INT NOT NULL,
    co_authors TEXT,
    adviser_id INT NOT NULL,
    department_id INT NOT NULL,
    program_id INT NOT NULL,
    publication_year INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size BIGINT,
    file_type VARCHAR(50),
    status ENUM('pending', 'under_review', 'approved', 'rejected') DEFAULT 'pending',
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approval_date TIMESTAMP NULL,
    views INT DEFAULT 0,
    downloads INT DEFAULT 0,
    FOREIGN KEY (author_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (adviser_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_author (author_id),
    INDEX idx_adviser (adviser_id),
    INDEX idx_year (publication_year),
    FULLTEXT idx_fulltext (title, abstract, keywords)
) ENGINE=InnoDB;

-- Approvals Table
CREATE TABLE approvals (
    approval_id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    comments TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thesis_id) REFERENCES thesis(thesis_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_thesis (thesis_id),
    INDEX idx_reviewer (reviewer_id)
) ENGINE=InnoDB;

-- Review Logs Table
CREATE TABLE review_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thesis_id) REFERENCES thesis(thesis_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Activity Logs Table
CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- System Settings Table
CREATE TABLE system_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert Default Admin User (password: admin123)
INSERT INTO users (username, email, password, first_name, last_name, role, status) 
VALUES ('admin', 'admin@thesis.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', 'active');

-- Insert Default Departments
INSERT INTO departments (department_name, department_code, description) VALUES
('Computer Science', 'CS', 'Department of Computer Science and Information Technology'),
('Engineering', 'ENG', 'Department of Engineering'),
('Business Administration', 'BA', 'Department of Business and Management');

-- Insert Default Programs
INSERT INTO programs (program_name, program_code, department_id, degree_level) VALUES
('BS Computer Science', 'BSCS', 1, 'undergraduate'),
('MS Computer Science', 'MSCS', 1, 'masters'),
('BS Civil Engineering', 'BSCE', 2, 'undergraduate'),
('MBA', 'MBA', 3, 'masters');

-- Insert Default System Settings
INSERT INTO system_settings (setting_key, setting_value) VALUES
('site_name', 'Thesis Archive Management System'),
('max_upload_size', '52428800'),
('allowed_file_types', 'pdf,doc,docx'),
('items_per_page', '10');
