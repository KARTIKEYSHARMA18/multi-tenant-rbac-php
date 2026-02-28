<?php
session_start();

require_once __DIR__ . '/../includes/permission.php';
require_once __DIR__ . '/../config/db.php';



if (!hasPermission('delete_user')) {
    die("Unauthorized Access");
}



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: list.php");
    exit;
}



if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header("Location: list.php");
    exit;
}

$user_id = (int) $_POST['id'];


if ($user_id === (int) $_SESSION['user_id']) {
    header("Location: list.php?error=selfdelete");
    exit;
}



$checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE id = ?");
mysqli_stmt_bind_param($checkStmt, "i", $user_id);
mysqli_stmt_execute($checkStmt);
$result = mysqli_stmt_get_result($checkStmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($checkStmt);

if (!$row) {
    header("Location: list.php?error=notfound");
    exit;
}

$target_user_id = $user_id;

$stmt = mysqli_prepare($conn, "SELECT role_id FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    header("Location: list.php?error=notfound");
    exit;
}

$target_role_id = (int) $user['role_id'];
$current_role_id = (int) $_SESSION['role_id'];

if ($target_role_id === 1) {
    header("Location: list.php?error=protected");
    exit;
}
$stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);



header("Location: list.php?success=deleted");
exit;
?>