<?php

// Include database connection
require_once '../../includes/config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

// Sample membership plans with benefits
$plans = [
    'Basic' => [
        'price' => 29.99,
        'duration' => 30,
        'benefits' => [
            'Access to gym facilities',
            '1 group class per week',
            'Basic workout tracking'
        ]
    ],
    'Premium' => [
        'price' => 49.99,
        'duration' => 30,
        'benefits' => [
            'Access to gym facilities',
            'Unlimited group classes',
            'Advanced workout & nutrition tracking',
            '1 personal training session per month'
        ]
    ],
    'Elite' => [
        'price' => 79.99,
        'duration' => 90,
        'benefits' => [
            'Access to gym facilities',
            'Unlimited group classes',
            'Advanced workout & nutrition tracking',
            '3 personal training sessions per month',
            'Priority booking for classes'
        ]
    ]
];

// Handle membership purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'purchase_plan') {
    $plan_name = $_POST['plan_name'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $duration = intval($_POST['duration'] ?? 0);

    if ($plan_name && $price > 0 && $duration > 0) {
        try {
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime("+$duration days"));
            $stmt = $pdo->prepare("INSERT INTO memberships (user_id, plan_name, price, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$_SESSION['user_id'], $plan_name, $price, $start_date, $end_date]);
            $success = 'Membership purchased successfully!';
        } catch (PDOException $e) {
            $error = 'Error purchasing membership: ' . $e->getMessage();
        }
    } else {
        $error = 'Invalid plan selection.';
    }
}

// Handle membership refund
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_refund') {
    $membership_id = intval($_POST['membership_id'] ?? 0);
    try {
        $stmt = $pdo->prepare("UPDATE memberships SET status = 'refunded' WHERE id = ? AND user_id = ? AND status = 'active'");
        $stmt->execute([$membership_id, $_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            $success = 'Refund requested successfully! Your membership has been deactivated.';
        } else {
            $error = 'Unable to process refund. Membership may already be refunded or expired.';
        }
    } catch (PDOException $e) {
        $error = 'Error requesting refund: ' . $e->getMessage();
    }
}

// Handle membership renewal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'renew_plan') {
    $plan_name = $_POST['plan_name'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $duration = intval($_POST['duration'] ?? 0);

    if ($plan_name && $price > 0 && $duration > 0) {
        try {
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime("+$duration days"));
            $stmt = $pdo->prepare("INSERT INTO memberships (user_id, plan_name, price, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$_SESSION['user_id'], $plan_name, $price, $start_date, $end_date]);
            $success = 'Membership renewed successfully!';
        } catch (PDOException $e) {
            $error = 'Error renewing membership: ' . $e->getMessage();
        }
    } else {
        $error = 'Invalid plan selection for renewal.';
    }
}

// Fetch user’s current membership
try {
    $stmt = $pdo->prepare("SELECT * FROM memberships WHERE user_id = ? AND status = 'active' AND end_date > CURDATE() LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $current_membership = $stmt->fetch();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Fetch past memberships
try {
    $stmt = $pdo->prepare("SELECT * FROM memberships WHERE user_id = ? AND (status != 'active' OR end_date <= CURDATE()) ORDER BY end_date DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $past_memberships = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Plans - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../assets/css/global.css" rel="stylesheet">
</head>
<body>
    <!-- Include Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <!-- Membership Plans Section -->
    <div class="store-section">
        <div class="container">
            <h2 class="text-center mb-4">Membership Plans</h2>

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

            <!-- Current Membership -->
            <?php if ($current_membership): ?>
                <div class="membership-card mb-4">
                    <h4>Your Current Membership</h4>
                    <p><strong>Plan:</strong> <?php echo htmlspecialchars($current_membership['plan_name']); ?></p>
                    <p><strong>Start Date:</strong> <?php echo $current_membership['start_date']; ?></p>
                    <p><strong>End Date:</strong> <?php echo $current_membership['end_date']; ?></p>
                    <p><strong>Benefits:</strong></p>
                    <ul>
                        <?php foreach ($plans[$current_membership['plan_name']]['benefits'] as $benefit): ?>
                            <li><?php echo htmlspecialchars($benefit); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to request a refund? This will deactivate your membership.');">
                        <input type="hidden" name="action" value="request_refund">
                        <input type="hidden" name="membership_id" value="<?php echo $current_membership['id']; ?>">
                        <button type="submit" class="btn btn-danger">Request Refund</button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Available Plans -->
                <h4 class="mb-3">Available Plans</h4>
                <div class="row">
                    <?php foreach ($plans as $name => $plan): ?>
                        <div class="col-md-4 mb-4">
                            <div class="plan-card">
                                <h5><?php echo htmlspecialchars($name); ?> Plan</h5>
                                <p><strong>Price:</strong> $<?php echo number_format($plan['price'], 2); ?></p>
                                <p><strong>Duration:</strong> <?php echo $plan['duration']; ?> days</p>
                                <p><strong>Benefits:</strong></p>
                                <ul>
                                    <?php foreach ($plan['benefits'] as $benefit): ?>
                                        <li><?php echo htmlspecialchars($benefit); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <form method="POST">
                                    <input type="hidden" name="action" value="purchase_plan">
                                    <input type="hidden" name="plan_name" value="<?php echo $name; ?>">
                                    <input type="hidden" name="price" value="<?php echo $plan['price']; ?>">
                                    <input type="hidden" name="duration" value="<?php echo $plan['duration']; ?>">
                                    <button type="submit" class="btn btn-primary w-100">Purchase</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Past Memberships -->
            <h4 class="mb-3">Membership History</h4>
            <?php if (!empty($past_memberships)): ?>
                <div class="cart-table">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($past_memberships as $membership): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($membership['plan_name']); ?></td>
                                    <td><?php echo $membership['start_date']; ?></td>
                                    <td><?php echo $membership['end_date']; ?></td>
                                    <td><?php echo htmlspecialchars($membership['status']); ?></td>
                                    <td>
                                        <?php if ($membership['status'] === 'expired'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="action" value="renew_plan">
                                                <input type="hidden" name="plan_name" value="<?php echo $membership['plan_name']; ?>">
                                                <input type="hidden" name="price" value="<?php echo $plans[$membership['plan_name']]['price']; ?>">
                                                <input type="hidden" name="duration" value="<?php echo $plans[$membership['plan_name']]['duration']; ?>">
                                                <button type="submit" class="btn btn-success btn-sm">Renew</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No past memberships found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .membership-card, .plan-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            color: #ffffff;
        }

        .membership-card h4, .plan-card h5 {
            color: #00ddeb;
            margin-bottom: 1rem;
        }

        .membership-card ul, .plan-card ul {
            padding-left: 20px;
        }
    </style>
</body>
</html>