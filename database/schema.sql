SET NAMES utf8;
CREATE DATABASE IF NOT EXISTS personnel_tracking CHARACTER SET utf8 COLLATE utf8_general_ci;
USE personnel_tracking;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'employee') DEFAULT 'employee',
    hourly_rate DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Vardiya tanımları
CREATE TABLE IF NOT EXISTS shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Katılım/Zaman takibi tablosu
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    shift_id INT,
    clock_in TIME,
    clock_out TIME,
    status ENUM('present', 'late', 'absent', 'excused', 'holiday') DEFAULT 'present',
    is_late BOOLEAN DEFAULT FALSE,
    overtime_hours DECIMAL(5, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Sistem ayarları
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Cari/Tedarikçi/Müşteri Tablosu (FAZ 1.1)
CREATE TABLE IF NOT EXISTS inv_entities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('supplier', 'customer', 'both', 'staff') DEFAULT 'supplier',
    tax_id VARCHAR(50),
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    balance DECIMAL(15,4) DEFAULT 0.0000 COMMENT 'Artı bakiye alacak, eksi bakiye borç',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tax_id (tax_id),
    INDEX idx_type (type),
    INDEX idx_balance (balance)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Cari Hareketler Tablosu / Cari Ekstresi (FAZ 1.2)
CREATE TABLE IF NOT EXISTS inv_entity_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_id INT NOT NULL,
    type ENUM('fatura', 'tahsilat', 'odeme', 'iade', 'diger') DEFAULT 'fatura',
    amount DECIMAL(15,4) NOT NULL,
    description TEXT,
    transaction_date DATE NOT NULL,
    document_no VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entity_id) REFERENCES inv_entities(id) ON DELETE CASCADE,
    INDEX idx_entity_date (entity_id, transaction_date),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Başlangıç verileri
INSERT INTO shifts (name, start_time, end_time) VALUES 
('Sabah (A)', '09:00:00', '17:00:00'),
('Akşam (B)', '17:00:00', '01:00:00'),
('Gece (C)', '01:00:00', '09:00:00');

INSERT INTO users (full_name, role, hourly_rate) VALUES 
('Sistem Yöneticisi', 'admin', 0),
('Ahmet Yılmaz', 'employee', 150.00),
('Ayşe Kaya', 'employee', 160.00);

-- Örnek Katılım Verileri
INSERT INTO attendance (user_id, date, shift_id, clock_in, clock_out, status, is_late) VALUES
(2, CURDATE(), 1, '09:00:00', '17:00:00', 'present', 0),
(3, CURDATE(), 1, '09:15:00', '17:00:00', 'late', 1);
