<?php 
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../config/db.php';
$sql = "SELECT id, name , email, role FROM users";
$result= mysqli_query($conn, $sql);
?>
<h2>All Users</h2>

<!-- Flash Messages -->
<?php if (isset($_GET['success'])): ?>
    <p style="color:green;">
        <?php 
        if ($_GET['success'] === 'deleted') echo "User deleted successfully.";
        if ($_GET['success'] === 'updated') echo "User updated successfully.";
        ?>
    </p>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <p style="color:red;">
        <?php
        if ($_GET['error'] === 'selfdelete') echo "You cannot delete yourself.";
        if ($_GET['error'] === 'delete_admin') echo "You cannot delete another admin.";
        if ($_GET['error'] === 'cannot_edit_admin') echo "You cannot edit another admin.";
        if ($_GET['error'] === 'notfound') echo "User not found.";
        ?>
    </p>
<?php endif; ?>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Action</th>
    </tr>
    <?php while($row = mysqli_fetch_assoc($result)) : ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td><?= htmlspecialchars($row['email']); ?></td>
            <td><?= htmlspecialchars($row['role']); ?></td>
            <td>
                <?php if ($row['role'] !== 'admin'): ?>
                    
                    <!-- Edit Button -->
                    <a href="edit_user.php?id=<?= $row['id']; ?>">
                        Edit
                    </a>

                    |

                    <!-- Delete Button -->
                    <form action="delete_user.php?id=<?= $row['id']; ?>" method="post" style="display:inline;">
                        <button type="submit" onclick="return confirm('Are you sure?')">
                        Delete
                        </button>
                    </form>


                <?php else: ?>
                    <span style="color:gray;">Protected</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile;?>

</table>
<br>
<a href = "admin.php">Back to admin page</a>
