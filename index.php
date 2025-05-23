<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-dumbbell me-2"></i>Fitness App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#classes">Classes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#trainers">Trainers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#membership">Membership</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#shop">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#blog">Blog</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userFeaturesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                User Features
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userFeaturesDropdown">
                                <li><a class="dropdown-item" href="#workouts">Workouts</a></li>
                                <li><a class="dropdown-item" href="#nutrition">Nutrition</a></li>
                                <li><a class="dropdown-item" href="#progress">Progress</a></li>
                                <li><a class="dropdown-item" href="php/dashboard/dashboard.php">Dashboard</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">Hello, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="php/auth/logout/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="authDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Account
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="authDropdown">
                                <li><a class="dropdown-item" href="php/auth/register/register.php">Register</a></li>
                                <li><a class="dropdown-item" href="php/auth/login/login.php">Login</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <!-- Notifications Icon -->
                    <li class="nav-item">
                        <a href="php/notifications/view.php" class="nav-icon"><i class="fas fa-bell"></i></a>
                    </li>
                    <!-- Contact Icon -->
                    <li class="nav-item">
                        <i class="fas fa-envelope nav-icon" id="contactToggle"></i>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Toast Notification -->
    <div class="toast" id="toastNotification">
        <span id="toastMessage"></span>
    </div>

    <section class="hero-section" id="home">
        <img src="assets/image/heroo.jpg" loading="lazy" alt="Hero Image" />
        <div class="container text-center">
            <h1 class="mb-4">Track, Train, Transform</h1>
            <p class="lead mb-4">Your ultimate fitness companion for workouts, nutrition, and community support.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#membership" class="btn btn-primary btn-lg">Join Now</a>
                <a href="#features" class="btn btn-outline-light btn-lg">Explore Features</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section reveal" id="features">
        <img src="assets/image/gym-background.jpg" alt="Gym Background" class="background-img" loading="lazy">
        <div class="container">
            <h2 class="text-center mb-5">Our Features</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-dumbbell fa-3x mb-3"></i>
                        <h4>Workout Tracking</h4>
                        <p>Log your exercises and monitor your progress with ease.</p>
                        <a href="php/workout/workout_plan.php" class="btn btn-outline-light">Start Now</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-utensils fa-3x mb-3"></i>
                        <h4>Nutrition Monitoring</h4>
                        <p>Track your meals and optimize your diet for better results.</p>
                        <a href="php/nutrition/meal_plan.php" class="btn btn-outline-light">Track Nutrition</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <h4>Progress Analytics</h4>
                        <p>Analyze your fitness journey with detailed insights.</p>
                        <a href="php/progress/log_workout.php" class="btn btn-outline-light">View Progress</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-tachometer-alt fa-3x mb-3"></i>
                        <h4>Stats Overview</h4>
                        <p>View your fitness stats at a glance.</p>
                        <a href="php/dashboard/dashboard.php" class="btn btn-outline-light">Go to Dashboard</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-bullseye fa-3x mb-3"></i>
                        <h4>Goal Setting</h4>
                        <p>Set and track your fitness goals.</p>
                        <a href="php/dashboard/dashboard.php" class="btn btn-outline-light">Set Goals</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-history fa-3x mb-3"></i>
                        <h4>Activity History</h4>
                        <p>Review your past workouts and progress.</p>
                        <a href="php/dashboard/dashboard.php" class="btn btn-outline-light">View History</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Classes Section -->
    <section class="classes-section reveal" id="classes">
        <div class="container">
            <h2 class="text-center mb-5">Group Classes</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <img src="assets/image/yoga-class.jpg" alt="Yoga Class" class="class-img" loading="lazy">
                        <h4>Yoga Sessions</h4>
                        <p>Join our calming yoga classes to improve flexibility.</p>
                        <a href="php/classes/classes.php" class="btn btn-outline-light">Book Now</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <img src="assets/image/spin.jpg" alt="Spin Class" class="class-img" loading="lazy">
                        <h4>Spin Classes</h4>
                        <p>High-energy cycling sessions to boost cardio.</p>
                        <a href="php/classes/classes.php" class="btn btn-outline-light">Book Now</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <img src="assets/image/strength.jpg" alt="Strength Training" class="class-img" loading="lazy">
                        <h4>Strength Training</h4>
                        <p>Group workouts to build muscle and strength.</p>
                        <a href="php/classes/classes.php" class="btn btn-outline-light">Book Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trainers Section -->
    <section class="trainers-section reveal" id="trainers">
        <div class="container">
            <h2 class="text-center mb-5">Meet Our Trainers</h2>
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="coach-card">
                        <img src="assets/image/coach.jpg" alt="Mohamed Ayman" class="trainer-img" loading="lazy">
                        <div class="card-body text-center">
                            <h5 class="fw-bold">Mohamed Ayman</h5>
                            <button class="btn btn-primary btn-sm mt-2 contact-trainer-btn" data-trainer-name="Mohamed Ayman" data-bs-toggle="modal" data-bs-target="#trainerContactModal">Contact Trainer</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="coach-card">
                        <img src="assets/image/mohamed_mahmoud.jpg" alt="Mohamed Mahmoud" class="trainer-img" loading="lazy">
                        <div class="card-body text-center">
                            <h5 class="fw-bold">Mohamed Mahmoud</h5>
                            <button class="btn btn-primary btn-sm mt-2 contact-trainer-btn" data-trainer-name="Mohamed Mahmoud" data-bs-toggle="modal" data-bs-target="#trainerContactModal">Contact Trainer</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="coach-card">
                        <img src="assets/image/tolba.jpg" alt="Abdelrahman Tolba" class="trainer-img" loading="lazy">
                        <div class="card-body text-center">
                            <h5 class="fw-bold">Abdelrahman Tolba</h5>
                            <button class="btn btn-primary btn-sm mt-2 contact-trainer-btn" data-trainer-name="Abdelrahman Tolba" data-bs-toggle="modal" data-bs-target="#trainerContactModal">Contact Trainer</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="coach-card">
                        <img src="assets/image/tolbaimg.jpg" alt="Zyad Mohamed" class="trainer-img" loading="lazy">
                        <div class="card-body text-center">
                            <h5 class="fw-bold">Zyad Mohamed</h5>
                            <button class="btn btn-primary btn-sm mt-2 contact-trainer-btn" data-trainer-name="Zyad Mohamed" data-bs-toggle="modal" data-bs-target="#trainerContactModal">Contact Trainer</button>
                        </div>
                    </div>
                </div>

                <!-- Single Contact Modal -->
                <div class="modal fade" id="trainerContactModal" tabindex="-1" aria-labelledby="trainerContactModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content trainer-modal-content">
                            <div class="modal-header trainer-modal-header">
                                <h5 class="modal-title" id="trainerContactModalLabel">Contact Trainer</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body trainer-modal-body">
                                <form action="php/contact/contact_coach.php" method="POST" id="trainerContactForm">
                                    <input type="hidden" name="coach_name" id="coachNameInput">
                                    <div class="mb-4 text-center">
                                        <h6 class="trainer-name-display">You are contacting: <span id="trainerNameDisplay"></span></h6>
                                    </div>
                                    <div class="mb-3">
                                        <label for="userName" class="form-label">Your Name</label>
                                        <input type="text" class="form-control trainer-input" id="userName" name="name" placeholder="Enter your full name (e.g., John Doe)" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="userEmail" class="form-label">Your Email</label>
                                        <input type="email" class="form-control trainer-input" id="userEmail" name="email" placeholder="Enter your email (e.g., john@example.com)" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="userMessage" class="form-label">Message</label>
                                        <textarea class="form-control trainer-input" id="userMessage" name="message" rows="4" placeholder="Write your message (e.g., I need help with my workout plan)" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 trainer-submit-btn">Send Message</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Membership Plans Section -->
    <section class="membership-section reveal" id="membership">
        <div class="container">
            <h2 class="text-center mb-5">Membership Plans</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="plan-card">
                        <div class="plan-img-wrapper">
                            <img src="assets/image/basic-plan.jpg" alt="Basic Plan" class="plan-img" loading="lazy">
                        </div>
                        <h3>Basic Plan</h3>
                        <p class="price">$29.99/month</p>
                        <ul>
                            <li>Workout Plans</li>
                            <li>Nutrition Guidance</li>
                            <li>Progress Tracking</li>
                        </ul>
                        <a href="php/membership/plans.php" class="btn btn-primary">Choose Plan</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="plan-card">
                        <div class="plan-img-wrapper">
                            <img src="assets/image/pro-plan.jpg" alt="Pro Plan" class="plan-img" loading="lazy">
                        </div>
                        <h3>Pro Plan</h3>
                        <p class="price">$49.99/month</p>
                        <ul>
                            <li>Custom Workouts</li>
                            <li>Nutrition Plans</li>
                            <li>Trainer Access</li>
                        </ul>
                        <a href="php/membership/plans.php" class="btn btn-primary">Choose Plan</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="plan-card premium">
                        <div class="plan-img-wrapper">
                            <img src="assets/image/elite-plan.jpg" alt="Elite Plan" class="plan-img" loading="lazy">
                            <span class="premium-badge">Premium</span>
                        </div>
                        <h3>Elite Plan</h3>
                        <p class="price">$99.99/month</p>
                        <ul>
                            <li>Personalized Coaching</li>
                            <li>Exclusive Group Classes</li>
                            <li>Priority Support Access</li>
                            <li>Advanced Progress Analytics</li>
                            <li>Monthly Fitness Assessments</li>
                            <li>VIP Event Invitations</li>
                        </ul>
                        <a href="php/membership/plans.php" class="btn btn-primary">Choose Plan</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Shop Section -->
    <section class="shop-section reveal" id="shop">
        <div class="container">
            <h2 class="text-center mb-5">Fitness Shop</h2>
            <div class="row text-center">
                <div class="col-md-6 mb-4">
                    <div class="shop-card best-seller">
                        <div class="shop-img-wrapper">
                            <img src="assets/image/supplements.jpg" alt="Supplements" class="shop-img" loading="lazy">
                            <span class="best-seller-badge">Best Seller</span>
                        </div>
                        <h4>Premium Supplements</h4>
                        <p>Elevate your performance with our top-rated protein powders, pre-workouts, and recovery formulas.</p>
                        <a href="php/ecommerce/store.php" class="btn btn-outline-light">Shop Now</a>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="shop-card">
                        <div class="shop-img-wrapper">
                            <img src="assets/image/equipment.jpg" alt="Equipment" class="shop-img" loading="lazy">
                        </div>
                        <h4>Durable Equipment</h4>
                        <p>Build your home gym with our high-quality dumbbells, kettlebells, and resistance bands.</p>
                        <a href="php/ecommerce/store.php" class="btn btn-outline-light">Shop Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section class="blog-section reveal" id="blog">
        <div class="container">
            <h2 class="text-center mb-5">Fitness Blog</h2>
            <div class="mb-5">
                <h3 class="text-center mb-4">Fitness Tips</h3>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="blog-card">
                            <div class="row g-0">
                                <div class="col-md-5">
                                    <img src="assets/image/top-5-workouts.jpg" alt="Top 5 Workouts" class="blog-img" loading="lazy">
                                </div>
                                <div class="col-md-7 d-flex align-items-center">
                                    <div class="blog-content">
                                        <h4>Top 5 Workouts for Strength</h4>
                                        <p>Discover the best exercises to build strength and endurance, perfect for all fitness levels.</p>
                                        <a href="php/blog/index.php" class="btn btn-outline-light">Read More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="blog-card">
                            <div class="row g-0">
                                <div class="col-md-5">
                                    <img src="assets/image/nutrition-tips.jpg" alt="Nutrition Tips" class="blog-img" loading="lazy">
                                </div>
                                <div class="col-md-7 d-flex align-items-center">
                                    <div class="blog-content">
                                        <h4>Nutrition Tips for Peak Performance</h4>
                                        <p>Learn how to fuel your body with the right nutrients for optimal fitness results.</p>
                                        <a href="php/blog/index.php" class="btn btn-outline-light">Read More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-center mb-4">Recovery Guides</h3>
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="blog-card featured">
                            <div class="row g-0">
                                <div class="col-md-5">
                                    <img src="assets/image/recovery-guide.jpg" alt="Recovery Guide" class="blog-img" loading="lazy">
                                    <span class="featured-badge">Featured</span>
                                </div>
                                <div class="col-md-7 d-flex align-items-center">
                                    <div class="blog-content">
                                        <h4>Ultimate Recovery Guide</h4>
                                        <p>Explore essential tips for effective post-workout recovery to keep you at your best.</p>
                                        <a href="php/blog/index.php" class="btn btn-outline-light">Read More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Workouts Section -->
    <section class="workouts-section reveal" id="workouts">
        <div class="container">
            <h2 class="text-center mb-5">Workout Plans</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="workout-card">
                        <div class="card-inner">
                            <div class="card-front">
                                <img src="assets/image/cardio-plan.jpg" alt="Cardio Plan" class="workout-img" loading="lazy">
                                <h4>Cardio Plans</h4>
                                <p>Boost your endurance with tailored cardio routines.</p>
                                <span class="intensity-label">Intensity: Moderate</span>
                            </div>
                            <div class="card-back">
                                <h4>Details</h4>
                                <p>Includes running, cycling, and HIIT workouts to improve cardiovascular health.</p>
                                <a href="php/workout/workout_plan.php" class="btn btn-outline-light">Start Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="workout-card popular">
                        <div class="card-inner">
                            <div class="card-front">
                                <img src="assets/image/strength-plan.jpg" alt="Strength Plan" class="workout-img" loading="lazy">
                                <span class="popular-badge">Most Popular</span>
                                <h4>Strength Plans</h4>
                                <p>Build muscle with structured strength programs.</p>
                                <span class="intensity-label">Intensity: High</span>
                            </div>
                            <div class="card-back">
                                <h4>Details</h4>
                                <p>Focus on compound lifts and progressive overload for maximum gains.</p>
                                <a href="php/workout/workout_plan.php" class="btn btn-outline-light">Start Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="workout-card">
                        <div class="card-inner">
                            <div class="card-front">
                                <img src="assets/image/flexibility-plan.jpg" alt="Flexibility Plan" class="workout-img" loading="lazy">
                                <h4>Flexibility Plans</h4>
                                <p>Improve mobility with yoga and stretching.</p>
                                <span class="intensity-label">Intensity: Low</span>
                            </div>
                            <div class="card-back">
                                <h4>Details</h4>
                                <p>Includes daily stretching routines and yoga flows for better flexibility.</p>
                                <a href="php/workout/workout_plan.php" class="btn btn-outline-light">Start Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nutrition Section -->
    <section class="nutrition-section reveal" id="nutrition">
        <div class="container">
            <h2 class="text-center mb-5">Nutrition Tracking</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="nutrition-card">
                        <div class="nutrition-img-wrapper">
                            <img src="assets/image/meal-plan.jpg" alt="Meal Plan" class="nutrition-img" loading="lazy">
                        </div>
                        <h4>Custom Meal Plans</h4>
                        <p>Get personalized diet plans tailored to your fitness goals and preferences.</p>
                        <a href="php/nutrition/meal_plan.php" class="btn btn-outline-light">Get Plan</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="nutrition-card">
                        <div class="nutrition-img-wrapper">
                            <img src="assets/image/calorie-tracking.jpg" alt="Calorie Tracking" class="nutrition-img" loading="lazy">
                        </div>
                        <h4>Smart Calorie Tracking</h4>
                        <p>Easily monitor your daily calorie intake with our intuitive tracking tools.</p>
                        <a href="php/nutrition/meal_plan.php" class="btn btn-outline-light">Track Now</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="nutrition-card recommended">
                        <div class="nutrition-img-wrapper">
                            <img src="assets/image/recipes.jpg" alt="Recipes" class="nutrition-img" loading="lazy">
                            <span class="recommended-badge">Recommended</span>
                        </div>
                        <h4>Healthy Recipes</h4>
                        <p>Discover delicious, nutrient-packed recipes to fuel your fitness journey.</p>
                        <a href="php/nutrition/meal_plan.php" class="btn btn-outline-light">Browse Recipes</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Progress Section -->
    <section class="progress-section reveal" id="progress">
        <div class="container">
            <h2 class="text-center mb-5">Track Your Progress</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="progress-card">
                        <div class="progress-img-wrapper">
                            <img src="assets/image/weight-tracking.jpg" alt="Weight Tracking" class="progress-img" loading="lazy">
                        </div>
                        <h6>Weight Tracking</h6>
                        <p>Monitor your weight changes over time with detailed insights.</p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 70%;">70%</div>
                        </div>
                        <a href="php/progress/log_workout.php" class="btn btn-outline-light">Track Weight</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="progress-card">
                        <div class="progress-img-wrapper">
                            <img src="assets/image/workout-stats.jpg" alt="Workout Stats" class="progress-img" loading="lazy">
                        </div>
                        <h4>Workout Stats</h4>
                        <p>Analyze your workout performance with in-depth statistics.</p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 85%;">85%</div>
                        </div>
                        <a href="php/progress/log_workout.php" class="btn btn-outline-light">View Stats</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="progress-card milestone">
                        <div class="progress-img-wrapper">
                            <img src="assets/image/achievements.jpg" alt="Achievements" class="progress-img" loading="lazy">
                            <span class="milestone-badge">Milestone</span>
                        </div>
                        <h4>Achievements</h4>
                        <p>Celebrate your fitness milestones and stay motivated.</p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 100%;">100%</div>
                        </div>
                        <a href="php/progress/log_workout.php" class="btn btn-outline-light">View Achievements</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>Fitness App</h5>
                    <p>Empowering your fitness journey with expert tools and coaching.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Our Gym Partners</h5>
                    <div class="gym-partners d-flex flex-wrap justify-content-center w-1">
                        <img class="w-" src="assets/image/evolve_text_white.webp" alt="Partner 1" loading="lazy">
                        <img src="assets/image/logo.webp" alt="Partner 2" loading="lazy">
                        <img src="assets/image/جيماوي-تكست.-1-optimized.webp" alt="Partner 3" loading="lazy">
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="php/dashboard/dashboard.php">Dashboard</a></li>
                        <li><a href="php/workout/workout_plan.php">Workouts</a></li>
                        <li><a href="php/nutrition/meal_plan.php">Nutrition</a></li>
                        <li><a href="php/progress/log_workout.php">Progress</a></li>
                    </ul>
                </div>
            </div>
            <hr class="bg-light">
            <p class="text-center mb-0">© 2025 Fitness App. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Contact Section -->
    <section class="contact-section reveal" id="contact">
        <div class="form-container text-center" id="contactForm">
            <i class="fas fa-times contact-toggle mb-3" id="contactClose"></i>
            <h2 class="mb-3">Contact Us</h2>
            <form action="php/contact/form.php" method="POST" onsubmit="showToast(event, 'Message sent successfully!')">
                <div class="mb-3">
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="mb-3">
                    <textarea class="form-control" id="message" name="message" rows="3" placeholder="Write your message" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Message</button>
            </form>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Contact Form Toggle
        const contactToggle = document.getElementById('contactToggle');
        const contactClose = document.getElementById('contactClose');
        const contactForm = document.getElementById('contactForm');

        contactToggle.addEventListener('click', () => {
            contactForm.classList.add('active');
        });

        contactClose.addEventListener('click', () => {
            contactForm.classList.remove('active');
        });

        // Scroll-Triggered Animations
        const reveals = document.querySelectorAll('.reveal');
        window.addEventListener('scroll', () => {
            reveals.forEach((el) => {
                const windowHeight = window.innerHeight;
                const elementTop = el.getBoundingClientRect().top;
                if (elementTop < windowHeight - 100) {
                    el.classList.add('active');
                }
            });
        });

        // Highlight Active Nav Link
        const sections = document.querySelectorAll('section');
        const navLinks = document.querySelectorAll('.nav-link');
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (scrollY >= sectionTop - 60) {
                    current = section.getAttribute('id');
                }
            });
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').includes(current)) {
                    link.classList.add('active');
                }
            });
        });

        // Toast Notification Function
        function showToast(event, message) {
            event.preventDefault();
            const toast = document.getElementById('toastNotification');
            const toastMessage = document.getElementById('toastMessage');
            toastMessage.textContent = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
                event.target.submit();
            }, 2000);
        }

        // Add active class to progress bars on scroll
        const progressCards = document.querySelectorAll('.progress-section .feature-card');
        window.addEventListener('scroll', () => {
            progressCards.forEach(card => {
                const cardTop = card.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                if (cardTop < windowHeight - 100) {
                    card.classList.add('active');
                }
            });
        });

        // Dynamic Trainer Modal Handler
        document.querySelectorAll('.contact-trainer-btn').forEach(button => {
            button.addEventListener('click', function () {
                const trainerName = this.getAttribute('data-trainer-name');
                // Update modal title
                document.getElementById('trainerContactModalLabel').textContent = `Contact ${trainerName}`;
                // Update the hidden input for the form
                document.getElementById('coachNameInput').value = trainerName;
                // Update the display text in the modal
                document.getElementById('trainerNameDisplay').textContent = trainerName;
                // Update the toast message dynamically
                const form = document.getElementById('trainerContactForm');
                form.onsubmit = function (event) {
                    showToast(event, `Message sent to ${trainerName}!`);
                };
            });
        });
    </script>
</body>
</html>