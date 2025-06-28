
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

// Check if transaction_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Transaction ID is required.";
    header("location: manage_transactions.php");
    exit;
}

$transaction_id = clean_input($_GET['id']);

// Get transaction details
$transaction_query = "SELECT * FROM transactions WHERE transaction_id = ? AND payment_status = 'pending'";
$transaction_stmt = mysqli_prepare($conn, $transaction_query);
mysqli_stmt_bind_param($transaction_stmt, "i", $transaction_id);
mysqli_stmt_execute($transaction_stmt);
$transaction_result = mysqli_stmt_get_result($transaction_stmt);

if (mysqli_num_rows($transaction_result) == 0) {
    $_SESSION['error'] = "Invalid transaction or already processed.";
    header("location: manage_transactions.php");
    exit;
}

$transaction = mysqli_fetch_assoc($transaction_result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update transaction status
    $update_query = "UPDATE transactions SET payment_status = 'successful' WHERE transaction_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "i", $transaction_id);

    if (mysqli_stmt_execute($update_stmt)) {
        $_SESSION['message'] = "Transaction approved successfully.";
        header("location: manage_transactions.php");
        exit;
    } else {
        $_SESSION['error'] = "Error approving transaction.";
        header("location: manage_transactions.php");
        exit;
    }
}

// Display confirmation form
include "includes/header.php";
?>

<h1>Approve Transaction</h1>

<div class="form-container" style="max-width: 600px;">
    <h2>Transaction Details</h2>
    
    <div class="transaction-details">
        <p><strong>Transaction ID:</strong> <?php echo $transaction['transaction_id']; ?></p>
        <p><strong>Amount:</strong> Rs.<?php echo number_format($transaction['amount'], 2); ?></p>
        <p><strong>Payment Method:</strong> <?php echo $transaction['payment_type']; ?></p>
        <p><strong>User Transaction ID:</strong> <?php echo htmlspecialchars($transaction['transaction_details']); ?></p>
        <p><strong>Date:</strong> <?php echo date('M j, Y, g:i a', strtotime($transaction['transaction_date'])); ?></p>
    </div>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $transaction_id); ?>" method="post">
        <div class="form-group">
            <p>Please verify that you have received this payment before approving.</p>
        </div>
        
        <div class="form-group">
            <input type="submit" class="btn" value="Confirm Approval">
            <a href="manage_transactions.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>
