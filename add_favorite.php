
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();

// Check parameters
if (!isset($_GET['type']) || empty($_GET['type']) || !isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid parameters.";
    header("location: index.php");
    exit;
}

$type = clean_input($_GET['type']);
$id = clean_input($_GET['id']);

// Check if type is valid
if ($type != 'book' && $type != 'ebook') {
    $_SESSION['error'] = "Invalid type.";
    header("location: index.php");
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    if ($type == 'book') {
        // Check if book exists
        $check_query = "SELECT * FROM books WHERE book_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) == 0) {
            $_SESSION['error'] = "Book not found.";
            header("location: books.php");
            exit;
        }
        
        // Check if already in favorites
        $favorite_query = "SELECT * FROM favorites WHERE user_id = ? AND book_id = ?";
        $favorite_stmt = mysqli_prepare($conn, $favorite_query);
        mysqli_stmt_bind_param($favorite_stmt, "ii", $_SESSION['user_id'], $id);
        mysqli_stmt_execute($favorite_stmt);
        $favorite_result = mysqli_stmt_get_result($favorite_stmt);
        
        if (mysqli_num_rows($favorite_result) > 0) {
            $_SESSION['error'] = "Book is already in your favorites.";
            header("location: book_details.php?id=$id");
            exit;
        }
        
        // Add to favorites
        $insert_query = "INSERT INTO favorites (user_id, book_id) VALUES (?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "ii", $_SESSION['user_id'], $id);
        mysqli_stmt_execute($insert_stmt);
        
        $_SESSION['message'] = "Book added to favorites.";
        header("location: book_details.php?id=$id");
    } else {
        // Check if ebook exists
        $check_query = "SELECT * FROM ebooks WHERE ebook_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) == 0) {
            $_SESSION['error'] = "E-book not found.";
            header("location: ebooks.php");
            exit;
        }
        
        // Check if already in favorites
        $favorite_query = "SELECT * FROM favorites WHERE user_id = ? AND ebook_id = ?";
        $favorite_stmt = mysqli_prepare($conn, $favorite_query);
        mysqli_stmt_bind_param($favorite_stmt, "ii", $_SESSION['user_id'], $id);
        mysqli_stmt_execute($favorite_stmt);
        $favorite_result = mysqli_stmt_get_result($favorite_stmt);
        
        if (mysqli_num_rows($favorite_result) > 0) {
            $_SESSION['error'] = "E-book is already in your favorites.";
            header("location: read_ebook.php?id=$id");
            exit;
        }
        
        // Add to favorites
        $insert_query = "INSERT INTO favorites (user_id, ebook_id) VALUES (?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "ii", $_SESSION['user_id'], $id);
        mysqli_stmt_execute($insert_stmt);
        
        $_SESSION['message'] = "E-book added to favorites.";
        header("location: read_ebook.php?id=$id");
    }
    
    // Commit transaction
    mysqli_commit($conn);
} catch (Exception $e) {
    // Rollback in case of error
    mysqli_rollback($conn);
    $_SESSION['error'] = "Error adding to favorites: " . $e->getMessage();
    header("location: index.php");
    exit;
}
?>
