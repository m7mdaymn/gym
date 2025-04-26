<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

// Sample recipes (in a real app, this would come from a database)
$recipes = [
    [
        'name' => 'Protein Smoothie',
        'ingredients' => '1 scoop protein powder, 1 banana, 1 cup almond milk, 1 tbsp peanut butter',
        'calories' => 350,
        'instructions' => 'Blend all ingredients until smooth. Serve chilled.'
    ],
    [
        'name' => 'Grilled Chicken Salad',
        'ingredients' => '200g grilled chicken, 2 cups mixed greens, 1 tbsp olive oil, 1 tsp lemon juice',
        'calories' => 400,
        'instructions' => 'Toss greens with olive oil and lemon juice. Top with grilled chicken.'
    ],
    [
        'name' => 'Oatmeal Bowl',
        'ingredients' => '1/2 cup oats, 1 cup water, 1 tbsp honey, 1/4 cup berries',
        'calories' => 300,
        'instructions' => 'Cook oats with water, top with honey and berries.'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipes - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../assets/css/global.css" rel="stylesheet">
</head>
<body>
    <!-- Include Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <!-- Recipes Section -->
    <div class="store-section" style="margin-bottom: 65px;">
        <div class="container">
            <h2 class="text-center mb-4">Recipes</h2>

            <div class="row">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="col-md-4 mb-4">
                        <div class="recipe-card">
                            <h5><?php echo htmlspecialchars($recipe['name']); ?></h5>
                            <p><strong>Calories:</strong> <?php echo $recipe['calories']; ?> kcal</p>
                            <p><strong>Ingredients:</strong> <?php echo htmlspecialchars($recipe['ingredients']); ?></p>
                            <p><strong>Instructions:</strong> <?php echo htmlspecialchars($recipe['instructions']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .recipe-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            color: #ffffff;
        }

        .recipe-card h5 {
            color: #00ddeb;
            margin-bottom: 1rem;
        }

    </style>
</body>
</html>