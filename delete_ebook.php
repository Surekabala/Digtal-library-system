
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

// Check if ebook_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "E-book ID is required.";
    header("location: manage_ebooks.php");
    exit;
}

$ebook_id = clean_input($_GET['id']);

// Delete e-book
$delete_query = "DELETE FROM ebooks WHERE ebook_id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, "i", $ebook_id);

if (mysqli_stmt_execute($delete_stmt)) {
    // Also delete from user_ebooks
    $delete_user_ebooks_query = "DELETE FROM user_ebooks WHERE ebook_id = ?";
    $delete_user_ebooks_stmt = mysqli_prepare($conn, $delete_user_ebooks_query);
    mysqli_stmt_bind_param($delete_user_ebooks_stmt, "i", $ebook_id);
    mysqli_stmt_execute($delete_user_ebooks_stmt);
    
    // And delete from favorites
    $delete_favorites_query = "DELETE FROM favorites WHERE ebook_id = ?";
    $delete_favorites_stmt = mysqli_prepare($conn, $delete_favorites_query);
    mysqli_stmt_bind_param($delete_favorites_stmt, "i", $ebook_id);
    mysqli_stmt_execute($delete_favorites_stmt);
    
    $_SESSION['message'] = "E-book deleted successfully.";
} else {
    $_SESSION['error'] = "Error deleting e-book.";
}

header("location: manage_ebooks.php");
exit;
?>
