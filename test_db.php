<?php
require_once './includes/cinfig.php';
try {
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) {
        echo "Database connection is working!";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>