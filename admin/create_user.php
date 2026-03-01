<?php 
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/permission.php';
require_once __DIR__ . '/../config/db.php';

requirePermission('view_users');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $passwordRaw = $_POST['password'];
    $role_id = (int) $_POST['role_id'];
    $tenant_id = $_SESSION['tenant_id'];

    if (empty($name) || empty($email) || empty($passwordRaw)) {
        http_response_code(400);
        exit("All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        exit("Invalid email format.");
    }

    if (strlen($passwordRaw) < 6) {
         http_response_code(400);
        exit("Password must be at least 6 characters.");
    }

    $roleCheckSql = "SELECT id FROM roles WHERE id = ? AND name != 'Admin'";
    $stmtRole = mysqli_prepare($conn, $roleCheckSql);
    mysqli_stmt_bind_param($stmtRole, "i", $role_id);
    mysqli_stmt_execute($stmtRole);
    $resultRole = mysqli_stmt_get_result($stmtRole);

    if (mysqli_num_rows($resultRole) === 0) {
        mysqli_stmt_close($stmtRole);
        http_response_code(400);
        exit("Invalid role selection.");
    }
    mysqli_stmt_close($stmtRole);
    $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password, role_id, tenant_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        http_response_code(500);
        exit("Prepare failed.");
    }

    mysqli_stmt_bind_param($stmt, "sssii", $name, $email, $password, $role_id, $tenant_id);

    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        exit("Insert failed.");
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: list.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
</head>
<body>

<form method="POST">
    <input type="text" name="name" placeholder="Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>

    <select name="role_id" required>
        <?php
        $sql = "SELECT id, name FROM roles WHERE name != 'Admin'";
        $result = mysqli_query($conn, $sql);
        while ($role = mysqli_fetch_assoc($result)) {
            echo "<option value='{$role['id']}'>{$role['name']}</option>";
        }
        ?>
    </select>

    <button type="submit">Create User</button>
</form>

</body>
</html>