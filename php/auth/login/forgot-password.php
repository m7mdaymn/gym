<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Forgot Password - Fitness App</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="../../../assets/css/login.css" rel="stylesheet">
    </head>
    <body>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="../../../index.php">FitnessApp</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="../../../index.php">Home</a>
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
                                    <li><a class="dropdown-item" href="../logout/logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link active" href="login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../register/register.php">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Forgot Password Form -->
        <div class="login-container">
            <h2>Forgot Password</h2>

            <!-- Response Message -->
            <div id="responseMessage" class="alert" style="display: none;"></div>

            <!-- Forgot Password Form -->
            <form id="forgotPasswordForm" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <button type="submit" class="btn btn-primary">Request Password Reset</button>
            </form>

            <!-- Back to Login Link -->
            <div class="register-link">
                <p><a href="login.php">Back to Login</a></p>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const responseMessage = document.getElementById('responseMessage');

                fetch('forgot-password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    responseMessage.style.display = 'block';
                    responseMessage.innerHTML = data.message;
                    if (data.success) {
                        responseMessage.classList.remove('alert-danger');
                        responseMessage.classList.add('alert-success');
                    } else {
                        responseMessage.classList.remove('alert-success');
                        responseMessage.classList.add('alert-danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    responseMessage.style.display = 'block';
                    responseMessage.classList.remove('alert-success');
                    responseMessage.classList.add('alert-danger');
                    responseMessage.innerHTML = 'An error occurred. Please try again.';
                });
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Simulated user database (since config.php with database connection was removed)
$users = [
    'mohamedayman@example.com' => [
        'id' => 1,
        'username' => 'mohamedayman',
        'password' => password_hash('password123', PASSWORD_DEFAULT)
    ]
];

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

// Check if email exists (simulated)
$message = 'If this email exists, a password reset link has been sent.';
if (isset($users[$email])) {
    // In a real application, you would generate a reset token and send an email
    // For this example, we just confirm the email exists
}

echo json_encode(['success' => true, 'message' => $message]);
exit;
?>