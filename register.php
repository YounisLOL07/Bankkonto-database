<?php
require 'users_db.php';

session_start(); // Make sure session is started

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$firstname, $lastname, $email, $password]);
        
        $_SESSION['success_message'] =  "Registration successful! Please login with your new account.";
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
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
</head>
<body>
    <h1>Register</h1>
    <form method="post" action="register.php">
        <label for="firstname">First Name</label><br>
        <input type="text" id="firstname" name="firstname" required placeholder="Enter your first name"><br><br>
        <label for="lastname">Last Name</label><br>
        <input type="text" id="lastname" name="lastname" required placeholder="Enter your last name"><br><br>
        <label for="email">Email</label><br>

        <input type="email" id="email" name="email" required placeholder="Enter your email"><br><br>
        <label for="password" >Password</label><br>
        <input type="password" id="password" name="password" required placeholder="Enter your password"><br><br>
        <a href="login.php">Already have a user? Click here to login!</a> <br><br>
        <input type="submit" value="Register">

    </form>
</body>
</html>