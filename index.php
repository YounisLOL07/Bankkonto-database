<?php
require 'users_db.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank of Younis</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="nav-container">
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="index.php">Home</a>
        <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['firstname']); ?></span>
        <a href="logout.php">Logout</a>
        
    <?php else: ?>
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    <?php endif; ?>
    </div>

    <div class="main-containerv2">
        <h1>Welcome to Bank of Younis</h1>
        <p>Your money is safe with us.</p>
        
        <a href="accounts_overview.php">Check out your account! <br></a>
        <br>
        <a href="create_account.php">Want a new bank account?</a>
    </div>
    <div class="footer-container">
        <p>&copy; 2025 Bank of Younis. All rights reserved.</p>


</body>
</html>