<?php
session_start();

require_once __DIR__ . '/../includes/permission.php';
require_once __DIR__ . '/../config/db.php';

/* 
   1. Permission Enforcement
*/

if (!hasPermission('view_users')) {
    die("Unauthorized Access");
}

if (!isset($_SESSION['tenant_id'])) {
    die("Tenant not found in session.");
}
$tenant_id = $_SESSION['tenant_id'];
/* 
   2. Fetch Users With Role Name
   (No old role column usage)
 */

$sql = "
    SELECT users.id, users.name, users.email, roles.name AS role_name
    FROM users
    JOIN roles ON users.role_id = roles.id
    WHERE users.tenant_id = ?
    ORDER BY users.id ASC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $tenant_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Database Error");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Users</title>
</head>
<body>

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
        if ($_GET['error'] === 'protected') echo "You cannot delete another admin (Not allowed)";
        if ($_GET['error'] === 'notfound') echo "User not found.";
        if($_GET['error'] === 'protected2') echo "You cannot edit another admin";
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

    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <tr>
            <td><?= (int) $row['id']; ?></td>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td><?= htmlspecialchars($row['email']); ?></td>
            <td><?= htmlspecialchars($row['role_name']); ?></td>
            <td>

                <?php if (hasPermission('edit_user')): ?>
                    <a href="edit_user.php?id=<?= (int) $row['id']; ?>">Edit</a>
                <?php endif; ?>

                <?php if (hasPermission('edit_user') && hasPermission('delete_user')): ?>
                    |
                <?php endif; ?>

                <?php if (hasPermission('delete_user')): ?>
                    <form action="delete_user.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= (int) $row['id']; ?>">
                        <button type="submit" onclick="return confirm('Are you sure?')">
                            Delete
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (!hasPermission('edit_user') && !hasPermission('delete_user')): ?>
                    <span style="color:gray;">No Actions</span>
                <?php endif; ?>

            </td>
        </tr>
    <?php endwhile; ?>

</table>

<br>
<a href="admin.php">Back to admin page</a>


</body>
</html>