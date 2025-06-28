
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();

include "includes/header.php";

// Initialize variables
$search = $genre = "";
$books = [];

// Process search and filters
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $search = clean_input($_GET['search']);
    
    if (isset($_GET['genre']) && !empty($_GET['genre'])) {
        $genre = clean_input($_GET['genre']);
    }
}

// Get all genres for filter
$genres_query = "SELECT DISTINCT genre FROM books ORDER BY genre";
$genres_result = mysqli_query($conn, $genres_query);
$genres = [];
while ($row = mysqli_fetch_assoc($genres_result)) {
    $genres[] = $row['genre'];
}

// Build the books query based on search and filters
$books_query = "SELECT * FROM books WHERE 1=1";

if (!empty($search)) {
    $books_query .= " AND (title LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%')";
}

if (!empty($genre)) {
    $books_query .= " AND genre = '$genre'";
}

$books_query .= " ORDER BY title";
$books_result = mysqli_query($conn, $books_query);
?>

<h1>Available Books</h1>

<!-- Search form -->
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="search-form">
    <input type="text" name="search" placeholder="Search by title, author, or ISBN" class="form-control" value="<?php echo $search; ?>">
    
    <select name="genre" class="form-control">
        <option value="">All Genres</option>
        <?php foreach($genres as $g): ?>
            <option value="<?php echo $g; ?>" <?php echo ($genre == $g) ? 'selected' : ''; ?>><?php echo $g; ?></option>
        <?php endforeach; ?>
    </select>
    
    <button type="submit" class="btn">Search</button>
    <?php if(!empty($search) || !empty($genre)): ?>
        <a href="books.php" class="btn">Clear</a>
    <?php endif; ?>
</form>

<!-- Genre tabs for quick filtering -->
<div class="tabs">
    <button class="tab <?php echo empty($genre) ? 'active' : ''; ?>" onclick="window.location='books.php<?php echo !empty($search) ? '?search='.$search : ''; ?>'">All</button>
    <?php foreach($genres as $g): ?>
        <button class="tab <?php echo ($genre == $g) ? 'active' : ''; ?>" 
                onclick="window.location='books.php?genre=<?php echo $g; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>'">
            <?php echo $g; ?>
        </button>
    <?php endforeach; ?>
</div>

<!-- Books listing -->
<div class="books-grid">
    <?php 
    if(mysqli_num_rows($books_result) > 0):
        while($book = mysqli_fetch_assoc($books_result)):
    ?>
        <div class="book-card">
            <div class="book-img">
                <img src="images/book-placeholder.png" alt="<?php echo htmlspecialchars($book['title']); ?>">
            </div>
            <div class="book-info">
                <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                <div class="book-genre"><?php echo htmlspecialchars($book['genre']); ?></div>
                <div class="book-format">Format: <?php echo $book['format']; ?></div>
                <?php if($book['available_copies'] > 0): ?>
                    <a href="borrow.php?book_id=<?php echo $book['book_id']; ?>" class="btn">Borrow</a>
                <?php else: ?>
                    <button class="btn" disabled>Out of Stock</button>
                <?php endif; ?>
            </div>
        </div>
    <?php 
        endwhile;
    else:
    ?>
        <div class="alert alert-info" style="width: 100%;">No books found matching your criteria.</div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
