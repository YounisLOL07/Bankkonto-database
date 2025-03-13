<?php
require 'users_db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account_id = $_POST['account_id'];
    $amount = $_POST['amount'];

    try {
        // Add amount to account
        $stmt = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE account_id = ? AND user_id = ?");
        $stmt->execute([$amount, $account_id, $_SESSION['user_id']]);

        // Record the transaction
        $stmt = $conn->prepare("INSERT INTO transactions (from_account_id, to_account_id, amount, transaction_date) VALUES (NULL, ?, ?, NOW())");
        $stmt->execute([$account_id, $amount]);

        $_SESSION['success_message'] = "Deposit successful!";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Deposit failed: " . $e->getMessage();
    }
}

header("Location: accounts_overview.php");
exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deposit Money</title>
</head>
<body>
    <h2>Deposit Money</h2>
    <?php include 'messages.php'; ?>
    <form method="POST" action="deposit.php">
        <label for="account_id">Account:</label>
        <input type="text" id="account_id" name="account_id" required>
        <br>
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" required>
        <br>
        <button type="submit">Deposit</button>
    </form>
    <a href="accounts_overview.php">Back to Accounts Overview</a>
</body>
</html>