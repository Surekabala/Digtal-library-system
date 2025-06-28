
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

// Check if book ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Book ID is required.";
    header("location: manage_books.php");
    exit;
}

$book_id = clean_input($_GET['id']);

// Get book details
$book_query = "SELECT * FROM books WHERE book_id = ?";
$book_stmt = mysqli_prepare($conn, $book_query);
mysqli_stmt_bind_param($book_stmt, "i", $book_id);
mysqli_stmt_execute($book_stmt);
$book_result = mysqli_stmt_get_result($book_stmt);

if (mysqli_num_rows($book_result) == 0) {
    $_SESSION['error'] = "Book not found.";
    header("location: manage_books.php");
    exit;
}

$book = mysqli_fetch_assoc($book_result);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = clean_input($_POST['title']);
    $author = clean_input($_POST['author']);
    $isbn = clean_input($_POST['isbn']);
    $genre = clean_input($_POST['genre']);
    $format = clean_input($_POST['format']);
    $available_copies = clean_input($_POST['available_copies']);
    $description = clean_input($_POST['description']);
    
    // Validate form data
    if (empty($title) || empty($author) || empty($isbn) || empty($genre)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        // Check if ISBN is unique (excluding current book)
        $isbn_check_query = "SELECT book_id FROM books WHERE isbn = ? AND book_id != ?";
        $isbn_check_stmt = mysqli_prepare($conn, $isbn_check_query);
        mysqli_stmt_bind_param($isbn_check_stmt, "si", $isbn, $book_id);
        mysqli_stmt_execute($isbn_check_stmt);
        $isbn_result = mysqli_stmt_get_result($isbn_check_stmt);
        
        if (mysqli_num_rows($isbn_result) > 0) {
            $_SESSION['error'] = "ISBN already exists. Please enter a unique ISBN.";
        } else {
            // Update book
            $update_query = "UPDATE books SET title = ?, author = ?, isbn = ?, genre = ?, format = ?, available_copies = ?, description = ? WHERE book_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "sssssssi", $title, $author, $isbn, $genre, $format, $available_copies, $description, $book_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $_SESSION['message'] = "Book updated successfully.";
                header("location: manage_books.php");
                exit;
            } else {
                $_SESSION['error'] = "Error updating book: " . mysqli_error($conn);
            }
        }
    }
}

include "includes/header.php";
?>

<h1>Edit Book</h1>

<div class="form-container">
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $book_id); ?>">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($book['title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Author</label>
            <input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($book['author']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>ISBN</label>
            <input type="text" name="isbn" class="form-control" value="<?php echo htmlspecialchars($book['isbn']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Genre</label>
            <input type="text" name="genre" class="form-control" value="<?php echo htmlspecialchars($book['genre']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Format</label>
            <select name="format" class="form-control" required>
                <option value="Physical" <?php echo $book['format'] == 'Physical' ? 'selected' : ''; ?>>Physical</option>
                <option value="E-Book" <?php echo $book['format'] == 'E-Book' ? 'selected' : ''; ?>>E-Book</option>
                <option value="Both" <?php echo $book['format'] == 'Both' ? 'selected' : ''; ?>>Both</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Available Copies</label>
            <input type="number" name="available_copies" class="form-control" value="<?php echo $book['available_copies']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($book['description'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <input type="submit" class="btn" value="Update Book">
            <a href="manage_books.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>
