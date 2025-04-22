<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Check if the request is AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    echo json_encode(['success' => true, 'message' => 'Logged out successfully! Redirecting...', 'redirect' => 'http://localhost/gym/index.php']);
    exit;
} else {
    header('Location: http://localhost/gym/index.php');
    exit;
}
?>