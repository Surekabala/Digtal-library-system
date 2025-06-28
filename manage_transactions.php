
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

include "includes/header.php";

// Get all transactions
$transactions_query = "
    SELECT t.*, u.username, bb.book_id, b.title, bb.borrow_date, bb.status as borrow_status
    FROM transactions t
    JOIN users u ON t.user_id = u.user_id
    JOIN borrowed_books bb ON t.borrow_id = bb.borrow_id
    JOIN books b ON bb.book_id = b.book_id
    ORDER BY t.transaction_date DESC
";
$transactions_result = mysqli_query($conn, $transactions_query);

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
?>

<h1>Manage Transactions</h1>

<div class="action-buttons">
    <a href="payment_settings.php" class="btn">Manage Payment Settings</a>
</div>

<div class="admin-dashboard">
    <div class="card transactions-card">
        <h2>All Transactions</h2>
        
        <?php if(mysqli_num_rows($transactions_result) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Book</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($transaction = mysqli_fetch_assoc($transactions_result)): ?>
                            <tr>
                                <td><?php echo $transaction['transaction_id']; ?></td>
                                <td><?php echo htmlspecialchars($transaction['username']); ?></td>
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
                                <td><?php echo htmlspecialchars($transaction['transaction_details']); ?></td>
                                <td>
                                    <?php if($transaction['payment_status'] == 'pending'): ?>
                                        <a href="approve_transaction.php?id=<?php echo $transaction['transaction_id']; ?>" class="btn btn-small">Approve</a>
                                        <a href="reject_transaction.php?id=<?php echo $transaction['transaction_id']; ?>" class="btn btn-small btn-danger">Reject</a>
                                    <?php else: ?>
                                        <span class="status-text">Approved</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No transactions found.</div>
        <?php endif; ?>
    </div>
    
    <div class="card payment-info-card">
        <h2>Payment Information</h2>
        <div class="qr-container">
            <h3>QR Code for Payments</h3>
            <div class="qr-code">
                <?php if(!empty($qr_image_path) && file_exists($qr_image_path)): ?>
                    <img src="<?php echo $qr_image_path; ?>" alt="UPI QR Code" class="qr-image">
                <?php else: ?>
                    <p>QR Code not uploaded yet</p>
                <?php endif; ?>
            </div>
            <div class="upi-details">
                <p><strong>Your Library UPI ID:</strong> <?php echo !empty($upi_id) ? htmlspecialchars($upi_id) : "Not set"; ?></p>
                <p>Share this QR code with users for UPI payments.</p>
                <a href="payment_settings.php" class="btn">Update Payment Settings</a>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
