<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/permission.php';
require_once __DIR__ . '/config/db.php';

$user_id = $_SESSION['user_id'];
$tenant_id = $_SESSION['tenant_id'];
/* -------------------------
   Fetch User + Role Name
-------------------------- */

$stmt = mysqli_prepare(
    $conn,
    "SELECT users.name, users.email, roles.name AS role_name
     FROM users
     JOIN roles ON users.role_id = roles.id
     WHERE users.id = ? AND users.tenant_id = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    session_destroy();
    header("Location: auth/signin.php");
    exit;
}

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>

<div class="container">
    <h1>Welcome, <?= htmlspecialchars($user['name']) ?></h1>
    
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($user['role_name']) ?></p>

    <br>
    <a href="auth/logout.php">Logout</a>

    <?php if (hasPermission('view_users')): ?>
        <br><br>
        <a href="admin/list.php">Go to Admin Panel</a>
    <?php endif; ?>

</div>

</body>
</html>