<?php
require 'users_db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Get user's accounts
    $stmt = $conn->prepare("
        SELECT 
            a.*, 
            at.type_name,
            (SELECT COUNT(*) FROM transactions t 
             WHERE t.from_account_id = a.account_id 
             OR t.to_account_id = a.account_id) as transaction_count
        FROM accounts a
        JOIN account_types at ON a.account_type = at.type_name
        WHERE a.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $accounts = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Failed to load accounts: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Accounts</title>
    <style>
        .account-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px;
            border-radius: 5px;
        }
        .balance {
            font-size: 24px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>My Accounts</h2>
    
    <?php include 'messages.php'; ?>

    <div class="accounts-container">
        <?php foreach ($accounts as $account): ?>
            <div class="account-card">
                <h3><?php echo htmlspecialchars($account['type_name']); ?></h3>
                <p>Account Number: <?php echo htmlspecialchars($account['account_number']); ?></p>
                <p class="balance">Balance: kr <?php echo number_format($account['balance'], 2); ?></p>
                <p>Interest Rate: <?php echo $account['interest_rate']; ?>%</p>
                <div class="actions">
                    <a href="transfer.php?account=<?php echo $account['account_id']; ?>">Transfer</a>
                    <a href="deposit.php?account=<?php echo $account['account_id']; ?>">Deposit</a>
                    <a href="withdraw.php?account=<?php echo $account['account_id']; ?>">Withdraw</a>
                    <a href="transactions.php?account=<?php echo $account['account_id']; ?>">
                        View Transactions (<?php echo $account['transaction_count']; ?>)
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="actions">
        <a href="create_account.php" class="button">Create New Account</a>
    </div>
</body>
</html>