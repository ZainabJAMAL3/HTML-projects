<?php
session_start();
include 'db.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

try {
    $stmt = $pdo->prepare("SELECT * FROM product WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
} catch (\PDOException $e) {
    die("Error fetching product details: " . $e->getMessage());
}

if (!$product) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['add_to_cart'])) {
    $id = intval($_POST['product_id']);
    $qty = intval($_POST['quantity']);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;
    header("Location: productDetails.php?id=$id&status=added");
    exit();
}

$cart_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUMINA CANDLES | <?= htmlspecialchars($product['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/550da1d535.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #fdfaf5; padding-bottom: 100px; }
        .main-section { margin: 50px auto; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .btn-custom { background-color: #8e7d66; color: white; border: none; font-weight: bold; }
        .btn-custom:hover { background-color: #6d5f4d; color: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg shadow-sm" style="background-color: #d8c3a5">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><strong>LUMINA CANDLES</strong></a>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="contactUs.php">Contact Us</a></li>
          </ul>
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link position-relative" href="cart.php">
                <i class="fas fa-shopping-cart"></i>
                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill"><?= $cart_count; ?></span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container">
      <div class="col-lg-10 offset-lg-1 p-4 main-section bg-white">
        <div class="row m-0 mt-4">
          <div class="col-lg-5 pb-3 text-center">
            <?php 
              $db_image = !empty($product['image']) ? trim($product['image']) : 'placeholder.jpeg';
              $base_name = pathinfo($db_image, PATHINFO_FILENAME);
              
              $image_file = $db_image;
              if (!file_exists("images/" . $image_file)) {
                  if (file_exists("images/" . $base_name . ".jpg")) { $image_file = $base_name . ".jpg"; }
                  elseif (file_exists("images/" . $base_name . ".jpeg")) { $image_file = $base_name . ".jpeg"; }
              }
            ?>
            <img src="images/<?= htmlspecialchars($image_file); ?>" class="img-fluid rounded shadow-sm" style="max-height: 350px; object-fit: contain;">
          </div>
          
          <div class="col-lg-7">
            <div class="border p-4 m-0 rounded bg-light">
              <h3 style="color: #8e7d66;"><?= htmlspecialchars($product['name']); ?></h3>
              <p class="h4 text-dark mt-2"><?= htmlspecialchars($product['price']); ?> SAR</p>
              <hr>
              
              <h5>Product Information</h5>
              <p class="text-muted mb-1">Scent Profile: <strong><?= htmlspecialchars($product['scent']); ?></strong></p>
              <p class="text-muted mb-1">Color Style: <strong><?= htmlspecialchars($product['color']); ?></strong></p>
              <p class="text-muted mb-3">Candle Size: <strong><?= htmlspecialchars($product['size']); ?></strong></p>
              <p class="small text-secondary">Available Stock: <strong><?= htmlspecialchars($product['stock']); ?> units</strong></p>
              <hr>

              <form method="POST" action="productDetails.php?id=<?= $product['product_id']; ?>">
                <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>">
                <div class="mb-3">
                  <label class="form-label">Quantity:</label>
                  <input type="number" name="quantity" class="form-control text-center" style="width: 80px;" value="1" min="1" max="<?= htmlspecialchars($product['stock']); ?>">
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" name="add_to_cart" class="btn btn-custom btn-lg w-70" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                        <i class="fas fa-cart-plus me-2"></i> <?= $product['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart' ?>
                    </button>
                    <a href="mailto:support@luminacandles.com?subject=Inquiry about <?= urlencode($product['name']) ?>" class="btn btn-outline-secondary btn-lg w-30" title="Get Help">
                        <i class="fa-solid fa-question-circle"></i> Help
                    </a>
                </div>
              </form>
              
              <?php if(isset($_GET['status']) && $_GET['status'] == 'added'): ?>
                <div class="alert alert-success mt-3 py-2 text-center" role="alert">Added Successfully! ✓</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <footer class="text-center py-3 fixed-bottom" style="background-color: #d8c3a5">
        <p class="mb-0">© 2026 LUMINA CANDLES | Product Details View</p>
    </footer>

</body>
</html>