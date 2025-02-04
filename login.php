<?php
require 'users_db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['is_verified']) {
                $_SESSION['error_message'] = "Please verify your account first!";
                header("Location: login.php");
                exit();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['success_message'] = "Login successful!";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid email or password";
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Login failed: " . $e->getMessage();
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <form method="post" action="login.php">
        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" required placeholder="Enter your email"><br>
        <label for="password">Password</label><br>
        <input type="password" id="password" name="password" required placeholder="Enter your password"><br><br>
        <a href="register.php">Don't have an account? Click here to register!</a> <br><br>
        <input type="submit" value="Login">

    </form>
</body>
</html>