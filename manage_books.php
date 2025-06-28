
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

include "includes/header.php";

// Handle book search if needed
$search = "";
$search_condition = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = clean_input($_GET['search']);
    $search_condition = "WHERE title LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%' OR genre LIKE '%$search%'";
}

// Get all books with search condition if provided
$books_query = "SELECT * FROM books $search_condition ORDER BY title";
$books_result = mysqli_query($conn, $books_query);
?>

<h1>Manage Books</h1>

<div class="admin-actions">
    <a href="add_book.php" class="btn">Add New Book</a>
    
    <!-- Add search form -->
    <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="search-form">
        <input type="text" name="search" placeholder="Search by title, author, ISBN or genre" value="<?php echo htmlspecialchars($search); ?>" class="form-control">
        <button type="submit" class="btn">Search</button>
        <?php if(!empty($search)): ?>
            <a href="manage_books.php" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<?php if(mysqli_num_rows($books_result) > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Genre</th>
                    <th>Format</th>
                    <th>Available Copies</th>
                    <th>Added Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($book = mysqli_fetch_assoc($books_result)): ?>
                    <tr>
                        <td><?php echo $book['book_id']; ?></td>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                        <td><?php echo htmlspecialchars($book['genre']); ?></td>
                        <td><?php echo $book['format']; ?></td>
                        <td><?php echo $book['available_copies']; ?></td>
                        <td><?php echo date('M j, Y', strtotime($book['added_date'])); ?></td>
                        <td>
                            <a href="edit_book.php?id=<?php echo $book['book_id']; ?>" class="btn">Edit</a>
                            <a href="delete_book.php?id=<?php echo $book['book_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">No books found.</div>
<?php endif; ?>

<?php include "includes/footer.php"; ?>
