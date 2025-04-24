<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo isset($_SESSION['user_id']) ? '../../../index.php' : '../../index.php'; ?>"><i class="fas fa-dumbbell me-2"></i>Fitness App</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo isset($_SESSION['user_id']) ? '../../index.php' : '../../index.php'; ?>">Home</a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Workouts Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="workoutsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Workouts
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="workoutsDropdown">
                            <li><a class="dropdown-item" href="../progress/log_workout.php">Log Workout</a></li>
                            <li><a class="dropdown-item" href="../workout/workout_plan.php">Workout Plan</a></li>
                            <li><a class="dropdown-item" href="../progress/progress.php">Progress</a></li>
                        </ul>
                    </li>

                    <!-- Nutrition Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="nutritionDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Nutrition
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nutritionDropdown">
                            <li><a class="dropdown-item" href="../nutrition/meal_plan.php">Log Nutrition</a></li>
                            <li><a class="dropdown-item" href="../nutrition/recipes.php">Recipes</a></li>
                            <li><a class="dropdown-item" href="../progress/progress.php">Progress</a></li>
                        </ul>
                    </li>

                    <!-- Classes Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="classesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Classes
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="classesDropdown">
                            <li><a class="dropdown-item" href="../classes/classes.php">View Classes</a></li>
                            <li><a class="dropdown-item" href="../classes/reservations.php">Book a Class</a></li>
                        </ul>
                    </li>

                    <!-- Membership Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="membershipDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Membership
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="membershipDropdown">
                            <li><a class="dropdown-item" href="../membership/plans.php">View Plans</a></li>
                            <li><a class="dropdown-item" href="../classes/reservations.php">Book with Membership</a></li>
                        </ul>
                    </li>

                    <!-- Store Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="storeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Store
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="storeDropdown">
                            <li><a class="dropdown-item" href="../ecommerce/store.php">Shop</a></li>
                            <li><a class="dropdown-item" href="../ecommerce/cart.php">Cart (<?php echo array_sum($_SESSION['cart'] ?? []); ?>)</a></li>
                            <li><a class="dropdown-item" href="../ecommerce/order_history.php">Order History</a></li>
                        </ul>
                    </li>

                    <!-- Trainers -->
                    <li class="nav-item">
                        <a class="nav-link" href="../trainers/trainers.php">Trainers</a>
                    </li>

                    <!-- Contact -->
                    <li class="nav-item">
                        <a class="nav-link" href="../contact/form.php">Contact</a>
                    </li>

                    <!-- Notifications -->
                    <li class="nav-item">
                        <a class="nav-link" href="../notifications/view.php">
                            <i class="fas fa-bell"></i> Notifications
                        </a>
                    </li>

                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle username" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="../dashboard/dashboard.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="../profile/profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="authDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="authDropdown">
                            <li><a class="dropdown-item" href="../auth/login/login.php">Login</a></li>
                            <li><a class="dropdown-item" href="../auth/register/register.php">Register</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>