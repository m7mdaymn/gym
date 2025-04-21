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
    <link href="assest/css/style.css" rel="stylesheet">
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
                        <li class="nav-item">
                            <a class="nav-link" href="#workouts">Workouts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#nutrition">Nutrition</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#progress">Progress</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="php/auth/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="php/auth/register/register.html">Register</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="php/auth/login/login.html">Login</a>
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
  <img src="image/heroo.jpg" loading="lazy" alt="..." />
  <div class="container text-center">
    <h1 class="mb-4">Track, Train, Transform</h1>
    <p class="lead mb-4">Your ultimate fitness companion for workouts, nutrition, and community support.</p>
    <div class="d-flex justify-content-center gap-3">
      <a href="php/auth/register/register.html" class="btn btn-primary btn-lg">Join Now</a>
      <a href="#features" class="btn btn-outline-light btn-lg">Explore Features</a>
    </div>
  </div>
</section>

    <!-- Features Section -->
    <section class="features-section reveal" id="features">
    <img src="image/gym-background.jpg" alt="Gym Background" class="background-img" loading="lazy">
        <div class="container">
            <h2 class="text-center mb-5">Our Features</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-dumbbell fa-3x mb-3"></i>
                        <h4>Workout Tracking</h4>
                        <p>Log your exercises and monitor your progress with ease.</p>
                        <a href="php/workout/plans.php" class="btn btn-outline-light">Start Now</a>
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
                        <a href="php/dashboard/index.php" class="btn btn-outline-light">Go to Dashboard</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-bullseye fa-3x mb-3"></i>
                        <h4>Goal Setting</h4>
                        <p>Set and track your fitness goals.</p>
                        <a href="php/dashboard/index.php" class="btn btn-outline-light">Set Goals</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-history fa-3x mb-3"></i>
                        <h4>Activity History</h4>
                        <p>Review your past workouts and progress.</p>
                        <a href="php/dashboard/index.php" class="btn btn-outline-light">View History</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
<!-- Classes Section -->
<section class="classes-section reveal" id="classes">
    <!-- Background image for the section can be added via CSS or inline -->
    <div class="container">
        <h2 class="text-center mb-5">Group Classes</h2>
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <img src="image\yoga-class.jpg" alt="Yoga Class" class="class-img">
                    <h4>Yoga Sessions</h4>
                    <p>Join our calming yoga classes to improve flexibility.</p>
                    <a href="php/class_schedule/calendar.php" class="btn btn-outline-light">Book Now</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <img src="image\spin.jpg" alt="Spin Class" class="class-img">
                    <h4>Spin Classes</h4>
                    <p>High-energy cycling sessions to boost cardio.</p>
                    <a href="php/class_schedule/calendar.php" class="btn btn-outline-light">Book Now</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <img src="image\strength.jpg" alt="Strength Training" class="class-img">
                    <h4>Strength Training</h4>
                    <p>Group workouts to build muscle and strength.</p>
                    <a href="php/class_schedule/calendar.php" class="btn btn-outline-light">Book Now</a>
                </div>
            </div>
        </div>
    </div>
</section>


    <!-- Trainers Section -->
    <section class="trainers-section reveal" id="trainers">
    <!-- <img src="image\coaches.jpg" alt="Mohamed Ayman" class="trainer-img"> -->
    <div class="container">
            <h2 class="text-center mb-5">Meet Our Trainers</h2>
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="coach-card">
                    <img src="image\coach.jpg" alt="Mohamed Ayman" class="trainer-img">
                        <div class="card-body text-center">
                            <h5 class="fw-bold">Coach Hevy</h5>
                            <button class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#contactModal0">Contact Trainer</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="coach-card">
                    <img src="image\coach.jpg" alt="Mohamed Ayman" class="trainer-img">
                        <div class="card-body text-center">
                            <h5 class="fw-bold">Mohamed Ayman</h5>
                            <button class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#contactModal1">Contact Trainer</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="coach-card">
                    <img src="image\coach.jpg" alt="Mohamed Ayman" class="trainer-img">
                        <div class="card-body text-center">
                            <h5 class="fw-bold">Abdelrahman Tolba</h5>
                            <button class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#contactModal2">Contact Trainer</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="coach-card">
                    <img src="image\coach.jpg" alt="Mohamed Ayman" class="trainer-img">
                        <div class="card-body text-center">
                            <h5 class="fw-bold">Zyad Mohamed</h5>
                            <button class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#contactModal3">Contact Trainer</button>
                        </div>
                    </div>
                </div>

                <!-- Contact Modals -->
                <div class="modal fade" id="contactModal0" tabindex="-1" aria-labelledby="contactModalLabel0" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="contactModalLabel0">Contact Coach Hevy</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="php/contact_coach.php" method="POST" onsubmit="showToast(event, 'Message sent to Coach Hevy!')">
                                    <input type="hidden" name="coach_name" value="Coach Hevy">
                                    <div class="mb-3">
                                        <label for="name0" class="form-label">Your Name</label>
                                        <input type="text" class="form-control" id="name0" name="name" placeholder="Enter your full name (e.g., John Doe)" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email0" class="form-label">Your Email</label>
                                        <input type="email" class="form-control" id="email0" name="email" placeholder="Enter your email (e.g., john@example.com)" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="message0" class="form-label">Message</label>
                                        <textarea class="form-control" id="message0" name="message" rows="4" placeholder="Write your message (e.g., I need help with my workout plan)" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Send Message</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="contactModal1" tabindex="-1" aria-labelledby="contactModalLabel1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="contactModalLabel1">Contact Mohamed Ayman</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="php/contact_coach.php" method="POST" onsubmit="showToast(event, 'Message sent to Mohamed Ayman!')">
                                    <input type="hidden" name="coach_name" value="Mohamed Ayman">
                                    <div class="mb-3">
                                        <label for="name1" class="form-label">Your Name</label>
                                        <input type="text" class="form-control" id="name1" name="name" placeholder="Enter your full name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email1" class="form-label">Your Email</label>
                                        <input type="email" class="form-control" id="email1" name="email" placeholder="Enter your email " required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="message1" class="form-label">Message</label>
                                        <textarea class="form-control" id="message1" name="message" rows="4" placeholder="Write your message" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Send Message</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="contactModal2" tabindex="-1" aria-labelledby="contactModalLabel2" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="contactModalLabel2">Contact Abdelrahman Tolba</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="php/contact_coach.php" method="POST" onsubmit="showToast(event, 'Message sent to Abdelrahman Tolba!')">
                                    <input type="hidden" name="coach_name" value="Abdelrahman Tolba">
                                    <div class="mb-3">
                                        <label for="name2" class="form-label">Your Name</label>
                                        <input type="text" class="form-control" id="name2" name="name" placeholder="Enter your full name (e.g., John Doe)" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email2" class="form-label">Your Email</label>
                                        <input type="email" class="form-control" id="email2" name="email" placeholder="Enter your email (e.g., john@example.com)" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="message2" class="form-label">Message</label>
                                        <textarea class="form-control" id="message2" name="message" rows="4" placeholder="Write your message (e.g., I need help with my workout plan)" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Send Message</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="contactModal3" tabindex="-1" aria-labelledby="contactModalLabel3" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="contactModalLabel3">Contact Zyad Mohamed</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="php/contact_coach.php" method="POST" onsubmit="showToast(event, 'Message sent to Zyad Mohamed!')">
                                    <input type="hidden" name="coach_name" value="Zyad Mohamed">
                                    <div class="mb-3">
                                        <label for="name3" class="form-label">Your Name</label>
                                        <input type="text" class="form-control" id="name3" name="name" placeholder="Enter your full name (e.g., John Doe)" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email3" class="form-label">Your Email</label>
                                        <input type="email" class="form-control" id="email3" name="email" placeholder="Enter your email (e.g., john@example.com)" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="message3" class="form-label">Message</label>
                                        <textarea class="form-control" id="message3" name="message" rows="4" placeholder="Write your message (e.g., I need help with my workout plan)" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Send Message</button>
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
                    <!-- Image placeholder: An image of a person jogging outdoors or using light dumbbells at home -->
                    <div class="plan-img-wrapper">
                        <img src="image/basic-plan.jpg" alt="Basic Plan" class="plan-img">
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
                    <!-- Image placeholder: An image of a personal trainer guiding a client in a gym -->
                    <div class="plan-img-wrapper">
                        <img src="image/pro-plan.jpg" alt="Pro Plan" class="plan-img">
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
                    <!-- Image placeholder: An image of a luxurious gym setup or a person in a premium studio -->
                    <div class="plan-img-wrapper">
                        <img src="image/elite-plan.jpg" alt="Elite Plan" class="plan-img">
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
                    <!-- Image placeholder: An image of fitness supplements like protein powder and a shaker bottle -->
                    <div class="shop-img-wrapper">
                        <img src="image/supplements.jpg" alt="Supplements" class="shop-img">
                        <span class="best-seller-badge">Best Seller</span>
                    </div>
                    <h4>Premium Supplements</h4>
                    <p>Elevate your performance with our top-rated protein powders, pre-workouts, and recovery formulas.</p>
                    <a href="php/ecommerce/store.php" class="btn btn-outline-light">Shop Now</a>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="shop-card">
                    <!-- Image placeholder: An image of fitness equipment like dumbbells and resistance bands -->
                    <div class="shop-img-wrapper">
                        <img src="image/equipment.jpg" alt="Equipment" class="shop-img">
                    </div>
                    <h4>Durable Equipment</h4>
                    <p>Build your home gym with our high-quality dumbbells, kettlebells, and resistance bands.</p>
                    <a href="php/ecommerce/store.php" class="btn btn-outline-light">Shop Now</a>
                </div>
            </div>
        </div>
    </div>
</section><!-- Blog Section -->
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
                                <!-- Image placeholder: An image of a person doing a high-intensity workout -->
                                <img src="image/top-5-workouts.jpg" alt="Top 5 Workouts" class="blog-img">
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
                                <!-- Image placeholder: An image of a colorful, healthy meal prep setup -->
                                <img src="image/nutrition-tips.jpg" alt="Nutrition Tips" class="blog-img">
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
                                <!-- Image placeholder: An image of a person stretching or foam rolling -->
                                <img src="image/recovery-guide.jpg" alt="Recovery Guide" class="blog-img">
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
                            <!-- Image placeholder: An image of a person running or cycling -->
                            <img src="image/cardio-plan.jpg" alt="Cardio Plan" class="workout-img">
                            <h4>Cardio Plans</h4>
                            <p>Boost your endurance with tailored cardio routines.</p>
                            <span class="intensity-label">Intensity: Moderate</span>
                        </div>
                        <div class="card-back">
                            <h4>Details</h4>
                            <p>Includes running, cycling, and HIIT workouts to improve cardiovascular health.</p>
                            <a href="php/workout/plans.php" class="btn btn-outline-light">Start Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="workout-card popular">
                    <div class="card-inner">
                        <div class="card-front">
                            <!-- Image placeholder: An image of a person lifting heavy weights -->
                            <img src="image/strength-plan.jpg" alt="Strength Plan" class="workout-img">
                            <span class="popular-badge">Most Popular</span>
                            <h4>Strength Plans</h4>
                            <p>Build muscle with structured strength programs.</p>
                            <span class="intensity-label">Intensity: High</span>
                        </div>
                        <div class="card-back">
                            <h4>Details</h4>
                            <p>Focus on compound lifts and progressive overload for maximum gains.</p>
                            <a href="php/workout/plans.php" class="btn btn-outline-light">Start Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="workout-card">
                    <div class="card-inner">
                        <div class="card-front">
                            <!-- Image placeholder: An image of a person doing a yoga pose -->
                            <img src="image/flexibility-plan.jpg" alt="Flexibility Plan" class="workout-img">
                            <h4>Flexibility Plans</h4>
                            <p>Improve mobility with yoga and stretching.</p>
                            <span class="intensity-label">Intensity: Low</span>
                        </div>
                        <div class="card-back">
                            <h4>Details</h4>
                            <p>Includes daily stretching routines and yoga flows for better flexibility.</p>
                            <a href="php/workout/plans.php" class="btn btn-outline-light">Start Now</a>
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
                    <!-- Image placeholder: An image of a weekly meal prep setup -->
                    <div class="nutrition-img-wrapper">
                        <img src="image/meal-plan.jpg" alt="Meal Plan" class="nutrition-img">
                    </div>
                    <h4>Custom Meal Plans</h4>
                    <p>Get personalized diet plans tailored to your fitness goals and preferences.</p>
                    <a href="php/nutrition/meal_plan.php" class="btn btn-outline-light">Get Plan</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="nutrition-card">
                    <!-- Image placeholder: An image of a person using a smartphone to log food -->
                    <div class="nutrition-img-wrapper">
                        <img src="image/calorie-tracking.jpg" alt="Calorie Tracking" class="nutrition-img">
                    </div>
                    <h4>Smart Calorie Tracking</h4>
                    <p>Easily monitor your daily calorie intake with our intuitive tracking tools.</p>
                    <a href="php/nutrition/meal_plan.php" class="btn btn-outline-light">Track Now</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="nutrition-card recommended">
                    <!-- Image placeholder: An image of a healthy recipe like a smoothie bowl -->
                    <div class="nutrition-img-wrapper">
                        <img src="image/recipes.jpg" alt="Recipes" class="nutrition-img">
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
                    <!-- Image placeholder: An image of a person stepping onto a scale -->
                    <div class="progress-img-wrapper">
                        <img src="image/weight-tracking.jpg" alt="Weight Tracking" class="progress-img">
                    </div>
                    <h4>Weight Tracking</h4>
                    <p>Monitor your weight changes over time with detailed insights.</p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 70%;">70%</div>
                    </div>
                    <a href="php/progress/log_workout.php" class="btn btn-outline-light">Track Weight</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="progress-card">
                    <!-- Image placeholder: An image of a fitness tracker displaying stats -->
                    <div class="progress-img-wrapper">
                        <img src="image/workout-stats.jpg" alt="Workout Stats" class="progress-img">
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
                    <!-- Image placeholder: An image of a person holding a medal or trophy -->
                    <div class="progress-img-wrapper">
                        <img src="image/achievements.jpg" alt="Achievements" class="progress-img">
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
                        <img class="w-" src="image\evolve_text_white.webp" alt="Partner 1">
                        <img src="image\logo.webp" alt="Partner 1">
                        <img src="image\جيماوي-تكست.-1-optimized.webp" alt="Partner 1">
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="php/dashboard/index.php">Dashboard</a></li>
                        <li><a href="php/workout/plans.php">Workouts</a></li>
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
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name " required>
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email " required>
                </div>
                <div class="mb-3">
                    <textarea class="form-control" id="message" name="message" rows="3" placeholder="Write your message " required></textarea>
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
    </script>
</body>
</html>