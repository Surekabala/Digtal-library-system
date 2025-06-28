
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

$title = $author = $isbn = $genre = $file_path = $description = "";
$title_err = $author_err = $isbn_err = $genre_err = $file_path_err = "";

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
        $sql = "SELECT ebook_id FROM ebooks WHERE isbn = ?";
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
    
    // Validate file_path
    if(empty(trim($_POST["file_path"]))) {
        $file_path_err = "Please enter a file path.";
    } else {
        $file_path = trim($_POST["file_path"]);
    }
    
    // Get description
    $description = trim($_POST["description"]);
    
    // Check input errors before inserting in database
    if(empty($title_err) && empty($author_err) && empty($isbn_err) && empty($genre_err) && empty($file_path_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO ebooks (title, author, isbn, genre, file_path, description) VALUES (?, ?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssssss", $param_title, $param_author, $param_isbn, $param_genre, $param_file_path, $param_description);
            
            // Set parameters
            $param_title = $title;
            $param_author = $author;
            $param_isbn = $isbn;
            $param_genre = $genre;
            $param_file_path = $file_path;
            $param_description = $description;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "E-book added successfully.";
                header("location: manage_ebooks.php");
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

<h1>Add New E-Book</h1>

<div class="form-container" style="max-width: 800px;">
    <h2>E-Book Details</h2>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
            <label>File Path</label>
            <input type="text" name="file_path" class="form-control <?php echo (!empty($file_path_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $file_path; ?>">
            <span class="invalid-feedback"><?php echo $file_path_err; ?></span>
            <small class="form-text">Path to the PDF file (e.g., ebooks/filename.pdf)</small>
        </div>
        
        <div class="form-group">
            <label>Description (Optional)</label>
            <textarea name="description" class="form-control" rows="5"><?php echo $description; ?></textarea>
        </div>
        
        <div class="form-group">
            <input type="submit" class="btn" value="Add E-Book">
            <a href="manage_ebooks.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>
