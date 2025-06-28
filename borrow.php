
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();

// Check if book_id is provided
if (!isset($_GET['book_id']) || empty($_GET['book_id'])) {
    $_SESSION['error'] = "Book ID is required.";
    header("location: books.php");
    exit;
}

$book_id = clean_input($_GET['book_id']);

// Get book details
$book_query = "SELECT * FROM books WHERE book_id = ? AND available_copies > 0";
$book_stmt = mysqli_prepare($conn, $book_query);
mysqli_stmt_bind_param($book_stmt, "i", $book_id);
mysqli_stmt_execute($book_stmt);
$book_result = mysqli_stmt_get_result($book_stmt);

if (mysqli_num_rows($book_result) == 0) {
    $_SESSION['error'] = "Book not available for borrowing.";
    header("location: books.php");
    exit;
}

$book = mysqli_fetch_assoc($book_result);

// Check if user has already borrowed this book and not returned
$check_query = "SELECT * FROM borrowed_books WHERE user_id = ? AND book_id = ? AND return_date IS NULL";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "ii", $_SESSION['user_id'], $book_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) > 0) {
    $_SESSION['error'] = "You have already borrowed this book and not returned it yet.";
    header("location: books.php");
    exit;
}

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

// Process borrow request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Borrow fee is set to Rs.30 as per requirement
    $amount = 30.00;
    $borrow_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+14 days')); // 2 weeks borrowing period
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert borrow record
        $borrow_query = "INSERT INTO borrowed_books (user_id, book_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')";
        $borrow_stmt = mysqli_prepare($conn, $borrow_query);
        mysqli_stmt_bind_param($borrow_stmt, "iiss", $_SESSION['user_id'], $book_id, $borrow_date, $due_date);
        mysqli_stmt_execute($borrow_stmt);
        $borrow_id = mysqli_insert_id($conn);
        
        // Update available copies
        $update_query = "UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "i", $book_id);
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
        
        $_SESSION['message'] = "Book borrowed successfully. Your transaction is pending approval by admin.";
        header("location: my_collection.php");
        exit;
    } catch (Exception $e) {
        // Rollback in case of error
        mysqli_rollback($conn);
        $_SESSION['error'] = "Error processing your request: " . $e->getMessage();
        header("location: books.php");
        exit;
    }
}

include "includes/header.php";
?>

<h1>Borrow Book</h1>

<div class="form-container" style="max-width: 600px;">
    <h2>Borrow Confirmation</h2>
    
    <div class="book-details">
        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
        <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
        <p><strong>Genre:</strong> <?php echo htmlspecialchars($book['genre']); ?></p>
        <p><strong>Format:</strong> <?php echo $book['format']; ?></p>
        <p><strong>Borrowing Fee:</strong> Rs.30</p>
    </div>
    
    <div class="payment-instructions">
        <h3>Payment Instructions</h3>
        <p>Please make a payment of Rs.30 to the following UPI ID:</p>
        <p><strong>UPI ID:</strong> <?php echo !empty($upi_id) ? htmlspecialchars($upi_id) : "Contact admin for UPI ID"; ?></p>
        
        <?php if(!empty($qr_image_path) && file_exists($qr_image_path)): ?>
            <div class="qr-code-container">
                <p><strong>Or scan this QR code:</strong></p>
                <img src="<?php echo $qr_image_path; ?>" alt="UPI QR Code" style="max-width: 200px;">
            </div>
        <?php endif; ?>
        
        <p>After making the payment, please enter your transaction ID below.</p>
    </div>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?book_id=" . $book_id); ?>" method="post">
        <div class="form-group">
            <label>Transaction ID</label>
            <input type="text" name="transaction_id" class="form-control" required placeholder="Enter your UPI transaction ID">
            <small>Enter the transaction ID from your UPI payment app</small>
        </div>
        
        <div class="form-group">
            <input type="submit" class="btn" value="Confirm Borrow">
            <a href="books.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>
