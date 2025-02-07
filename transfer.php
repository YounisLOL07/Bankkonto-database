<?php
require 'users_db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_account = $_POST['from_account'];
    $to_account = $_POST['to_account'];
    $amount = floatval($_POST['amount']);
    $description = $_POST['description'];
    $kid = $_POST['kid'] ?? null;

    try {
        $conn->beginTransaction();

        // Check sufficient funds
        $stmt = $conn->prepare("SELECT balance FROM accounts WHERE account_id = ? AND user_id = ?");
        $stmt->execute([$from_account, $_SESSION['user_id']]);
        $current_balance = $stmt->fetchColumn();

        if ($current_balance < $amount) {
            throw new Exception("Insufficient funds");
        }

        // Update balances
        $stmt = $conn->prepare("UPDATE accounts SET balance = balance - ? WHERE account_id = ?");
        $stmt->execute([$amount, $from_account]);

        $stmt = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE account_number = ?");
        $stmt->execute([$amount, $to_account]);

        // Record transaction
        $stmt = $conn->prepare("INSERT INTO transactions (from_account_id, to_account_id, amount, 
                               transaction_type, kid_number, description) 
                               VALUES (?, ?, ?, 'TRANSFER', ?, ?)");
        $stmt->execute([$from_account, $to_account, $amount, $kid, $description]);

        $conn->commit();
        $_SESSION['success_message'] = "Transfer successful!";
        header("Location: accounts_overview.php");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Transfer failed: " . $e->getMessage();
    }
}

// Get user's accounts
try {
    $stmt = $conn->prepare("SELECT account_id, account_number, balance FROM accounts WHERE user_id = ?");
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
    <title>Transfer Money</title>
</head>
<body>
    <h2>Transfer Money</h2>

    <?php include 'messages.php'; ?>

    <form method="post">
        <div>
            <label for="from_account">From Account:</label>
            <select name="from_account" required>
                <?php foreach ($accounts as $account): ?>
                    <option value="<?php echo $account['account_id']; ?>">
                        <?php echo htmlspecialchars($account['account_number']); ?> 
                        (Balance: kr <?php echo number_format($account['balance'], 2); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="to_account">To Account Number:</label>
            <input type="text" name="to_account" required>
        </div>

        <div>
            <label for="amount">Amount (kr):</label>
            <input type="number" name="amount" step="0.01" required>
        </div>

        <div>
            <label for="kid">KID Number (optional):</label>
            <input type="text" name="kid">
        </div>

        <div>
            <label for="description">Description:</label>
            <input type="text" name="description" required>
        </div>

        <button type="submit">Transfer</button>
    </form>
</body>
</html>