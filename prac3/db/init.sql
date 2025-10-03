CREATE DATABASE IF NOT EXISTS electronics CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE electronics;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO products (name, description, price) VALUES
('Smartphone X1', 'Современный смартфон с OLED', 499.00),
('Laptop Pro 15', 'Ноутбук для разработчиков', 1299.00),
('Wireless Headphones Z', 'Шумоподавляющие наушники', 199.00);

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash CHAR(64) NOT NULL, -- SHA256 hex
  role VARCHAR(50) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- пароль admin: adminpass (sha256)
INSERT INTO users (username, password_hash, role) VALUES
('admin', SHA2('adminpass',256), 'admin'),
('editor', SHA2('editorpass',256), 'editor');
