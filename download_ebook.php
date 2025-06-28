
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();

// Check if book_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "E-book ID is required.";
    header("location: ebooks.php");
    exit;
}

$ebook_id = clean_input($_GET['id']);

// Get e-book details
$ebook_query = "SELECT * FROM ebooks WHERE ebook_id = ?";
$ebook_stmt = mysqli_prepare($conn, $ebook_query);
mysqli_stmt_bind_param($ebook_stmt, "i", $ebook_id);
mysqli_stmt_execute($ebook_stmt);
$ebook_result = mysqli_stmt_get_result($ebook_stmt);

if (mysqli_num_rows($ebook_result) == 0) {
    $_SESSION['error'] = "E-book not found.";
    header("location: ebooks.php");
    exit;
}

$ebook = mysqli_fetch_assoc($ebook_result);

// Record e-book access
$check_query = "SELECT * FROM user_ebooks WHERE user_id = ? AND ebook_id = ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "ii", $_SESSION['user_id'], $ebook_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) == 0) {
    // First time access, insert record
    $insert_query = "INSERT INTO user_ebooks (user_id, ebook_id) VALUES (?, ?)";
    $insert_stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, "ii", $_SESSION['user_id'], $ebook_id);
    mysqli_stmt_execute($insert_stmt);
} else {
    // Update access date
    $update_query = "UPDATE user_ebooks SET access_date = CURRENT_TIMESTAMP WHERE user_id = ? AND ebook_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ii", $_SESSION['user_id'], $ebook_id);
    mysqli_stmt_execute($update_stmt);
}

// In a real implementation, this would redirect to the actual PDF file
// For this example, we'll just show a notification

$_SESSION['message'] = "Download started for: " . $ebook['title'];
header("location: ebooks.php");
exit;
?>
