
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

$title = $author = $isbn = $genre = $format = $available_copies = $description = "";
$title_err = $author_err = $isbn_err = $genre_err = $format_err = $available_copies_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate title
    if(empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }
    
    // Validate author
    if(empty(trim($_POST["author"]))) {
        $author_err = "Please enter an author.";
    } else {
        $author = trim($_POST["author"]);
    }
    
    // Validate isbn
    if(empty(trim($_POST["isbn"]))) {
        $isbn_err = "Please enter an ISBN.";
    } else {
        // Check if ISBN already exists
        $sql = "SELECT book_id FROM books WHERE isbn = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_isbn);
            $param_isbn = trim($_POST["isbn"]);
            if(mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1) {
                    $isbn_err = "This ISBN already exists.";
                } else {
                    $isbn = trim($_POST["isbn"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate genre
    if(empty(trim($_POST["genre"]))) {
        $genre_err = "Please enter a genre.";
    } else {
        $genre = trim($_POST["genre"]);
    }
    
    // Validate format
    if(empty(trim($_POST["format"]))) {
        $format_err = "Please select a format.";
    } else {
        $format = trim($_POST["format"]);
    }
    
    // Validate available_copies
    if(empty(trim($_POST["available_copies"]))) {
        $available_copies_err = "Please enter the number of available copies.";
    } else {
        $available_copies = trim($_POST["available_copies"]);
        // Check if available_copies is a positive integer
        if(!ctype_digit($available_copies) || $available_copies < 0) {
            $available_copies_err = "Please enter a valid positive number.";
        }
    }
    
    // Get description
    $description = trim($_POST["description"]);
    
    // Check input errors before inserting in database
    if(empty($title_err) && empty($author_err) && empty($isbn_err) && empty($genre_err) && empty($format_err) && empty($available_copies_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO books (title, author, isbn, genre, format, available_copies, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssis", $param_title, $param_author, $param_isbn, $param_genre, $param_format, $param_available_copies, $param_description);
            
            // Set parameters
            $param_title = $title;
            $param_author = $author;
            $param_isbn = $isbn;
            $param_genre = $genre;
            $param_format = $format;
            $param_available_copies = $available_copies;
            $param_description = $description;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Book added successfully.";
                header("location: manage_books.php");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($conn);
}

include "includes/header.php";
?>

<h1>Add New Book</h1>

<div class="form-container" style="max-width: 800px;">
    <h2>Book Details</h2>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">

        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
            <span class="invalid-feedback"><?php echo $title_err; ?></span>
        </div>
        
        <div class="form-group">
            <label>Author</label>
            <input type="text" name="author" class="form-control <?php echo (!empty($author_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $author; ?>">
            <span class="invalid-feedback"><?php echo $author_err; ?></span>
        </div>
        
        <div class="form-group">
            <label>ISBN</label>
            <input type="text" name="isbn" class="form-control <?php echo (!empty($isbn_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $isbn; ?>">
            <span class="invalid-feedback"><?php echo $isbn_err; ?></span>
        </div>
        
        <div class="form-group">
            <label>Genre</label>
            <input type="text" name="genre" class="form-control <?php echo (!empty($genre_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $genre; ?>">
            <span class="invalid-feedback"><?php echo $genre_err; ?></span>
        </div>
        
        <div class="form-group">
            <label>Format</label>
            <select name="format" class="form-control <?php echo (!empty($format_err)) ? 'is-invalid' : ''; ?>">
                <option value="">Select format</option>
                <option value="Physical" <?php echo ($format == "Physical") ? 'selected' : ''; ?>>Physical</option>
                <option value="E-Book" <?php echo ($format == "E-Book") ? 'selected' : ''; ?>>E-Book</option>
                <option value="Both" <?php echo ($format == "Both") ? 'selected' : ''; ?>>Both</option>
            </select>
            <span class="invalid-feedback"><?php echo $format_err; ?></span>
        </div>
        
        <div class="form-group">
            <label>Available Copies</label>
            <input type="number" name="available_copies" class="form-control <?php echo (!empty($available_copies_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $available_copies; ?>" min="0">
            <span class="invalid-feedback"><?php echo $available_copies_err; ?></span>
        </div>
        
        <div class="form-group">
            <label>Description (Optional)</label>
            <textarea name="description" class="form-control" rows="5"><?php echo $description; ?></textarea>
        </div>
        
        <div class="form-group">
            <input type="submit" class="btn" value="Add Book">
            <a href="manage_books.php" class="btn btn-secondary">Cancel</a>
        </div>
        <div class="form-group">
        <label>Book Cover Image</label>
        <input type="file" name="cover_image" class="form-control" accept="image/*">
        </div>

    </form>
</div>

<?php include "includes/footer.php"; ?>
