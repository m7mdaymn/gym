<?php
session_start();
require_once 'C:\xampp\htdocs\fitness_app\includes\config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('Received POST data: ' . print_r($_POST, true));

    if (empty($_POST)) {
        $rawData = file_get_contents('php://input');
        error_log('Raw POST data: ' . $rawData);
        parse_str($rawData, $_POST);
    }

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    error_log("Extracted Data - Username: $username, Email: $email, Password: [hidden]");

    // Validation
    $errors = [];

    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Email already exists.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error during email check: " . $e->getMessage();
            error_log("Email check failed: " . $e->getMessage());
        }
    }

    if (empty($password) || strlen($password) < 3) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);

            $user_id = $pdo->lastInsertId();

            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;

            error_log("User registered successfully: ID $user_id, Username $username");

            echo json_encode(['success' => true, 'message' => 'Registration successful! Redirecting to login...']);
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database error during registration: " . $e->getMessage();
            error_log("Registration failed: " . $e->getMessage());
        }
    }

    echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method. Use POST.']);
exit;
?>