<?php 
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../config/db.php';
$sql = "SELECT id, name , email, role FROM users";
$result= mysqli_query($conn, $sql);
?>
<h2>ALL users</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
    </tr>
    <?php while($row = mysqli_fetch_assoc($result)) : ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td><?= htmlspecialchars($row['email']); ?></td>
            <td><?= $row['role']; ?></td>
        </tr>
        <?php endwhile;?>

</table>
<br>
<a href = "admin.php">Back to admin page</a>
