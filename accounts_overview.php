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
        .nav-container {
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            padding: 20px 50px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .nav-container a {
            text-decoration: none;
            color: #333;
            font-size: 1.2rem;
            transition: 0.3s;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border-radius: 5px;
            margin: 0 5px;
        }
        .nav-container a:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        .nav-container a:active {
            background-color: #004085;
            transform: translateY(0);
        }
        .footer-container {
            display: flex;
            justify-content: center;
            padding: 50px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: auto;
        }
        .actions a {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.3s;
        }
        .actions a:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        .actions a:active {
            background-color: #004085;
            transform: translateY(0);
        }
    </style>
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

<div class="footer-container">
    <p>&copy; 2025 Bank of Younis. All rights reserved.</p>
</div>
</body>
</html>