
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();

// Check if borrow_id is provided
if (!isset($_GET['borrow_id']) || empty($_GET['borrow_id'])) {
    $_SESSION['error'] = "Borrow ID is required.";
    header("location: my_collection.php");
    exit;
}

$borrow_id = clean_input($_GET['borrow_id']);

// Get borrow details
$borrow_query = "
    SELECT bb.*, b.title, b.author 
    FROM borrowed_books bb
    JOIN books b ON bb.book_id = b.book_id
    WHERE bb.borrow_id = ? AND bb.user_id = ? AND bb.return_date IS NULL
";
$borrow_stmt = mysqli_prepare($conn, $borrow_query);
mysqli_stmt_bind_param($borrow_stmt, "ii", $borrow_id, $_SESSION['user_id']);
mysqli_stmt_execute($borrow_stmt);
$borrow_result = mysqli_stmt_get_result($borrow_stmt);

if (mysqli_num_rows($borrow_result) == 0) {
    $_SESSION['error'] = "Invalid borrow record or you are not authorized to renew this book.";
    header("location: my_collection.php");
    exit;
}

$borrow = mysqli_fetch_assoc($borrow_result);

// Get payment settings
$upi_id = "";
$qr_image_path = "";

$settings_query = "SELECT * FROM system_settings WHERE setting_name IN ('upi_id', 'qr_image_path')";
$settings_result = mysqli_query($conn, $settings_query);

while ($setting = mysqli_fetch_assoc($settings_result)) {
    if ($setting['setting_name'] == 'upi_id') {
        $upi_id = $setting['setting_value'];
    } elseif ($setting['setting_name'] == 'qr_image_path') {
        $qr_image_path = $setting['setting_value'];
    }
}

// Process renew request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Renewal fee is set to Rs.15 as per requirement
    $amount = 15.00;
    $new_due_date = date('Y-m-d', strtotime($borrow['due_date'] . ' + 14 days')); // Extend by 2 weeks
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update borrow record
        $update_query = "UPDATE borrowed_books SET due_date = ?, status = 'renewed' WHERE borrow_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "si", $new_due_date, $borrow_id);
        mysqli_stmt_execute($update_stmt);
        
        // Create transaction record
        $payment_type = "UPI";
        $transaction_id = clean_input($_POST['transaction_id']);
        $transaction_details = "Transaction ID: " . $transaction_id;
        
        $transaction_query = "INSERT INTO transactions (user_id, borrow_id, amount, payment_type, transaction_details) VALUES (?, ?, ?, ?, ?)";
        $transaction_stmt = mysqli_prepare($conn, $transaction_query);
        mysqli_stmt_bind_param($transaction_stmt, "iidss", $_SESSION['user_id'], $borrow_id, $amount, $payment_type, $transaction_details);
        mysqli_stmt_execute($transaction_stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        $_SESSION['message'] = "Book renewed successfully. Your transaction is pending approval by admin.";
        header("location: my_collection.php");
        exit;
    } catch (Exception $e) {
        // Rollback in case of error
        mysqli_rollback($conn);
        $_SESSION['error'] = "Error processing your request: " . $e->getMessage();
        header("location: my_collection.php");
        exit;
    }
}

include "includes/header.php";
?>

<h1>Renew Book</h1>

<div class="form-container" style="max-width: 600px;">
    <h2>Renewal Confirmation</h2>
    
    <div class="book-details">
        <h3><?php echo htmlspecialchars($borrow['title']); ?></h3>
        <p><strong>Author:</strong> <?php echo htmlspecialchars($borrow['author']); ?></p>
        <p><strong>Current Due Date:</strong> <?php echo date('M j, Y', strtotime($borrow['due_date'])); ?></p>
        <p><strong>New Due Date:</strong> <?php echo date('M j, Y', strtotime($borrow['due_date'] . ' + 14 days')); ?></p>
        <p><strong>Renewal Fee:</strong> Rs.15</p>
    </div>
    
    <div class="payment-instructions">
        <h3>Payment Instructions</h3>
        <p>Please make a payment of Rs.15 to the following UPI ID:</p>
        <p><strong>UPI ID:</strong> <?php echo !empty($upi_id) ? htmlspecialchars($upi_id) : "Contact admin for UPI ID"; ?></p>
        
        <?php if(!empty($qr_image_path) && file_exists($qr_image_path)): ?>
            <div class="qr-code-container">
                <p><strong>Or scan this QR code:</strong></p>
                <img src="<?php echo $qr_image_path; ?>" alt="UPI QR Code" style="max-width: 200px;">
            </div>
        <?php endif; ?>
        
        <p>After making the payment, please enter your transaction ID below.</p>
    </div>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?borrow_id=" . $borrow_id); ?>" method="post">
        <div class="form-group">
            <label>Transaction ID</label>
            <input type="text" name="transaction_id" class="form-control" required placeholder="Enter your UPI transaction ID">
            <small>Enter the transaction ID from your UPI payment app</small>
        </div>
        
        <div class="form-group">
            <input type="submit" class="btn" value="Confirm Renewal">
            <a href="my_collection.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>
