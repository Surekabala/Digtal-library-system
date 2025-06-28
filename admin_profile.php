
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

include "includes/header.php";

// Get admin details
$admin_query = "SELECT * FROM users WHERE user_id = ?";
$admin_stmt = mysqli_prepare($conn, $admin_query);
mysqli_stmt_bind_param($admin_stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($admin_stmt);
$admin_result = mysqli_stmt_get_result($admin_stmt);
$admin = mysqli_fetch_assoc($admin_result);
?>

<h1>Admin Profile</h1>

<div class="form-container" style="max-width: 600px;">
    <h2>Account Information</h2>
    
    <div class="profile-details">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($admin['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($admin['phone']); ?></p>
        <p><strong>Account Type:</strong> Administrator</p>
        <p><strong>Registered Since:</strong> <?php echo date('M j, Y', strtotime($admin['registration_date'])); ?></p>
    </div>
    
    <div class="form-group">
        <a href="change_password.php" class="btn">Change Password</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<?php include "includes/footer.php"; ?>
