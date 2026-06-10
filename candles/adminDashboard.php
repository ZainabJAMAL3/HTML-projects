<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: adminLogIn.php');
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: adminLogIn.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['name']);
        $scent = trim($_POST['scent']);
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $image = $name . '.jpeg';

        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO product (name, scent, price, stock, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $scent, $price, $stock, $image]);
        }
    }

    if ($_POST['action'] === 'edit') {
        $id = intval($_POST['product_id']);
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);

        $stmt = $pdo->prepare("UPDATE product SET price = ?, stock = ? WHERE product_id = ?");
        $stmt->execute([$price, $stock, $id]);
    }

    // هنا تم تصحيح القفلة البرمجية لضمان الحذف الفوري والنهائي داخل قاعدة البيانات (phpMyAdmin)
    if ($_POST['action'] === 'delete') {
        $id = intval($_POST['product_id']);
        $stmt = $pdo->prepare("DELETE FROM product WHERE product_id = ?");
        $stmt->execute([$id]);
        
        // إعادة التوجيه الفوري هنا تجبر السيرفر على تحديث الـ Database ومسح السجل نهائياً
        header('Location: adminDashboard.php');
        exit();
    }
    
    header('Location: adminDashboard.php');
    exit();
}

$searchQuery = '';
$products = [];

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchQuery = trim($_GET['search']);
    $stmt = $pdo->prepare("SELECT * FROM product WHERE name LIKE ?");
    $stmt->execute(['%' . $searchQuery . '%']);
    $products = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT * FROM product");
    $products = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LUMINA CANDLES | Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/550da1d535.js" crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="style.css">

    <style>
        body { background-color: #f8f9fa; padding-bottom: 100px; }
        .product-details span { font-size: 14px; }
        .qty h5 { margin: 0 10px; }
        .admin-card {
            transition: 0.3s;
            border-left: 5px solid #8e7d66;
        }
        .admin-card:hover { background-color: #fdfaf5 !important; }
        .btn-edit { color: #8e7d66; cursor: pointer; margin-right: 15px; font-size: 18px; }
        .btn-delete { color: #dc3545; cursor: pointer; font-size: 18px; }
        .btn-custom { background-color: #8e7d66; color: white; }
        .btn-custom:hover { background-color: #6d5f4d; color: white; }
        .product-img { width: 70px; height: 70px; object-fit: contain; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light shadow-sm" style="background-color: #d8c3a5">
      <div class="container-fluid">
        <a class="navbar-brand" href="adminDashboard.php"><strong>LUMINA CANDLES (Admin)</strong></a>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="adminDashboard.php?logout=true">Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container mt-5 mb-5">
      <div class="d-flex justify-content-center row">
        <div class="col-md-10">
          
          <div class="p-2 d-flex justify-content-between align-items-center">
            <h4><i class="fa-solid fa-gears"></i> Product Management</h4>
            <button id="addCandleBtn" class="btn btn-custom btn-custom btn-sm">
                <i class="fa fa-plus"></i> Add New Candle
            </button>
          </div>

          <form method="GET" action="adminDashboard.php" class="d-flex flex-row align-items-center mt-4 p-2 bg-white rounded shadow-sm">
            <input type="text" name="search" class="form-control border-0" placeholder="Search by Candle Name..." value="<?= htmlspecialchars($searchQuery) ?>">
            <button class="btn btn-outline-dark btn-sm ms-2" type="submit">Search</button>
            <?php if (!empty($searchQuery)): ?>
                <a href="adminDashboard.php" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
            <?php endif; ?>
          </form>

          <div id="productContainer">
            <?php if (empty($products)): ?>
                <div class="text-center mt-4">
                    <h5 class="text-muted">No products found matching your search. 🕯️</h5>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                <div class="d-flex flex-row justify-content-between align-items-center p-3 bg-white mt-4 rounded shadow-sm admin-card">
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
                  <div><img class="product-img rounded" src="images/<?= htmlspecialchars($image_file) ?>" alt="<?= htmlspecialchars($product['name']) ?>" onerror="this.src='images/placeholder.jpg'"></div>
                  <div class="d-flex flex-column align-items-center product-details">
                    <span class="font-weight-bold"><?= htmlspecialchars($product['name']) ?></span>
                    <div class="d-flex flex-row product-desc">
                      <div class="color"><span class="text-muted">Scent:</span><span class="font-weight-bold">&nbsp;<?= htmlspecialchars($product['scent'] ?? '') ?></span></div>
                    </div>
                  </div>
                  <div class="qty"><span class="text-muted small">Stock:</span><h5 class="d-inline"><?= htmlspecialchars($product['stock']) ?></h5></div>
                  <div><h5 class="text-dark product-price"><?= htmlspecialchars($product['price']) ?> SAR</h5></div>
                  <div class="d-flex align-items-center">
                    <i class="fa-solid fa-pen-to-square btn-edit" title="Edit" data-id="<?= $product['product_id'] ?>" data-price="<?= $product['price'] ?>" data-stock="<?= $product['stock'] ?>"></i>
                    <i class="fa fa-trash btn-delete" title="Delete" data-id="<?= $product['product_id'] ?>"></i>
                  </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>

    <form id="adminActionForm" method="POST" action="adminDashboard.php" style="display: none;">
        <input type="hidden" name="action" id="formAction">
        <input type="hidden" name="product_id" id="formProductId">
        <input type="hidden" name="name" id="formName">
        <input type="hidden" name="scent" id="formScent">
        <input type="hidden" name="price" id="formPrice">
        <input type="hidden" name="stock" id="formStock">
    </form>

    <footer class="text-center py-3 fixed-bottom" style="background-color: #d8c3a5">
      <div class="container">
        <p class="mb-0">© 2026 LUMINA CANDLES | Admin Panel</p>
        <p class="mb-0"><small>Lana, Zainab, Eiman, Miad - IAU CCSIT</small></p>
      </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addBtn = document.getElementById('addCandleBtn');
            const actionForm = document.getElementById('adminActionForm');
            const formAction = document.getElementById('formAction');
            const formProductId = document.getElementById('formProductId');
            const formName = document.getElementById('formName');
            const formScent = document.getElementById('formScent');
            const formPrice = document.getElementById('formPrice');
            const formStock = document.getElementById('formStock');

            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    const name = prompt("Enter Candle Name:");
                    if (!name) return;
                    const scent = prompt("Enter Scent:");
                    if (!scent) return;
                    const price = prompt("Enter Price:");
                    if (!price) return;
                    const stock = prompt("Enter Initial Stock Quantity:");
                    if (!stock) return;

                    formAction.value = 'add';
                    formName.value = name;
                    formScent.value = scent;
                    formPrice.value = price;
                    formStock.value = stock;
                    actionForm.submit();
                });
            }

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-delete')) {
                    const productId = e.target.getAttribute('data-id');
                    if (confirm("Are you sure you want to delete this product? (This will permanently delete it from the system and database)")) {
                        formAction.value = 'delete';
                        formProductId.value = productId;
                        actionForm.submit();
                    }
                }
                
                if (e.target.classList.contains('btn-edit')) {
                    const productId = e.target.getAttribute('data-id');
                    const currentPrice = e.target.getAttribute('data-price');
                    const currentStock = e.target.getAttribute('data-stock');

                    let newPrice = prompt("Edit Price:", currentPrice);
                    if (newPrice === null) return;
                    
                    let newStock = prompt("Edit Stock Quantity:", currentStock);
                    if (newStock === null) return;

                    formAction.value = 'edit';
                    formProductId.value = productId;
                    formPrice.value = newPrice;
                    formStock.value = newStock;
                    actionForm.submit();
                }
            });
        });
    </script>
</body>
</html>