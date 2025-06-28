
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

// Check if user_id and borrow_id are provided
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['borrow_id']) || empty($_GET['borrow_id'])) {
    $_SESSION['error'] = "User ID and Borrow ID are required.";
    header("location: manage_users.php");
    exit;
}

$user_id = clean_input($_GET['id']);
$borrow_id = clean_input($_GET['borrow_id']);

// Get borrow and user details
$query = "
    SELECT bb.*, u.username, u.email, b.title
    FROM borrowed_books bb
    JOIN users u ON bb.user_id = u.user_id
    JOIN books b ON bb.book_id = b.book_id
    WHERE bb.borrow_id = ? AND bb.user_id = ? AND bb.due_date < CURDATE() AND bb.return_date IS NULL
";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $borrow_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Invalid record or book is not overdue.";
    header("location: manage_users.php");
    exit;
}

$record = mysqli_fetch_assoc($result);

// In a real application, this would send an email or SMS
// For this example, we'll just simulate notification

$_SESSION['message'] = "Notification sent to " . $record['username'] . " about overdue book: " . $record['title'];
header("location: manage_users.php");
exit;
?>
