<?php
// Include database connection
require_once '../../includes/config.php';

// Fetch the username, height, weight, and age from the database using user_id
try {
    $stmt = $pdo->prepare("SELECT username, height, weight, age FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Set the username, fallback to 'User' if not found
    $username = $user && !empty($user['username']) ? $user['username'] : 'User';
    $height = $user['height'] ?? null;
    $weight = $user['weight'] ?? null;
    $age = $user['age'] ?? null;
} catch (PDOException $e) {
    $username = 'User';
    $height = null;
    $weight = null;
    $age = null;
}

// Determine if the form should be shown
$show_form = true;
if ($height && $weight && $age && !isset($_POST['edit_profile'])) {
    $show_form = false;
}

// Handle form submission to update height, weight, and age
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $height = filter_input(INPUT_POST, 'height', FILTER_VALIDATE_FLOAT);
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT);
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);

    // Validate inputs
    $errors = [];
    if ($height === false || $height <= 0) {
        $errors[] = 'Please enter a valid height.';
    }
    if ($weight === false || $weight <= 0) {
        $errors[] = 'Please enter a valid weight.';
    }
    if ($age === false || $age <= 0) {
        $errors[] = 'Please enter a valid age.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET height = ?, weight = ?, age = ? WHERE id = ?");
            $stmt->execute([$height, $weight, $age, $_SESSION['user_id']]);
            $show_form = false; // Hide form after successful submission
        } catch (PDOException $e) {
            $errors[] = 'Failed to update profile: ' . $e->getMessage();
        }
    }
}

// Handle "Edit Profile" button to show the form again
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profile'])) {
    $show_form = true;
}

// Calculate ideal weight (using Devine Formula for men) and progress
$ideal_weight = null;
$weight_progress = null;
if ($height && $weight) {
    $height_in_inches = $height / 2.54; // Convert cm to inches
    $ideal_weight = 50 + 2.3 * ($height_in_inches - 60); // Devine Formula for men

    // Calculate progress based on current weight, ideal weight, and max weight
    $max_weight = 114.66; // Calibrated so 100 kg gives 70% green for 170 cm
    if ($weight > $ideal_weight) {
        // Overweight: Progress is how close to ideal weight
        $total_range = $max_weight - $ideal_weight;
        $weight_excess = $weight - $ideal_weight;
        $progress_red = ($weight_excess / $total_range) * 100; // Red portion
        $weight_progress = max(0, 100 - $progress_red); // Green portion
        $weight_progress = min(100, max(0, $weight_progress)); // Clamp between 0 and 100
    } elseif ($weight < $ideal_weight) {
        // Underweight: Progress is how close to ideal weight
        $min_weight = $ideal_weight - 20; // Arbitrary min weight for underweight
        $total_range = $ideal_weight - $min_weight;
        $weight_deficit = $ideal_weight - $weight;
        $progress_red = ($weight_deficit / $total_range) * 100; // Red portion
        $weight_progress = max(0, 100 - $progress_red); // Green portion
        $weight_progress = min(100, max(0, $weight_progress)); // Clamp between 0 and 100
    } else {
        $weight_progress = 100; // At ideal weight
    }
}

// Convert weight progress percentage to degrees for conic-gradient
$progress_degrees = ($weight_progress ?? 0) * 3.6; // 100% = 360 degrees, so 1% = 3.6 degrees

// Fetch dynamic data for the Stats Overview
try {
    $stmt = $pdo->prepare("SELECT calories_burned FROM user_stats WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no stats exist, set default values
    $calories = $stats ? $stats['calories_burned'] : 12208; // Match the image value
} catch (PDOException $e) {
    $calories = 12208; // Fallback value
}

// Calculate total duration from user_activities (for reference, not displayed)
try {
    $stmt = $pdo->prepare("SELECT SUM(duration) as total_minutes FROM user_activities WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_minutes = $result['total_minutes'] ?? 0;

    // Convert total minutes to hours and minutes
    $hours = floor($total_minutes / 60);
    $minutes = $total_minutes % 60;
} catch (PDOException $e) {
    $hours = 135; // Fallback values
    $minutes = 42;
}

// Count the number of sessions (each activity is a session)
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as session_count FROM user_activities WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $sessions = $result['session_count'] ?? 0;
} catch (PDOException $e) {
    $sessions = 127; // Fallback value to match the image
}

// Fetch weekly progress data (duration per day for the current week)
$weekly_progress = array_fill(0, 7, 0); // Initialize array for Mon-Sun (0 minutes)
$days_of_week = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

try {
    // Get the start and end of the current week (Monday to Sunday)
    $today = new DateTime();
    $today->setTimezone(new DateTimeZone('EET')); // Set timezone to EET
    $day_of_week = $today->format('N'); // 1 (Mon) to 7 (Sun)
    $start_of_week = clone $today;
    $start_of_week->modify('-' . ($day_of_week - 1) . ' days'); // Go to Monday
    $end_of_week = clone $start_of_week;
    $end_of_week->modify('+6 days'); // Go to Sunday

    $start_date = $start_of_week->format('Y-m-d 00:00:00');
    $end_date = $end_of_week->format('Y-m-d 23:59:59');

    // Fetch total duration per day for the current week
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as activity_date, SUM(duration) as total_duration
        FROM user_activities
        WHERE user_id = ? AND created_at BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
    ");
    $stmt->execute([$_SESSION['user_id'], $start_date, $end_date]);
    $daily_durations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map the durations to the corresponding day of the week
    foreach ($daily_durations as $row) {
        $activity_date = new DateTime($row['activity_date']);
        $day_index = ($activity_date->format('N') - 1); // 0 (Mon) to 6 (Sun)
        $weekly_progress[$day_index] = (int)$row['total_duration'];
    }
} catch (PDOException $e) {
    // Fallback: Use some sample data if the query fails
    $weekly_progress = [30, 45, 20, 60, 15, 50, 25]; // Sample minutes for Mon-Sun
}

// Calculate calories burned progress (e.g., goal is 2000 kcal)
$calories_goal = 2000;
$calories_progress = ($calories / $calories_goal) * 100;

// Handle adding a new activity
$activity_errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_activity'])) {
    $exercise = filter_input(INPUT_POST, 'exercise', FILTER_SANITIZE_STRING);
    $duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);

    // Validate inputs
    if (empty($exercise) || !in_array($exercise, ['Running', 'Jumping', 'Cardio', 'Fitness'])) {
        $activity_errors[] = 'Please select a valid exercise.';
    }
    if ($duration === false || $duration <= 0) {
        $activity_errors[] = 'Please enter a valid duration.';
    }

    if (empty($activity_errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO user_activities (user_id, exercise, duration) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $exercise, $duration]);
            // Redirect to refresh the page and update stats
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $activity_errors[] = 'Failed to add activity: ' . $e->getMessage();
        }
    }
}

// Handle updating the completion status of an activity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_activity_completion'])) {
    $activity_id = filter_input(INPUT_POST, 'activity_id', FILTER_VALIDATE_INT);
    $completed = isset($_POST['completed']) ? 1 : 0;

    if ($activity_id !== false && $activity_id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE user_activities SET completed = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$completed, $activity_id, $_SESSION['user_id']]);
        } catch (PDOException $e) {
            $activity_errors[] = 'Failed to update activity: ' . $e->getMessage();
        }
    }
}

// Handle deleting an activity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_activity'])) {
    $activity_id = filter_input(INPUT_POST, 'activity_id', FILTER_VALIDATE_INT);

    if ($activity_id !== false && $activity_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM user_activities WHERE id = ? AND user_id = ?");
            $stmt->execute([$activity_id, $_SESSION['user_id']]);
            // Redirect to refresh the page and update stats
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $activity_errors[] = 'Failed to delete activity: ' . $e->getMessage();
        }
    }
}

// Fetch the user's activities
try {
    $stmt = $pdo->prepare("SELECT id, exercise, duration, completed, created_at FROM user_activities WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $activities = [];
    $activity_errors[] = 'Failed to fetch activities: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0A0A0A, #1C2526);
            color: #FFFFFF;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background: #000000;
            padding: 20px;
            transition: transform 0.5s ease;
            z-index: 1000;
            transform: translateX(-100%);
        }

        .sidebar.loaded {
            transform: translateX(0);
        }

        .sidebar .logo {
            font-size: 24px;
            font-weight: 700;
            color: #FFFFFF;
            margin-bottom: 30px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar .nav-link {
            color: #FFFFFF;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: background 0.3s, transform 0.2s;
        }

        .sidebar .nav-link:hover {
            background: #6D28D9;
            transform: translateX(5px);
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(90deg, #0A0A0A, #2D2D2D);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
        }

        .header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .header .btn-danger {
            background-color: #F97316;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .header .btn-danger:hover {
            background-color: #E36209;
        }

        .card {
            background: linear-gradient(145deg, #0A0A0A, #2D2D2D);
            border: none;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease forwards;
        }

        .card:nth-child(2) {
            background: linear-gradient(145deg, #1C1A2E, #6D28D9);
        }

        .card:nth-child(3) {
            background: linear-gradient(145deg, #0A0A0A, #2D2D2D);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
        }

        .card h4 {
            color: #6D28D9;
            font-weight: 600;
            font-size: 1.7rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Stats Overview */
        .stats-overview {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .stats-chart-container {
            flex: 1;
            background: #00A3E0;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease forwards;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stats-chart-container h5 {
            font-size: 1.1rem;
            font-weight: 500;
            text-transform: uppercase;
            margin-bottom: 15px;
            color: #FFFFFF;
        }

        .stats-chart {
            width: 100%;
            max-height: 200px;
        }

        .stats-small-cards {
            display: flex;
            flex-direction: column;
            gap: 20px;
            flex: 0.7;
        }

        .stats-small-card {
            background: #003087;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease forwards;
        }

        .stats-chart-container:hover,
        .stats-small-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
        }

        .stats-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }

        .stats-icon i {
            color: #FFFFFF;
            font-size: 1.2rem;
        }

        .stats-small-card h5 {
            font-size: 1.1rem;
            font-weight: 500;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: #FFFFFF;
        }

        .stats-small-card .value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #FFFFFF;
        }

        .stats-small-card .unit {
            font-size: 1rem;
            font-weight: 400;
            color: #FFFFFF;
            text-transform: uppercase;
        }

        /* Activity History */
        .activity-form {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .activity-form select,
        .activity-form input {
            background: #1C2526;
            border: 2px solid #FFFFFF;
            color: #FFFFFF;
            border-radius: 10px;
            padding: 8px 15px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .activity-form select:focus,
        .activity-form input:focus {
            outline: none;
            border-color: #6D28D9;
            box-shadow: 0 0 5px rgba(109, 40, 217, 0.5);
        }

        .activity-form button {
            background: #6D28D9;
            border: none;
            padding: 8px 15px;
            border-radius: 10px;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .activity-form button:hover {
            background: #5B21B6;
            transform: scale(1.05);
        }

        .activity-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: background 0.3s ease;
            color: white;
        }

        .activity-item:hover {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
        }

        .activity-item i {
            font-size: 1.2rem;
            color: #6D28D9;
        }

        .activity-item .exercise-info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .activity-item .exercise-info input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #10B981;
        }

        .activity-item .time {
            font-size: 0.9rem;
            color: #A0A0A0;
        }

        .activity-item .delete-btn {
            background: transparent;
            border: none;
            color: #F97316;
            font-size: 1.2rem;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .activity-item .delete-btn:hover {
            color: #E36209;
            transform: scale(1.1);
        }

        .goal-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        .goal-item{
            span{
                color: white;
            }
        }

        /* Circular Progress Bar for Goals */
        .circular-progress {
            position: relative;
            width: 100px;
            height: 100px;
        }

        .circular-progress svg {
            transform: rotate(-90deg);
        }

        .circular-progress .circle-bg {
            fill: none;
            stroke: #1C2526;
            stroke-width: 10;
        }

        .circular-progress .circle {
            fill: none;
            stroke: #10B981;
            stroke-width: 10;
            stroke-linecap: round;
            stroke-dasharray: 283;
            stroke-dashoffset: 283;
            transition: stroke-dashoffset 2s ease;
        }

        .circular-progress .percentage {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 16px;
            font-weight: bold;
            color:white
        }

        .weight-progress-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .weight-progress {
            position: relative;
            width: 150px;
            height: 150px;
            background: conic-gradient(#10B981 0deg, #10B981 0deg, #EF4444 0deg, #EF4444 360deg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 2s ease;
        }

        .weight-progress::before {
            content: '';
            position: absolute;
            width: 110px;
            height: 110px;
            background: #0A0A0A;
            border-radius: 50%;
        }

        .weight-progress .percentage {
            position: relative;
            font-size: 20px;
            font-weight: 700;
            color: white;
        }

        /* Profile Form Styles */
        .profile-form label {
            font-size: 14px;
            margin-bottom: 5px;
            display: block;
            color: white;
        }

        .profile-form input {
            background: #1C2526;
            border: 2px solid #FFFFFF;
            color: #FFFFFF;
            border-radius: 15px;
            padding: 10px 15px;
            margin: 5px 0;
            width: 100%;
            max-width: 200px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .profile-form input:focus {
            outline: none;
            border-color: #6D28D9;
            box-shadow: 0 0 5px rgba(109, 40, 217, 0.5);
        }

        .profile-form button {
            background: #6D28D9;
            border: none;
            padding: 10px 20px;
            border-radius: 15px;
            margin-top: 15px;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .profile-form button:hover {
            background: #5B21B6;
            transform: scale(1.05);
        }

        .edit-profile-btn {
            background: #6D28D9;
            border: none;
            padding: 8px 15px;
            border-radius: 15px;
            font-size: 14px;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .edit-profile-btn:hover {
            background: #5B21B6;
            transform: scale(1.05);
        }

        .error {
            color: #F97316;
            font-size: 14px;
            margin-top: 10px;
        }

        /* Fade-In Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .hamburger {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1100;
            }

            .circular-progress,
            .weight-progress {
                width: 100px;
                height: 100px;
            }

            .weight-progress::before {
                width: 70px;
                height: 70px;
            }

            .circular-progress .percentage,
            .weight-progress .percentage {
                font-size: 16px;
            }

            .activity-form {
                flex-direction: column;
                align-items: stretch;
            }

            .activity-form select,
            .activity-form input {
                width: 100%;
            }

            .stats-overview {
                flex-direction: column;
            }

            .stats-chart-container,
            .stats-small-cards {
                flex: 1;
            }

            .stats-chart {
                max-height: 150px;
            }
        }
    </style>
</head>
<body>
    <!-- Hamburger Menu for Mobile -->
    <button class="hamburger btn btn-outline-light d-none" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <i class="fas fa-dumbbell"></i> Fitness App
        </div>
        <nav>
            <a href="http://localhost/gym/index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="http://localhost/gym/php/progress/log_workout.php" class="nav-link"><i class="fas fa-dumbbell"></i> Progress</a>
            <a href="http://localhost/gym/php/nutrition/meal_plan.php" class="nav-link"><i class="fas fa-utensils"></i> Nutrition</a>
            <a href="http://localhost/gym/php/classes/classes.php" class="nav-link"><i class="fas fa-users"></i> Classes</a>
            <a href="http://localhost/gym/php/membership/plans.php" class="nav-link"><i class="fas fa-credit-card"></i> Membership</a>
            <a href="http://localhost/gym/php/ecommerce/store.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Store</a>
            <a href="http://localhost/gym/php/trainers/trainers.php" class="nav-link"><i class="fas fa-user-tie"></i> Trainers</a>
            <a href="http://localhost/gym/php/contact/form.php" class="nav-link"><i class="fas fa-envelope"></i> Contact</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        </div>

        <!-- Stats Overview -->
        <div class="stats-overview">
            <!-- Chart Container (Weekly Progress) -->
            <div class="stats-chart-container">
                <h5>Weekly Progress</h5>
                <canvas id="weeklyProgressChart" class="stats-chart"></canvas>
            </div>
            <!-- Small Cards (Calories and Session) -->
            <div class="stats-small-cards">
                <div class="stats-small-card">
                    <div class="stats-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                    <h5>Calories</h5>
                    <div class="value"><?php echo number_format($calories, 0, '.', ','); ?><span class="unit">kcal</span></div>
                </div>
                <div class="stats-small-card">
                    <div class="stats-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h5>Session</h5>
                    <div class="value"><?php echo htmlspecialchars($sessions); ?></div>
                </div>
            </div>
        </div>

        <!-- Profile and Ideal Weight -->
        <div class="card">
            <h4><i class="fas fa-user"></i> Your Profile</h4>
            <?php if ($show_form): ?>
                <div class="profile-form">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="height"><i class="fas fa-ruler-vertical"></i> Height (cm):</label>
                            <input type="number" step="0.01" name="height" id="height" value="<?php echo htmlspecialchars($height ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="weight"><i class="fas fa-weight"></i> Weight (kg):</label>
                            <input type="number" step="0.01" name="weight" id="weight" value="<?php echo htmlspecialchars($weight ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="age"><i class="fas fa-calendar-alt"></i> Age:</label>
                            <input type="number" name="age" id="age" value="<?php echo htmlspecialchars($age ?? ''); ?>" required>
                        </div>
                        <button type="submit" name="update_profile">Update Profile</button>
                    </form>
                    <?php if (!empty($errors)): ?>
                        <div class="error">
                            <?php echo implode('<br>', $errors); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="mt-3">
                    <h5 style="color: #6D28D9; margin: 0 0 20px 0;"><i class="fas fa-weight-scale"></i> Weight Progress</h5>
                    <p style="color: #FFFFFF; font-size: 1.1rem;"><b>Current Weight:</b> <?php echo htmlspecialchars($weight); ?> kg</p>
                    <p style="color: #10B981; font-weight: 700; font-size: 1.1rem;"><b>Ideal Weight:</b> <?php echo round($ideal_weight, 1); ?> kg</p>
                    <div class="weight-progress-container">
                        <div class="weight-progress" data-progress="<?php echo $progress_degrees; ?>">
                            <div class="percentage"><?php echo round($weight_progress); ?>%</div>
                        </div>
                    </div>
                    <form method="POST" action="" class="text-center">
                        <button type="submit" name="edit_profile" class="edit-profile-btn">Edit Profile</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Activity History -->
        <div class="card">
            <h4><i class="fas fa-history"></i> Activity History</h4>
            <!-- Form to Add New Activity -->
            <div class="activity-form">
                <form method="POST" action="" class="d-flex gap-3 align-items-center">
                    <div>
                        <select name="exercise" id="exercise" required>
                            <option value="" disabled selected>Select Exercise</option>
                            <option value="Running">Running</option>
                            <option value="Jumping">Jumping</option>
                            <option value="Cardio">Cardio</option>
                            <option value="Fitness">Fitness</option>
                        </select>
                    </div>
                    <div>
                        <input type="number" name="duration" id="duration" placeholder="Duration (mins)" min="1" required>
                    </div>
                    <button type="submit" name="add_activity"><i class="fas fa-plus"></i> Add</button>
                </form>
            </div>
            <?php if (!empty($activity_errors)): ?>
                <div class="error">
                    <?php echo implode('<br>', $activity_errors); ?>
                </div>
            <?php endif; ?>
            <!-- Display Activities -->
            <?php if (empty($activities)): ?>
                <p style="color: white;">No activities added yet.</p>
            <?php else: ?>
                <?php foreach ($activities as $activity): ?>
                    <div class="activity-item">
                        <div class="exercise-info">
                            <?php
                            $icon = '';
                            switch ($activity['exercise']) {
                                case 'Running':
                                    $icon = '<i class="fas fa-running"></i>';
                                    break;
                                case 'Jumping':
                                    $icon = '<i class="fas fa-person-running"></i>';
                                    break;
                                case 'Cardio':
                                    $icon = '<i class="fas fa-heart-pulse"></i>';
                                    break;
                                case 'Fitness':
                                    $icon = '<i class="fas fa-dumbbell"></i>';
                                    break;
                            }
                            echo $icon;
                            ?>
                            <span><?php echo htmlspecialchars($activity['exercise']) . ' - ' . htmlspecialchars($activity['duration']) . ' mins'; ?></span>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="activity_id" value="<?php echo $activity['id']; ?>">
                                <input type="checkbox" name="completed" <?php echo $activity['completed'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <input type="hidden" name="update_activity_completion" value="1">
                            </form>
                        </div>
                        <span class="time"><?php echo date('M d, Y, h:i A', strtotime($activity['created_at'])); ?></span>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="activity_id" value="<?php echo $activity['id']; ?>">
                            <button type="submit" name="delete_activity" class="delete-btn"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Goal Setting with Circular Progress -->
        <div class="card">
            <h4><i class="fas fa-bullseye"></i> Current Goals</h4>
            <div class="goal-item">
                <span>Weight Goal (Ideal: <?php echo round($ideal_weight, 1); ?> kg)</span>
                <div class="circular-progress">
                    <svg width="100" height="100">
                        <circle class="circle-bg" cx="50" cy="50" r="45" />
                        <circle class="circle" cx="50" cy="50" r="45" data-progress="<?php echo $weight_progress; ?>" />
                    </svg>
                    <div class="percentage"><?php echo round($weight_progress); ?>%</div>
                </div>
            </div>
            <div class="goal-item">
                <span>Run 10 km</span>
                <div class="circular-progress">
                    <svg width="100" height="100">
                        <circle class="circle-bg" cx="50" cy="50" r="45" />
                        <circle class="circle" cx="50" cy="50" r="45" data-progress="40" />
                    </svg>
                    <div class="percentage">40%</div>
                </div>
            </div>
            <div class="goal-item">
                <span>Calories Burned (Goal: 2000 kcal)</span>
                <div class="circular-progress">
                    <svg width="100" height="100">
                        <circle class="circle-bg" cx="50" cy="50" r="45" />
                        <circle class="circle" cx="50" cy="50" r="45" data-progress="<?php echo $calories_progress; ?>" />
                    </svg>
                    <div class="percentage"><?php echo round($calories_progress); ?>%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Slide-In Animation
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.add('loaded');

            // Circular Progress Animation for Goals
            document.querySelectorAll('.circular-progress .circle').forEach(circle => {
                const progress = circle.getAttribute('data-progress');
                const circumference = 2 * Math.PI * 45;
                const offset = circumference - (progress / 100) * circumference;
                circle.style.strokeDashoffset = offset;
            });

            // Weight Progress Animation
            const weightProgress = document.querySelector('.weight-progress');
            const progressDegrees = weightProgress.getAttribute('data-progress');
            weightProgress.style.background = `conic-gradient(#10B981 0deg ${progressDegrees}deg, #EF4444 ${progressDegrees}deg 360deg)`;

            // Weekly Progress Chart
            const ctx = document.getElementById('weeklyProgressChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($days_of_week); ?>,
                    datasets: [{
                        label: 'Minutes',
                        data: <?php echo json_encode($weekly_progress); ?>,
                        backgroundColor: '#FFFFFF',
                        borderColor: '#FFFFFF',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Minutes',
                                color: '#FFFFFF',
                                font: {
                                    size: 14
                                }
                            },
                            ticks: {
                                color: '#FFFFFF',
                                stepSize: 10
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.2)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#FFFFFF'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#FFFFFF',
                            titleColor: '#000000',
                            bodyColor: '#000000'
                        }
                    }
                }
            });
        });

        // Toggle Sidebar for Mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>