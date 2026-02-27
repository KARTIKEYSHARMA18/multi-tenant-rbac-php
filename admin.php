<?php 
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='admin'){
    header("Location: dashboard.php");
    exit;

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
</head>
<body>

<h1>Welcome Admin</h1>
<p>This page is only for admin users.</p>

<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>