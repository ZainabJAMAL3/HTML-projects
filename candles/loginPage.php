<?php
session_start();
include 'db.php';

$error_msg = "";
$login_success = false;
$signup_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $email = trim($_POST['loginEmail'] ?? '');
        $password = trim($_POST['loginPass'] ?? '');

        if (isset($_SESSION['registered_user']) && $_SESSION['registered_user'] === $email && $_SESSION['registered_pass'] === $password) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['customer_email'] = $email;
            $login_success = true;
        } else {
            $error_msg = "Invalid email or password! Please sign up first.";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'signup') {
        $regEmail = trim($_POST['regEmail'] ?? '');
        $regPass = trim($_POST['regPass'] ?? '');
        $regConfirm = trim($_POST['regConfirm'] ?? '');

        if (!empty($regEmail) && strpos($regEmail, '@') !== false && strlen($regPass) >= 6 && $regPass === $regConfirm) {
            $_SESSION['registered_user'] = $regEmail;
            $_SESSION['registered_pass'] = $regPass;
            $_SESSION['user_logged_in'] = true;
            $_SESSION['customer_email'] = $regEmail;
            $signup_success = true;
        } else {
            $error_msg = "Signup failed. Please ensure passwords match and email is valid.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LUMINA CANDLES | Sign In</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/550da1d535.js" crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="style.css">

    <style>
        .container-login {
            max-width: 430px;
            width: 100%;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
            margin-top: 50px;
        }
        .container-login header {
            font-size: 28px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
            color: #8e7d66;
        }
        .container-login input {
            height: 50px;
            width: 100%;
            padding: 0 15px;
            font-size: 17px;
            margin-top: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            outline: none;
        }
        .container-login input:focus { border-color: #8e7d66; }
        .container-login .button {
            background: #8e7d66;
            color: #fff;
            cursor: pointer;
            transition: 0.3s;
            border: none;
            margin-top: 25px;
            font-weight: bold;
        }
        .container-login .button:hover { background: #6d5f4d; }
        .signup { font-size: 15px; text-align: center; margin-top: 15px; }
        .signup label, .signup a { color: #8e7d66; cursor: pointer; text-decoration: none; font-weight: bold; }
        
        #check { display: none; }
        #check:checked ~ .registration { display: block; }
        #check:checked ~ .login { display: none; }
        .registration { display: none; }

        .error-banner {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 0.9rem;
            text-align: left;
        }
        .error-text {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
            display: none; 
            text-align: left;
        }
    </style>
  </head>
  <body>

    <nav class="navbar navbar-expand-lg navbar-light shadow-sm" style="background-color: #d8c3a5">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><strong>LUMINA CANDLES</strong></a>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="contactUs.php">Contact Us</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container d-flex justify-content-center">
      <div class="container-login text-center">
        <input type="checkbox" id="check" />
        
        <div class="login form">
          <header>Login</header>

          <?php if (!empty($error_msg) && isset($_POST['action']) && $_POST['action'] === 'login'): ?>
              <div class="error-banner"><?= htmlspecialchars($error_msg) ?></div>
          <?php endif; ?>

          <form id="userLoginForm" method="POST" action="loginPage.php" novalidate>
            <input type="hidden" name="action" value="login">
            <input type="email" name="loginEmail" id="loginEmail" placeholder="example@gmail.com" required>
            <div id="emailError" class="error-text">⚠️ Invalid format: Email must contain '@' and a domain.</div>
            
            <input type="password" name="loginPass" id="loginPass" placeholder="Password" required>
            <div id="passError" class="error-text">⚠️ Please enter your password.</div>
            
            <input type="submit" class="button" value="Login">
          </form>
          <div class="signup">
            <span>Don't have an account? <label for="check">Signup</label></span>
          </div>
          <hr>
          <div class="signup">
            <span>Are you an Admin? <a href="adminLogIn.php">Dashboard Login</a></span>
          </div>
        </div>

        <div class="registration form">
          <header>Signup</header>

          <?php if (!empty($error_msg) && isset($_POST['action']) && $_POST['action'] === 'signup'): ?>
              <div class="error-banner"><?= htmlspecialchars($error_msg) ?></div>
          <?php endif; ?>

          <form id="userSignupForm" method="POST" action="loginPage.php" novalidate>
            <input type="hidden" name="action" value="signup">
            <input type="text" name="regEmail" id="regEmail" placeholder="Email Address" required>
            <div id="regEmailError" class="error-text">⚠️ Valid email is required.</div>
            
            <input type="password" name="regPass" id="regPass" placeholder="Create a password" required>
            <div id="regPassError" class="error-text">⚠️ Password is required.</div>
            
            <input type="password" name="regConfirm" id="regConfirm" placeholder="Confirm your password" required>
            <div id="regConfirmError" class="error-text">⚠️ Passwords do not match.</div>
            
            <input type="submit" class="button" value="Signup">
          </form>
          <div class="signup">
            <span>Already have an account? <label for="check">Login</label></span>
          </div>
        </div>
      </div>
    </div>

    <footer class="text-center py-3 fixed-bottom" style="background-color: #d8c3a5">
      <div class="container">
        <p class="mb-0">© 2026 LUMINA CANDLES | Group 6 Project</p>
        <p class="mb-0"><small>Lana, Zainab, Eiman, Miad - IAU CCSIT</small></p>
      </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($login_success): ?>
                alert("Welcome back to LUMINA CANDLES! ✨");
                window.location.href = "index.php";
            <?php endif; ?>

            <?php if ($signup_success): ?>
                alert("Account created successfully! Enjoy shopping at LUMINA CANDLES.");
                window.location.href = "index.php";
            <?php endif; ?>

            const loginForm = document.getElementById('userLoginForm');
            if(loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    const email = document.getElementById('loginEmail');
                    const pass = document.getElementById('loginPass');

                    if (email && !email.value.includes('@')) {
                        document.getElementById('emailError').style.setProperty('display', 'block', 'important');
                        isValid = false;
                    } else { 
                        if(document.getElementById('emailError')) document.getElementById('emailError').style.setProperty('display', 'none', 'important'); 
                    }

                    if (pass && pass.value.trim() === "") {
                        document.getElementById('passError').style.setProperty('display', 'block', 'important');
                        isValid = false;
                    } else { 
                        if(document.getElementById('passError')) document.getElementById('passError').style.setProperty('display', 'none', 'important'); 
                    }

                    if (!isValid) e.preventDefault();
                });
            }

            const signupForm = document.getElementById('userSignupForm');
            if(signupForm) {
                signupForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    const regEmail = document.getElementById('regEmail');
                    const regPass = document.getElementById('regPass');
                    const regConfirm = document.getElementById('regConfirm');

                    if (regEmail && !regEmail.value.includes('@')) {
                        document.getElementById('regEmailError').style.setProperty('display', 'block', 'important');
                        isValid = false;
                    } else { 
                        if(document.getElementById('regEmailError')) document.getElementById('regEmailError').style.setProperty('display', 'none', 'important'); 
                    }

                    if (regPass && regPass.value.length < 6) {
                        document.getElementById('regPassError').innerText = "⚠️ Password must be at least 6 characters.";
                        document.getElementById('regPassError').style.setProperty('display', 'block', 'important');
                        isValid = false;
                    } else { 
                        if(document.getElementById('regPassError')) document.getElementById('regPassError').style.setProperty('display', 'none', 'important'); 
                    }

                    if (regConfirm && (regConfirm.value !== regPass.value || regConfirm.value === "")) {
                        document.getElementById('regConfirmError').style.setProperty('display', 'block', 'important');
                        isValid = false;
                    } else { 
                        if(document.getElementById('regConfirmError')) document.getElementById('regConfirmError').style.setProperty('display', 'none', 'important'); 
                    }

                    if (!isValid) e.preventDefault();
                });
            }
        });
    </script>
  </body>
</html>