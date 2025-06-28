
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

// Check if book_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Book ID is required.";
    header("location: manage_books.php");
    exit;
}

$book_id = clean_input($_GET['id']);

// Check if book is currently borrowed
$check_query = "SELECT COUNT(*) as count FROM borrowed_books WHERE book_id = ? AND return_date IS NULL";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "i", $book_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);
$check = mysqli_fetch_assoc($check_result);

if ($check['count'] > 0) {
    $_SESSION['error'] = "Cannot delete book as it is currently borrowed by a user.";
    header("location: manage_books.php");
    exit;
}

// Delete book
$delete_query = "DELETE FROM books WHERE book_id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, "i", $book_id);

if (mysqli_stmt_execute($delete_stmt)) {
    $_SESSION['message'] = "Book deleted successfully.";
} else {
    $_SESSION['error'] = "Error deleting book.";
}

header("location: manage_books.php");
exit;
?>
