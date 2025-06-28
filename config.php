
<?php
session_start();

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'digital_library');

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

// Function to redirect to login page if not logged in
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("location: login.php");
        exit;
    }
}

// Function to redirect to home if already logged in
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("location: index.php");
        exit;
    }
}

// Function to redirect to user home if not admin
function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header("location: index.php");
        exit;
    }
}

// Clean input data
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}
?>
