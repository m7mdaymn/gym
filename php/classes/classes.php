<?php

// Include database connection
require_once '../../includes/config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

// Fetch available classes
try {
    $stmt = $pdo->prepare("SELECT c.*, t.name as trainer_name FROM classes c LEFT JOIN trainers t ON c.trainer_id = t.id WHERE c.schedule > NOW()");
    $stmt->execute();
    $classes = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../assets/css/global.css" rel="stylesheet">
</head>
<body>
    <!-- Include Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <!-- Classes Section -->
    <div class="store-section" style="margin-top: 100px;">
        <div class="container">
            <h2 class="text-center mb-4">Available Classes</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($classes)): ?>
                <div class="row">
                    <?php foreach ($classes as $class): ?>
                        <div class="col-md-4 mb-4">
                            <div class="class-card">
                                <h5><?php echo htmlspecialchars($class['class_name']); ?></h5>
                                <p><strong>Schedule:</strong> <?php echo $class['schedule']; ?></p>
                                <p><strong>Trainer:</strong> <?php echo htmlspecialchars($class['trainer_name'] ?? 'TBD'); ?></p>
                                <p><?php echo htmlspecialchars($class['description'] ?? 'No description available.'); ?></p>
                                <a href="reservations.php" class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center">No classes available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .class-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            color: #ffffff;
        }

        .class-card h5 {
            color: #00ddeb;
            margin-bottom: 1rem;
        }
    </style>
</body>
</html>