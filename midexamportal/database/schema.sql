-- Database schema for College Question Paper Generation & Examination Management System
CREATE DATABASE IF NOT EXISTS midexamportal CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE midexamportal;

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(40) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    module VARCHAR(80) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    faculty_id VARCHAR(30) NULL,
    employee_id VARCHAR(40) NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    department_id INT UNSIGNED NULL,
    designation VARCHAR(120) NULL,
    password VARCHAR(255) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE subjects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    credits TINYINT UNSIGNED NOT NULL,
    semester TINYINT UNSIGNED NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    regulation VARCHAR(50) NOT NULL,
    department_id INT UNSIGNED NOT NULL,
    faculty_id INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE question_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE question_units (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE question_bank (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_id INT UNSIGNED NOT NULL,
    department_id INT UNSIGNED NOT NULL,
    faculty_id INT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    image_path VARCHAR(255) NULL,
    question_type_id INT UNSIGNED NOT NULL,
    objective_options JSON NULL,
    marks TINYINT UNSIGNED NOT NULL,
    unit_id INT UNSIGNED NOT NULL,
    co_mapping VARCHAR(60) NOT NULL,
    bloom_level VARCHAR(50) NOT NULL,
    difficulty VARCHAR(40) NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_type_id) REFERENCES question_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (unit_id) REFERENCES question_units(id) ON DELETE RESTRICT,
    FULLTEXT KEY idx_question_text (question_text)
) ENGINE=InnoDB;

CREATE TABLE generated_papers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    paper_code VARCHAR(60) NOT NULL UNIQUE,
    subject_id INT UNSIGNED NOT NULL,
    department_id INT UNSIGNED NOT NULL,
    faculty_id INT UNSIGNED NOT NULL,
    semester TINYINT UNSIGNED NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    regulation VARCHAR(50) NOT NULL,
    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    set_type ENUM('SET-I','SET-II') NOT NULL,
    status ENUM('draft','final') NOT NULL DEFAULT 'final',
    file_path VARCHAR(255) NOT NULL,
    download_count INT UNSIGNED NOT NULL DEFAULT 0,
    ip_address VARCHAR(45) NOT NULL,
    browser_info VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE generated_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    generated_paper_id INT UNSIGNED NOT NULL,
    question_bank_id INT UNSIGNED NOT NULL,
    question_order TINYINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_paper_id) REFERENCES generated_papers(id) ON DELETE CASCADE,
    FOREIGN KEY (question_bank_id) REFERENCES question_bank(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE generated_objective_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    generated_paper_id INT UNSIGNED NOT NULL,
    question_bank_id INT UNSIGNED NOT NULL,
    question_order TINYINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_paper_id) REFERENCES generated_papers(id) ON DELETE CASCADE,
    FOREIGN KEY (question_bank_id) REFERENCES question_bank(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE downloads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    generated_paper_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    downloaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NULL,
    FOREIGN KEY (generated_paper_id) REFERENCES generated_papers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE email_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    generated_paper_id INT UNSIGNED NULL,
    recipient VARCHAR(150) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('sent','failed') NOT NULL,
    error_message TEXT NULL,
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_paper_id) REFERENCES generated_papers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    message VARCHAR(255) NOT NULL,
    category VARCHAR(80) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    reference_type VARCHAR(100) NULL,
    reference_id INT UNSIGNED NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO roles (name, slug, description) VALUES
('Administrator', 'admin', 'Full system access'),
('Principal', 'principal', 'Principal access'),
('Dean', 'dean', 'Dean access'),
('Controller of Examinations', 'coe', 'COE access'),
('Head of Department', 'hod', 'HOD access'),
('Faculty', 'faculty', 'Faculty access');

INSERT INTO question_types (name) VALUES
('Objective'),
('6 Marks'),
('12 Marks');

INSERT INTO question_units (name) VALUES
('Unit 1'),
('Unit 2'),
('Unit 3'),
('Unit 4'),
('Unit 5');
