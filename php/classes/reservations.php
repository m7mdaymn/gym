<?php
// Include database connection
require_once '../../includes/config.php';

// Check if the user is logged in and exists in the database
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

// Verify the user exists in the database
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        $error = "User does not exist in the database. Please register or log in again.";
        unset($_SESSION['user_id']);
        header('Location: ../auth/login/login.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Fetch available classes with filtering support
$class_filters = [
    'date' => $_GET['class_date'] ?? null,
    'trainer' => $_GET['trainer_filter'] ?? null,
    'type' => $_GET['class_type'] ?? null
];

$class_query = "SELECT c.*, t.name as trainer_name 
                FROM classes c 
                LEFT JOIN trainers t ON c.trainer_id = t.id 
                WHERE c.schedule > NOW()";
$class_params = [];

if ($class_filters['date']) {
    $class_query .= " AND DATE(c.schedule) = ?";
    $class_params[] = $class_filters['date'];
}
if ($class_filters['trainer']) {
    $class_query .= " AND c.trainer_id = ?";
    $class_params[] = $class_filters['trainer'];
}
if ($class_filters['type']) {
    $class_query .= " AND c.class_name LIKE ?";
    $class_params[] = "%{$class_filters['type']}%";
}

$class_query .= " ORDER BY c.schedule ASC";

try {
    $stmt = $pdo->prepare($class_query);
    $stmt->execute($class_params);
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

// Fetch trainer availability
$trainer_availability = [];
foreach ($trainers as $trainer) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM trainer_availability WHERE trainer_id = ? AND available_date >= CURDATE() ORDER BY available_date, start_time");
        $stmt->execute([$trainer['id']]);
        $trainer_availability[$trainer['id']] = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
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
    $availability_id = isset($_POST['availability_id']) ? intval($_POST['availability_id']) : null;

    if (!$membership && $reservation_type === 'session') {
        $error = 'You need an active membership to book a personal session with a trainer.';
    } elseif ($reservation_type === 'class' && !$class_id) {
        $error = 'Please select a class to book.';
    } elseif ($reservation_type === 'session' && (!$trainer_id || !$reservation_date || !$availability_id)) {
        $error = 'Please select a trainer, date, and time slot for your session.';
    } else {
        try {
            $pdo->beginTransaction();

            // Check class capacity if booking a class
            $reservation_status = 'confirmed';
            if ($reservation_type === 'class') {
                $stmt = $pdo->prepare("SELECT capacity, current_attendees, schedule FROM classes WHERE id = ?");
                $stmt->execute([$class_id]);
                $class = $stmt->fetch();

                if (!$class) {
                    throw new Exception("Selected class does not exist.");
                }

                $reservation_date = $class['schedule'];

                if ($class['current_attendees'] >= $class['capacity']) {
                    $reservation_status = 'waitlisted';
                } else {
                    $stmt = $pdo->prepare("UPDATE classes SET current_attendees = current_attendees + 1 WHERE id = ?");
                    $stmt->execute([$class_id]);
                }
            } elseif ($reservation_type === 'session') {
                // Verify the selected availability slot
                $stmt = $pdo->prepare("SELECT * FROM trainer_availability WHERE id = ? AND trainer_id = ?");
                $stmt->execute([$availability_id, $trainer_id]);
                $availability = $stmt->fetch();

                if (!$availability) {
                    throw new Exception("Selected time slot is not available.");
                }

                // Check if the slot is already booked
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations 
                                       WHERE trainer_id = ? AND reservation_date = ? AND status != 'cancelled'");
                $stmt->execute([$trainer_id, $reservation_date]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("This time slot is already booked.");
                }
            }

            // Insert reservation
            $stmt = $pdo->prepare("INSERT INTO reservations (user_id, class_id, trainer_id, reservation_type, reservation_date, status, session_focus) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $class_id, $trainer_id, $reservation_type, $reservation_date, $reservation_status, $session_focus]);

            // Add notification
            $message = $reservation_type === 'class' 
                ? "You have " . ($reservation_status === 'confirmed' ? 'booked' : 'been waitlisted for') . " a class on " . $reservation_date 
                : "You have booked a personal session with a trainer on " . $reservation_date;
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $message]);

            $pdo->commit();
            $success = 'Reservation booked successfully! ' . ($reservation_status === 'waitlisted' ? 'You are on the waitlist.' : '');
            header('Location: reservations.php');
            exit;
        } catch (Exception $e) {
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
    <title>Reservations - Fitness App</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Flatpickr CSS for Calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Custom CSS -->
    <link href="../../assets/css/global.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }

        .store-section {
            padding: 3rem 0;
        }

        .container {
            max-width: 1200px;
        }

        h2 {
            font-weight: 700;
            color: #00ddeb;
            margin-bottom: 2rem;
        }

        /* Advanced Tabs */
        .nav-tabs {
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 2rem;
        }

        .nav-tabs .nav-link {
            color: #b0b0b0;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border: none;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            color: #ffffff;
            border-bottom: 3px solid #00ddeb;
        }

        .nav-tabs .nav-link.active {
            color: #00ddeb;
            border-bottom: 3px solid #00ddeb;
            background: rgba(0, 221, 235, 0.1);
        }

        /* Card Styling */
        .reservation-card, .trainer-card, .reservation-item {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .reservation-card:hover, .trainer-card:hover, .reservation-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .reservation-card h5, .trainer-card h5 {
            color: #00ddeb;
            font-weight: 600;
        }

        /* Form Styling */
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #00ddeb;
            box-shadow: 0 0 10px rgba(0, 221, 235, 0.3);
            color: #ffffff;
        }

        .form-control::placeholder {
            color: #b0b0b0;
        }

        .btn-primary {
            background: linear-gradient(45deg, #00ddeb, #00a3af);
            border: none;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #00a3af, #00ddeb);
            box-shadow: 0 5px 15px rgba(0, 221, 235, 0.4);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: linear-gradient(45deg, #ff4d4d, #ff7878);
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background: linear-gradient(45deg, #ff7878, #ff4d4d);
            box-shadow: 0 5px 15px rgba(255, 77, 77, 0.4);
        }

        /* Filter Section */
        .filter-section {
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        /* Calendar Styling */
        .flatpickr-calendar {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
        }

        .flatpickr-day {
            color: #ffffff !important;
        }

        .flatpickr-day.selected, .flatpickr-day:hover {
            background: #00ddeb !important;
            border-color: #00ddeb !important;
        }

        /* Modal Styling */
        .modal-content {
            background: rgba(26, 26, 46, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            color: #ffffff;
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-title {
            color: #00ddeb;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        /* Badge Styling */
        .badge-confirmed {
            background: #28a745;
        }

        .badge-waitlisted {
            background: #ffc107;
            color: #000;
        }

        .badge-session {
            background: #00ddeb;
        }
    </style>
</head>
<body>
    <!-- Include Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <!-- Reservation Section -->
    <div class="store-section">
        <div class="container">
            <h2 class="text-center mb-4">Book a Reservation</h2>

            <!-- Success/Error Messages -->
            <?php if (isset($success)): ?>
                <div class="alert alert-success animate-fade-in">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger animate-fade-in">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <!-- Tabs for Class, Session, and My Reservations -->
            <ul class="nav nav-tabs mb-4" id="reservationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="class-tab" data-bs-toggle="tab" data-bs-target="#class" type="button" role="tab" aria-controls="class" aria-selected="true">
                        <i class="fas fa-users me-2"></i> Book a Class
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="session-tab" data-bs-toggle="tab" data-bs-target="#session" type="button" role="tab" aria-controls="session" aria-selected="false">
                        <i class="fas fa-user-check me-2"></i> Book a Session
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="my-reservations-tab" data-bs-toggle="tab" data-bs-target="#my-reservations" type="button" role="tab" aria-controls="my-reservations" aria-selected="false">
                        <i class="fas fa-calendar-check me-2"></i> My Reservations
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="reservationTabContent">
                <!-- Book a Class -->
                <div class="tab-pane fade show active" id="class" role="tabpanel" aria-labelledby="class-tab">
                    <!-- Class Filters -->
                    <div class="filter-section mb-4">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="class_date" class="form-label">Filter by Date</label>
                                <input type="date" class="form-control" id="class_date" name="class_date" value="<?php echo $class_filters['date'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="trainer_filter" class="form-label">Filter by Trainer</label>
                                <select class="form-select" id="trainer_filter" name="trainer_filter">
                                    <option value="">All Trainers</option>
                                    <?php foreach ($trainers as $trainer): ?>
                                        <option value="<?php echo $trainer['id']; ?>" <?php echo $class_filters['trainer'] == $trainer['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($trainer['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="class_type" class="form-label">Filter by Class Type</label>
                                <input type="text" class="form-control" id="class_type" name="class_type" placeholder="e.g., Yoga, Strength" value="<?php echo $class_filters['type'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                            </div>
                        </form>
                    </div>

                    <!-- Available Classes -->
                    <div class="row">
                        <?php if (!empty($classes)): ?>
                            <?php foreach ($classes as $class): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="reservation-card">
                                        <h5><?php echo htmlspecialchars($class['class_name']); ?></h5>
                                        <p><strong>Date:</strong> <?php echo $class['schedule']; ?></p>
                                        <p><strong>Trainer:</strong> <?php echo htmlspecialchars($class['trainer_name'] ?? 'TBD'); ?></p>
                                        <p><strong>Capacity:</strong> <?php echo $class['current_attendees']; ?>/<?php echo $class['capacity']; ?> spots</p>
                                        <p><strong>Description:</strong> <?php echo htmlspecialchars($class['description'] ?? 'No description available.'); ?></p>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="book_reservation">
                                            <input type="hidden" name="reservation_type" value="class">
                                            <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                            <button type="submit" class="btn btn-primary w-100" <?php echo $class['current_attendees'] >= $class['capacity'] ? 'disabled' : ''; ?>>
                                                <?php echo $class['current_attendees'] >= $class['capacity'] ? 'Class Full' : 'Book Class'; ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center">
                                <p>No classes available matching your criteria.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Book a Session -->
                <div class="tab-pane fade" id="session" role="tabpanel" aria-labelledby="session-tab">
                    <!-- Membership Check -->
                    <?php if (!$membership): ?>
                        <div class="alert alert-warning text-center">
                            <p>You need an active membership to book a personal session. <a href="membership.php" class="text-primary">Get a membership now!</a></p>
                        </div>
                    <?php else: ?>
                        <!-- Trainer Selection -->
                        <div class="row mb-4">
                            <?php foreach ($trainers as $trainer): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="trainer-card text-center">
                                        <h5><?php echo htmlspecialchars($trainer['name']); ?></h5>
                                        <p><strong>Expertise:</strong> <?php echo htmlspecialchars($trainer['expertise'] ?? 'General Fitness'); ?></p>
                                        <p><strong>Bio:</strong> <?php echo htmlspecialchars($trainer['bio'] ?? 'No bio available.'); ?></p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal-<?php echo $trainer['id']; ?>">
                                            Schedule a Session
                                        </button>
                                    </div>
                                </div>

                                <!-- Schedule Modal -->
                                <div class="modal fade" id="scheduleModal-<?php echo $trainer['id']; ?>" tabindex="-1" aria-labelledby="scheduleModalLabel-<?php echo $trainer['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="scheduleModalLabel-<?php echo $trainer['id']; ?>">Schedule a Session with <?php echo htmlspecialchars($trainer['name']); ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="book_reservation">
                                                    <input type="hidden" name="reservation_type" value="session">
                                                    <input type="hidden" name="trainer_id" value="<?php echo $trainer['id']; ?>">
                                                    <div class="mb-3">
                                                        <label for="availability_id-<?php echo $trainer['id']; ?>" class="form-label">Select Available Time Slot</label>
                                                        <select class="form-select" id="availability_id-<?php echo $trainer['id']; ?>" name="availability_id" required onchange="updateReservationDate(this, '<?php echo $trainer['id']; ?>')">
                                                            <option value="">-- Select a Time Slot --</option>
                                                            <?php foreach ($trainer_availability[$trainer['id']] as $slot): ?>
                                                                <option value="<?php echo $slot['id']; ?>" 
                                                                        data-date="<?php echo $slot['available_date'] . ' ' . $slot['start_time']; ?>">
                                                                    <?php echo $slot['available_date'] . ' ' . $slot['start_time'] . ' - ' . $slot['end_time']; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="reservation_date-<?php echo $trainer['id']; ?>" class="form-label">Selected Date and Time</label>
                                                        <input type="datetime-local" class="form-control" id="reservation_date-<?php echo $trainer['id']; ?>" name="reservation_date" readonly required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="session_focus-<?php echo $trainer['id']; ?>" class="form-label">Session Focus (e.g., Strength, Flexibility)</label>
                                                        <select class="form-select" id="session_focus-<?php echo $trainer['id']; ?>" name="session_focus" required>
                                                            <option value="">-- Select Focus --</option>
                                                            <option value="Strength Training">Strength Training</option>
                                                            <option value="Cardio">Cardio</option>
                                                            <option value="Flexibility">Flexibility</option>
                                                            <option value="Weight Loss">Weight Loss</option>
                                                            <option value="Endurance">Endurance</option>
                                                            <option value="Custom">Custom</option>
                                                        </select>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Book Session</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- My Reservations -->
                <div class="tab-pane fade" id="my-reservations" role="tabpanel" aria-labelledby="my-reservations-tab">
                    <?php if (!empty($reservations)): ?>
                        <div class="row">
                            <?php foreach ($reservations as $reservation): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="reservation-item">
                                        <h5><?php echo htmlspecialchars(ucfirst($reservation['reservation_type'])); ?> Reservation</h5>
                                        <p>
                                            <strong>Details:</strong> 
                                            <?php 
                                            if ($reservation['reservation_type'] === 'class') {
                                                echo htmlspecialchars($reservation['class_name']) . ' (' . $reservation['schedule'] . ')';
                                            } else {
                                                echo 'Session with ' . htmlspecialchars($reservation['trainer_name']) . ($reservation['session_focus'] ? ' - Focus: ' . htmlspecialchars($reservation['session_focus']) : '');
                                            }
                                            ?>
                                        </p>
                                        <p><strong>Date:</strong> <?php echo $reservation['reservation_date']; ?></p>
                                        <p>
                                            <strong>Status:</strong> 
                                            <span class="badge <?php echo $reservation['status'] === 'confirmed' ? 'badge-confirmed' : 'badge-waitlisted'; ?>">
                                                <?php echo htmlspecialchars(ucfirst($reservation['status'])); ?>
                                            </span>
                                        </p>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                            <input type="hidden" name="action" value="cancel_reservation">
                                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                            <button type="submit" class="btn btn-danger">Cancel Reservation</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <p>No active reservations found.</p>
                            <a href="#class" class="btn btn-primary" data-bs-toggle="tab">Book a Class Now</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize Flatpickr for date inputs
        flatpickr("input[type='datetime-local']", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            theme: "dark"
        });

        // Update reservation date based on selected availability
        function updateReservationDate(selectElement, trainerId) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const reservationDate = selectedOption.getAttribute('data-date');
            document.getElementById(`reservation_date-${trainerId}`).value = reservationDate;
        }

        // Add animation on tab switch
        document.querySelectorAll('.nav-link').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function () {
                const targetPane = document.querySelector(this.getAttribute('data-bs-target'));
                targetPane.querySelectorAll('.animate-fade-in').forEach(el => {
                    el.classList.remove('animate-fade-in');
                    void el.offsetWidth; // Trigger reflow to restart animation
                    el.classList.add('animate-fade-in');
                });
            });
        });
    </script>
</body>
</html>