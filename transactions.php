<?php
require 'users_db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$account_id = $_GET['account'];

try {
    // Get transactions for the account
    $stmt = $conn->prepare("
        SELECT 
            t.*, 
            a_from.account_number AS from_account_number, 
            a_to.account_number AS to_account_number
        FROM transactions t
        LEFT JOIN accounts a_from ON t.from_account_id = a_from.account_id
        LEFT JOIN accounts a_to ON t.to_account_id = a_to.account_id
        WHERE t.from_account_id = ? OR t.to_account_id = ?
        ORDER BY t.transaction_date DESC
    ");
    $stmt->execute([$account_id, $account_id]);
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Failed to load transactions: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transactions</title>
</head>
<body>
    <h2>Transactions</h2>
    <?php include 'messages.php'; ?>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>From Account</th>
                <th>To Account</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['from_account_number'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($transaction['to_account_number'] ?? ''); ?></td>
                    <td><?php echo number_format($transaction['amount'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="accounts_overview.php">Back to Accounts Overview</a>
</body>
</html>