<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

// Simulated workout data (replace with database query)
$workouts = isset($_SESSION['workouts']) ? $_SESSION['workouts'] : [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exercise = trim($_POST['exercise'] ?? '');
    $weight = floatval($_POST['weight'] ?? 0);
    $reps = intval($_POST['reps'] ?? 0);
    $sets = intval($_POST['sets'] ?? 0);
    $date = date('Y-m-d H:i:s');

    if (!empty($exercise) && $weight > 0 && $reps > 0 && $sets > 0) {
        $workouts[] = [
            'exercise' => $exercise,
            'weight' => $weight,
            'reps' => $reps,
            'sets' => $sets,
            'date' => $date,
            'user_id' => $_SESSION['user_id']
        ];
        $_SESSION['workouts'] = $workouts;
        $success = 'Workout logged successfully!';
    } else {
        $error = 'Please fill in all fields with valid values.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Workout - Fitness App</title>
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

        .workout-container {
            background: #1a1a1a;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            max-width: 600px;
            margin: 0 auto;
            color: #ffffff;
        }

        .workout-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #cccccc;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #444444;
            border-radius: 5px;
            background: #2a2a2a;
            color: #ffffff;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        .btn-primary {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(90deg, #007bff, #0056b3);
            border: none;
            border-radius: 5px;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #0056b3, #003d82);
        }

        .alert {
            margin-bottom: 1.2rem;
            padding: 0.75rem;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #28a745;
            color: #ffffff;
        }

        .alert-danger {
            background: #dc3545;
            color: #ffffff;
        }

        .workout-history {
            margin-top: 2rem;
        }

        .workout-history h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .workout-history table {
            width: 100%;
            color: #ffffff;
            background: #2a2a2a;
            border-radius: 5px;
            overflow: hidden;
        }

        .workout-history th,
        .workout-history td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #444444;
        }

        .workout-history th {
            background: #343a40;
        }

        @media (max-width: 576px) {
            .workout-container {
                padding: 1.5rem;
                max-width: 90%;
            }

            .workout-history table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="workout-container">
        <h2>Log Your Workout</h2>

        <!-- Display Messages -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <!-- Workout Form -->
        <form method="POST">
            <div class="form-group">
                <label for="exercise">Exercise</label>
                <select id="exercise" name="exercise" required>
                    <option value="">Select an exercise</option>
                    <option value="Bench Press">Bench Press</option>
                    <option value="Squat">Squat</option>
                    <option value="Deadlift">Deadlift</option>
                    <option value="Pull-Up">Pull-Up</option>
                    <option value="Push-Up">Push-Up</option>
                </select>
            </div>
            <div class="form-group">
                <label for="weight">Weight (kg)</label>
                <input type="number" id="weight" name="weight" step="0.1" min="0" required>
            </div>
            <div class="form-group">
                <label for="reps">Reps</label>
                <input type="number" id="reps" name="reps" min="1" required>
            </div>
            <div class="form-group">
                <label for="sets">Sets</label>
                <input type="number" id="sets" name="sets" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Log Workout</button>
        </form>

        <!-- Workout History -->
        <?php if (!empty($workouts)): ?>
            <div class="workout-history">
                <h3>Your Workout History</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Exercise</th>
                            <th>Weight (kg)</th>
                            <th>Reps</th>
                            <th>Sets</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workouts as $workout): ?>
                            <?php if ($workout['user_id'] === $_SESSION['user_id']): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($workout['exercise']); ?></td>
                                    <td><?php echo htmlspecialchars($workout['weight']); ?></td>
                                    <td><?php echo htmlspecialchars($workout['reps']); ?></td>
                                    <td><?php echo htmlspecialchars($workout['sets']); ?></td>
                                    <td><?php echo htmlspecialchars($workout['date']); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>