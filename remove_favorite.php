
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();

// Check if favorite_id is provided or type and id
if (isset($_GET['id']) && !empty($_GET['id'])) {
    if (isset($_GET['type']) && !empty($_GET['type'])) {
        // Remove by type and id
        $type = clean_input($_GET['type']);
        $id = clean_input($_GET['id']);
        
        if ($type == 'book') {
            $delete_query = "DELETE FROM favorites WHERE user_id = ? AND book_id = ?";
            $redirect = "book_details.php?id=$id";
        } else if ($type == 'ebook') {
            $delete_query = "DELETE FROM favorites WHERE user_id = ? AND ebook_id = ?";
            $redirect = "read_ebook.php?id=$id";
        } else {
            $_SESSION['error'] = "Invalid type.";
            header("location: my_collection.php");
            exit;
        }
        
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "ii", $_SESSION['user_id'], $id);
    } else {
        // Remove by favorite_id
        $favorite_id = clean_input($_GET['id']);
        
        $delete_query = "DELETE FROM favorites WHERE favorite_id = ? AND user_id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "ii", $favorite_id, $_SESSION['user_id']);
        
        $redirect = "my_collection.php";
    }
    
    if (mysqli_stmt_execute($delete_stmt)) {
        $_SESSION['message'] = "Item removed from favorites.";
    } else {
        $_SESSION['error'] = "Error removing from favorites.";
    }
    
    header("location: $redirect");
    exit;
} else {
    $_SESSION['error'] = "Invalid parameters.";
    header("location: my_collection.php");
    exit;
}
?>
