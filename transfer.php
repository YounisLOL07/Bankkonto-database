<?php
require 'users_db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $from_account_id = $_POST['from_account_id'];
    $to_account_id = $_POST['to_account_id'];
    $amount = $_POST['amount'];

    try {
        $conn->beginTransaction();

        // Check if from_account has sufficient balance
        $stmt = $conn->prepare("SELECT balance FROM accounts WHERE account_id = ? AND user_id = ?");
        $stmt->execute([$from_account_id, $_SESSION['user_id']]);
        $from_account = $stmt->fetch();

        if ($from_account && $from_account['balance'] >= $amount) {
            // Deduct amount from from_account
            $stmt = $conn->prepare("UPDATE accounts SET balance = balance - ? WHERE account_id = ?");
            $stmt->execute([$amount, $from_account_id]);

            // Add amount to to_account
            $stmt = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE account_id = ?");
            $stmt->execute([$amount, $to_account_id]);

            // Record the transaction
            $stmt = $conn->prepare("INSERT INTO transactions (from_account_id, to_account_id, amount, transaction_date) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$from_account_id, $to_account_id, $amount]);

            $conn->commit();
            $_SESSION['success_message'] = "Transfer successful!";
        } else {
            $_SESSION['error_message'] = "Insufficient balance in the source account.";
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Transfer failed: " . $e->getMessage();
    }
}

header("Location: accounts_overview.php");
exit();
?>
// ...existing code...
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transfer Money</title>
</head>
<body>
    <h2>Transfer Money</h2>
    <?php include 'messages.php'; ?>
    <form method="POST" action="transfer.php">
        <label for="from_account_id">From Account:</label>
        <input type="text" id="from_account_id" name="from_account_id" required>
        <br>
        <label for="to_account_id">To Account:</label>
        <input type="text" id="to_account_id" name="to_account_id" required>
        <br>
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" required>
        <br>
        <button type="submit">Transfer</button>
    </form>
    <a href="accounts_overview.php">Back to Accounts Overview</a>
</body>
</html>