<?php
session_start();
include 'db.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit();
}

$cart_products = [];
$total_price = 0;
$total_items = 0;

$ids = array_keys($_SESSION['cart']);
$in_clause = implode(',', array_fill(0, count($ids), '?'));

try {
    $stmt = $pdo->prepare("SELECT * FROM product WHERE product_id IN ($in_clause)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $product) {
        $id = $product['product_id'];
        $qty = $_SESSION['cart'][$id];
        $subtotal = $product['price'] * $qty;
        
        $total_price += $subtotal;
        $total_items += $qty;

        $cart_products[] = [
            'name' => $product['name'],
            'subtotal' => $subtotal
        ];
    }
} catch (\PDOException $e) {
    die("Error processing checkout: " . $e->getMessage());
}

$order_placed = false;
$customer_name = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['firstName'] ?? '');
    
    if (!preg_match("/^[a-zA-Z\s]+$/", $customer_name)) {
        $error_msg = "First name must contain letters only.";
    } else {
        $_SESSION['cart'] = [];
        $order_placed = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LUMINA CANDLES | Check out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/550da1d535.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css" />
    <style>
        .error-text { color: #dc3545; font-size: 0.85rem; display: none; }
        .btn-custom { background-color: #8e7d66; color: white; border: none; }
        .btn-custom:hover { background-color: #6d5f4d; color: white; }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #d8c3a5">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><strong>LUMINA CANDLES</strong></a>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="contactUs.php">Contact Us</a></li>
          </ul>
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="cart.php">
                <span><i class="fas fa-shopping-cart"></i></span>
                <span class="badge badge-pill bg-danger"><?= $order_placed ? 0 : $total_items ?></span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container" style="margin-top: 5%">
      <?php if ($order_placed): ?>
        <div class="row justify-content-center">
            <div class="col-md-8 text-center py-5 shadow-sm bg-white rounded">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h3 style="color: #8e7d66;">Thank you, <?= htmlspecialchars($customer_name) ?>! ✨</h3>
                <p class="lead text-muted">Your order has been successfully placed using Cash on Delivery.</p>
                <a href="index.php" class="btn btn-custom mt-3 px-5">Back to Home</a>
            </div>
        </div>
      <?php else: ?>
      
      <?php if(!empty($error_msg)): ?>
          <div class="alert alert-danger text-center"><?= htmlspecialchars($error_msg) ?></div>
      <?php endif; ?>

      <div class="row">
        <div class="col-md-4 order-md-2 mb-4">
          <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Your cart</span>
            <span class="badge badge-secondary badge-pill" style="color: grey"><?= $total_items ?></span>
          </h4>
          <ul class="list-group mb-3">
            <?php foreach ($cart_products as $item): ?>
            <li class="list-group-item d-flex justify-content-between lh-condensed">
              <div><h6 class="my-0"><?= htmlspecialchars($item['name']) ?></h6></div>
              <span class="text-muted"><?= number_format($item['subtotal'], 2) ?> SAR</span>
            </li>
            <?php endforeach; ?>
            <li class="list-group-item d-flex justify-content-between bg-light">
              <span>Total (SAR)</span>
              <strong><?= number_format($total_price, 2) ?> SAR</strong>
            </li>
          </ul>
        </div>
        <div class="col-md-8 order-md-1">
          <h4 class="mb-3">Shipping address</h4>
          <form id="checkoutForm" method="POST" action="checkOut.php" novalidate="">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="firstName">First name</label>
                <input type="text" name="firstName" class="form-control" id="firstName" required="" />
                <div id="nameError" class="error-text">⚠️ Name is required and must contain letters only.</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="lastName">Last name</label>
                <input type="text" class="form-control" id="lastName" required="" />
              </div>
            </div>
            <div class="mb-3">
              <label for="email">Email </label>
              <input type="email" class="form-control" id="email" placeholder="you@example.com" />
            </div>
            <div class="mb-3">
              <label for="address">Address</label>
              <input type="text" class="form-control" id="address" placeholder="1234 Main St" required="" />
              <div id="addressError" class="error-text">Please enter your shipping address.</div>
            </div>
            <div class="row">
              <div class="col-md-5 mb-3">
                <label for="country">Country</label>
                <select class="form-select d-block w-100" id="country" required="">
                  <option value="">Choose...</option>
                  <option>Saudi Arabia</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label for="City">City</label>
                <select class="form-select d-block w-100" id="City" required="">
                  <option value="">Choose...</option>
                  <option>Dammam</option>
                  <option>Khobar</option>
                  <option>Dhahran</option>
                </select>
              </div>
            </div>
            <hr class="mb-4" />
            <h4 class="mb-3">Payment</h4>
            <div class="d-block my-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="pay" id="cod" checked />
                <label class="form-check-label" for="cod">Cash on Delivery</label>
              </div>
            </div>
            <hr class="mb-4" />
            <button class="btn btn-custom btn-lg w-100" type="submit">Place Order</button>
          </form>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <script>
      const form = document.getElementById('checkoutForm');
      if (form) {
          form.addEventListener('submit', function(e) {
              let valid = true;
              const name = document.getElementById('firstName').value;
              const address = document.getElementById('address').value;
              const nameRegex = /^[a-zA-Z\s]+$/;

              if(name.trim() === "" || !nameRegex.test(name)) {
                  document.getElementById('nameError').style.display = 'block';
                  valid = false;
              } else { document.getElementById('nameError').style.display = 'none'; }

              if(address.trim() === "") {
                  document.getElementById('addressError').style.display = 'block';
                  valid = false;
              } else { document.getElementById('addressError').style.display = 'none'; }

              if(!valid) e.preventDefault();
          });
      }
    </script>
  </body>
</html>