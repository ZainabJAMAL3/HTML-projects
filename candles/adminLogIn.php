<?php
session_start();
include 'db.php';

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = trim($_POST['username']);
    $pass_input = trim($_POST['password']);

    if (empty($user_input) || empty($pass_input)) {
        $error_msg = "Username and Password are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
            $stmt->execute([$user_input, $pass_input]);
            $admin = $stmt->fetch();

            if ($admin) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                header("Location: adminDashboard.php");
                exit();
            } else {
                $error_msg = "Invalid credentials!";
            }
        } catch (\PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUMINA CANDLES | Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/550da1d535.js" crossorigin="anonymous"></script>
    <style>
        body { background-color: #fdfaf5; }
        .login-box {
            max-width: 400px;
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-top: 100px;
        }
        .login-box header {
            color: #8e7d66;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .btn-admin {
            background-color: #8e7d66;
            color: white;
            font-weight: bold;
            border: none;
            padding: 12px;
            transition: 0.3s;
        }
        .btn-admin:hover { background-color: #6d5f4d; color: #fff; }
        .error-banner {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center">
        <div class="login-box text-center">
            <i class="fa-solid fa-user-shield fa-3x mb-3" style="color: #8e7d66;"></i>
            <header>Admin Portal</header>

            <?php if (!empty($error_msg)): ?>
                <div class="error-banner"><?php echo htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>

            <form action="adminLogIn.php" method="POST">
                <div class="mb-3 text-start">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter admin username" required>
                </div>

                <div class="mb-4 text-start">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-admin w-100">Login to Dashboard</button>
            </form>

            <div class="mt-4">
                <a href="index.php" class="text-muted small text-decoration-none">← Back to Store</a>
            </div>
        </div>
    </div>

    <footer class="text-center py-3 fixed-bottom" style="background-color: #d8c3a5">
      <div class="container">
        <p class="mb-0">© 2026 LUMINA CANDLES | Secure Admin Access</p>
        <p class="mb-0"><small>Lana, Zainab, Eiman, Miad - IAU CCSIT</small></p>
      </div>
    </footer>

</body>
</html>