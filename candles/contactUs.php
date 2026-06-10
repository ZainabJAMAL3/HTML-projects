<?php
session_start();
include 'db.php';

$message_sent = false;
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['userName'] ?? '');
    $email = trim($_POST['userEmail'] ?? '');
    $msg = trim($_POST['message'] ?? '');

    if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $error_msg = "Full name must contain letters only.";
    } elseif (!empty($name) && !empty($email) && strpos($email, '@') !== false && !empty($msg)) {
        $message_sent = true;
    }
}

$total_items = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $total_items = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUMINA CANDLES | Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/550da1d535.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm" style="background-color: #d8c3a5">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><strong>LUMINA CANDLES</strong></a>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link active" href="contactUs.php">Contact Us</a></li>
          </ul>
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="cart.php">
                <span><i class="fas fa-shopping-cart"></i></span>
                <span class="badge badge-pill bg-danger"><?= $total_items ?></span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <div class="contact-container">
                    <h3 class="mb-4" style="color: #8e7d66;">Send us a Message</h3>
                    
                    <?php if ($message_sent): ?>
                        <div class="alert alert-success" role="alert">Message Sent Successfully! ✨</div>
                    <?php endif; ?>

                    <?php if (!empty($error_msg)): ?>
                        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_msg) ?></div>
                    <?php endif; ?>

                    <form id="contactForm" method="POST" action="contactUs.php" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="userName" class="form-control" id="userName" placeholder="Your Name" required>
                            <div id="nameError" class="error-text" style="display: none; color: #dc3545; font-size: 0.85rem;">⚠️ Name is required and must contain letters only.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="userEmail" class="form-control" id="userEmail" placeholder="example@gmail.com" required>
                            <div id="emailError" class="error-text" style="display: none; color: #dc3545; font-size: 0.85rem;">⚠️ Invalid format: Email must contain '@' and a domain.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" id="message" rows="4" placeholder="How can we help?" required></textarea>
                            <div id="messageError" class="error-text" style="display: none; color: #dc3545; font-size: 0.85rem;">⚠️ Please enter your message.</div>
                        </div>
                        <button type="submit" class="btn btn-custom w-100" style="background-color: #8e7d66; color: white;">Submit</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <h3 style="color: #8e7d66;">Our Location</h3>
                <p>IAU - CCSIT, Ar Rakah, Dammam.</p>
                <div class="map-container">
                    <iframe title="IAU College of Computer Science" src="https://maps.google.com/maps?q=IAU%20College%20of%20Computer%20Science%20Dammam&t=&z=13&ie=UTF8&iwloc=&output=embed" width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-3" style="background-color: #d8c3a5; margin-top: 50px;">
      <div class="container">
        <p class="mb-0">© 2026 LUMINA CANDLES | Group 6 Project</p>
        <p class="mb-0"><small>Lana, Zainab, Eiman, Miad - IAU CCSIT</small></p>
      </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contactForm = document.getElementById('contactForm');
            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    const name = document.getElementById('userName');
                    const email = document.getElementById('userEmail');
                    const message = document.getElementById('message');
                    
                    const nameError = document.getElementById('nameError');
                    const emailError = document.getElementById('emailError');
                    const messageError = document.getElementById('messageError');

                    const nameRegex = /^[a-zA-Z\s]+$/;

                    if (name && (name.value.trim() === "" || !nameRegex.test(name.value))) {
                        nameError.style.setProperty('display', 'block', 'important');
                        isValid = false;
                    } else { if(nameError) nameError.style.setProperty('display', 'none', 'important'); }

                    if (email && !email.value.includes('@')) {
                        emailError.style.setProperty('display', 'block', 'important');
                        isValid = false;
                    } else { if(emailError) emailError.style.setProperty('display', 'none', 'important'); }

                    if (message && message.value.trim() === "") {
                        messageError.style.setProperty('display', 'block', 'important');
                        isValid = false;
                    } else { if(messageError) messageError.style.setProperty('display', 'none', 'important'); }

                    if (!isValid) e.preventDefault();
                });
            }
        });
    </script>
</body>
</html>