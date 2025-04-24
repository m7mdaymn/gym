<?php
// Include database connection
require_once '../../includes/config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

// Fetch available classes
try {
    $stmt = $pdo->prepare("SELECT c.*, t.name as trainer_name FROM classes c LEFT JOIN trainers t ON c.trainer_id = t.id WHERE c.schedule > NOW()");
    $stmt->execute();
    $classes = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Fetch available trainers
try {
    $stmt = $pdo->prepare("SELECT * FROM trainers");
    $stmt->execute();
    $trainers = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Check user’s membership status
try {
    $stmt = $pdo->prepare("SELECT * FROM memberships WHERE user_id = ? AND end_date > CURDATE() AND status = 'active' LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $membership = $stmt->fetch();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Fetch user’s reservations
try {
    $stmt = $pdo->prepare("SELECT r.*, c.class_name, c.schedule, t.name as trainer_name 
                           FROM reservations r 
                           LEFT JOIN classes c ON r.class_id = c.id 
                           LEFT JOIN trainers t ON r.trainer_id = t.id 
                           WHERE r.user_id = ? AND r.status != 'cancelled'");
    $stmt->execute([$_SESSION['user_id']]);
    $reservations = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Handle reservation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_reservation') {
    $reservation_type = $_POST['reservation_type'] ?? '';
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : null;
    $trainer_id = isset($_POST['trainer_id']) ? intval($_POST['trainer_id']) : null;
    $reservation_date = $_POST['reservation_date'] ?? '';
    $session_focus = trim($_POST['session_focus'] ?? '');

    if (!$membership && $reservation_type === 'session') {
        $error = 'You need an active membership to book a personal session with a trainer.';
    } elseif ($reservation_type === 'class' && !$class_id) {
        $error = 'Please select a class to book.';
    } elseif ($reservation_type === 'session' && (!$trainer_id || !$reservation_date)) {
        $error = 'Please select a trainer and a date for your session.';
    } else {
        try {
            $pdo->beginTransaction();

            // Check class capacity if booking a class
            $reservation_status = 'confirmed';
            if ($reservation_type === 'class') {
                $stmt = $pdo->prepare("SELECT capacity, current_attendees FROM classes WHERE id = ?");
                $stmt->execute([$class_id]);
                $class = $stmt->fetch();

                if ($class['current_attendees'] >= $class['capacity']) {
                    $reservation_status = 'waitlisted';
                } else {
                    $stmt = $pdo->prepare("UPDATE classes SET current_attendees = current_attendees + 1 WHERE id = ?");
                    $stmt->execute([$class_id]);
                }
            }

            // Insert reservation
            $stmt = $pdo->prepare("INSERT INTO reservations (user_id, class_id, trainer_id, reservation_type, reservation_date, status, session_focus) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $class_id, $trainer_id, $reservation_type, $reservation_date, $reservation_status, $session_focus]);

            // Add notification
            $message = $reservation_type === 'class' 
                ? "You have " . ($reservation_status === 'confirmed' ? 'booked' : 'been waitlisted for') . " a class on " . $reservation_date 
                : "You have booked a personal session with a trainer on " . $reservation_date;
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $message]);

            $pdo->commit();
            $success = 'Reservation booked successfully! ' . ($reservation_status === 'waitlisted' ? 'You are on the waitlist.' : '');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error booking reservation: ' . $e->getMessage();
        }
    }
}

// Handle reservation cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_reservation') {
    $reservation_id = intval($_POST['reservation_id'] ?? 0);
    try {
        $pdo->beginTransaction();

        // Fetch reservation details
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? AND user_id = ?");
        $stmt->execute([$reservation_id, $_SESSION['user_id']]);
        $reservation = $stmt->fetch();

        if ($reservation) {
            // Update reservation status
            $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$reservation_id]);

            // Decrease class attendees if it was a confirmed class booking
            if ($reservation['reservation_type'] === 'class' && $reservation['status'] === 'confirmed') {
                $stmt = $pdo->prepare("UPDATE classes SET current_attendees = current_attendees - 1 WHERE id = ?");
                $stmt->execute([$reservation['class_id']]);

                // Check if there are waitlisted users and promote one
                $stmt = $pdo->prepare("SELECT * FROM reservations WHERE class_id = ? AND status = 'waitlisted' ORDER BY created_at ASC LIMIT 1");
                $stmt->execute([$reservation['class_id']]);
                $waitlisted = $stmt->fetch();
                if ($waitlisted) {
                    $stmt = $pdo->prepare("UPDATE reservations SET status = 'confirmed' WHERE id = ?");
                    $stmt->execute([$waitlisted['id']]);
                    $stmt = $pdo->prepare("UPDATE classes SET current_attendees = current_attendees + 1 WHERE id = ?");
                    $stmt->execute([$reservation['class_id']]);
                    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $stmt->execute([$waitlisted['user_id'], "You have been moved off the waitlist and confirmed for your class!"]);
                }
            }

            // Add cancellation notification
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], "You have cancelled your " . $reservation['reservation_type'] . " reservation."]);
        }

        $pdo->commit();
        $success = 'Reservation cancelled successfully!';
        header('Location: reservations.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Error cancelling reservation: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Reservation - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../assets/css/global.css" rel="stylesheet">
</head>
<body>
    <!-- Include Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <!-- Reservation Section -->
    <div class="store-section">
        <div class="container">
            <h2 class="text-center mb-4">Book a Reservation</h2>

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

            <!-- Tabs for Class or Session Booking -->
            <ul class="nav nav-tabs mb-4" id="reservationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="class-tab" data-bs-toggle="tab" data-bs-target="#class" type="button" role="tab" aria-controls="class" aria-selected="true">Book a Class</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="session-tab" data-bs-toggle="tab" data-bs-target="#session" type="button" role="tab" aria-controls="session" aria-selected="false">Book a Session</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="my-reservations-tab" data-bs-toggle="tab" data-bs-target="#my-reservations" type="button" role="tab" aria-controls="my-reservations" aria-selected="false">My Reservations</button>
                </li>
            </ul>

            <div class="tab-content" id="reservationTabContent">
                <!-- Book a Class -->
                <div class="tab-pane fade show active" id="class" role="tabpanel" aria-labelledby="class-tab">
                    <form method="POST">
                        <input type="hidden" name="action" value="book_reservation">
                        <input type="hidden" name="reservation_type" value="class">
                        <div class="mb-3">
                            <label for="class_id" class="form-label">Select a Class</label>
                            <select class="form-control" id="class_id" name="class_id" required>
                                <option value="">-- Select a Class --</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>">
                                        <?php echo htmlspecialchars($class['class_name']); ?> (<?php echo $class['schedule']; ?>) 
                                        - Trainer: <?php echo htmlspecialchars($class['trainer_name'] ?? 'TBD'); ?> 
                                        (<?php echo $class['current_attendees']; ?>/<?php echo $class['capacity']; ?> spots)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Book Class</button>
                    </form>
                </div>

                <!-- Book a Session -->
                <div class="tab-pane fade" id="session" role="tabpanel" aria-labelledby="session-tab">
                    <form method="POST">
                        <input type="hidden" name="action" value="book_reservation">
                        <input type="hidden" name="reservation_type" value="session">
                        <div class="mb-3">
                            <label for="trainer_id" class="form-label">Select a Trainer</label>
                            <select class="form-control" id="trainer_id" name="trainer_id" required>
                                <option value="">-- Select a Trainer --</option>
                                <?php foreach ($trainers as $trainer): ?>
                                    <option value="<?php echo $trainer['id']; ?>">
                                        <?php echo htmlspecialchars($trainer['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="reservation_date" class="form-label">Select Date and Time</label>
                            <input type="datetime-local" class="form-control" id="reservation_date" name="reservation_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="session_focus" class="form-label">Session Focus (e.g., strength, flexibility)</label>
                            <input type="text" class="form-control" id="session_focus" name="session_focus">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Book Session</button>
                    </form>
                </div>

                <!-- My Reservations -->
                <div class="tab-pane fade" id="my-reservations" role="tabpanel" aria-labelledby="my-reservations-tab">
                    <?php if (!empty($reservations)): ?>
                        <div class="cart-table">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Details</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $reservation): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(ucfirst($reservation['reservation_type'])); ?></td>
                                            <td>
                                                <?php 
                                                if ($reservation['reservation_type'] === 'class') {
                                                    echo htmlspecialchars($reservation['class_name']) . ' (' . $reservation['schedule'] . ')';
                                                } else {
                                                    echo 'Session with ' . htmlspecialchars($reservation['trainer_name']) . ($reservation['session_focus'] ? ' - Focus: ' . htmlspecialchars($reservation['session_focus']) : '');
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo $reservation['reservation_date']; ?></td>
                                            <td><?php echo htmlspecialchars(ucfirst($reservation['status'])); ?></td>
                                            <td>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                                    <input type="hidden" name="action" value="cancel_reservation">
                                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No active reservations found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>