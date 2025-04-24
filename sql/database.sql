-- Create the database (already done in phpMyAdmin, but included for completeness)
CREATE DATABASE IF NOT EXISTS fitness_app;
USE fitness_app;

-- Users table (for login/register)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



-- Create a new workouts table that matches log_workout.php data
CREATE TABLE workouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exercise VARCHAR(100) NOT NULL,
    weight DECIMAL(6, 2) NOT NULL, -- e.g., 50.50 kg
    reps INT NOT NULL,
    sets INT NOT NULL,
    rpe INT,
    workout_date DATETIME NOT NULL,
    muscle_group VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create table for custom exercises
CREATE TABLE custom_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    muscle_group VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create table for templates
CREATE TABLE templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    template_name VARCHAR(100) NOT NULL,
    exercise VARCHAR(100) NOT NULL,
    weight DECIMAL(6, 2) NOT NULL,
    reps INT NOT NULL,
    sets INT NOT NULL,
    rpe INT,
    muscle_group VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Nutrition logs table (for nutrition tracking)
CREATE TABLE nutrition_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    meal_name VARCHAR(100) NOT NULL,
    calories INT NOT NULL,
    meal_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Progress logs table (for progress tracking)
CREATE TABLE progress_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    weight DECIMAL(5, 2), -- e.g., 70.50 kg
    body_fat_percentage DECIMAL(4, 1), -- e.g., 15.5%
    log_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Classes table (for group classes)
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL,
    description TEXT,
    schedule DATETIME NOT NULL,
    trainer_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Trainers table (for trainers section)
CREATE TABLE trainers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Memberships table (for membership plans)
CREATE TABLE memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_name VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Products table (for e-commerce/shop section)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table (for e-commerce purchases)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table (for items in each order)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- here is data for the store -- 
INSERT INTO products (product_name, description, price, stock, created_at) VALUES
('Protein Powder', 'High-quality whey protein powder, 1kg, chocolate flavor.', 29.99, 50, NOW()),
('Dumbbell Set', 'Adjustable dumbbell set, 5-25kg, perfect for home workouts.', 89.99, 20, NOW()),
('Yoga Mat', 'Non-slip yoga mat, 6mm thickness, black.', 19.99, 100, NOW()),
('Resistance Bands', 'Set of 5 resistance bands with varying strengths.', 15.99, 75, NOW()),
('Gym Gloves', 'Padded gym gloves for weightlifting, size M.', 12.99, 60, NOW());


-- changes --
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);



CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    class_id INT,
    trainer_id INT,
    reservation_type ENUM('class', 'session') NOT NULL,
    reservation_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE SET NULL
);
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
INSERT INTO trainers (name, email, bio) VALUES
('John Doe', 'john.doe@example.com', 'Certified trainer with 10 years of experience.'),
('Jane Smith', 'jane.smith@example.com', 'Specializes in yoga and strength training.');

INSERT INTO classes (class_name, description, schedule, trainer_id) VALUES
('Yoga Session', 'A relaxing yoga session for all levels.', '2025-04-26 10:00:00', 1),
('Strength Training', 'Build muscle with this intense workout.', '2025-04-27 15:00:00', 2);



ALTER TABLE memberships
    ADD COLUMN status ENUM('active', 'refunded', 'expired') DEFAULT 'active' AFTER end_date;
    
ALTER TABLE classes
    ADD COLUMN capacity INT NOT NULL DEFAULT 20,
    ADD COLUMN current_attendees INT NOT NULL DEFAULT 0;

ALTER TABLE reservations
    ADD COLUMN status ENUM('confirmed', 'waitlisted', 'cancelled') DEFAULT 'confirmed',
    ADD COLUMN session_focus VARCHAR(100);

CREATE TABLE goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    goal_type ENUM('weight', 'calories', 'total_weight_lifted') NOT NULL,
    target_value DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
UPDATE classes SET capacity = 2 WHERE id = 1;


ALTER TABLE nutrition_logs
    ADD COLUMN fat DECIMAL(5, 2) DEFAULT 0,
    ADD COLUMN protein DECIMAL(5, 2) DEFAULT 0,
    ADD COLUMN carbs DECIMAL(5, 2) DEFAULT 0,
    ADD COLUMN minerals DECIMAL(5, 2) DEFAULT 0;

CREATE TABLE nutrition_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    goal_date DATE NOT NULL,
    calories INT NOT NULL,
    fat DECIMAL(5, 2) NOT NULL,
    protein DECIMAL(5, 2) NOT NULL,
    carbs DECIMAL(5, 2) NOT NULL,
    minerals DECIMAL(5, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, goal_date)
);
