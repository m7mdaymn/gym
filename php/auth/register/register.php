<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <style>
        /* Register Section Background */
.register-section {
    background-color: #000;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem;
}

/* Two-layer container */
.outer-layer {
    background-color: #1a1a1a;
    border: 1px solid #ffffff10;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.05);
    max-width: 500px;
    width: 100%;
}

.inner-layer {
    background-color: #000;
    padding: 2rem;
    border-radius: 0.75rem;
    border: 1px solid #ffffff15;
}

/* Text and inputs */
h2 {
    color: #fff;
    font-weight: 600;
}

label {
    color: #fff;
    font-weight: 500;
}

.form-control {
    background-color: #111;
    color: #fff;
    border: 1px solid #444;
    border-radius: 0.5rem;
    padding: 0.75rem;
}

.form-control:focus {
    background-color: #111;
    color: #fff;
    border-color: #fff;
    box-shadow: none;
}

.btn-primary {
    background-color: #fff;
    color: #000;
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #ccc;
    color: #000;
}

a {
    color: #fff;
    text-decoration: underline;
    transition: color 0.3s;
}

a:hover {
    color: #ccc;
}

#responseMessage {
    color: #f00;
    font-weight: 500;
}
.navbar {
    background-color: #000000;
    padding: 1.5rem 0;
    border-bottom: 1px solid #FFFFFF;
    box-shadow: 0 2px 15px rgba(255, 255, 255, 0.1);
}
.dropdown-menu {
    background-color: #000000;
    border: 1px solid #FFFFFF;
}
.dropdown-item {
    color: #FFFFFF;
    text-transform: uppercase;
}
.dropdown-item:hover {
    background-color: #333333;
    color: #CCCCCC;
}
.navbar-brand {
    font-size: 2rem;
    font-weight: 700;
    color: #FFFFFF !important;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.navbar-brand:hover {
    color: #CCCCCC !important;
}

.nav-link {
    color: #FFFFFF !important;
    font-weight: 500;
    padding: 0.5rem 1.5rem;
    text-transform: uppercase;
    transition: color 0.3s ease, transform 0.3s ease;
}

.nav-link:hover {
    color: #CCCCCC !important;
    transform: translateY(-3px);
}

.nav-link.active {
    color: #CCCCCC !important;
    border-bottom: 2px solid #FFFFFF;
}

.nav-icon {
    font-size: 1.5rem;
    color: #FFFFFF;
    padding: 0.5rem 1rem;
    cursor: pointer;
    transition: color 0.3s ease, transform 0.3s ease;
}

.nav-icon:hover {
    color: #CCCCCC;
    transform: scale(1.1);
}
/* Register Section Background */
.register-section {
    background-color: #000;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem;
}

/* Two-layer container */
.outer-layer {
    background-color: #1a1a1a;
    border: 1px solid #ffffff10;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.05);
    max-width: 500px;
    width: 100%;
}

.inner-layer {
    background-color: #000;
    padding: 2rem;
    border-radius: 0.75rem;
    border: 1px solid #ffffff15;
}

/* Text and inputs */
h2 {
    color: #fff;
    font-weight: 600;
}

label {
    color: #fff;
    font-weight: 500;
}

.form-control {
    background-color: #111;
    color: #fff;
    border: 1px solid #444;
    border-radius: 0.5rem;
    padding: 0.75rem;
}

.form-control:focus {
    background-color: #111;
    color: #fff;
    border-color: #fff;
    box-shadow: none;
}

.btn-primary {
    background-color: #fff;
    color: #000;
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #ccc;
    color: #000;
}

a {
    color: #fff;
    text-decoration: underline;
    transition: color 0.3s;
}

a:hover {
    color: #ccc;
}

#responseMessage {
    color: #f00;
    font-weight: 500;
}


    </style>
   <!-- Navigation Bar for Login Page -->
   <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../../../index.php"><i class="fas fa-dumbbell me-2"></i>Fitness App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavLogin" aria-controls="navbarNavLogin" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavLogin">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="../../../index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="authDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="authDropdown">
                            <li><a class="dropdown-item" href="../../../php/auth/login/login.php">Login</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Register Page -->
<div class="register-section">
    <div class="outer-layer">
        <div class="inner-layer">
            <h2 class="text-center mb-4">Create Your Account</h2>
            <form id="registerForm" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <div id="responseMessage" class="mt-3"></div>
            <p class="text-center mt-3">
                Already have an account?
                <a href="../login/login.php">Login</a>
            </p>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('process_register.php', {
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
                const responseMessage = document.getElementById('responseMessage');
                responseMessage.innerHTML = data.message;
                if (data.success) {
                    responseMessage.classList.add('text-success');
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    }
                } else {
                    responseMessage.classList.add('text-danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('responseMessage').innerHTML = 'An error occurred: ' + error.message;
                document.getElementById('responseMessage').classList.add('text-danger');
            });
        });
    </script>
</body>
</html>