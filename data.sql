CREATE DATABASE IF NOT EXISTS labdb;
USE labdb;

DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

-- demo user (plain text for demo only)
INSERT INTO users (username, password) VALUES ('admin', 'adminpass');

DROP TABLE IF EXISTS transfers;
CREATE TABLE transfers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender VARCHAR(50),
  receiver VARCHAR(50),
  amount DECIMAL(10,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
