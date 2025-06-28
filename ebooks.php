
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();

include "includes/header.php";

// Initialize variables
$search = $genre = "";
$ebooks = [];

// Process search and filters
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $search = clean_input($_GET['search']);
    
    if (isset($_GET['genre']) && !empty($_GET['genre'])) {
        $genre = clean_input($_GET['genre']);
    }
}

// Get all genres for filter
$genres_query = "SELECT DISTINCT genre FROM ebooks ORDER BY genre";
$genres_result = mysqli_query($conn, $genres_query);
$genres = [];
while ($row = mysqli_fetch_assoc($genres_result)) {
    $genres[] = $row['genre'];
}

// Build the e-books query based on search and filters
$ebooks_query = "SELECT * FROM ebooks WHERE 1=1";

if (!empty($search)) {
    $ebooks_query .= " AND (title LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%')";
}

if (!empty($genre)) {
    $ebooks_query .= " AND genre = '$genre'";
}

$ebooks_query .= " ORDER BY title";
$ebooks_result = mysqli_query($conn, $ebooks_query);
?>

<h1>E-Books Library</h1>

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
        <a href="ebooks.php" class="btn">Clear</a>
    <?php endif; ?>
</form>

<!-- Genre tabs for quick filtering -->
<div class="tabs">
    <button class="tab <?php echo empty($genre) ? 'active' : ''; ?>" onclick="window.location='ebooks.php<?php echo !empty($search) ? '?search='.$search : ''; ?>'">All</button>
    <?php foreach($genres as $g): ?>
        <button class="tab <?php echo ($genre == $g) ? 'active' : ''; ?>" 
                onclick="window.location='ebooks.php?genre=<?php echo $g; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>'">
            <?php echo $g; ?>
        </button>
    <?php endforeach; ?>
</div>

<!-- E-books listing -->
<div class="books-grid">
    <?php 
    if(mysqli_num_rows($ebooks_result) > 0):
        while($ebook = mysqli_fetch_assoc($ebooks_result)):
    ?>
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
    <?php 
        endwhile;
    else:
    ?>
        <div class="alert alert-info" style="width: 100%;">No e-books found matching your criteria.</div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
