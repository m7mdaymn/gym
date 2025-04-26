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

// Handle adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);

    if ($product_id > 0 && $quantity > 0) {
        try {
            // Check if product exists and has enough stock
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if ($product && $product['stock'] >= $quantity) {
                // Add to cart (stored in session)
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
                $success = 'Product added to cart successfully!';
            } else {
                $error = 'Product Cooker or insufficient stock.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Invalid product or quantity.';
    }
}

// Fetch products from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM products ORDER BY created_at DESC");
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
    $products = [];
}

// Base URL for images (adjust if your project is in a subdirectory)
$baseImageUrl = '/gym/'; // Adjust this based on your server setup
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store - Fitness App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../assets/css/global.css" rel="stylesheet">
    <link href="../../assets/css/ecommerce.css" rel="stylesheet">
    <script src="../../assets/js/global.js"></script>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Store Section -->
    <div class="store-section">
        <div class="container">
            <h2 class="text-center mb-4">Fitness Store</h2>

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

            <!-- Products Grid -->
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col">
                            <div class="product-card">
                                <div class="product-image">
                                    <?php
                                    // Construct the image path based on product ID
                                    $imageFileName = 'product' . $product['id'] . '.jpg'; // e.g., product1.jpg for id=1
                                    $imagePath = 'assets/image/products/' . $imageFileName;
                                    $absoluteImagePath = __DIR__ . '/../../' . $imagePath;

                                    // Check if the image file exists on the server
                                    if (file_exists($absoluteImagePath)) {
                                        $imageSrc = $baseImageUrl . $imagePath;
                                    } else {
                                        $imageSrc = $baseImageUrl . 'assets/image/product-placeholder.jpg';
                                        echo "<!-- Debug: Image not found for product {$product['id']}, using fallback -->";
                                    }
                                    ?>
                                    <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                </div>
                                <div class="product-info">
                                    <h5><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                    <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>
                                    <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                                    <p class="stock"><?php echo $product['stock'] > 0 ? "In Stock: {$product['stock']}" : 'Out of Stock'; ?></p>
                                    <form method="POST" class="add-to-cart-form">
                                        <input type="hidden" name="action" value="add_to_cart">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <div class="quantity-input">
                                            <button type="button" class="quantity-btn minus">-</button>
                                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" readonly>
                                            <button type="button" class="quantity-btn plus">+</button>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100 mt-2" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No products available at the moment.</p>
                <?php endif; ?>
            </div>
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
                    if (value > 1) {
                        input.value = value - 1;
                    }
                }
            });
        });
    </script>
</body>
</html>