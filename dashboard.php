<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT name, email FROM users WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
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
    <div class=" container">
        <h1>welcome, <?= htmlspecialchars($user['name'])?></h1>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email'])?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($_SESSION['user_role']) ?></p>
        <br>
        <a href = "auth/logout.php">Logout</a>
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <br><br>
        <a href="admin/admin.php">Go to Admin Panel</a>
        <?php endif; ?>
    </div>
    
</body>
</html>