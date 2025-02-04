<?php
require 'users_db.php';
session_start();

// If no pending verification, redirect to login
if (!isset($_SESSION['pending_verification'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = trim($_POST['verification_code']);
    $stored_data = $_SESSION['pending_verification'];

    if ($entered_code === $stored_data['code']) {
        try {
            // Mark user as verified
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
            $stmt->execute([$stored_data['email']]);

            // Clear verification data
            unset($_SESSION['pending_verification']);
            unset($_SESSION['show_verification']);

            $_SESSION['success_message'] = "Account verified successfully! Please login.";
            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Verification failed: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Invalid verification code!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account</title>
    <style>
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .verification-code {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>Verify Your Account</h1>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="message error-message">
            <?php 
                echo htmlspecialchars($_SESSION['error_message']);
                unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['show_verification'])): ?>
        <div class="verification-code">
            Your verification code is: <?php echo htmlspecialchars($_SESSION['pending_verification']['code']); ?>
        </div>
        <?php unset($_SESSION['show_verification']); ?>
    <?php endif; ?>

    <form method="post" action="verify.php">
        <label for="verification_code">Enter Verification Code</label><br>
        <input type="text" id="verification_code" name="verification_code" required><br><br>
        <input type="submit" value="Verify Account">
    </form>
</body>
</html>