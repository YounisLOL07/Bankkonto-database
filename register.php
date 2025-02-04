<?php
require 'users_db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = trim($_POST['address']);
    
    // Generate a 6-digit verification code
    $verification_code = sprintf("%06d", mt_rand(0, 999999));

    // Validation
    if (strlen($password) < 8) {
        $_SESSION['error_message'] = "Password must be at least 8 characters long!";
        header("Location: register.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "Email already registered!";
        header("Location: register.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Start transaction
        $conn->beginTransaction();

        $sql = "INSERT INTO users (firstname, lastname, email, password, address, verification_code, is_verified) 
                VALUES (?, ?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$firstname, $lastname, $email, $hashed_password, $address, $verification_code]);
        
        // Store registration data in session
        $_SESSION['pending_verification'] = [
            'email' => $email,
            'code' => $verification_code
        ];

        $conn->commit();
        
        // Show verification code once
        $_SESSION['show_verification'] = true;
        header("Location: verify.php");
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Registration failed: " . $e->getMessage();
        header("Location: register.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- ... existing styles ... -->
</head>
<body>
    <h1>Register</h1>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="message error-message show">
            <?php 
                echo htmlspecialchars($_SESSION['error_message']);
                unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php" id="registerForm">
        <label for="firstname">First Name</label><br>
        <input type="text" id="firstname" name="firstname" required><br><br>

        <label for="lastname">Last Name</label><br>
        <input type="text" id="lastname" name="lastname" required><br><br>

        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="address">Address</label><br>
        <textarea id="address" name="address" required></textarea><br><br>

        <label for="password">Password (minimum 8 characters)</label><br>
        <input type="password" id="password" name="password" required minlength="8"><br><br>

        <label for="confirm_password">Confirm Password</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <input type="submit" value="Register">
    </form>

    <!-- ... existing JavaScript ... -->
</body>
</html>