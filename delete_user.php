
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

// Check if user_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "User ID is required.";
    header("location: manage_users.php");
    exit;
}

$user_id = clean_input($_GET['id']);

// Check if user has any borrowed books
$check_query = "SELECT COUNT(*) as count FROM borrowed_books WHERE user_id = ? AND return_date IS NULL";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "i", $user_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);
$check = mysqli_fetch_assoc($check_result);

if ($check['count'] > 0) {
    $_SESSION['error'] = "Cannot delete user as they have borrowed books that aren't returned yet.";
    header("location: manage_users.php");
    exit;
}

// Delete user
$delete_query = "DELETE FROM users WHERE user_id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, "i", $user_id);

if (mysqli_stmt_execute($delete_stmt)) {
    $_SESSION['message'] = "User deleted successfully.";
} else {
    $_SESSION['error'] = "Error deleting user.";
}

header("location: manage_users.php");
exit;
?>
