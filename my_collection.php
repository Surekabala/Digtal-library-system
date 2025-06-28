
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();

include "includes/header.php";

// Get borrowed books
$borrowed_query = "
    SELECT bb.*, b.title, b.author, b.genre 
    FROM borrowed_books bb
    JOIN books b ON bb.book_id = b.book_id
    WHERE bb.user_id = ? AND bb.return_date IS NULL
    ORDER BY bb.due_date ASC
";
$borrowed_stmt = mysqli_prepare($conn, $borrowed_query);
mysqli_stmt_bind_param($borrowed_stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($borrowed_stmt);
$borrowed_result = mysqli_stmt_get_result($borrowed_stmt);

// Get user e-books
$ebooks_query = "
    SELECT ue.*, e.title, e.author, e.genre, e.file_path
    FROM user_ebooks ue
    JOIN ebooks e ON ue.ebook_id = e.ebook_id
    WHERE ue.user_id = ?
    ORDER BY ue.access_date DESC
";
$ebooks_stmt = mysqli_prepare($conn, $ebooks_query);
mysqli_stmt_bind_param($ebooks_stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($ebooks_stmt);
$ebooks_result = mysqli_stmt_get_result($ebooks_stmt);

// Get favorites
$favorites_query = "
    SELECT f.*, 
           b.title as book_title, b.author as book_author, 'book' as type,
           NULL as ebook_title, NULL as ebook_author
    FROM favorites f
    JOIN books b ON f.book_id = b.book_id
    WHERE f.user_id = ? AND f.book_id IS NOT NULL
    UNION
    SELECT f.*, 
           NULL as book_title, NULL as book_author, 'ebook' as type,
           e.title as ebook_title, e.author as ebook_author
    FROM favorites f
    JOIN ebooks e ON f.ebook_id = e.ebook_id
    WHERE f.user_id = ? AND f.ebook_id IS NOT NULL
    ORDER BY date_added DESC
";
$favorites_stmt = mysqli_prepare($conn, $favorites_query);
mysqli_stmt_bind_param($favorites_stmt, "ii", $_SESSION["user_id"], $_SESSION["user_id"]);
mysqli_stmt_execute($favorites_stmt);
$favorites_result = mysqli_stmt_get_result($favorites_stmt);
?>

<h1>My Collection</h1>

<div class="tabs">
    <button class="tab active" id="borrowed-tab">Borrowed Books</button>
    <button class="tab" id="ebooks-tab">My E-Books</button>
    <button class="tab" id="favorites-tab">Favorites</button>
</div>

<div id="borrowed-content">
    <h2>Borrowed Books</h2>
    
    <?php if(mysqli_num_rows($borrowed_result) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Genre</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($book = mysqli_fetch_assoc($borrowed_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['genre']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($book['borrow_date'])); ?></td>
                            <td>
                                <?php 
                                    echo date('M j, Y', strtotime($book['due_date']));
                                    $today = new DateTime();
                                    $due = new DateTime($book['due_date']);
                                    if ($today > $due) {
                                        echo ' <span style="color: red;">(Overdue)</span>';
                                    }
                                ?>
                            </td>
                            <td><?php echo ucfirst($book['status']); ?></td>
                            <td>
                                <a href="renew.php?borrow_id=<?php echo $book['borrow_id']; ?>" class="btn">Renew</a>
                                <a href="return.php?borrow_id=<?php echo $book['borrow_id']; ?>" class="btn btn-secondary">Return</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">You haven't borrowed any books yet.</div>
    <?php endif; ?>
</div>

<div id="ebooks-content" style="display:none;">
    <h2>My E-Books</h2>
    
    <?php if(mysqli_num_rows($ebooks_result) > 0): ?>
        <div class="books-grid">
            <?php while($ebook = mysqli_fetch_assoc($ebooks_result)): ?>
                <div class="book-card">
                    <div class="book-img">
                        <img src="images/ebook-placeholder.png" alt="<?php echo htmlspecialchars($ebook['title']); ?>">
                    </div>
                    <div class="book-info">
                        <div class="book-title"><?php echo htmlspecialchars($ebook['title']); ?></div>
                        <div class="book-author">by <?php echo htmlspecialchars($ebook['author']); ?></div>
                        <div class="book-genre"><?php echo htmlspecialchars($ebook['genre']); ?></div>
                        <a href="read_ebook.php?id=<?php echo $ebook['ebook_id']; ?>" class="btn">Read Online</a>
                        <a href="download_ebook.php?id=<?php echo $ebook['ebook_id']; ?>" class="btn btn-secondary">Download</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">You haven't accessed any e-books yet.</div>
    <?php endif; ?>
</div>

<div id="favorites-content" style="display:none;">
    <h2>Favorites</h2>
    
    <?php if(mysqli_num_rows($favorites_result) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Type</th>
                        <th>Added On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($favorite = mysqli_fetch_assoc($favorites_result)): ?>
                        <tr>
                            <td>
                                <?php 
                                    if($favorite['type'] == 'book') {
                                        echo htmlspecialchars($favorite['book_title']);
                                    } else {
                                        echo htmlspecialchars($favorite['ebook_title']);
                                    }
                                ?>
                            </td>
                            <td>
                                <?php 
                                    if($favorite['type'] == 'book') {
                                        echo htmlspecialchars($favorite['book_author']);
                                    } else {
                                        echo htmlspecialchars($favorite['ebook_author']);
                                    }
                                ?>
                            </td>
                            <td><?php echo ucfirst($favorite['type']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($favorite['date_added'])); ?></td>
                            <td>
                                <?php if($favorite['type'] == 'book'): ?>
                                    <a href="book_details.php?id=<?php echo $favorite['book_id']; ?>" class="btn">View</a>
                                <?php else: ?>
                                    <a href="read_ebook.php?id=<?php echo $favorite['ebook_id']; ?>" class="btn">Read</a>
                                <?php endif; ?>
                                <a href="remove_favorite.php?id=<?php echo $favorite['favorite_id']; ?>" class="btn btn-danger">Remove</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">You don't have any favorites yet.</div>
    <?php endif; ?>
</div>

<script>
    // Tab switching
    document.getElementById('borrowed-tab').addEventListener('click', function() {
        document.getElementById('borrowed-content').style.display = 'block';
        document.getElementById('ebooks-content').style.display = 'none';
        document.getElementById('favorites-content').style.display = 'none';
        
        document.getElementById('borrowed-tab').classList.add('active');
        document.getElementById('ebooks-tab').classList.remove('active');
        document.getElementById('favorites-tab').classList.remove('active');
    });
    
    document.getElementById('ebooks-tab').addEventListener('click', function() {
        document.getElementById('borrowed-content').style.display = 'none';
        document.getElementById('ebooks-content').style.display = 'block';
        document.getElementById('favorites-content').style.display = 'none';
        
        document.getElementById('borrowed-tab').classList.remove('active');
        document.getElementById('ebooks-tab').classList.add('active');
        document.getElementById('favorites-tab').classList.remove('active');
    });
    
    document.getElementById('favorites-tab').addEventListener('click', function() {
        document.getElementById('borrowed-content').style.display = 'none';
        document.getElementById('ebooks-content').style.display = 'none';
        document.getElementById('favorites-content').style.display = 'block';
        
        document.getElementById('borrowed-tab').classList.remove('active');
        document.getElementById('ebooks-tab').classList.remove('active');
        document.getElementById('favorites-tab').classList.add('active');
    });
</script>

<?php include "includes/footer.php"; ?>
