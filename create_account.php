<?php
require 'users_db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $account_type = $_POST['account_type'];
    
    // Generate unique 11-digit account number
    $account_number = date('Y') . sprintf("%07d", mt_rand(0, 9999999));
    
    // Get interest rate from account_types table
    try {
        $stmt = $conn->prepare("SELECT base_interest_rate FROM account_types WHERE type_name = ?");
        $stmt->execute([$account_type]);
        $interest_rate = $stmt->fetchColumn();

        $stmt = $conn->prepare("INSERT INTO accounts (user_id, account_type, account_number, interest_rate) 
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $account_type, $account_number, $interest_rate]);
        
        $_SESSION['success_message'] = "Account created successfully! Account number: " . $account_number;
        header("Location: accounts_overview.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Account creation failed: " . $e->getMessage();
    }
}

// Get available account types
try {
    $stmt = $conn->query("SELECT type_name, description FROM account_types");
    $account_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$account_types) {
        $_SESSION['error_message'] = "No account types found.";
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Failed to load account types: " . $e->getMessage();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Bank Account</title>
    <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }
        .success-message {
            color: green;
        }
        .error-message {
            color: red;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Create New Bank Account</h2>
        
        <?php include 'messages.php'; ?>

        <form method="post">
            <div>
                <label for="account_type">Account Type:</label>
                <select name="account_type" id="account_type" required>
                    <?php if (!empty($account_types)): ?>
                        <?php foreach ($account_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type['type_name']); ?>">
                                <?php echo htmlspecialchars($type['type_name']); ?> - 
                                <?php echo htmlspecialchars($type['description']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No account types available</option>
                    <?php endif; ?>
                </select>
            </div>
            <button type="submit">Create Account</button>
        </form>
    </div>
</body>
</html>