<?php
session_start();
include 'db.php';

if (isset($_POST['add_to_cart'])) {
    $id = intval($_POST['product_id']);
    $qty = 1;
    //Situation A (New Item): If the candle isn't in the cart yet, $_SESSION['cart'][$id] doesn't exist.
    // The ?? 0 tells PHP: "If it doesn't exist, treat its current quantity as 0." Then it adds the new quantity (0 + $qty).

    //Situation B (Existing Item): If the candle is already in the cart (for example, they already added 2), 
    //PHP ignores the ?? 0 and uses the existing number. It then adds the new quantity on top of it (2 + $qty)
    if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;
    //The Query Parameter: It tacks ?status=added onto the end of the URL
    header("Location: index.php?status=added");
    exit();
}

try {
    $stmt = $pdo->query("SELECT * FROM product");
    $products = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}
//This part calculates how many total individual candles the customer has selected so far.
$total_items = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $total_items = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LUMINA CANDLES | Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/550da1d535.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        .btn-custom { background-color: #8e7d66; color: white; }
        .btn-custom:hover { background-color: #6d5f4d; color: white; }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm" style="background-color: #d8c3a5">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><strong>LUMINA CANDLES</strong></a>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="contactUs.php">Contact Us</a></li>
          </ul>
          <ul class="navbar-nav d-flex flex-row me-3">
            <li class="nav-item me-3"><a class="nav-link" href="loginPage.php"><i class="fa-solid fa-user"></i> Login</a></li>
            <li class="nav-item me-3"><a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> <span id="cartBadge" class="badge bg-danger"><?= $total_items ?></span></a></li>
            <li class="nav-item"><a class="nav-link" href="adminLogIn.php"><i class="fa-solid fa-user-shield"></i> Admin</a></li>
          </ul>
        </div>
      </div>
    </nav>
    <!--  (Location: index.php?status=added)? This is the receiving end <!-->
    <div class="container mt-5 mb-5">
        <?php if(isset($_GET['status']) && $_GET['status'] == 'added'): ?>
            <div class="alert alert-success text-center py-2 auto-dismiss">Added to Cart Successfully! ✓</div>
        <?php endif; ?>

        <div class="row" id="productContainer">
            <div class="col-12 text-center mb-5">
                <h2 style="color: #8e7d66;">Our Scented Candles</h2>
                <p>Experience the luxury of hand-poured, customized candles.</p>
            </div>
            
            <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4 product-column">
                <div class="p-3 product-card shadow-sm text-center d-flex flex-column h-100">
                    <?php 
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
                    <a href="productDetails.php?id=<?= $product['product_id'] ?>">
                        <img src="images/<?= htmlspecialchars($image_file) ?>" class="img-fluid rounded mb-3" alt="Candle" style="max-height: 180px; object-fit: contain;">
                    </a>
                    
                    <h5><a href="productDetails.php?id=<?= $product['product_id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($product['name']) ?></a></h5>
                    <p class="text-muted small mb-1"><?= htmlspecialchars($product['scent'] ?? '') ?></p>
                    <p class="text-secondary small mb-2">Available Quantity: <strong><?= htmlspecialchars($product['stock']) ?></strong></p>
                    <h5 class="text-dark mt-auto mb-3"><?= htmlspecialchars($product['price']) ?> SAR</h5>
                    
                    <div class="d-flex gap-2">
                       <!-- The Stock Safety Lock: Notice the syntax If a candle has 0 quantity left in the database,
                       PHP automatically drops the HTML  disabled attribute onto the button. This grays it out and stops 
                        users from buying out-of-stock items. -->
                        <form method="POST" action="index.php" class="w-70 m-0">
                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" name="add_to_cart" class="btn btn-custom btn-sm w-100" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
                        </form>
                        <a href="mailto:support@luminacandles.com?subject=Help" class="btn btn-outline-secondary btn-sm w-30"><i class="fa-solid fa-question-circle"></i> Help</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
  </body>
</html>