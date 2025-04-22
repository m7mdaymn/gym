<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

$workouts = isset($_SESSION['workouts']) ? $_SESSION['workouts'] : [];
$totalWeight = 0;
$totalReps = 0;
$count = 0;

foreach ($workouts as $workout) {
    if ($workout['user_id'] === $_SESSION['user_id']) {
        $totalWeight += $workout['weight'] * $workout['reps'] * $workout['sets'];
        $totalReps += $workout['reps'] * $workout['sets'];
        $count++;
    }
}

$averageReps = $count > 0 ? $totalReps / $count : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Stats - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: url('../../assets/image/gym-background.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            padding: 20px;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: -1;
        }

        .stats-container {
            background: #1a1a1a;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            max-width: 600px;
            margin: 0 auto;
            color: #ffffff;
        }

        .stats-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .stat {
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .stat label {
            font-weight: 500;
            color: #cccccc;
        }

        .stat span {
            float: right;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="stats-container">
        <h2>Your Progress Stats</h2>
        <div class="stat">
            <label>Total Weight Lifted:</label>
            <span><?php echo number_format($totalWeight, 2); ?> kg</span>
        </div>
        <div class="stat">
            <label>Total Reps:</label>
            <span><?php echo $totalReps; ?></span>
        </div>
        <div class="stat">
            <label>Average Reps per Session:</label>
            <span><?php echo number_format($averageReps, 2); ?></span>
        </div>
        <a href="log_workout.php" class="btn btn-primary w-100 mt-3">Log Another Workout</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>