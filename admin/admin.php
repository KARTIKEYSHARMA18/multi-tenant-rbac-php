<?php 
require_once __DIR__ . '/../includes/admin_check.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
</head>
<body>
<h1>Welcome Admin</h1>
<p>This page is only for admin users.</p>
<a href = "list.php">View All users</a><br><br>
<a href="../dashboard.php">Back to Dashboard(user)</a>
<a href = "../auth/logout.php">Logout.</a>

</body>
</html>