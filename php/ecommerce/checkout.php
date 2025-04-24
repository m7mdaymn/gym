<?php


// Include database connection
require_once '../../includes/config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch cart items from the database and validate stock
$cart_items = [];
$subtotal = 0;
$stock_issues = [];
if (!empty($_SESSION['cart'])) {
    try {
        $product_ids = array_keys($_SESSION['cart']);
        if (!empty($product_ids)) {
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->execute($product_ids);
            $cart_items = $stmt->fetchAll();

            // Validate stock and calculate subtotal
            foreach ($cart_items as $item) {
                $quantity = $_SESSION['cart'][$item['id']];
                if ($quantity > $item['stock']) {
                    $stock_issues[] = "Only {$item['stock']} units of " . htmlspecialchars($item['product_name']) . " are available. You have $quantity in your cart.";
                    $_SESSION['cart'][$item['id']] = $item['stock'];
                    $quantity = $item['stock'];
                    if ($item['stock'] == 0) {
                        unset($_SESSION['cart'][$item['id']]);
                        continue;
                    }
                }
                $subtotal += $item['price'] * $quantity;
            }
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
        $cart_items = [];
    }
}

// If cart is empty after stock validation, redirect to cart
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');

    $errors = [];
    if (empty($shipping_address)) {
        $errors[] = 'Shipping address is required.';
    }
    if (empty($payment_method)) {
        $errors[] = 'Please select a payment method.';
    }

    if (empty($errors)) {
        try {
            // Begin transaction
            $pdo->beginTransaction();

            // Insert order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $subtotal, $shipping_address, $payment_method]);
            $order_id = $pdo->lastInsertId();

            // Insert order items and update stock
            foreach ($cart_items as $item) {
                $quantity = $_SESSION['cart'][$item['id']];
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['id'], $quantity, $item['price']]);

                // Update stock
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$quantity, $item['id']]);
            }

            // Commit transaction
            $pdo->commit();

            // Clear cart
            $_SESSION['cart'] = [];

            // Redirect to confirmation page
            header('Location: confirmation.php?order_id=' . $order_id);
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error placing order: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../assets/css/global.css" rel="stylesheet">
    <link href="../../assets/css/ecommerce.css" rel="stylesheet">
    <script src="../../assets/js/global.js"></script>
</head>
<body>
    <!-- Include Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <!-- Checkout Section -->
    <div class="store-section">
        <div class="container">
            <h2 class="text-center mb-4">Checkout</h2>

            <!-- Display Messages -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            <?php if (!empty($stock_issues)): ?>
                <div class="alert alert-warning">
                    <?php foreach ($stock_issues as $issue): ?>
                        <p><?php echo htmlspecialchars($issue); ?></p>
                    <?php endforeach; ?>
                    <p>Please <a href="cart.php">update your cart</a> before proceeding.</p>
                </div>
            <?php endif; ?>

            <?php if (empty($stock_issues)): ?>
                <div class="row">
                    <!-- Checkout Form -->
                    <div class="col-md-6">
                        <h4>Shipping & Payment Information</h4>
                        <form method="POST">
                            <input type="hidden" name="action" value="place_order">
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">Shipping Address</label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="Credit Card" required>
                                        <label class="form-check-label" for="credit_card">Credit Card</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="PayPal">
                                        <label class="form-check-label" for="paypal">PayPal</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="Cash on Delivery">
                                        <label class="form-check-label" for="cod">Cash on Delivery</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Place Order</button>
                        </form>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-md-6">
                        <h4>Order Summary</h4>
                        <div class="cart-table">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <?php $quantity = $_SESSION['cart'][$item['id']]; ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo $quantity; ?></td>
                                            <td>$<?php echo number_format($item['price'] * $quantity, 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="cart-summary text-end">
                            <h4>Subtotal: $<?php echo number_format($subtotal, 2); ?></h4>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>