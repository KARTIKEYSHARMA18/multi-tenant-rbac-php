<?php

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/permission.php';

requirePermission('view_users');

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

<a href="../auth/logout.php">Logout</a><br><br>

<?php if (hasPermission('create_user')): ?>
    <a href="create_user.php">
        <button>create a new user?</button>
    </a>
<?php endif; ?>
</body>
</html>