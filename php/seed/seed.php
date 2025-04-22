<?php
require_once '../../includes/config.php';

try {
    // Insert a test user
    $username = 'mohamedayman';
    $email = 'mohamedayman@example.com';
    $password = password_hash('password123', PASSWORD_DEFAULT); // Hash the password

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $password]);

    echo "Test user created successfully! Username: mohamedayman, Email: mohamedayman@example.com, Password: password123\n";

    // Optionally, insert sample data for other tables
    // Insert a workout
    $stmt = $pdo->prepare("INSERT INTO workouts (user_id, workout_name, duration, calories_burned, workout_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([1, 'Morning Run', 30, 300, '2025-04-22']);

    // Insert a nutrition log
    $stmt = $pdo->prepare("INSERT INTO nutrition_logs (user_id, meal_name, calories, meal_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([1, 'Breakfast - Oatmeal', 400, '2025-04-22']);

    // Insert a progress log
    $stmt = $pdo->prepare("INSERT INTO progress_logs (user_id, weight, body_fat_percentage, log_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([1, 70.5, 15.5, '2025-04-22']);

    echo "Sample data inserted successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>