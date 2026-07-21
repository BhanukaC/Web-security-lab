CREATE DATABASE IF NOT EXISTS seclab;
USE seclab;

DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL
);

-- Legacy plaintext password column exists only so vulnerable.php keeps
-- working. Never store plaintext passwords outside a lab like this.
INSERT INTO users (username, password, password_hash) VALUES
  ('admin', 'adminpass', '$2y$10$WUffo80Zm7EFqJhzEmwCbOPlznZ9bjzhPWSsEJtZzDrwLxou.OVq6'),
  ('student1', 'student1pass', '$2y$10$Ml4mQ38M4rdM07wT3PTGMOmrq/KvpRkDjvI2CIHWNl7Lr9KF45.rm'),
  ('student2', 'student2pass', '$2y$10$oARIcBRMzjjGo852DHRxYuMtii/T7VOpBEQzDlvZfwMLsWplreS8.');

DROP TABLE IF EXISTS transfers;
CREATE TABLE transfers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender VARCHAR(50),
  receiver VARCHAR(50),
  amount DECIMAL(10,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS comments;
CREATE TABLE comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  body TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
