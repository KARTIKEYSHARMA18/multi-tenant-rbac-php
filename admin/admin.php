<?php
session_start();

require_once __DIR__ . '/../includes/permission.php';
if (!hasPermission('view_users')) {
    die("Unauthorized Access");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
</head>
<body>

<h1>Admin Panel</h1>
<p>This page is accessible based on permissions.</p>

<a href="list.php">View All Users</a>
<br><br>

<a href="../dashboard.php">Back to Dashboard</a>
<br><br>

<a href="../auth/logout.php">Logout</a>

</body>
</html>