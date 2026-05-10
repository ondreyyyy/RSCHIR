CREATE DATABASE IF NOT EXISTS weather CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE weather;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS weather (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    temperature FLOAT,
    description VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO weather (city, temperature, description) VALUES
    ('Москва', 15.2, 'Ясно'),
    ('Санкт-Петербург', 12.8, 'Облачно'),
    ('Новосибирск', 8.5, 'Дождь');

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Пароль: adminpass, хэш в Apache-compatible SHA-формате
INSERT INTO users (username, password) VALUES ('admin', '{SHA}dJE/XNX2HsC8/bd1QUwvs9FhtiA=');
