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
    <title>Login - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      background-color: #000;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .outer-container {
      background-color: #1a1a1a;
      padding: 50px;
      border-radius: 25px;
      box-shadow: 0 0 80px rgba(255,255,255,0.05);
      margin-top: 52px;
    }

    .inner-container {
      background-color: #000;
      padding: 70px;
      border-radius: 20px;
      border: 1px solid #333;
      width: 100%;
      max-width: 400px;
    }

    h2 {
      text-align: center;
      font-size: 28px;
      margin-bottom: 30px;
      font-weight: 600;
      color: #fff;
    }

    .form-label {
      display: block;
      margin-bottom: 8px;
      color: #ccc;
      font-weight: 500;
    }

    .form-control {
      width: 100%;
      padding: 12px 15px;
      background-color: #111;
      border: 1px solid #333;
      border-radius: 10px;
      margin-bottom: 20px;
      font-size: 15px;
      transition: 0.3s ease;
      color: white !important;
    }

    .form-control:focus {
      outline: none;
      border-color: #fff;
      background-color: #111;
    }

    .btn {
      width: 100%;
      background-color: #fff;
      color: #000;
      border: none;
      padding: 12px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 10px;
      cursor: pointer;
      transition: 0.3s ease;
    }

    .btn:hover {
      background-color: #e6e6e6;
    }

    #responseMessage {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
    }

    p.text-center {
      text-align: center;
      margin-top: 25px;
      font-size: 14px;
    }

    a {
      color: #aaa;
      text-decoration: none;
      transition: 0.2s ease;
    }

    a:hover {
      color: #fff;
      text-decoration: underline;
    }

    @media (max-width: 480px) {
      .outer-container {
        padding: 30px 20px;
      }

      .inner-container {
        padding: 30px 20px;
      }
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
  </style>
</head>
<body>
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
                            <li><a class="dropdown-item" href="../../auth/register/register.php">Register</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

  <div class="outer-container">
    <div class="inner-container">
      <h2>Login</h2>
      <form id="loginForm" method="POST">
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
      </form>
      <div id="responseMessage"></div>
      <p class="text-center">
        <a href="forgot-password.php">Forgot Password?</a> |
        <a href="../register/register.php">Register</a>
      </p>
    </div>
  </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('process_login.php', {
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