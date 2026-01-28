-- Database setup for Vegetable & Fruit Stock Management System

CREATE DATABASE IF NOT EXISTS vegetable_stock;
USE vegetable_stock;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('vegetable', 'fruit') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Items table
CREATE TABLE IF NOT EXISTS items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default categories
INSERT INTO categories (name, type) VALUES
('Leafy Greens', 'vegetable'),
('Root Vegetables', 'vegetable'),
('Cruciferous', 'vegetable'),
('Citrus Fruits', 'fruit'),
('Berries', 'fruit'),
('Tropical Fruits', 'fruit'),
('Stone Fruits', 'fruit');

-- Insert default admin (password: admin123)
INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$l3YQ6TeVXgHGrTsh7Gh.c.Y8jFGMJM7SCCpQ4zKpHTUFwCpyhI8Le');

-- Insert sample items
INSERT INTO items (name, category_id, quantity, price, description) VALUES
('Spinach', 1, 50, 2.99, 'Fresh organic spinach'),
('Carrots', 2, 100, 1.49, 'Crunchy orange carrots'),
('Broccoli', 3, 30, 3.49, 'Green broccoli florets'),
('Oranges', 4, 75, 0.99, 'Sweet navel oranges'),
('Strawberries', 5, 40, 4.99, 'Juicy red strawberries'),
('Bananas', 6, 60, 0.59, 'Yellow bananas'),
('Peaches', 7, 25, 2.49, 'Ripe yellow peaches');
