<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/permission.php';
require_once __DIR__ . '/../config/db.php';

requirePermission('delete_user');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: list.php");
    exit;
}



if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header("Location: list.php");
    exit;
}

$user_id = (int) $_POST['id'];
$tenant_id = $_SESSION['tenant_id'];

if ($user_id === (int) $_SESSION['user_id']) {
    header("Location: list.php?error=selfdelete");
    exit;
}



$checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE id = ? AND tenant_id = ?");
mysqli_stmt_bind_param($checkStmt, "ii", $user_id, $tenant_id);
mysqli_stmt_execute($checkStmt);
$result = mysqli_stmt_get_result($checkStmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($checkStmt);

if (!$row) {
    header("Location: list.php?error=notfound");
    exit;
}

if ((int)$user['role_id'] === 1) {
    header("Location: list.php?error=protected");
    exit;
}


$stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND tenant_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $user_id, $tenant_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

mysqli_close($conn);

header("Location: list.php?success=deleted");
exit;
?>