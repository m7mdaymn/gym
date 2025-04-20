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
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section d-flex align-items-center text-center text-white" style="height: 100vh;">
        <div class="container">
            <h1 class="display-3 fw-bold mb-3">Welcome to Fitness App</h1>
            <p class="lead mb-4">Track your workouts and nutrition to achieve your fitness goals!</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="php/auth/register/register.html" class="btn btn-primary btn-lg px-5 py-3">Register</a>
                <a href="php/auth/login/login.html" class="btn btn-outline-light btn-lg px-5 py-3">Login</a>
            </div>
        </div>
    </section>

    <!-- Who We Are Section -->
    <section class="who-we-are py-5 bg-light">
        <div class="container">
            <h2 class="text-center display-5 fw-bold mb-5 text-dark">Who We Are?</h2>
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="lead text-dark">
                        We are a passionate team dedicated to helping you achieve your fitness goals. At Fitness App, we provide tools to track your workouts, monitor your nutrition, and stay motivated on your journey to a healthier lifestyle. Whether you're a beginner or a pro, our app is designed to support you every step of the way.
                    </p>
                </div>
                <div class="col-md-6 text-center">
                    <img src="image/fitnes_group.jpg" alt="Team Fitness" class="img-fluid rounded shadow" style="max-height: 300px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services py-5 bg-dark text-white">
        <div class="container">
            <h2 class="text-center display-5 fw-bold mb-5">Our Features</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <i class="fas fa-dumbbell fa-3x mb-3 text-primary"></i>
                    <h4 class="fw-bold">Workout Tracking</h4>
                    <p>Log your exercises, track your progress, and set goals to stay on top of your fitness routine.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <i class="fas fa-utensils fa-3x mb-3 text-danger"></i>
                    <h4 class="fw-bold">Nutrition Monitoring</h4>
                    <p>Record your meals, monitor your calorie intake, and balance your macros for optimal health.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <i class="fas fa-chart-line fa-3x mb-3 text-primary"></i>
                    <h4 class="fw-bold">Progress Analytics</h4>
                    <p>Get insights into your fitness journey with detailed charts and analytics.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Cards Section -->
    <section class="team py-5">
        <div class="container">
            <h2 class="text-center display-5 fw-bold mb-5 text-dark">Meet Our Team</h2>
            <div class="row justify-content-center">
                <?php
                $team = [
                    ["name" => "Mohamed Mahmoud", "image" => "image/f9a7a30e3f6cada77cbd30f47f5a430f.jpg"],
                    ["name" => "Mohamed Ayman", "image" => "image/f9a7a30e3f6cada77cbd30f47f5a430f.jpg"],
                    ["name" => "Abdelrahman tolba", "image" => "image/tolbaimg.jpg"],
                    ["name" => "Zyad Mohamed", "image" => "image/f9a7a30e3f6cada77cbd30f47f5a430f.jpg"],
                    ["name" => "Mohamed Khaled ", "image" => "image/tolbaimg.jpg"],
                    ["name" => "Asmaa Nasr", "image" => "image/f9a7a30e3f6cada77cbd30f47f5a430f.jpg"],
                    ["name" => "Rawda Ahmed", "image" => "image/f9a7a30e3f6cada77cbd30f47f5a430f.jpg"],
                    ["name" => "Rawan Ibrahim", "image" => "image/rawanimg.jpg"],
                ];
                foreach ($team as $member) {
                    echo '
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="team-card">
                            <img src="' . $member['image'] . '" alt="' . $member['name'] . '" class="img-fluid rounded">
                            <div class="team-card-overlay">
                                <h5 class="team-card-name">' . $member['name'] . '</h5>
                            </div>
                        </div>
                    </div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <p>© 2025 Fitness App. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>