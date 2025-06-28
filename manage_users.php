
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

include "includes/header.php";

// Get all users
$users_query = "SELECT * FROM users WHERE user_type = 'user' ORDER BY username";
$users_result = mysqli_query($conn, $users_query);

// Get overdue books
$overdue_query = "
    SELECT bb.*, u.username, b.title, b.author,
           DATEDIFF(CURRENT_DATE, bb.due_date) as days_overdue
    FROM borrowed_books bb
    JOIN users u ON bb.user_id = u.user_id
    JOIN books b ON bb.book_id = b.book_id
    WHERE bb.due_date < CURDATE() AND bb.return_date IS NULL
    ORDER BY bb.due_date ASC
";
$overdue_result = mysqli_query($conn, $overdue_query);
?>

<h1>Manage Users</h1>

<div class="tabs">
    <button class="tab active" id="users-tab">All Users</button>
    <button class="tab" id="overdue-tab">Overdue Books</button>
</div>

<div id="users-content">
    <h2>User Accounts</h2>
    
    <?php if(mysqli_num_rows($users_result) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($user['registration_date'])); ?></td>
                            <td>
                                <a href="view_user.php?id=<?php echo $user['user_id']; ?>" class="btn">View Details</a>
                                <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No users found.</div>
    <?php endif; ?>
</div>

<div id="overdue-content" style="display:none;">
    <h2>Overdue Books</h2>
    
    <?php if(mysqli_num_rows($overdue_result) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Days Overdue</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($overdue = mysqli_fetch_assoc($overdue_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($overdue['username']); ?></td>
                            <td><?php echo htmlspecialchars($overdue['title']); ?></td>
                            <td><?php echo htmlspecialchars($overdue['author']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($overdue['borrow_date'])); ?></td>
                            <td><?php echo date('M j, Y', strtotime($overdue['due_date'])); ?></td>
                            <td><?php echo $overdue['days_overdue']; ?></td>
                            <td><?php echo ucfirst($overdue['status']); ?></td>
                            <td>
                                <a href="notify_user.php?id=<?php echo $overdue['user_id']; ?>&borrow_id=<?php echo $overdue['borrow_id']; ?>" class="btn">Notify User</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No overdue books found.</div>
    <?php endif; ?>
</div>

<script>
    // Tab switching
    document.getElementById('users-tab').addEventListener('click', function() {
        document.getElementById('users-content').style.display = 'block';
        document.getElementById('overdue-content').style.display = 'none';
        
        document.getElementById('users-tab').classList.add('active');
        document.getElementById('overdue-tab').classList.remove('active');
    });
    
    document.getElementById('overdue-tab').addEventListener('click', function() {
        document.getElementById('users-content').style.display = 'none';
        document.getElementById('overdue-content').style.display = 'block';
        
        document.getElementById('users-tab').classList.remove('active');
        document.getElementById('overdue-tab').classList.add('active');
    });
</script>

<?php include "includes/footer.php"; ?>
