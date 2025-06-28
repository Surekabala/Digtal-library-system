
<?php 
require_once "config/config.php";

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Library System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo">Digital Library</div>
            <nav>
                <ul>
                    <?php if(isLoggedIn()): ?>
                        <?php if(isAdmin()): ?>
                            <!-- Admin navigation -->
                            <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Dashboard</a></li>
                            <li><a href="manage_books.php" class="<?php echo $current_page == 'manage_books.php' ? 'active' : ''; ?>">Manage Books</a></li>
                            <li><a href="manage_ebooks.php" class="<?php echo $current_page == 'manage_ebooks.php' ? 'active' : ''; ?>">Manage E-Books</a></li>
                            <li><a href="manage_users.php" class="<?php echo $current_page == 'manage_users.php' ? 'active' : ''; ?>">Manage Users</a></li>
                            <li><a href="manage_transactions.php" class="<?php echo $current_page == 'manage_transactions.php' ? 'active' : ''; ?>">Transactions</a></li>
                            <li class="dropdown">
                                <a href="#">Admin (<?php echo htmlspecialchars($_SESSION["username"]); ?>)</a>
                                <div class="dropdown-content">
                                    <a href="admin_profile.php">Profile</a>
                                    <a href="logout.php">Logout</a>
                                </div>
                            </li>
                        <?php else: ?>
                            <!-- User navigation -->
                            <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Dashboard</a></li>
                            <li><a href="books.php" class="<?php echo $current_page == 'books.php' ? 'active' : ''; ?>">Books</a></li>
                            <li><a href="ebooks.php" class="<?php echo $current_page == 'ebooks.php' ? 'active' : ''; ?>">E-Books</a></li>
                            <li><a href="my_collection.php" class="<?php echo $current_page == 'my_collection.php' ? 'active' : ''; ?>">My Collection</a></li>
                            <li><a href="transactions.php" class="<?php echo $current_page == 'transactions.php' ? 'active' : ''; ?>">Transactions</a></li>
                            <li class="dropdown">
                                <a href="#"><?php echo htmlspecialchars($_SESSION["username"]); ?></a>
                                <div class="dropdown-content">
                                    <a href="profile.php">Profile</a>
                                    <a href="logout.php">Logout</a>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <div class="container">
        <?php 
        // Display session messages if any
        if(isset($_SESSION['message'])) {
            echo '<div class="alert alert-success">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
        }
        if(isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>
