<?php


// Include database connection
require_once '../../includes/config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

// Predefined foods with nutritional information (per 100g)
$foods = [
    'Chicken Breast' => ['calories' => 165, 'fat' => 3.6, 'protein' => 31, 'carbs' => 0, 'minerals' => 0.5],
    'Salmon' => ['calories' => 208, 'fat' => 13, 'protein' => 20, 'carbs' => 0, 'minerals' => 0.8],
    'Brown Rice' => ['calories' => 123, 'fat' => 1, 'protein' => 2.6, 'carbs' => 26, 'minerals' => 0.3],
    'Broccoli' => ['calories' => 35, 'fat' => 0.4, 'protein' => 2.8, 'carbs' => 7, 'minerals' => 0.7],
    'Avocado' => ['calories' => 160, 'fat' => 15, 'protein' => 2, 'carbs' => 9, 'minerals' => 0.6],
    'Eggs' => ['calories' => 155, 'fat' => 11, 'protein' => 13, 'carbs' => 1.1, 'minerals' => 0.4],
    'Almonds' => ['calories' => 579, 'fat' => 50, 'protein' => 21, 'carbs' => 22, 'minerals' => 1.2],
    'Sweet Potato' => ['calories' => 86, 'fat' => 0.1, 'protein' => 1.6, 'carbs' => 20, 'minerals' => 0.5]
];

// Handle meal plan form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_meal') {
    $food_name = trim($_POST['food_name'] ?? '');
    $quantity = floatval($_POST['quantity'] ?? 0);
    $meal_date = $_POST['meal_date'] ?? '';

    if ($food_name && $quantity > 0 && $meal_date && isset($foods[$food_name])) {
        try {
            $food = $foods[$food_name];
            $calories = ($food['calories'] * $quantity) / 100;
            $fat = ($food['fat'] * $quantity) / 100;
            $protein = ($food['protein'] * $quantity) / 100;
            $carbs = ($food['carbs'] * $quantity) / 100;
            $minerals = ($food['minerals'] * $quantity) / 100;

            $stmt = $pdo->prepare("INSERT INTO nutrition_logs (user_id, meal_name, calories, fat, protein, carbs, minerals, meal_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $food_name, $calories, $fat, $protein, $carbs, $minerals, $meal_date]);
            $success = 'Meal added to your plan successfully!';
        } catch (PDOException $e) {
            $error = 'Error adding meal: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}

// Handle nutrition goal setting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'set_goal') {
    $goal_date = $_POST['goal_date'] ?? '';
    $calories = intval($_POST['calories'] ?? 0);
    $fat = floatval($_POST['fat'] ?? 0);
    $protein = floatval($_POST['protein'] ?? 0);
    $carbs = floatval($_POST['carbs'] ?? 0);
    $minerals = floatval($_POST['minerals'] ?? 0);

    if ($goal_date && $calories > 0 && $fat >= 0 && $protein >= 0 && $carbs >= 0 && $minerals >= 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO nutrition_goals (user_id, goal_date, calories, fat, protein, carbs, minerals) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE calories = ?, fat = ?, protein = ?, carbs = ?, minerals = ?");
            $stmt->execute([$_SESSION['user_id'], $goal_date, $calories, $fat, $protein, $carbs, $minerals, 
                            $calories, $fat, $protein, $carbs, $minerals]);
            $success = 'Nutrition goal set successfully!';
        } catch (PDOException $e) {
            $error = 'Error setting goal: ' . $e->getMessage();
        }
    } else {
        $error = 'Please provide valid goal values.';
    }
}

// Fetch user’s meal plans for today
$current_date = date('Y-m-d');
try {
    $stmt = $pdo->prepare("SELECT * FROM nutrition_logs WHERE user_id = ? AND DATE(meal_date) = ? ORDER BY meal_date DESC");
    $stmt->execute([$_SESSION['user_id'], $current_date]);
    $meal_plans = $stmt->fetchAll();

    // Calculate daily totals
    $daily_totals = ['calories' => 0, 'fat' => 0, 'protein' => 0, 'carbs' => 0, 'minerals' => 0];
    foreach ($meal_plans as $meal) {
        $daily_totals['calories'] += $meal['calories'];
        $daily_totals['fat'] += $meal['fat'];
        $daily_totals['protein'] += $meal['protein'];
        $daily_totals['carbs'] += $meal['carbs'];
        $daily_totals['minerals'] += $meal['minerals'];
    }
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Fetch today’s nutrition goals
try {
    $stmt = $pdo->prepare("SELECT * FROM nutrition_goals WHERE user_id = ? AND goal_date = ?");
    $stmt->execute([$_SESSION['user_id'], $current_date]);
    $nutrition_goal = $stmt->fetch();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Calculate progress percentages
$progress = [];
if ($nutrition_goal) {
    $progress['calories'] = min(100, ($daily_totals['calories'] / $nutrition_goal['calories']) * 100);
    $progress['fat'] = min(100, ($daily_totals['fat'] / $nutrition_goal['fat']) * 100);
    $progress['protein'] = min(100, ($daily_totals['protein'] / $nutrition_goal['protein']) * 100);
    $progress['carbs'] = min(100, ($daily_totals['carbs'] / $nutrition_goal['carbs']) * 100);
    $progress['minerals'] = min(100, ($daily_totals['minerals'] / $nutrition_goal['minerals']) * 100);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Plan - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../assets/css/global.css" rel="stylesheet">
    <style>
        .nutrition-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            color: #ffffff;
        }

        .progress {
            height: 25px;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.3);
        }

        .progress-bar {
            transition: width 1s ease-in-out;
        }
    </style>
</head>
<body>
    <!-- Include Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <!-- Meal Plan Section -->
    <div class="store-section">
        <div class="container">
            <h2 class="text-center mb-4">Meal Plan</h2>

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

            <!-- Set Nutrition Goals -->
            <div class="mb-4">
                <h4>Set Daily Nutrition Goals</h4>
                <form method="POST">
                    <input type="hidden" name="action" value="set_goal">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label for="goal_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="goal_date" name="goal_date" value="<?php echo $current_date; ?>" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="calories_goal" class="form-label">Calories (kcal)</label>
                            <input type="number" class="form-control" id="calories_goal" name="calories" value="<?php echo $nutrition_goal['calories'] ?? 2000; ?>" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="fat_goal" class="form-label">Fat (g)</label>
                            <input type="number" step="0.1" class="form-control" id="fat_goal" name="fat" value="<?php echo $nutrition_goal['fat'] ?? 70; ?>" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="protein_goal" class="form-label">Protein (g)</label>
                            <input type="number" step="0.1" class="form-control" id="protein_goal" name="protein" value="<?php echo $nutrition_goal['protein'] ?? 150; ?>" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="carbs_goal" class="form-label">Carbs (g)</label>
                            <input type="number" step="0.1" class="form-control" id="carbs_goal" name="carbs" value="<?php echo $nutrition_goal['carbs'] ?? 250; ?>" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="minerals_goal" class="form-label">Minerals (mg)</label>
                            <input type="number" step="0.1" class="form-control" id="minerals_goal" name="minerals" value="<?php echo $nutrition_goal['minerals'] ?? 5; ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Set Goals</button>
                </form>
            </div>

            <!-- Add Meal Form -->
            <div class="mb-4">
                <h4>Add a Meal to Your Plan</h4>
                <form method="POST">
                    <input type="hidden" name="action" value="add_meal">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="food_name" class="form-label">Select Food</label>
                            <select class="form-control" id="food_name" name="food_name" required>
                                <option value="">-- Select a Food --</option>
                                <?php foreach ($foods as $food_name => $nutrients): ?>
                                    <option value="<?php echo htmlspecialchars($food_name); ?>" 
                                            data-calories="<?php echo $nutrients['calories']; ?>"
                                            data-fat="<?php echo $nutrients['fat']; ?>"
                                            data-protein="<?php echo $nutrients['protein']; ?>"
                                            data-carbs="<?php echo $nutrients['carbs']; ?>"
                                            data-minerals="<?php echo $nutrients['minerals']; ?>">
                                        <?php echo htmlspecialchars($food_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="quantity" class="form-label">Quantity (grams)</label>
                            <input type="number" step="1" class="form-control" id="quantity" name="quantity" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="meal_date" class="form-label">Meal Date</label>
                            <input type="date" class="form-control" id="meal_date" name="meal_date" value="<?php echo $current_date; ?>" required>
                        </div>
                    </div>
                    <div class="nutrition-card mb-3" id="food-nutrition">
                        <h5>Nutritional Information (per 100g)</h5>
                        <p><strong>Calories:</strong> <span id="food-calories">0</span> kcal</p>
                        <p><strong>Fat:</strong> <span id="food-fat">0</span> g</p>
                        <p><strong>Protein:</strong> <span id="food-protein">0</span> g</p>
                        <p><strong>Carbs:</strong> <span id="food-carbs">0</span> g</p>
                        <p><strong>Minerals:</strong> <span id="food-minerals">0</span> mg</p>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Meal</button>
                </form>
            </div>

            <!-- Daily Progress -->
            <?php if ($nutrition_goal): ?>
                <div class="mb-4">
                    <h4>Today's Nutrition Progress</h4>
                    <div class="nutrition-card">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <p><strong>Calories:</strong> <?php echo round($daily_totals['calories']); ?> / <?php echo $nutrition_goal['calories']; ?> kcal</p>
                                <div class="progress">
                                    <div class="progress-bar <?php echo $progress['calories'] >= 100 ? 'bg-success' : 'bg-warning'; ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo $progress['calories']; ?>%;" 
                                         aria-valuenow="<?php echo $progress['calories']; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?php echo round($progress['calories']); ?>%
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <p><strong>Fat:</strong> <?php echo round($daily_totals['fat'], 1); ?> / <?php echo $nutrition_goal['fat']; ?> g</p>
                                <div class="progress">
                                    <div class="progress-bar <?php echo $progress['fat'] >= 100 ? 'bg-success' : 'bg-warning'; ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo $progress['fat']; ?>%;" 
                                         aria-valuenow="<?php echo $progress['fat']; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?php echo round($progress['fat']); ?>%
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <p><strong>Protein:</strong> <?php echo round($daily_totals['protein'], 1); ?> / <?php echo $nutrition_goal['protein']; ?> g</p>
                                <div class="progress">
                                    <div class="progress-bar <?php echo $progress['protein'] >= 100 ? 'bg-success' : 'bg-warning'; ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo $progress['protein']; ?>%;" 
                                         aria-valuenow="<?php echo $progress['protein']; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?php echo round($progress['protein']); ?>%
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <p><strong>Carbs:</strong> <?php echo round($daily_totals['carbs'], 1); ?> / <?php echo $nutrition_goal['carbs']; ?> g</p>
                                <div class="progress">
                                    <div class="progress-bar <?php echo $progress['carbs'] >= 100 ? 'bg-success' : 'bg-warning'; ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo $progress['carbs']; ?>%;" 
                                         aria-valuenow="<?php echo $progress['carbs']; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?php echo round($progress['carbs']); ?>%
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <p><strong>Minerals:</strong> <?php echo round($daily_totals['minerals'], 1); ?> / <?php echo $nutrition_goal['minerals']; ?> mg</p>
                                <div class="progress">
                                    <div class="progress-bar <?php echo $progress['minerals'] >= 100 ? 'bg-success' : 'bg-warning'; ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo $progress['minerals']; ?>%;" 
                                         aria-valuenow="<?php echo $progress['minerals']; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?php echo round($progress['minerals']); ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Meal Plan List -->
            <?php if (!empty($meal_plans)): ?>
                <div class="cart-table">
                    <h4>Today's Meals</h4>
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Food Name</th>
                                <th>Calories (kcal)</th>
                                <th>Fat (g)</th>
                                <th>Protein (g)</th>
                                <th>Carbs (g)</th>
                                <th>Minerals (mg)</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($meal_plans as $meal): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($meal['meal_name']); ?></td>
                                    <td><?php echo round($meal['calories']); ?></td>
                                    <td><?php echo round($meal['fat'], 1); ?></td>
                                    <td><?php echo round($meal['protein'], 1); ?></td>
                                    <td><?php echo round($meal['carbs'], 1); ?></td>
                                    <td><?php echo round($meal['minerals'], 1); ?></td>
                                    <td><?php echo $meal['meal_date']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No meals in your plan yet for today. Add one above!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update nutritional information when a food is selected
        document.getElementById('food_name').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById('food-calories').textContent = selectedOption.getAttribute('data-calories');
            document.getElementById('food-fat').textContent = selectedOption.getAttribute('data-fat');
            document.getElementById('food-protein').textContent = selectedOption.getAttribute('data-protein');
            document.getElementById('food-carbs').textContent = selectedOption.getAttribute('data-carbs');
            document.getElementById('food-minerals').textContent = selectedOption.getAttribute('data-minerals');
        });
    </script>
</body>
</html>