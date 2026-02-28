<?php
session_start();

require_once __DIR__ . '/../includes/permission.php';
require_once __DIR__ . '/../config/db.php';

/* -------------------------
   1. Permission Enforcement
-------------------------- */

if (!hasPermission('delete_user')) {
    die("Unauthorized Access");
}

/* -------------------------
   2. Allow POST Only
-------------------------- */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: list.php");
    exit;
}

/* -------------------------
   3. Validate Input
-------------------------- */

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header("Location: list.php");
    exit;
}

$user_id = (int) $_POST['id'];

/* -------------------------
   4. Prevent Self Delete
-------------------------- */

if ($user_id === (int) $_SESSION['user_id']) {
    header("Location: list.php?error=selfdelete");
    exit;
}

/* -------------------------
   5. Check If User Exists
-------------------------- */

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

/* -------------------------
   6. Perform Delete
-------------------------- */

$stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

/* -------------------------
   7. Redirect
-------------------------- */

header("Location: list.php?success=deleted");
exit;
?>