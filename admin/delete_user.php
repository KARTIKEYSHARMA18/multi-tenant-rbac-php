<?php
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../config/db.php';
if(!isset($_GET['id'])|| !is_numeric($_GET['id'])){
    header("Location: list.php");
    exit;
}
$user_id = (int) $_GET['id'];
if ($user_id == $_SESSION['user_id']) {
    
    header("Location: list.php?error=selfdelete");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header("Location: list.php");
    exit;
}
$checkStmt = mysqli_prepare($conn, "SELECT role FROM users WHERE id = ?");
mysqli_stmt_bind_param($checkStmt, "i", $user_id);
mysqli_stmt_execute($checkStmt);
$result = mysqli_stmt_get_result($checkStmt);
$row =mysqli_fetch_assoc($result);

if (!$row) {
    header("Location: list.php?error=notfound");
    exit;
}
if ($row['role'] === 'admin') {
    header("Location: list.php?error=delete_admin");
    exit;
}

mysqli_stmt_close($checkStmt);


$stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($conn);
header("Location: list.php?success=deleted");
exit;
?>