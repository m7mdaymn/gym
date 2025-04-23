<?php

// Include database connection
require_once '../../includes/config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'log_workout') {
            $exercise = trim($_POST['exercise'] ?? '');
            $weight = floatval($_POST['weight'] ?? 0);
            $reps = intval($_POST['reps'] ?? 0);
            $sets = intval($_POST['sets'] ?? 0);
            $rpe = !empty($_POST['rpe']) ? intval($_POST['rpe']) : null;
            $date = date('Y-m-d H:i:s');
            $muscle_group = $_POST['muscle_group'] ?? 'Unknown';
            $user_id = $_SESSION['user_id'];

            if (!empty($exercise) && $weight > 0 && $reps > 0 && $sets > 0) {
                $stmt = $pdo->prepare("INSERT INTO workouts (user_id, exercise, weight, reps, sets, rpe, workout_date, muscle_group) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $exercise, $weight, $reps, $sets, $rpe, $date, $muscle_group]);
                $success = 'Workout logged successfully!';
            } else {
                $error = 'Please fill in all fields with valid values.';
            }
        } elseif ($_POST['action'] === 'add_custom_exercise') {
            $customExercise = trim($_POST['custom_exercise'] ?? '');
            $muscleGroup = trim($_POST['custom_muscle_group'] ?? '');
            $user_id = $_SESSION['user_id'];

            if (!empty($customExercise) && !empty($muscleGroup)) {
                $stmt = $pdo->prepare("INSERT INTO custom_exercises (user_id, name, muscle_group) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $customExercise, $muscleGroup]);
                $success = 'Custom exercise added successfully!';
            } else {
                $error = 'Please provide both exercise name and muscle group.';
            }
        } elseif ($_POST['action'] === 'save_template') {
            $templateName = trim($_POST['template_name'] ?? '');
            $exercise = trim($_POST['exercise'] ?? '');
            $weight = floatval($_POST['weight'] ?? 0);
            $reps = intval($_POST['reps'] ?? 0);
            $sets = intval($_POST['sets'] ?? 0);
            $rpe = !empty($_POST['rpe']) ? intval($_POST['rpe']) : null;
            $muscle_group = $_POST['muscle_group'] ?? 'Unknown';
            $user_id = $_SESSION['user_id'];

            if (!empty($templateName) && !empty($exercise) && $weight > 0 && $reps > 0 && $sets > 0) {
                $stmt = $pdo->prepare("INSERT INTO templates (user_id, template_name, exercise, weight, reps, sets, rpe, muscle_group) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $templateName, $exercise, $weight, $reps, $sets, $rpe, $muscle_group]);
                $success = 'Template saved successfully!';
            } else {
                $error = 'Please fill in all fields to save the template.';
            }
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Fetch data from the database
try {
    // Fetch workouts
    $stmt = $pdo->prepare("SELECT * FROM workouts WHERE user_id = ? ORDER BY workout_date DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $workouts = $stmt->fetchAll();

    // Fetch custom exercises
    $stmt = $pdo->prepare("SELECT * FROM custom_exercises WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $customExercises = $stmt->fetchAll();

    // Fetch templates
    $stmt = $pdo->prepare("SELECT * FROM templates WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
    $templates = array_map('reset', $templates);
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
    $workouts = [];
    $customExercises = [];
    $templates = [];
}

// Calculate stats
$totalWeight = 0;
$totalReps = 0;
$count = 0;
$muscleGroupVolume = [];
$oneRepMax = [];

foreach ($workouts as $workout) {
    $volume = $workout['weight'] * $workout['reps'] * $workout['sets'];
    $totalWeight += $volume;
    $totalReps += $workout['reps'] * $workout['sets'];
    $count++;

    // Volume per muscle group
    $muscleGroup = $workout['muscle_group'];
    if (!isset($muscleGroupVolume[$muscleGroup])) {
        $muscleGroupVolume[$muscleGroup] = 0;
    }
    $muscleGroupVolume[$muscleGroup] += $volume;

    // Calculate 1RM (using Epley formula: 1RM = weight × (1 + (reps / 30)))
    $exercise = $workout['exercise'];
    $current1RM = $workout['weight'] * (1 + ($workout['reps'] / 30));
    if (!isset($oneRepMax[$exercise]) || $current1RM > $oneRepMax[$exercise]) {
        $oneRepMax[$exercise] = $current1RM;
    }
}

$averageReps = $count > 0 ? $totalReps / $count : 0;

// Prepare data for chart
$chartData = [];
$selectedExercise = $_POST['selected_exercise'] ?? 'Bench Press';
foreach ($workouts as $workout) {
    if ($workout['exercise'] === $selectedExercise) {
        $chartData[] = [
            'date' => $workout['workout_date'],
            'weight' => $workout['weight']
        ];
    }
}

// Emoji mapping for exercises
$exerciseEmojis = [
    // Chest
    'Bench Press' => '🏋️',
    'Incline Bench Press' => '🏋️',
    'Dumbbell Flyes' => '🏋️',
    'Push-Up' => '🏋️',
    'Chest Dips' => '🏋️',
    'Incline Dumbbell Press' => '🏋️',
    'Cable Crossovers' => '🏋️',
    // Back
    'Deadlift' => '🏋️‍♂️',
    'Pull-Up' => '🏋️‍♂️',
    'Lat Pulldown' => '🏋️‍♂️',
    'Bent-Over Row' => '🏋️‍♂️',
    'Seated Cable Row' => '🏋️‍♂️',
    'T-Bar Row' => '🏋️‍♂️',
    'Single-Arm Dumbbell Row' => '🏋️‍♂️',
    // Shoulders
    'Overhead Press' => '💪',
    'Lateral Raise' => '💪',
    'Front Raise' => '💪',
    'Shrugs' => '💪',
    'Reverse Flyes' => '💪',
    'Arnold Press' => '💪',
    'Face Pulls' => '💪',
    // Arms
    'Bicep Curl' => '🏋️‍♀️',
    'Tricep Dip' => '🏋️‍♀️',
    'Hammer Curl' => '🏋️‍♀️',
    'Skull Crushers' => '🏋️‍♀️',
    'Concentration Curl' => '🏋️‍♀️',
    'Tricep Pushdown' => '🏋️‍♀️',
    'Preacher Curl' => '🏋️‍♀️',
    // Legs
    'Squat' => '🦵',
    'Lunges' => '🦵',
    'Leg Press' => '🦵',
    'Calf Raise' => '🦵',
    'Romanian Deadlift' => '🦵',
    'Bulgarian Split Squat' => '🦵',
    'Step-Up' => '🦵',
    // Core
    'Plank' => '🧘',
    'Russian Twist' => '🧘',
    'Leg Raise' => '🧘',
    'Bicycle Crunch' => '🧘',
    'Mountain Climbers' => '🧘',
    'Hanging Leg Raise' => '🧘',
    'Side Plank' => '🧘'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Workout - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets\css\progress.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../index.php">FitnessApp</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="../../index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="featuresDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            User Features
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="featuresDropdown">
                            <li><a class="dropdown-item" href="#progress">Progress</a></li>
                            <li><a class="dropdown-item" href="#nutrition">Nutrition</a></li>
                            <li><a class="dropdown-item" href="#workout">Workout</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="../dashboard/dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="../profile/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../auth/logout/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/login/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/register/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Left Side: Workout Form -->
        <div class="workout-form-container">
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
                <input type="hidden" name="action" value="log_workout">
                <div class="form-group search-container">
                    <label for="exerciseSearch">Search Exercises</label>
                    <i class="fas fa-search"></i>
                    <input type="text" id="exerciseSearch" placeholder="Search for an exercise..." class="form-control">
                </div>
                <div class="form-group">
                    <label for="exercise">Exercise</label>
                    <select id="exercise" name="exercise" required>
                        <option value="">Select an exercise</option>
                        <optgroup label="Chest">
                            <option value="Bench Press" data-muscle="Chest">Bench Press</option>
                            <option value="Incline Bench Press" data-muscle="Chest">Incline Bench Press</option>
                            <option value="Dumbbell Flyes" data-muscle="Chest">Dumbbell Flyes</option>
                            <option value="Push-Up" data-muscle="Chest">Push-Up</option>
                            <option value="Chest Dips" data-muscle="Chest">Chest Dips</option>
                            <option value="Incline Dumbbell Press" data-muscle="Chest">Incline Dumbbell Press</option>
                            <option value="Cable Crossovers" data-muscle="Chest">Cable Crossovers</option>
                        </optgroup>
                        <optgroup label="Back">
                            <option value="Deadlift" data-muscle="Back">Deadlift</option>
                            <option value="Pull-Up" data-muscle="Back">Pull-Up</option>
                            <option value="Lat Pulldown" data-muscle="Back">Lat Pulldown</option>
                            <option value="Bent-Over Row" data-muscle="Back">Bent-Over Row</option>
                            <option value="Seated Cable Row" data-muscle="Back">Seated Cable Row</option>
                            <option value="T-Bar Row" data-muscle="Back">T-Bar Row</option>
                            <option value="Single-Arm Dumbbell Row" data-muscle="Back">Single-Arm Dumbbell Row</option>
                        </optgroup>
                        <optgroup label="Shoulders">
                            <option value="Overhead Press" data-muscle="Shoulders">Overhead Press</option>
                            <option value="Lateral Raise" data-muscle="Shoulders">Lateral Raise</option>
                            <option value="Front Raise" data-muscle="Shoulders">Front Raise</option>
                            <option value="Shrugs" data-muscle="Shoulders">Shrugs</option>
                            <option value="Reverse Flyes" data-muscle="Shoulders">Reverse Flyes</option>
                            <option value="Arnold Press" data-muscle="Shoulders">Arnold Press</option>
                            <option value="Face Pulls" data-muscle="Shoulders">Face Pulls</option>
                        </optgroup>
                        <optgroup label="Arms">
                            <option value="Bicep Curl" data-muscle="Arms">Bicep Curl</option>
                            <option value="Tricep Dip" data-muscle="Arms">Tricep Dip</option>
                            <option value="Hammer Curl" data-muscle="Arms">Hammer Curl</option>
                            <option value="Skull Crushers" data-muscle="Arms">Skull Crushers</option>
                            <option value="Concentration Curl" data-muscle="Arms">Concentration Curl</option>
                            <option value="Tricep Pushdown" data-muscle="Arms">Tricep Pushdown</option>
                            <option value="Preacher Curl" data-muscle="Arms">Preacher Curl</option>
                        </optgroup>
                        <optgroup label="Legs">
                            <option value="Squat" data-muscle="Legs">Squat</option>
                            <option value="Lunges" data-muscle="Legs">Lunges</option>
                            <option value="Leg Press" data-muscle="Legs">Leg Press</option>
                            <option value="Calf Raise" data-muscle="Legs">Calf Raise</option>
                            <option value="Romanian Deadlift" data-muscle="Legs">Romanian Deadlift</option>
                            <option value="Bulgarian Split Squat" data-muscle="Legs">Bulgarian Split Squat</option>
                            <option value="Step-Up" data-muscle="Legs">Step-Up</option>
                        </optgroup>
                        <optgroup label="Core">
                            <option value="Plank" data-muscle="Core">Plank</option>
                            <option value="Russian Twist" data-muscle="Core">Russian Twist</option>
                            <option value="Leg Raise" data-muscle="Core">Leg Raise</option>
                            <option value="Bicycle Crunch" data-muscle="Core">Bicycle Crunch</option>
                            <option value="Mountain Climbers" data-muscle="Core">Mountain Climbers</option>
                            <option value="Hanging Leg Raise" data-muscle="Core">Hanging Leg Raise</option>
                            <option value="Side Plank" data-muscle="Core">Side Plank</option>
                        </optgroup>
                        <?php if (!empty($customExercises)): ?>
                            <optgroup label="Custom Exercises">
                                <?php foreach ($customExercises as $custom): ?>
                                    <option value="<?php echo htmlspecialchars($custom['name']); ?>" data-muscle="<?php echo htmlspecialchars($custom['muscle_group']); ?>">
                                        <?php echo htmlspecialchars($custom['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                    <input type="hidden" name="muscle_group" id="muscleGroup">
                </div>
                <div class="form-group">
                    <label for="weight">Weight (kg)</label>
                    <input type="number" id="weight" name="weight" step="0.1" min="0" required data-bs-toggle="tooltip" data-bs-placement="top" title="Enter weight in kg">
                </div>
                <div class="form-group">
                    <label for="reps">Reps</label>
                    <input type="number" id="reps" name="reps" min="1" required data-bs-toggle="tooltip" data-bs-placement="top" title="Number of repetitions per set">
                </div>
                <div class="form-group">
                    <label for="sets">Sets</label>
                    <input type="number" id="sets" name="sets" min="1" required data-bs-toggle="tooltip" data-bs-placement="top" title="Number of sets performed">
                </div>
                <div class="form-group">
                    <label for="rpe">RPE (Rate of Perceived Exertion, 1-10)</label>
                    <input type="number" id="rpe" name="rpe" min="1" max="10" data-bs-toggle="tooltip" data-bs-placement="top" title="Rate of Perceived Exertion (optional, 1-10)">
                </div>
                <button type="submit" class="btn btn-primary">Log Workout</button>
            </form>

            <!-- Save as Template -->
            <form method="POST" class="mt-3">
                <input type="hidden" name="action" value="save_template">
                <input type="hidden" name="exercise" id="templateExercise">
                <input type="hidden" name="weight" id="templateWeight">
                <input type="hidden" name="reps" id="templateReps">
                <input type="hidden" name="sets" id="templateSets">
                <input type="hidden" name="rpe" id="templateRpe">
                <input type="hidden" name="muscle_group" id="templateMuscleGroup">
                <div class="form-group">
                    <label for="templateName">Save as Template</label>
                    <input type="text" id="templateName" name="template_name" placeholder="Template Name (e.g., Chest Day)" class="form-control" data-bs-toggle="tooltip" data-bs-placement="top" title="Give your template a name">
                </div>
                <button type="submit" class="btn btn-secondary">Save Template</button>
            </form>

            <!-- Load Template -->
            <?php if (!empty($templates)): ?>
                <div class="form-group mt-3">
                    <label for="loadTemplate">Load Template</label>
                    <select id="loadTemplate" class="form-control">
                        <option value="">Select a template</option>
                        <?php foreach ($templates as $name => $template): ?>
                            <option value="<?php echo htmlspecialchars($name); ?>" 
                                    data-exercise="<?php echo htmlspecialchars($template['exercise']); ?>"
                                    data-weight="<?php echo $template['weight']; ?>"
                                    data-reps="<?php echo $template['reps']; ?>"
                                    data-sets="<?php echo $template['sets']; ?>"
                                    data-rpe="<?php echo $template['rpe']; ?>"
                                    data-muscle="<?php echo htmlspecialchars($template['muscle_group']); ?>">
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <!-- Add Custom Exercise Button -->
            <button type="button" class="btn btn-secondary mt-2" data-bs-toggle="modal" data-bs-target="#customExerciseModal">
                Add Custom Exercise
            </button>
        </div>

        <!-- Right Side: Workout Details -->
        <div class="workout-details-container">
            <!-- Workout History -->
            <?php if (!empty($workouts)): ?>
                <div class="workout-history">
                    <h3>Your Workout History</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Icon</th>
                                <th>Exercise</th>
                                <th>Weight (kg)</th>
                                <th>Reps</th>
                                <th>Sets</th>
                                <th>RPE</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($workouts as $workout): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $exercise = $workout['exercise'];
                                        echo isset($exerciseEmojis[$exercise]) ? $exerciseEmojis[$exercise] : '🤸'; // Default emoji for custom exercises
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($workout['exercise']); ?></td>
                                    <td><?php echo htmlspecialchars($workout['weight']); ?></td>
                                    <td><?php echo htmlspecialchars($workout['reps']); ?></td>
                                    <td><?php echo htmlspecialchars($workout['sets']); ?></td>
                                    <td><?php echo htmlspecialchars($workout['rpe'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($workout['workout_date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Progress Stats -->
            <div class="stats-section">
                <h3>Your Progress Stats</h3>
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
                <h4>Volume by Muscle Group</h4>
                <?php foreach ($muscleGroupVolume as $muscle => $volume): ?>
                    <div class="stat">
                        <label><?php echo htmlspecialchars($muscle); ?>:</label>
                        <span><?php echo number_format($volume, 2); ?> kg</span>
                    </div>
                <?php endforeach; ?>
                <h4>One Rep Max (1RM)</h4>
                <?php foreach ($oneRepMax as $exercise => $orm): ?>
                    <div class="stat">
                        <label><?php echo htmlspecialchars($exercise); ?>:</label>
                        <span><?php echo number_format($orm, 2); ?> kg</span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Progress Chart -->
            <div class="progress-chart">
                <h3>Progress Over Time</h3>
                <?php if (!empty($workouts)): ?>
                    <form method="POST" class="mb-3">
                        <div class="form-group">
                            <label for="selectedExercise">Select Exercise for Chart</label>
                            <select id="selectedExercise" name="selected_exercise" class="form-control" onchange="this.form.submit()">
                                <?php
                                $uniqueExercises = array_unique(array_column($workouts, 'exercise'));
                                foreach ($uniqueExercises as $exercise): ?>
                                    <option value="<?php echo htmlspecialchars($exercise); ?>" <?php echo $selectedExercise === $exercise ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($exercise); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                    <canvas id="progressChart"></canvas>
                <?php else: ?>
                    <p>No workouts logged yet to display progress.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Custom Exercise Modal -->
    <div class="modal fade" id="customExerciseModal" tabindex="-1" aria-labelledby="customExerciseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.2);">
                <div class="modal-header">
                    <h5 class="modal-title" id="customExerciseModalLabel">Add Custom Exercise</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_custom_exercise">
                        <div class="form-group">
                            <label for="customExercise">Exercise Name</label>
                            <input type="text" id="customExercise" name="custom_exercise" class="form-control" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="customMuscleGroup">Muscle Group</label>
                            <select id="customMuscleGroup" name="custom_muscle_group" class="form-control" required>
                                <option value="Chest">Chest</option>
                                <option value="Back">Back</option>
                                <option value="Shoulders">Shoulders</option>
                                <option value="Arms">Arms</option>
                                <option value="Legs">Legs</option>
                                <option value="Core">Core</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Exercise</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>FitnessApp</h5>
                    <p>Your ultimate fitness companion to track progress, plan workouts, and achieve your goals.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="../../index.php">Home</a></li>
                        <li><a href="#progress">Progress</a></li>
                        <li><a href="#nutrition">Nutrition</a></li>
                        <li><a href="#workout">Workout</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Follow Us</h5>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p>© 2025 FitnessApp. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Bootstrap tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        // Exercise search functionality
        const exerciseSearch = document.getElementById('exerciseSearch');
        const exerciseSelect = document.getElementById('exercise');
        const options = exerciseSelect.querySelectorAll('option');

        exerciseSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            options.forEach(option => {
                if (option.value === '') return;
                const text = option.textContent.toLowerCase();
                option.style.display = text.includes(searchTerm) ? '' : 'none';
            });

            const optgroups = exerciseSelect.querySelectorAll('optgroup');
            optgroups.forEach(optgroup => {
                const visibleOptions = Array.from(optgroup.querySelectorAll('option')).some(opt => opt.style.display !== 'none');
                optgroup.style.display = visibleOptions ? '' : 'none';
            });
        });

        // Set muscle group hidden input when exercise is selected
        exerciseSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const muscleGroup = selectedOption.getAttribute('data-muscle') || 'Unknown';
            document.getElementById('muscleGroup').value = muscleGroup;

            document.getElementById('templateExercise').value = this.value;
            document.getElementById('templateMuscleGroup').value = muscleGroup;
        });

        // Update template hidden fields on input change
        document.getElementById('weight').addEventListener('input', function() {
            document.getElementById('templateWeight').value = this.value;
        });
        document.getElementById('reps').addEventListener('input', function() {
            document.getElementById('templateReps').value = this.value;
        });
        document.getElementById('sets').addEventListener('input', function() {
            document.getElementById('templateSets').value = this.value;
        });
        document.getElementById('rpe').addEventListener('input', function() {
            document.getElementById('templateRpe').value = this.value;
        });

        // Load template data into form
        const loadTemplate = document.getElementById('loadTemplate');
        if (loadTemplate) {
            loadTemplate.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value === '') return;

                document.getElementById('exercise').value = selectedOption.getAttribute('data-exercise');
                document.getElementById('weight').value = selectedOption.getAttribute('data-weight');
                document.getElementById('reps').value = selectedOption.getAttribute('data-reps');
                document.getElementById('sets').value = selectedOption.getAttribute('data-sets');
                document.getElementById('rpe').value = selectedOption.getAttribute('data-rpe');
                document.getElementById('muscleGroup').value = selectedOption.getAttribute('data-muscle');

                document.getElementById('templateExercise').value = selectedOption.getAttribute('data-exercise');
                document.getElementById('templateWeight').value = selectedOption.getAttribute('data-weight');
                document.getElementById('templateReps').value = selectedOption.getAttribute('data-reps');
                document.getElementById('templateSets').value = selectedOption.getAttribute('data-sets');
                document.getElementById('templateRpe').value = selectedOption.getAttribute('data-rpe');
                document.getElementById('templateMuscleGroup').value = selectedOption.getAttribute('data-muscle');
            });
        }

        // Chart.js setup
        const chartData = <?php echo json_encode($chartData); ?>;
        if (chartData.length > 0) {
            const ctx = document.getElementById('progressChart')?.getContext('2d');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.map(data => data.date),
                        datasets: [{
                            label: 'Weight Lifted (kg)',
                            data: chartData.map(data => data.weight),
                            borderColor: '#00ddeb',
                            backgroundColor: 'rgba(0, 221, 235, 0.2)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Date',
                                    color: '#ffffff'
                                },
                                ticks: { color: '#b0b0b0' }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Weight (kg)',
                                    color: '#ffffff'
                                },
                                ticks: { color: '#b0b0b0' },
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                labels: { color: '#ffffff' }
                            }
                        }
                    }
                });
            }
        }
    </script>
</body>
</html>