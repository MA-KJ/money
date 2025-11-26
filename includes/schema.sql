-- Loan Tracking System Database Schema
-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS loan_tracking_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE loan_tracking_system;

-- Users table for authentication and user management
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    password_reset_token VARCHAR(255) DEFAULT NULL,
    password_reset_expires TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Loans table for tracking all loan information
CREATE TABLE IF NOT EXISTS loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    borrower_name VARCHAR(100) NOT NULL,
    borrower_phone VARCHAR(20) DEFAULT NULL,
    borrower_email VARCHAR(100) DEFAULT NULL,
    borrower_address TEXT DEFAULT NULL,
    loan_amount DECIMAL(15,2) NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    duration_days INT NOT NULL,
    total_payable DECIMAL(15,2) NOT NULL,
    start_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('unpaid', 'paid', 'overdue', 'partially_paid') NOT NULL DEFAULT 'unpaid',
    amount_paid DECIMAL(15,2) DEFAULT 0.00,
    date_paid TIMESTAMP NULL,
    notes TEXT DEFAULT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_due_date (due_date),
    INDEX idx_start_date (start_date),
    INDEX idx_borrower_name (borrower_name)
);

-- Payments table for tracking partial payments
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_loan_id (loan_id),
    INDEX idx_payment_date (payment_date)
);

-- Loan history table for tracking changes
CREATE TABLE IF NOT EXISTS loan_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    action ENUM('created', 'updated', 'paid', 'deleted', 'payment_added') NOT NULL,
    old_values JSON DEFAULT NULL,
    new_values JSON DEFAULT NULL,
    performed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_loan_id (loan_id),
    INDEX idx_action (action)
);

-- Settings table for application configuration
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description TEXT DEFAULT NULL,
    updated_by INT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default super admin user (password: admin123)
-- You should change this password after first login
INSERT INTO users (username, email, password_hash, full_name, role, status) 
VALUES ('admin', 'admin@loansystem.com', '$2y$10$za9n8k6/x78MSCeJ0MCDW.cy38yxziR4tNzt3Y8ft8gWYP5bAHsoa', 'System Administrator', 'super_admin', 'active')
ON DUPLICATE KEY UPDATE id=id;

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'Loan Tracking System', 'Website name'),
('default_interest_rate', '10.00', 'Default interest rate percentage'),
('currency_symbol', 'K', 'Currency symbol'),
('date_format', 'Y-m-d', 'Date display format'),
('timezone', 'UTC', 'System timezone')
ON DUPLICATE KEY UPDATE setting_key=setting_key;

-- Indexes already created in table definitions above

-- Create a view for loan statistics
CREATE OR REPLACE VIEW loan_statistics AS
SELECT 
    YEAR(start_date) as year,
    MONTH(start_date) as month,
    COUNT(*) as total_loans,
    SUM(loan_amount) as total_amount_lent,
    SUM(CASE WHEN status = 'paid' THEN total_payable - loan_amount ELSE 0 END) as total_interest_earned,
    SUM(CASE WHEN status = 'paid' THEN amount_paid ELSE 0 END) as total_amount_repaid,
    COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_loans,
    COUNT(CASE WHEN status = 'unpaid' THEN 1 END) as unpaid_loans,
    COUNT(CASE WHEN status = 'overdue' THEN 1 END) as overdue_loans
FROM loans 
GROUP BY YEAR(start_date), MONTH(start_date)
ORDER BY year DESC, month DESC;
