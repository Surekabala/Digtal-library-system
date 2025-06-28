
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
    SELECT bb.*, b.title, b.author, b.book_id 
    FROM borrowed_books bb
    JOIN books b ON bb.book_id = b.book_id
    WHERE bb.borrow_id = ? AND bb.user_id = ? AND bb.return_date IS NULL
";
$borrow_stmt = mysqli_prepare($conn, $borrow_query);
mysqli_stmt_bind_param($borrow_stmt, "ii", $borrow_id, $_SESSION['user_id']);
mysqli_stmt_execute($borrow_stmt);
$borrow_result = mysqli_stmt_get_result($borrow_stmt);

if (mysqli_num_rows($borrow_result) == 0) {
    $_SESSION['error'] = "Invalid borrow record or you are not authorized to return this book.";
    header("location: my_collection.php");
    exit;
}

$borrow = mysqli_fetch_assoc($borrow_result);

// Process return request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update borrow record
        $return_date = date('Y-m-d');
        $update_query = "UPDATE borrowed_books SET return_date = ?, status = 'returned' WHERE borrow_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "si", $return_date, $borrow_id);
        mysqli_stmt_execute($update_stmt);
        
        // Update available copies
        $update_book_query = "UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?";
        $update_book_stmt = mysqli_prepare($conn, $update_book_query);
        mysqli_stmt_bind_param($update_book_stmt, "i", $borrow['book_id']);
        mysqli_stmt_execute($update_book_stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        $_SESSION['message'] = "Book returned successfully.";
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

<h1>Return Book</h1>

<div class="form-container" style="max-width: 600px;">
    <h2>Return Confirmation</h2>
    
    <div class="book-details">
        <h3><?php echo htmlspecialchars($borrow['title']); ?></h3>
        <p><strong>Author:</strong> <?php echo htmlspecialchars($borrow['author']); ?></p>
        <p><strong>Borrow Date:</strong> <?php echo date('M j, Y', strtotime($borrow['borrow_date'])); ?></p>
        <p><strong>Due Date:</strong> <?php echo date('M j, Y', strtotime($borrow['due_date'])); ?></p>
        <?php 
        $today = new DateTime();
        $due = new DateTime($borrow['due_date']);
        if ($today > $due) {
            $diff = $today->diff($due);
            echo '<p style="color: red;"><strong>Overdue by:</strong> ' . $diff->days . ' days</p>';
        }
        ?>
    </div>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?borrow_id=" . $borrow_id); ?>" method="post">
        <div class="form-group">
            <p>Are you sure you want to return this book?</p>
        </div>
        
        <div class="form-group">
            <input type="submit" class="btn" value="Yes, Return Book">
            <a href="my_collection.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>
