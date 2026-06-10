<?php
session_start();
include 'db.php';

if (isset($_POST['update_custom_cart'])) {
    foreach ($_POST['quantity'] as $id => $qty) {
        $qty = intval($qty);
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id] = $qty;
        }
    }
    header("Location: cart.php");
    exit();
}

if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit();
}

if (isset($_GET['clear_all'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit();
}

$cart_products = [];
$total_price = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    
    if (!empty($product_ids)) {
        $in_clause = implode(',', array_fill(0, count($product_ids), '?'));
        try {
            $stmt = $pdo->prepare("SELECT * FROM product WHERE product_id IN ($in_clause)");
            $stmt->execute($product_ids);
            $cart_products = $stmt->fetchAll();
        } catch (\PDOException $e) {
            die("Error loading cart: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUMINA CANDLES | Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/550da1d535.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #fdfaf5; padding-bottom: 100px; }
        .cart-container { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .btn-custom { background-color: #8e7d66; color: white; border: none; font-weight: bold; }
        .btn-custom:hover { background-color: #6d5f4d; color: white; }
        .product-img { width: 70px; height: 70px; object-fit: contain; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm" style="background-color: #d8c3a5">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><strong>LUMINA CANDLES</strong></a>
      </div>
    </nav>

    <div class="container mt-5">
        <div class="cart-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 style="color: #8e7d66;"><i class="fas fa-shopping-cart"></i> Your Shopping Cart</h3>
                <?php if (!empty($cart_products)): ?>
                    <a href="cart.php?clear_all=true" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to clear your cart?')">
                        <i class="fas fa-trash-alt"></i> Clear Cart
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($cart_products)): ?>
                <div class="text-center py-5">
                    <h4 class="text-muted">Your cart is currently empty. 🕯️</h4>
                    <a href="index.php" class="btn btn-custom mt-3">Continue Shopping</a>
                </div>
            <?php else: ?>
                <form method="POST" action="cart.php">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Details</th>
                                <th>Price</th>
                                <th style="width: 120px;">Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($cart_products as $product): 
                                $id = $product['product_id'];
                                $qty = $_SESSION['cart'][$id];
                                $subtotal = $product['price'] * $qty;
                                $total_price += $subtotal;

                                $candle_name = strtolower($product['name']);
                                $image_file = "placeholder.jpg";

                                if (strpos($candle_name, 'lavender') !== false) { $search_word = 'lavender'; }
                                elseif (strpos($candle_name, 'vanilla') !== false) { $search_word = 'vanilla'; }
                                elseif (strpos($candle_name, 'ocean') !== false) { $search_word = 'ocean'; }
                                elseif (strpos($candle_name, 'rose') !== false) { $search_word = 'rose'; }
                                elseif (strpos($candle_name, 'apple') !== false) { $search_word = 'apple'; }
                                else { $search_word = 'unknown'; }

                                $dir_files = scandir("images/");
                                if ($dir_files !== false) {
                                    foreach ($dir_files as $file) {
                                        if ($file !== '.' && $file !== '..' && strpos(strtolower($file), $search_word) !== false) {
                                            $image_file = $file;
                                            break;
                                        }
                                    }
                                }
                            ?>
                            <tr>
                                <td>
                                    <img src="images/<?= htmlspecialchars($image_file) ?>" class="product-img rounded" alt="<?= htmlspecialchars($product['name']) ?>" onerror="this.src='images/placeholder.jpg'">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($product['name']) ?></strong><br>
                                    <small class="text-muted">Scent: <?= htmlspecialchars($product['scent']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($product['price']) ?> SAR</td>
                                <td>
                                    <input type="number" name="quantity[<?= $id ?>]" class="form-control text-center" value="<?= $qty ?>" min="1" max="<?= htmlspecialchars($product['stock']) ?>">
                                </td>
                                <td><strong><?= number_format($subtotal, 2) ?> SAR</strong></td>
                                <td>
                                    <a href="cart.php?remove=<?= $id ?>" class="text-danger" title="Remove item"><i class="fas fa-times-circle fa-lg"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="row mt-4 align-items-center">
                        <div class="col-md-6">
                            <button type="submit" name="update_custom_cart" class="btn btn-outline-secondary">Update Cart</button>
                            <a href="index.php" class="btn btn-link text-decoration-none text-secondary ms-2">Continue Shopping</a>
                        </div>
                        <div class="col-md-6 text-end">
                            <h4>Grand Total: <span style="color: #8e7d66;"><?= number_format($total_price, 2) ?> SAR</span></h4>
                            <a href="checkOut.php" class="btn btn-custom btn-lg px-5 mt-2">Proceed to Checkout</a>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>