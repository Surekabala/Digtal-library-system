
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
$transaction_query = "
    SELECT t.*, bb.book_id, bb.borrow_id, bb.status as borrow_status
    FROM transactions t
    JOIN borrowed_books bb ON t.borrow_id = bb.borrow_id
    WHERE t.transaction_id = ? AND t.payment_status = 'pending'
";
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

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Delete the transaction
    $delete_transaction_query = "DELETE FROM transactions WHERE transaction_id = ?";
    $delete_transaction_stmt = mysqli_prepare($conn, $delete_transaction_query);
    mysqli_stmt_bind_param($delete_transaction_stmt, "i", $transaction_id);
    mysqli_stmt_execute($delete_transaction_stmt);
    
    // If this was a borrow transaction, delete the borrow record and update book availability
    if ($transaction['borrow_status'] == 'borrowed') {
        // Delete borrow record
        $delete_borrow_query = "DELETE FROM borrowed_books WHERE borrow_id = ?";
        $delete_borrow_stmt = mysqli_prepare($conn, $delete_borrow_query);
        mysqli_stmt_bind_param($delete_borrow_stmt, "i", $transaction['borrow_id']);
        mysqli_stmt_execute($delete_borrow_stmt);
        
        // Update book availability
        $update_book_query = "UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?";
        $update_book_stmt = mysqli_prepare($conn, $update_book_query);
        mysqli_stmt_bind_param($update_book_stmt, "i", $transaction['book_id']);
        mysqli_stmt_execute($update_book_stmt);
    } elseif ($transaction['borrow_status'] == 'renewed') {
        // If it was a renewal transaction, revert the due date
        $update_borrow_query = "UPDATE borrowed_books SET due_date = DATE_SUB(due_date, INTERVAL 14 DAY), status = 'borrowed' WHERE borrow_id = ?";
        $update_borrow_stmt = mysqli_prepare($conn, $update_borrow_query);
        mysqli_stmt_bind_param($update_borrow_stmt, "i", $transaction['borrow_id']);
        mysqli_stmt_execute($update_borrow_stmt);
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    $_SESSION['message'] = "Transaction rejected and associated records updated.";
} catch (Exception $e) {
    // Rollback in case of error
    mysqli_rollback($conn);
    $_SESSION['error'] = "Error rejecting transaction: " . $e->getMessage();
}

header("location: manage_transactions.php");
exit;
?>
