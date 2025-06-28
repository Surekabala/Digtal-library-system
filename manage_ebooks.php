
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

include "includes/header.php";

// Get all e-books
$ebooks_query = "SELECT * FROM ebooks ORDER BY title";
$ebooks_result = mysqli_query($conn, $ebooks_query);
?>

<h1>Manage E-Books</h1>

<div class="admin-actions">
    <a href="add_ebook.php" class="btn">Add New E-Book</a>
</div>

<?php if(mysqli_num_rows($ebooks_result) > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Genre</th>
                    <th>File Path</th>
                    <th>Added Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($ebook = mysqli_fetch_assoc($ebooks_result)): ?>
                    <tr>
                        <td><?php echo $ebook['ebook_id']; ?></td>
                        <td><?php echo htmlspecialchars($ebook['title']); ?></td>
                        <td><?php echo htmlspecialchars($ebook['author']); ?></td>
                        <td><?php echo htmlspecialchars($ebook['isbn']); ?></td>
                        <td><?php echo htmlspecialchars($ebook['genre']); ?></td>
                        <td><?php echo htmlspecialchars($ebook['file_path']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($ebook['added_date'])); ?></td>
                        <td>
                            <a href="edit_ebook.php?id=<?php echo $ebook['ebook_id']; ?>" class="btn">Edit</a>
                            <a href="delete_ebook.php?id=<?php echo $ebook['ebook_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this e-book?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">No e-books found.</div>
<?php endif; ?>

<?php include "includes/footer.php"; ?>
