
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();

include "includes/header.php";

// Get user transactions
$transactions_query = "
    SELECT t.*, bb.book_id, b.title, bb.borrow_date, bb.status as borrow_status
    FROM transactions t
    JOIN borrowed_books bb ON t.borrow_id = bb.borrow_id
    JOIN books b ON bb.book_id = b.book_id
    WHERE t.user_id = ?
    ORDER BY t.transaction_date DESC
";
$transactions_stmt = mysqli_prepare($conn, $transactions_query);
mysqli_stmt_bind_param($transactions_stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($transactions_stmt);
$transactions_result = mysqli_stmt_get_result($transactions_stmt);
?>

<div class="container">
    <h1>My Transactions</h1>

    <div class="transaction-dashboard">
        <?php if(mysqli_num_rows($transactions_result) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Book</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($transaction = mysqli_fetch_assoc($transactions_result)): ?>
                            <tr>
                                <td><?php echo $transaction['transaction_id']; ?></td>
                                <td><?php echo htmlspecialchars($transaction['title']); ?></td>
                                <td><?php echo ucfirst($transaction['borrow_status']); ?></td>
                                <td>Rs.<?php echo number_format($transaction['amount'], 2); ?></td>
                                <td><?php echo $transaction['payment_type']; ?></td>
                                <td>
                                    <?php if($transaction['payment_status'] == 'pending'): ?>
                                        <span class="status-pending">Pending</span>
                                    <?php else: ?>
                                        <span class="status-success">Successful</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y, g:i a', strtotime($transaction['transaction_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">You don't have any transactions yet.</div>
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>
