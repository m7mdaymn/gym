<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Include database connection
require_once '../../includes/config.php';

// Check if the user is logged in and exists in the database
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

// Verify the user exists in the database
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        $error = "User does not exist in the database. Please register or log in again.";
        unset($_SESSION['user_id']); // Clear invalid session
        header('Location: ../auth/login/login.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle date range selection
$start_date = $_POST['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_POST['end_date'] ?? date('Y-m-d');

// Fetch workout progress (total weight lifted over time)
$workout_progress = [];
$total_weight_lifted = 0;
$workout_labels = '[]';
$workout_data = '[]';
$workout_progress_percentage = 0;

if (!isset($error)) {
    try {
        $stmt = $pdo->prepare("SELECT DATE(workout_date) as date, SUM(weight * reps * sets) as total_weight_lifted
                               FROM workouts
                               WHERE user_id = ? AND workout_date BETWEEN ? AND ?
                               GROUP BY DATE(workout_date)
                               ORDER BY workout_date ASC");
        $stmt->execute([$_SESSION['user_id'], $start_date, $end_date]);
        $workout_progress = $stmt->fetchAll();

        $total_weight_lifted = array_sum(array_column($workout_progress, 'total_weight_lifted'));
        $workout_labels = json_encode(array_column($workout_progress, 'date'));
        $workout_data = json_encode(array_column($workout_progress, 'total_weight_lifted'));

        $first_workout = $workout_progress[0]['total_weight_lifted'] ?? 0;
        $last_workout = end($workout_progress)['total_weight_lifted'] ?? 0;
        $workout_improvement = $last_workout > 0 && $first_workout > 0 ? (($last_workout - $first_workout) / $first_workout * 100) : 0;
        $workout_progress_percentage = min(100, max(0, $workout_improvement));
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Fetch body weight progress
$weight_progress = [];
$weight_labels = '[]';
$weight_data = '[]';
$weight_progress_percentage = 0;

if (!isset($error)) {
    try {
        $stmt = $pdo->prepare("SELECT log_date, weight
                               FROM progress_logs
                               WHERE user_id = ? AND log_date BETWEEN ? AND ?
                               ORDER BY log_date ASC");
        $stmt->execute([$_SESSION['user_id'], $start_date, $end_date]);
        $weight_progress = $stmt->fetchAll();

        $weight_labels = json_encode(array_column($weight_progress, 'log_date'));
        $weight_data = json_encode(array_column($weight_progress, 'weight'));

        $first_weight = $weight_progress[0]['weight'] ?? 0;
        $last_weight = end($weight_progress)['weight'] ?? 0;
        $weight_change = $first_weight > 0 ? (($first_weight - $last_weight) / $first_weight * 100) : 0;
        $weight_progress_percentage = min(100, max(0, $weight_change * 2));
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Fetch nutrition progress (average daily calories over time)
$nutrition_progress = [];
$nutrition_labels = '[]';
$nutrition_data = '[]';
$nutrition_progress_percentage = 0;

if (!isset($error)) {
    try {
        $stmt = $pdo->prepare("SELECT meal_date, AVG(calories) as avg_calories
                               FROM nutrition_logs
                               WHERE user_id = ? AND meal_date BETWEEN ? AND ?
                               GROUP BY meal_date
                               ORDER BY meal_date ASC");
        $stmt->execute([$_SESSION['user_id'], $start_date, $end_date]);
        $nutrition_progress = $stmt->fetchAll();

        $nutrition_labels = json_encode(array_column($nutrition_progress, 'meal_date'));
        $nutrition_data = json_encode(array_column($nutrition_progress, 'avg_calories'));

        $first_calories = $nutrition_progress[0]['avg_calories'] ?? 0;
        $last_calories = end($nutrition_progress)['avg_calories'] ?? 0;
        $calorie_improvement = $first_calories > 0 ? (($last_calories - $first_calories) / $first_calories * 100) : 0;
        $nutrition_progress_percentage = min(100, max(0, $calorie_improvement));
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Fetch muscle group volume
$muscle_group_volume = [];
$muscle_labels = '[]';
$muscle_data = '[]';

if (!isset($error)) {
    try {
        $stmt = $pdo->prepare("SELECT muscle_group, SUM(weight * reps * sets) as volume
                               FROM workouts
                               WHERE user_id = ? AND workout_date BETWEEN ? AND ?
                               GROUP BY muscle_group");
        $stmt->execute([$_SESSION['user_id'], $start_date, $end_date]);
        $muscle_group_volume = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $muscle_labels = json_encode(array_column($muscle_group_volume, 'muscle_group'));
        $muscle_data = json_encode(array_column($muscle_group_volume, 'volume'));
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Fetch user goals
$goals = [];
$goals_by_type = [];

if (!isset($error)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM goals WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $goals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $goals_by_type = array_column($goals, null, 'goal_type');
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Handle goal setting
$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'set_goal' && !isset($error)) {
    $goal_type = $_POST['goal_type'] ?? '';
    $target_value = floatval($_POST['target_value'] ?? 0);

    if (!$goal_type) {
        $error = 'Goal type is missing. Please try again.';
    } elseif ($target_value <= 0) {
        $error = 'Please enter a positive target value greater than 0.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO goals (user_id, goal_type, target_value) 
                                   VALUES (?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE target_value = ?");
            $stmt->execute([$_SESSION['user_id'], $goal_type, $target_value, $target_value]);
            $success = 'Goal set successfully!';
            header('Location: progress.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error setting goal: ' . $e->getMessage();
        }
    }
}

// Calculate achievements
$achievements = [];
if ($total_weight_lifted >= 1000) {
    $achievements[] = ['name' => 'Heavy Lifter', 'description' => 'Lifted 1000kg in total!', 'icon' => 'fa-dumbbell'];
}
if (count($nutrition_progress) >= 30) {
    $achievements[] = ['name' => 'Nutrition Master', 'description' => 'Logged nutrition for 30 days!', 'icon' => 'fa-apple-whole'];
}
if ($weight_change >= 5) {
    $achievements[] = ['name' => 'Weight Warrior', 'description' => 'Lost 5% of your starting weight!', 'icon' => 'fa-weight-scale'];
}

// Calculate goal progress
$weight_goal_progress = isset($goals_by_type['weight']) ? min(100, ($last_weight / $goals_by_type['weight']['target_value']) * 100) : 0;
$calories_goal_progress = isset($goals_by_type['calories']) ? min(100, ($last_calories / $goals_by_type['calories']['target_value']) * 100) : 0;
$weight_lifted_goal_progress = isset($goals_by_type['total_weight_lifted']) ? min(100, ($total_weight_lifted / $goals_by_type['total_weight_lifted']['target_value']) * 100) : 0;

// Calculate workout consistency (number of workout days)
$consistency = 0;
$consistency_percentage = 0;

if (!isset($error)) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT DATE(workout_date)) as workout_days
                               FROM workouts
                               WHERE user_id = ? AND workout_date BETWEEN ? AND ?");
        $stmt->execute([$_SESSION['user_id'], $start_date, $end_date]);
        $consistency = $stmt->fetch()['workout_days'] ?? 0;

        $days_in_range = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
        $consistency_percentage = $days_in_range > 0 ? ($consistency / $days_in_range) * 100 : 0;
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Motivational message based on progress
$motivation = '';
if ($workout_progress_percentage < 50 || $weight_progress_percentage < 50 || $nutrition_progress_percentage < 50) {
    $motivation = "You're making progress, but there's room to grow! Focus more on consistency with your workouts and nutrition to reach your goals faster.";
} else {
    $motivation = "Great job! You're making excellent progress. Keep pushing to maintain your momentum!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../assets/css/global.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .progress-card, .achievement-card, .dashboard-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            color: #ffffff;
            animation: fadeIn 0.5s ease-in-out;
        }

        .progress-card h4, .achievement-card h5, .dashboard-card h5 {
            color: #00ddeb;
            margin-bottom: 1rem;
        }

        .progress {
            height: 25px;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.3);
        }

        .progress-bar {
            transition: width 1s ease-in-out;
        }

        .achievement-card {
            text-align: center;
        }

        .achievement-card i {
            font-size: 2rem;
            color: #00ddeb;
            margin-bottom: 0.5rem;
        }

        .dashboard-card p {
            margin: 0.5rem 0;
        }

        .chart-container {
            max-height: 400px;
        }

        .motivation-message {
            background: rgba(0, 221, 235, 0.2);
            border-left: 5px solid #00ddeb;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Include Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <!-- Progress Section -->
    <div class="store-section">
        <div class="container">
            <h2 class="text-center mb-4">Your Fitness Progress</h2>

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

            <?php if (!isset($error)): ?>
                <!-- Date Range Selection -->
                <div class="mb-4">
                    <h4>Select Date Range</h4>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Update Range</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Motivational Message -->
                <div class="motivation-message">
                    <p><?php echo htmlspecialchars($motivation); ?></p>
                </div>

                <!-- Summary Dashboard -->
                <div class="mb-4">
                    <h4>Progress Summary</h4>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="dashboard-card">
                                <h5>Total Weight Lifted</h5>
                                <p><?php echo number_format($total_weight_lifted, 2); ?> kg</p>
                                <div class="progress">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: <?php echo $workout_progress_percentage; ?>%;" 
                                         aria-valuenow="<?php echo $workout_progress_percentage; ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?php echo round($workout_progress_percentage); ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="dashboard-card">
                                <h5>Weight Change</h5>
                                <p><?php echo $last_weight > 0 ? round($last_weight, 2) : 'N/A'; ?> kg</p>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?php echo $weight_progress_percentage; ?>%;" 
                                         aria-valuenow="<?php echo $weight_progress_percentage; ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?php echo round($weight_progress_percentage); ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="dashboard-card">
                                <h5>Nutrition Consistency</h5>
                                <p><?php echo $last_calories > 0 ? round($last_calories) : 'N/A'; ?> kcal/day</p>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" role="progressbar" 
                                         style="width: <?php echo $nutrition_progress_percentage; ?>%;" 
                                         aria-valuenow="<?php echo $nutrition_progress_percentage; ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?php echo round($nutrition_progress_percentage); ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Set Goals -->
                <div class="mb-4">
                    <h4>Set Your Goals</h4>
                    <div class="row">
                        <!-- Weight Goal Form -->
                        <div class="col-md-4 mb-3">
                            <form method="POST">
                                <input type="hidden" name="action" value="set_goal">
                                <input type="hidden" name="goal_type" value="weight">
                                <label for="goal_weight" class="form-label">Target Weight (kg)</label>
                                <input type="number" step="0.1" class="form-control" id="goal_weight" name="target_value" 
                                       value="<?php echo $goals_by_type['weight']['target_value'] ?? ''; ?>" 
                                       min="0.1" required>
                                <button type="submit" class="btn btn-primary w-100 mt-2">Set Weight Goal</button>
                            </form>
                        </div>
                        <!-- Calorie Goal Form -->
                        <div class="col-md-4 mb-3">
                            <form method="POST">
                                <input type="hidden" name="action" value="set_goal">
                                <input type="hidden" name="goal_type" value="calories">
                                <label for="goal_calories" class="form-label">Daily Calories (kcal)</label>
                                <input type="number" class="form-control" id="goal_calories" name="target_value" 
                                       value="<?php echo $goals_by_type['calories']['target_value'] ?? ''; ?>" 
                                       min="1" required>
                                <button type="submit" class="btn btn-primary w-100 mt-2">Set Calorie Goal</button>
                            </form>
                        </div>
                        <!-- Weight Lifted Goal Form -->
                        <div class="col-md-4 mb-3">
                            <form method="POST">
                                <input type="hidden" name="action" value="set_goal">
                                <input type="hidden" name="goal_type" value="total_weight_lifted">
                                <label for="goal_weight_lifted" class="form-label">Total Weight Lifted (kg)</label>
                                <input type="number" class="form-control" id="goal_weight_lifted" name="target_value" 
                                       value="<?php echo $goals_by_type['total_weight_lifted']['target_value'] ?? ''; ?>" 
                                       min="1" required>
                                <button type="submit" class="btn btn-primary w-100 mt-2">Set Lifting Goal</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Goal Progress -->
                <div class="mb-4">
                    <h4>Goal Progress</h4>
                    <div class="row">
                        <?php if (isset($goals_by_type['weight'])): ?>
                            <div class="col-md-4 mb-3">
                                <div class="progress-card">
                                    <h5>Weight Goal</h5>
                                    <p>Current: <?php echo round($last_weight, 2); ?> kg / Target: <?php echo $goals_by_type['weight']['target_value']; ?> kg</p>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?php echo $weight_goal_progress; ?>%;" 
                                             aria-valuenow="<?php echo $weight_goal_progress; ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?php echo round($weight_goal_progress); ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($goals_by_type['calories'])): ?>
                            <div class="col-md-4 mb-3">
                                <div class="progress-card">
                                    <h5>Calorie Goal</h5>
                                    <p>Current: <?php echo round($last_calories); ?> kcal / Target: <?php echo $goals_by_type['calories']['target_value']; ?> kcal</p>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: <?php echo $calories_goal_progress; ?>%;" 
                                             aria-valuenow="<?php echo $calories_goal_progress; ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?php echo round($calories_goal_progress); ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($goals_by_type['total_weight_lifted'])): ?>
                            <div class="col-md-4 mb-3">
                                <div class="progress-card">
                                    <h5>Weight Lifted Goal</h5>
                                    <p>Current: <?php echo number_format($total_weight_lifted, 2); ?> kg / Target: <?php echo $goals_by_type['total_weight_lifted']['target_value']; ?> kg</p>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: <?php echo $weight_lifted_goal_progress; ?>%;" 
                                             aria-valuenow="<?php echo $weight_lifted_goal_progress; ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?php echo round($weight_lifted_goal_progress); ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Detailed Metrics -->
                <div class="mb-4">
                    <h4>Detailed Metrics</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="progress-card">
                                <h5>Workout Consistency</h5>
                                <p>Workout Days: <?php echo $consistency; ?> / <?php echo $days_in_range; ?> days</p>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: <?php echo $consistency_percentage; ?>%;" 
                                         aria-valuenow="<?php echo $consistency_percentage; ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?php echo round($consistency_percentage); ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="progress-card">
                                <h5>Muscle Group Volume</h5>
                                <canvas id="muscleGroupChart" class="chart-container"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Charts -->
                <div class="mb-4">
                    <h4>Progress Over Time</h4>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="progress-card">
                                <h5>Total Weight Lifted</h5>
                                <canvas id="workoutChart" class="chart-container"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="progress-card">
                                <h5>Body Weight</h5>
                                <canvas id="weightChart" class="chart-container"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="progress-card">
                                <h5>Average Daily Calories</h5>
                                <canvas id="nutritionChart" class="chart-container"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Achievements -->
                <?php if (!empty($achievements)): ?>
                    <div class="mb-4">
                        <h4>Your Achievements</h4>
                        <div class="row">
                            <?php foreach ($achievements as $achievement): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="achievement-card">
                                        <i class="fas <?php echo $achievement['icon']; ?>"></i>
                                        <h5><?php echo htmlspecialchars($achievement['name']); ?></h5>
                                        <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Only initialize charts if there's no error
        <?php if (!isset($error)): ?>
            // Workout Chart
            const workoutCtx = document.getElementById('workoutChart')?.getContext('2d');
            if (workoutCtx) {
                new Chart(workoutCtx, {
                    type: 'line',
                    data: {
                        labels: <?php echo $workout_labels; ?>,
                        datasets: [{
                            label: 'Total Weight Lifted (kg)',
                            data: <?php echo $workout_data; ?>,
                            borderColor: '#00ddeb',
                            backgroundColor: 'rgba(0, 221, 235, 0.2)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: { title: { display: true, text: 'Date', color: '#ffffff' }, ticks: { color: '#b0b0b0' } },
                            y: { title: { display: true, text: 'Weight (kg)', color: '#ffffff' }, ticks: { color: '#b0b0b0' }, beginAtZero: true }
                        },
                        plugins: { legend: { labels: { color: '#ffffff' } } }
                    }
                });
            }

            // Weight Chart
            const weightCtx = document.getElementById('weightChart')?.getContext('2d');
            if (weightCtx) {
                new Chart(weightCtx, {
                    type: 'line',
                    data: {
                        labels: <?php echo $weight_labels; ?>,
                        datasets: [{
                            label: 'Body Weight (kg)',
                            data: <?php echo $weight_data; ?>,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.2)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: { title: { display: true, text: 'Date', color: '#ffffff' }, ticks: { color: '#b0b0b0' } },
                            y: { title: { display: true, text: 'Weight (kg)', color: '#ffffff' }, ticks: { color: '#b0b0b0' }, beginAtZero: false }
                        },
                        plugins: { legend: { labels: { color: '#ffffff' } } }
                    }
                });
            }

            // Nutrition Chart
            const nutritionCtx = document.getElementById('nutritionChart')?.getContext('2d');
            if (nutritionCtx) {
                new Chart(nutritionCtx, {
                    type: 'line',
                    data: {
                        labels: <?php echo $nutrition_labels; ?>,
                        datasets: [{
                            label: 'Average Daily Calories (kcal)',
                            data: <?php echo $nutrition_data; ?>,
                            borderColor: '#ffc107',
                            backgroundColor: 'rgba(255, 193, 7, 0.2)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: { title: { display: true, text: 'Date', color: '#ffffff' }, ticks: { color: '#b0b0b0' } },
                            y: { title: { display: true, text: 'Calories (kcal)', color: '#ffffff' }, ticks: { color: '#b0b0b0' }, beginAtZero: true }
                        },
                        plugins: { legend: { labels: { color: '#ffffff' } } }
                    }
                });
            }

            // Muscle Group Volume Chart
            const muscleCtx = document.getElementById('muscleGroupChart')?.getContext('2d');
            if (muscleCtx) {
                new Chart(muscleCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo $muscle_labels; ?>,
                        datasets: [{
                            label: 'Volume (kg)',
                            data: <?php echo $muscle_data; ?>,
                            backgroundColor: 'rgba(0, 221, 235, 0.5)',
                            borderColor: '#00ddeb',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: { title: { display: true, text: 'Muscle Group', color: '#ffffff' }, ticks: { color: '#b0b0b0' } },
                            y: { title: { display: true, text: 'Volume (kg)', color: '#ffffff' }, ticks: { color: '#b0b0b0' }, beginAtZero: true }
                        },
                        plugins: { legend: { labels: { color: '#ffffff' } } }
                    }
                });
            }
        <?php endif; ?>
    </script>
</body>
</html>