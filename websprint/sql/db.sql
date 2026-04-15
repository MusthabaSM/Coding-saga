-- Create database and tables for Web Sprint (websprint_db)
-- Run in phpMyAdmin or MySQL shell.

-- 1) Create database (if not exists)
CREATE DATABASE IF NOT EXISTS websprint_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE websprint_db;

-- 2) requests table: stores incoming client requests / enquiries
DROP TABLE IF EXISTS requests;
CREATE TABLE requests (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) NOT NULL,
  email VARCHAR(191) NOT NULL,
  phone VARCHAR(50) DEFAULT NULL,
  business VARCHAR(191) DEFAULT NULL,
  package VARCHAR(50) DEFAULT NULL,
  requirements TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (email),
  INDEX (package)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) orders table: stores advance orders/payments and links to requests
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  request_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(191) NOT NULL,
  email VARCHAR(191) NOT NULL,
  business VARCHAR(191) DEFAULT NULL,
  package VARCHAR(50) NOT NULL,
  amount INT NOT NULL COMMENT 'Amount stored = advance paid (in INR units)',
  currency VARCHAR(10) NOT NULL DEFAULT 'INR',
  gateway VARCHAR(50) NOT NULL DEFAULT 'razorpay',
  razorpay_order_id VARCHAR(100) DEFAULT NULL,
  base_price_info VARCHAR(100) DEFAULT NULL COMMENT 'Informational: "Starts from ₹X"',
  final_price INT DEFAULT NULL COMMENT 'Final negotiated price (nullable until set)',
  status ENUM('pending','paid','failed','cancelled') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (request_id),
  INDEX (email),
  INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Foreign key: orders.request_id -> requests.id
ALTER TABLE orders
  ADD CONSTRAINT fk_orders_request
  FOREIGN KEY (request_id) REFERENCES requests(id)
  ON DELETE SET NULL
  ON UPDATE CASCADE;
  ALTER TABLE orders ADD COLUMN phone VARCHAR(32) DEFAULT NULL;


-- 5) Optional: admin_activity table to record admin actions (log)
DROP TABLE IF EXISTS admin_activity;
CREATE TABLE admin_activity (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  admin_user VARCHAR(100) NOT NULL,
  action VARCHAR(255) NOT NULL,
  target_type VARCHAR(50) DEFAULT NULL,
  target_id INT DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (admin_user),
  INDEX (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) Seed sample data (optional). Uncomment if you want quick test rows.

-- INSERT INTO requests (name,email,phone,business,package,requirements)
-- VALUES ('Test Client','client@example.com','9876543210','Salon','Basic','Need single page with contact and gallery');

-- INSERT INTO orders (request_id,name,email,business,package,amount,currency,gateway,status,base_price_info)
-- VALUES (1,'Test Client','client@example.com','Salon','Basic',200,'INR','razorpay','pending','Starts from ₹1,999');

-- End of db.sql