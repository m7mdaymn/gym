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

// Handle cart updates (update quantity or remove item)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_cart') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);

        if ($product_id > 0) {
            try {
                // Check stock availability
                $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();

                if ($product) {
                    if ($quantity <= 0) {
                        // Remove item if quantity is 0 or less
                        unset($_SESSION['cart'][$product_id]);
                        $success = 'Item removed from cart.';
                    } elseif ($quantity <= $product['stock']) {
                        // Update quantity if within stock
                        $_SESSION['cart'][$product_id] = $quantity;
                        $success = 'Cart updated successfully!';
                    } else {
                        $error = "Requested quantity ($quantity) for " . htmlspecialchars($product['product_name']) . " exceeds available stock (" . $product['stock'] . ").";
                    }
                } else {
                    $error = 'Product not found.';
                    unset($_SESSION['cart'][$product_id]); // Remove invalid product
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        } else {
            $error = 'Invalid product ID.';
        }
    }
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
                    $_SESSION['cart'][$item['id']] = $item['stock']; // Adjust quantity to max available
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Fitness App</title>
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

    <!-- Cart Section -->
    <div class="store-section">
        <div class="container">
            <h2 class="text-center mb-4">Your Cart</h2>

            <!-- Display Messages -->
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
            <?php if (!empty($stock_issues)): ?>
                <div class="alert alert-warning">
                    <?php foreach ($stock_issues as $issue): ?>
                        <p><?php echo htmlspecialchars($issue); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Cart Items -->
            <?php if (!empty($cart_items)): ?>
                <div class="cart-table">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <?php $quantity = $_SESSION['cart'][$item['id']]; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="update_cart">
                                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                            <div class="quantity-input">
                                                <button type="button" class="quantity-btn minus">-</button>
                                                <input type="number" name="quantity" value="<?php echo $quantity; ?>" min="0" max="<?php echo $item['stock']; ?>" readonly>
                                                <button type="button" class="quantity-btn plus">+</button>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary ms-2">Update</button>
                                        </form>
                                    </td>
                                    <td>$<?php echo number_format($item['price'] * $quantity, 2); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline remove-item-form">
                                            <input type="hidden" name="action" value="update_cart">
                                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="quantity" value="0">
                                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Subtotal and Checkout -->
                <div class="cart-summary text-end">
                    <h4>Subtotal: $<?php echo number_format($subtotal, 2); ?></h4>
                    <a href="store.php" class="btn btn-secondary mt-3 me-2">Continue Shopping</a>
                    <a href="checkout.php" class="btn btn-primary mt-3">Proceed to Checkout</a>
                </div>
            <?php else: ?>
                <p class="text-center">Your cart is empty. <a href="store.php">Continue shopping</a>.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Quantity input controls
        document.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                let value = parseInt(input.value);
                const max = parseInt(input.max);

                if (this.classList.contains('plus')) {
                    if (value < max) {
                        input.value = value + 1;
                    }
                } else if (this.classList.contains('minus')) {
                    if (value > 0) {
                        input.value = value - 1;
                    }
                }
            });
        });

        // Confirm removal of items
        document.querySelectorAll('.remove-item-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to remove this item from your cart?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>