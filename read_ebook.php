
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();

// Check if book_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "E-book ID is required.";
    header("location: ebooks.php");
    exit;
}

$ebook_id = clean_input($_GET['id']);

// Get e-book details
$ebook_query = "SELECT * FROM ebooks WHERE ebook_id = ?";
$ebook_stmt = mysqli_prepare($conn, $ebook_query);
mysqli_stmt_bind_param($ebook_stmt, "i", $ebook_id);
mysqli_stmt_execute($ebook_stmt);
$ebook_result = mysqli_stmt_get_result($ebook_stmt);

if (mysqli_num_rows($ebook_result) == 0) {
    $_SESSION['error'] = "E-book not found.";
    header("location: ebooks.php");
    exit;
}

$ebook = mysqli_fetch_assoc($ebook_result);

// Record e-book access
$check_query = "SELECT * FROM user_ebooks WHERE user_id = ? AND ebook_id = ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "ii", $_SESSION['user_id'], $ebook_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) == 0) {
    // First time access, insert record
    $insert_query = "INSERT INTO user_ebooks (user_id, ebook_id) VALUES (?, ?)";
    $insert_stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, "ii", $_SESSION['user_id'], $ebook_id);
    mysqli_stmt_execute($insert_stmt);
} else {
    // Update access date
    $update_query = "UPDATE user_ebooks SET access_date = CURRENT_TIMESTAMP WHERE user_id = ? AND ebook_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ii", $_SESSION['user_id'], $ebook_id);
    mysqli_stmt_execute($update_stmt);
}

// Check if book is in favorites
$favorite_query = "SELECT * FROM favorites WHERE user_id = ? AND ebook_id = ?";
$favorite_stmt = mysqli_prepare($conn, $favorite_query);
mysqli_stmt_bind_param($favorite_stmt, "ii", $_SESSION['user_id'], $ebook_id);
mysqli_stmt_execute($favorite_stmt);
$favorite_result = mysqli_stmt_get_result($favorite_stmt);
$is_favorite = mysqli_num_rows($favorite_result) > 0;

include "includes/header.php";
?>

<h1>E-Book Reader</h1>

<div class="reader-container">
    <div class="reader-header">
        <h2><?php echo htmlspecialchars($ebook['title']); ?></h2>
        <p>by <?php echo htmlspecialchars($ebook['author']); ?></p>
        
        <?php if($is_favorite): ?>
            <a href="remove_favorite.php?type=ebook&id=<?php echo $ebook_id; ?>" class="btn btn-secondary">Remove from Favorites</a>
        <?php else: ?>
            <a href="add_favorite.php?type=ebook&id=<?php echo $ebook_id; ?>" class="btn">Add to Favorites</a>
        <?php endif; ?>
        
        <a href="download_ebook.php?id=<?php echo $ebook_id; ?>" class="btn">Download PDF</a>
    </div>
    
    <div class="reader-content">
        <!-- In a real implementation, this would be an embedded PDF or e-reader -->
        <div style="background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 20px;">
            <h3>E-Book Preview</h3>
            <p>This is a placeholder for the e-book content. In a real implementation, this would be replaced with an actual e-book reader or embedded PDF.</p>
            <p><strong>Title:</strong> <?php echo htmlspecialchars($ebook['title']); ?></p>
            <p><strong>Author:</strong> <?php echo htmlspecialchars($ebook['author']); ?></p>
            <p><strong>Genre:</strong> <?php echo htmlspecialchars($ebook['genre']); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($ebook['description'] ?? 'No description available.'); ?></p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, nisl vel ultricies lacinia, nisl nisl aliquam nisl, eu aliquam nisl nisl eu nisl. Sed euismod, nisl vel ultricies lacinia, nisl nisl aliquam nisl, eu aliquam nisl nisl eu nisl.</p>
            <p>Sed euismod, nisl vel ultricies lacinia, nisl nisl aliquam nisl, eu aliquam nisl nisl eu nisl. Sed euismod, nisl vel ultricies lacinia, nisl nisl aliquam nisl, eu aliquam nisl nisl eu nisl.</p>
            <!-- Add more placeholder content as needed -->
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
