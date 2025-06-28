
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();

include "includes/header.php";

// Get user details
$user_query = "SELECT * FROM users WHERE user_id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);

// Get borrowed books count
$borrowed_query = "SELECT COUNT(*) as count FROM borrowed_books WHERE user_id = ? AND return_date IS NULL";
$borrowed_stmt = mysqli_prepare($conn, $borrowed_query);
mysqli_stmt_bind_param($borrowed_stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($borrowed_stmt);
$borrowed_result = mysqli_stmt_get_result($borrowed_stmt);
$borrowed = mysqli_fetch_assoc($borrowed_result);

// Get total e-books accessed
$ebooks_query = "SELECT COUNT(*) as count FROM user_ebooks WHERE user_id = ?";
$ebooks_stmt = mysqli_prepare($conn, $ebooks_query);
mysqli_stmt_bind_param($ebooks_stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($ebooks_stmt);
$ebooks_result = mysqli_stmt_get_result($ebooks_stmt);
$ebooks = mysqli_fetch_assoc($ebooks_result);
?>

<h1>My Profile</h1>

<div class="form-container" style="max-width: 600px;">
    <h2>Account Information</h2>
    
    <div class="profile-details">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
        <p><strong>Registered Since:</strong> <?php echo date('M j, Y', strtotime($user['registration_date'])); ?></p>
    </div>
    
    <h3>Library Activity</h3>
    <div class="profile-details">
        <p><strong>Currently Borrowed Books:</strong> <?php echo $borrowed['count']; ?></p>
        <p><strong>Total E-Books Accessed:</strong> <?php echo $ebooks['count']; ?></p>
    </div>
    
    <div class="form-group">
        <a href="change_password.php" class="btn">Change Password</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<?php include "includes/footer.php"; ?>
