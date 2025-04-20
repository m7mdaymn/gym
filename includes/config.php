<?php
$host = 'localhost';
$db = 'fitness_tracker';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;port=3307;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>