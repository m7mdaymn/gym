<?php


// Include database connection
require_once '../../includes/config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

// Muscle group icons (using Font Awesome)
$muscle_icons = [
    'Chest' => 'fa-heart',
    'Legs' => 'fa-running',
    'Back' => 'fa-dumbbell',
    'Arms' => 'fa-hand-fist',
    'Core' => 'fa-person'
];

// Fetch user’s workout plans, grouped by template_name
try {
    $stmt = $pdo->prepare("SELECT * FROM templates WHERE user_id = ? ORDER BY template_name, id");
    $stmt->execute([$_SESSION['user_id']]);
    $plans = $stmt->fetchAll();

    $grouped_plans = [];
    foreach ($plans as $plan) {
        $grouped_plans[$plan['template_name']][] = $plan;
    }
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Plan - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../assets/css/global.css" rel="stylesheet">
</head>
<body>
    <!-- Include Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <!-- Workout Plan Section -->
    <div class="store-section">
        <div class="container">
            <h2 class="text-center mb-4">Choose a Workout Plan</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <!-- Workout Plans List -->
            <?php if (!empty($grouped_plans)): ?>
                <div class="row">
                    <?php foreach ($grouped_plans as $template_name => $exercises): ?>
                        <div class="col-md-4 mb-4">
                            <div class="plan-section">
                                <h4 class="plan-title"><?php echo htmlspecialchars($template_name); ?></h4>
                                <div class="exercise-list">
                                    <?php foreach ($exercises as $exercise): ?>
                                        <div class="exercise-card">
                                            <div class="exercise-header">
                                                <i class="fas <?php echo $muscle_icons[$exercise['muscle_group']] ?? 'fa-dumbbell'; ?> me-2"></i>
                                                <span><?php echo htmlspecialchars($exercise['exercise']); ?></span>
                                            </div>
                                            <div class="exercise-details">
                                                <p><strong>Weight:</strong> <?php echo number_format($exercise['weight'], 2); ?> kg</p>
                                                <p><strong>Reps:</strong> <?php echo $exercise['reps']; ?></p>
                                                <p><strong>Sets:</strong> <?php echo $exercise['sets']; ?></p>
                                                <p><strong>RPE:</strong> <?php echo $exercise['rpe'] ?? 'N/A'; ?></p>
                                                <p><strong>Muscle Group:</strong> <?php echo htmlspecialchars($exercise['muscle_group'] ?? 'N/A'); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <form method="POST" action="log_workout.php">
                                    <input type="hidden" name="action" value="log_workout">
                                    <?php foreach ($exercises as $exercise): ?>
                                        <input type="hidden" name="exercise" value="<?php echo htmlspecialchars($exercise['exercise']); ?>">
                                        <input type="hidden" name="weight" value="<?php echo $exercise['weight']; ?>">
                                        <input type="hidden" name="reps" value="<?php echo $exercise['reps']; ?>">
                                        <input type="hidden" name="sets" value="<?php echo $exercise['sets']; ?>">
                                        <input type="hidden" name="rpe" value="<?php echo $exercise['rpe']; ?>">
                                        <input type="hidden" name="muscle_group" value="<?php echo htmlspecialchars($exercise['muscle_group']); ?>">
                                    <?php break; // Only log the first exercise for simplicity ?>
                                    <?php endforeach; ?>
                                    <button type="submit" class="btn btn-primary w-100 mt-2">Use This Plan</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center">No workout plans yet. Create one in the Log Workout page!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .plan-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
        }

        .plan-title {
            color: #00ddeb;
            margin-bottom: 1rem;
        }

        .exercise-card {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            color: #ffffff;
        }

        .exercise-header {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .exercise-details p {
            margin: 0;
            font-size: 0.9rem;
        }
    </style>
</body>
</html>