-- Create the database
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

-- Workouts table (for log_workout.php)
CREATE TABLE workouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exercise VARCHAR(100) NOT NULL,
    weight DECIMAL(6, 2) NOT NULL, -- e.g., 50.50 kg
    reps INT NOT NULL,
    sets INT NOT NULL,
    rpe INT CHECK (rpe BETWEEN 1 AND 10),
    workout_date DATETIME NOT NULL,
    muscle_group VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Custom exercises table (for log_workout.php)
CREATE TABLE custom_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    muscle_group VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Templates table (for log_workout.php and workout_plan.php)
CREATE TABLE templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    template_name VARCHAR(100) NOT NULL,
    exercise VARCHAR(100) NOT NULL,
    weight DECIMAL(6, 2) NOT NULL,
    reps INT NOT NULL,
    sets INT NOT NULL,
    rpe INT CHECK (rpe BETWEEN 1 AND 10),
    muscle_group VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Nutrition logs table (for meal_plan.php)
CREATE TABLE nutrition_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    meal_name VARCHAR(100) NOT NULL,
    calories INT NOT NULL,
    fat DECIMAL(5, 2) DEFAULT 0,
    protein DECIMAL(5, 2) DEFAULT 0,
    carbs DECIMAL(5, 2) DEFAULT 0,
    minerals DECIMAL(5, 2) DEFAULT 0,
    meal_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Nutrition goals table (for meal_plan.php)
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
    UNIQUE KEY unique_user_goal_date (user_id, goal_date)
);

-- Progress logs table (for progress.php)
CREATE TABLE progress_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    weight DECIMAL(5, 2), -- e.g., 70.50 kg
    body_fat_percentage DECIMAL(4, 1), -- e.g., 15.5%
    log_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Goals table (for progress.php)
CREATE TABLE goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    goal_type ENUM('weight', 'calories', 'total_weight_lifted') NOT NULL,
    target_value DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_goal_type (user_id, goal_type)
);

-- Trainers table (for trainers.php)
CREATE TABLE trainers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    expertise VARCHAR(255),
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Trainer availability table (for trainers.php)
CREATE TABLE trainer_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    available_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE CASCADE
);

-- Bookings table (for trainers.php)
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trainer_id INT NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE CASCADE
);

-- Trainer reviews table (for trainers.php)
CREATE TABLE trainer_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trainer_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE CASCADE
);

-- Classes table (for group classes)
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL,
    description TEXT,
    schedule DATETIME NOT NULL,
    trainer_id INT,
    capacity INT NOT NULL DEFAULT 20,
    current_attendees INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE SET NULL
);

-- Reservations table
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    class_id INT,
    trainer_id INT,
    reservation_type ENUM('class', 'session') NOT NULL,
    reservation_date DATETIME NOT NULL,
    status ENUM('confirmed', 'waitlisted', 'cancelled') DEFAULT 'confirmed',
    session_focus VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE SET NULL
);

-- Memberships table (corrected)
CREATE TABLE memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Fixed from 'physicist' to 'user_id'
    plan_name VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'refunded', 'expired') DEFAULT 'active',
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

-- Orders table (updated version)
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

-- Order items table (updated version)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample data for products
INSERT INTO products (product_name, description, price, stock, created_at) VALUES
('Protein Powder', 'High-quality whey protein powder, 1kg, chocolate flavor.', 29.99, 50, NOW()),
('Dumbbell Set', 'Adjustable dumbbell set, 5-25kg, perfect for home workouts.', 89.99, 20, NOW()),
('Yoga Mat', 'Non-slip yoga mat, 6mm thickness, black.', 19.99, 100, NOW()),
('Resistance Bands', 'Set of 5 resistance bands with varying strengths.', 15.99, 75, NOW()),
('Gym Gloves', 'Padded gym gloves for weightlifting, size M.', 12.99, 60, NOW());

-- Insert sample data for trainers
INSERT INTO trainers (name, email, expertise, bio) VALUES
('mohamed ayman', 'mohamed@example.com', 'Strength Training', 'Certified trainer with 10 years of experience in strength training.'),
('mohamed mahmoud', 'mahmoud@example.com', 'Nutrition & Weight Loss', 'Specializes in nutrition plans and weight loss programs.'),
('zeka', 'mike.zeka@example.com', 'Yoga & Flexibility', 'Expert in yoga and improving flexibility with 8 years of experience.');

-- Insert sample availability for trainers
INSERT INTO trainer_availability (trainer_id, available_date, start_time, end_time) VALUES
(1, '2025-04-25', '09:00:00', '10:00:00'),
(1, '2025-04-25', '10:00:00', '11:00:00'),
(2, '2025-04-25', '14:00:00', '15:00:00'),
(3, '2025-04-26', '08:00:00', '09:00:00');

-- Insert sample data for classes
INSERT INTO classes (class_name, description, schedule, trainer_id, capacity) VALUES
('Yoga Session', 'A relaxing yoga session for all levels.', '2025-04-26 10:00:00', 1, 2),
('Strength Training', 'Build muscle with this intense workout.', '2025-04-27 15:00:00', 2, 20);

-- Indexes for faster lookups
CREATE INDEX idx_reservations_user_id ON reservations(user_id);
CREATE INDEX idx_reservations_trainer_id ON reservations(trainer_id);
CREATE INDEX idx_reservations_class_id ON reservations(class_id);
CREATE INDEX idx_reservations_date ON reservations(reservation_date);
CREATE INDEX idx_trainer_availability_trainer_date ON trainer_availability(trainer_id, available_date);
CREATE INDEX idx_classes_schedule ON classes(schedule);


ALTER TABLE reservations ADD COLUMN priority INT DEFAULT 0 AFTER session_focus;

INSERT INTO trainer_availability (trainer_id, available_date, start_time, end_time) 
VALUES (1, '2025-04-25', '09:00:00', '12:00:00'),
       (1, '2025-04-25', '14:00:00', '17:00:00'),
       (2, '2025-04-25', '10:00:00', '13:00:00');