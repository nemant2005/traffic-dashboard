CREATE DATABASE IF NOT EXISTS traffic_dashboard;
USE traffic_dashboard;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    password VARCHAR(255)
);

CREATE TABLE traffic_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(255),
    date DATE,
    time TIME,
    severity ENUM('Low', 'Moderate', 'High'),
    description TEXT
);

CREATE TABLE alert_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    severity_threshold ENUM('Low', 'Moderate', 'High'),
    email VARCHAR(255)
);
