<?php
session_start();
require_once __DIR__ . '/config/db.php';
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $company_name = trim($_POST['company_name']);
    $admin_name   = trim($_POST['admin_name']);
    $email        = trim($_POST['email']);
    $password     = $_POST['password'];

    if (empty($company_name) || empty($admin_name) || empty($email) || empty($password)) {
        die("All fields are required.");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    mysqli_begin_transaction($conn);

    try {

        // 1️ Create Tenant
        $tenant_sql = "INSERT INTO tenants (name, created_at) VALUES (?, NOW())";
        $stmt = mysqli_prepare($conn, $tenant_sql);
        mysqli_stmt_bind_param($stmt, "s", $company_name);
        mysqli_stmt_execute($stmt);

        $tenant_id = mysqli_insert_id($conn);

        // 2️ Get Admin Role ID (Global)
        $role_sql = "SELECT id FROM roles WHERE name = 'Admin' LIMIT 1";
        $role_result = mysqli_query($conn, $role_sql);
        $role = mysqli_fetch_assoc($role_result);

        if (!$role) {
            throw new Exception("Admin role not found.");
        }

        $admin_role_id = $role['id'];

        // 3️ Create Admin User
        $user_sql = "INSERT INTO users (tenant_id, name, email, password, role_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $user_sql);
        mysqli_stmt_bind_param($stmt, "isssi", $tenant_id, $admin_name, $email, $hashed_password, $admin_role_id);
        mysqli_stmt_execute($stmt);

        $user_id = mysqli_insert_id($conn);

        mysqli_commit($conn);

        // 4️ Auto Login
        $_SESSION['user_id']   = $user_id;
        $_SESSION['tenant_id'] = $tenant_id;
        $_SESSION['role_id']   = $admin_role_id;
        // Fetch Permissions
        $sql = "
        SELECT p.name 
        FROM permissions p
        JOIN role_permissions rp ON p.id = rp.permission_id
        WHERE rp.role_id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $admin_role_id);
        mysqli_stmt_execute($stmt);
        $permResult = mysqli_stmt_get_result($stmt);

        $permissions = [];

        while ($row = mysqli_fetch_assoc($permResult)) {
            $permissions[] = $row['name'];
        }

        $_SESSION['permissions'] = $permissions;

        if (in_array('view_users', $_SESSION['permissions'])) {
            header("Location: admin/admin.php");
        } 
        else {
            header("Location: dashboard.php");
        }
        exit;

    } catch (Exception $e) {

        mysqli_rollback($conn);
        die("Registration failed: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Company Registration</title>
</head>
<body>

<h2>Register Company</h2>

<form method="POST">
    <input type="text" name="company_name" placeholder="Company Name" required><br><br>
    <input type="text" name="admin_name" placeholder="Admin Name" required><br><br>
    <input type="email" name="email" placeholder="Admin Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Register Company</button>
</form>

</body>
</html>